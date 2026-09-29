<?php

declare(strict_types=1);

require_once __DIR__ . '/Notification_model.php';

/**
 * Repost Model
 * Manages user reposts / retweets for stories and notes.
 */
class Repost_model
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Toggle repost status for a post by a user
     * Updates repost_count column on posts table and sends notification.
     *
     * @param int $userId
     * @param int $postId
     * @return array
     */
    public function toggleRepost(int $userId, int $postId): array
    {
        // 1. Verify post exists
        $this->db->query("SELECT id, user_id, title FROM posts WHERE id = :post_id LIMIT 1");
        $this->db->bind(':post_id', $postId);
        $post = $this->db->single();

        if (!$post) {
            return [
                'status' => 'error',
                'message' => 'Post not found',
                'is_reposted' => false,
                'repost_count' => 0,
                'count' => 0
            ];
        }

        // 2. Check if already reposted
        $this->db->query("SELECT id FROM reposts WHERE user_id = :user_id AND post_id = :post_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        $existing = $this->db->single();

        if ($existing) {
            // Delete repost
            $this->db->query("DELETE FROM reposts WHERE user_id = :user_id AND post_id = :post_id");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $isReposted = false;
            $action = 'unreposted';
        } else {
            // Insert repost
            $this->db->query("INSERT INTO reposts (user_id, post_id, created_at) VALUES (:user_id, :post_id, CURRENT_TIMESTAMP)");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $isReposted = true;
            $action = 'reposted';

            // Send notification to post author if not self-reposting
            $postAuthorId = (int)($post['user_id'] ?? 0);
            if ($postAuthorId > 0 && $postAuthorId !== $userId) {
                $notificationModel = new Notification_model();
                $notificationModel->addNotification($postAuthorId, $userId, 'repost', $postId);
            }
        }

        // 3. Fetch latest repost count from reposts table
        $this->db->query("SELECT COUNT(*) AS count FROM reposts WHERE post_id = :post_id");
        $this->db->bind(':post_id', $postId);
        $countRow = $this->db->single();
        $count = (int)($countRow['count'] ?? 0);

        // 4. Sync posts table repost_count column
        $this->db->query("UPDATE posts SET repost_count = :count WHERE id = :post_id");
        $this->db->bind(':count', $count);
        $this->db->bind(':post_id', $postId);
        $this->db->execute();

        return [
            'status' => 'success',
            'action' => $action,
            'is_reposted' => $isReposted,
            'repost_count' => $count,
            'count' => $count
        ];
    }

    /**
     * Check if a user has reposted a post
     *
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public function isReposted(int $userId, int $postId): bool
    {
        $this->db->query("SELECT id FROM reposts WHERE user_id = :user_id AND post_id = :post_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        return (bool)$this->db->single();
    }

    /**
     * Get all post IDs reposted by a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserRepostedPostIds(int $userId): array
    {
        $this->db->query("SELECT post_id FROM reposts WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $rows = $this->db->resultSet();
        return array_map('intval', array_column($rows, 'post_id'));
    }

    /**
     * Get repost count for a specific post
     *
     * @param int $postId
     * @return int
     */
    public function getRepostCount(int $postId): int
    {
        $this->db->query("SELECT COUNT(*) AS count FROM reposts WHERE post_id = :post_id");
        $this->db->bind(':post_id', $postId);
        $row = $this->db->single();
        return (int)($row['count'] ?? 0);
    }

    /**
     * Fetch all posts reposted by a specific user, ordered by repost date newest first
     *
     * @param int $userId
     * @param int|null $currentUserId
     * @return array
     */
    public function getRepostedPostsByUser(int $userId, ?int $currentUserId = null): array
    {
        $isRepostedSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM reposts r2 WHERE r2.post_id = posts.id AND r2.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT 
                    posts.*,
                    author.username,
                    author.name,
                    author.profile_picture,
                    author.email,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    {$isRepostedSelect}
                    reposter.id AS repost_user_id,
                    reposter.username AS repost_username,
                    reposter.name AS repost_name,
                    reposts.created_at AS reposted_at
                  FROM reposts
                  INNER JOIN users reposter ON reposts.user_id = reposter.id
                  INNER JOIN posts ON reposts.post_id = posts.id
                  INNER JOIN users author ON posts.user_id = author.id
                  WHERE reposts.user_id = :user_id AND posts.status = 'published'
                  ORDER BY reposts.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
}
