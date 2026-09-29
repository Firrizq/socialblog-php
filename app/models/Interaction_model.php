<?php

declare(strict_types=1);

require_once __DIR__ . '/Notification_model.php';

/**
 * Interaction Model
 * Handles database operations for Likes, Bookmarks, and Follow relationships.
 */
class Interaction_model
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Toggle like status for a post by a user
     * Updates like count on the posts table accordingly.
     *
     * @param int $userId
     * @param int $postId
     * @return array ['status' => 'liked'|'unliked', 'count' => int]
     */
    public function toggleLike(int $userId, int $postId): array
    {
        // 1. Check if like already exists
        $this->db->query("SELECT id FROM likes WHERE user_id = :user_id AND post_id = :post_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        $existing = $this->db->single();

        if ($existing) {
            // Delete like
            $this->db->query("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $status = 'unliked';
        } else {
            // Insert like
            $this->db->query("INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $status = 'liked';

            // Fetch post owner and insert notification
            $this->db->query("SELECT user_id FROM posts WHERE id = :post_id LIMIT 1");
            $this->db->bind(':post_id', $postId);
            $postOwner = $this->db->single();
            if ($postOwner && !empty($postOwner['user_id'])) {
                $notificationModel = new Notification_model();
                $notificationModel->addNotification((int)$postOwner['user_id'], $userId, 'like_post', $postId);
            }
        }

        // 2. Fetch updated like count
        $this->db->query("SELECT like_count FROM posts WHERE id = :post_id LIMIT 1");
        $this->db->bind(':post_id', $postId);
        $post = $this->db->single();
        $count = (int)($post['like_count'] ?? 0);

        return [
            'status' => $status,
            'count' => $count
        ];
    }

    /**
     * Toggle bookmark status for a post by a user
     *
     * @param int $userId
     * @param int $postId
     * @return array ['status' => 'bookmarked'|'unbookmarked']
     */
    public function toggleBookmark(int $userId, int $postId): array
    {
        // 1. Verify post exists
        $this->db->query("SELECT id FROM posts WHERE id = :post_id LIMIT 1");
        $this->db->bind(':post_id', $postId);
        $post = $this->db->single();

        if (!$post) {
            return [
                'status' => 'error',
                'message' => 'Post not found'
            ];
        }

        $this->db->query("SELECT id FROM bookmarks WHERE user_id = :user_id AND post_id = :post_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        $existing = $this->db->single();

        if ($existing) {
            $this->db->query("DELETE FROM bookmarks WHERE user_id = :user_id AND post_id = :post_id");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $status = 'unbookmarked';
        } else {
            $this->db->query("INSERT INTO bookmarks (user_id, post_id) VALUES (:user_id, :post_id)");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $status = 'bookmarked';
        }

        // 2. Fetch updated bookmark count
        $this->db->query("SELECT COUNT(*) AS count FROM bookmarks WHERE post_id = :post_id");
        $this->db->bind(':post_id', $postId);
        $countRow = $this->db->single();
        $count = (int)($countRow['count'] ?? 0);

        return [
            'status' => $status,
            'count' => $count
        ];
    }

    public function toggleFollow(int $followerId, int $followedId): array
    {
        if ($followerId === $followedId) {
            return ['status' => 'error', 'message' => 'You cannot follow yourself', 'follower_count' => 0];
        }

        // Check against the NEW 'followings' table
        $this->db->query("SELECT id FROM followings WHERE user_id = :follower_id AND target_id = :followed_id LIMIT 1");
        $this->db->bind(':follower_id', $followerId);
        $this->db->bind(':followed_id', $followedId);
        $existing = $this->db->single();

        if ($existing) {
            // UNFOLLOW ACTION
            $this->db->query("DELETE FROM followings WHERE user_id = :follower_id AND target_id = :followed_id");
            $this->db->bind(':follower_id', $followerId);
            $this->db->bind(':followed_id', $followedId);
            $this->db->execute();

            $this->db->query("DELETE FROM followers WHERE user_id = :followed_id AND target_id = :follower_id");
            $this->db->bind(':followed_id', $followedId);
            $this->db->bind(':follower_id', $followerId);
            $this->db->execute();

            // Re-introduce manual updates since the new tables lack Triggers
            $this->db->query("UPDATE users SET follower_count = GREATEST(follower_count - 1, 0) WHERE id = :followed_id");
            $this->db->bind(':followed_id', $followedId);
            $this->db->execute();

            $this->db->query("UPDATE users SET following_count = GREATEST(following_count - 1, 0) WHERE id = :follower_id");
            $this->db->bind(':follower_id', $followerId);
            $this->db->execute();

            $status = 'unfollowed';
        } else {
            // FOLLOW ACTION
            $this->db->query("INSERT INTO followings (user_id, target_id) VALUES (:follower_id, :followed_id)");
            $this->db->bind(':follower_id', $followerId);
            $this->db->bind(':followed_id', $followedId);
            $this->db->execute();

            $this->db->query("INSERT INTO followers (user_id, target_id) VALUES (:followed_id, :follower_id)");
            $this->db->bind(':followed_id', $followedId);
            $this->db->bind(':follower_id', $followerId);
            $this->db->execute();

            // Re-introduce manual updates
            $this->db->query("UPDATE users SET follower_count = follower_count + 1 WHERE id = :followed_id");
            $this->db->bind(':followed_id', $followedId);
            $this->db->execute();

            $this->db->query("UPDATE users SET following_count = following_count + 1 WHERE id = :follower_id");
            $this->db->bind(':follower_id', $followerId);
            $this->db->execute();

            $status = 'followed';

            // Insert follow notification
            $notificationModel = new Notification_model();
            $notificationModel->addNotification($followedId, $followerId, 'follow');
        }

        $this->db->query("SELECT follower_count FROM users WHERE id = :followed_id LIMIT 1");
        $this->db->bind(':followed_id', $followedId);
        $user = $this->db->single();
        $followerCount = (int)($user['follower_count'] ?? 0);

        return [
            'status' => $status,
            'follower_count' => $followerCount
        ];
    }

    /**
     * Check if a user has liked a specific post
     *
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public function isLiked(int $userId, int $postId): bool
    {
        $this->db->query("SELECT id FROM likes WHERE user_id = :user_id AND post_id = :post_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        return (bool)$this->db->single();
    }

    /**
     * Check if a user has bookmarked a specific post
     *
     * @param int $userId
     * @param int $postId
     * @return bool
     */
    public function isBookmarked(int $userId, int $postId): bool
    {
        $this->db->query("SELECT id FROM bookmarks WHERE user_id = :user_id AND post_id = :post_id LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        return (bool)$this->db->single();
    }

    public function isFollowing(int $followerId, int $followedId): bool
    {
        $this->db->query("SELECT id FROM followings WHERE user_id = :follower_id AND target_id = :followed_id LIMIT 1");
        $this->db->bind(':follower_id', $followerId);
        $this->db->bind(':followed_id', $followedId);
        return (bool)$this->db->single();
    }

    /**
     * Get all post IDs liked by a user
     *
     * @param int $userId
     * @return array List of post IDs
     */
    public function getUserLikedPostIds(int $userId): array
    {
        $this->db->query("SELECT post_id FROM likes WHERE user_id = :user_id AND post_id IS NOT NULL");
        $this->db->bind(':user_id', $userId);
        $rows = $this->db->resultSet();
        return array_column($rows, 'post_id');
    }

    /**
     * Get all post IDs bookmarked by a user
     *
     * @param int $userId
     * @return array List of post IDs
     */
    public function getUserBookmarkedPostIds(int $userId): array
    {
        $this->db->query("SELECT post_id FROM bookmarks WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $rows = $this->db->resultSet();
        return array_map('intval', array_column($rows, 'post_id'));
    }

    /**
     * Toggle repost status for a post by a user
     *
     * @param int $userId
     * @param int $postId
     * @return array
     */
    public function toggleRepost(int $userId, int $postId): array
    {
        // 1. Verify post exists
        $this->db->query("SELECT id, user_id FROM posts WHERE id = :post_id LIMIT 1");
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
            $this->db->query("DELETE FROM reposts WHERE user_id = :user_id AND post_id = :post_id");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $isReposted = false;
            $action = 'unreposted';
        } else {
            $this->db->query("INSERT INTO reposts (user_id, post_id, created_at) VALUES (:user_id, :post_id, CURRENT_TIMESTAMP)");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            $isReposted = true;
            $action = 'reposted';

            // Send notification to author
            $authorId = (int)($post['user_id'] ?? 0);
            if ($authorId > 0 && $authorId !== $userId) {
                $notificationModel = new Notification_model();
                $notificationModel->addNotification($authorId, $userId, 'repost', $postId);
            }
        }

        // 3. Fetch updated repost count
        $this->db->query("SELECT COUNT(*) AS count FROM reposts WHERE post_id = :post_id");
        $this->db->bind(':post_id', $postId);
        $countRow = $this->db->single();
        $count = (int)($countRow['count'] ?? 0);

        // Update posts table column
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
}
