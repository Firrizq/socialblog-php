<?php

declare(strict_types=1);

/**
 * History Model
 * Manages user reading history, bookmarked stories, and liked posts library.
 */
class History_model
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Fetch user reading history ordered by most recently read
     *
     * @param int $userId
     * @return array
     */
    public function getReadingHistory(int $userId): array
    {
        $query = "SELECT 
                    reading_history.progress,
                    reading_history.last_read_at,
                    reading_history.last_read_at AS history_date,
                    posts.*,
                    users.username,
                    users.name,
                    users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count
                  FROM reading_history
                  INNER JOIN posts ON reading_history.post_id = posts.id
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE reading_history.user_id = :user_id 
                    AND posts.status = 'published'
                  ORDER BY reading_history.last_read_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Fetch all posts bookmarked by user
     *
     * @param int $userId
     * @return array
     */
    public function getBookmarkedPosts(int $userId): array
    {
        $query = "SELECT 
                    bookmarks.created_at AS bookmarked_at,
                    posts.*,
                    users.username,
                    users.name,
                    users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count
                  FROM bookmarks
                  INNER JOIN posts ON bookmarks.post_id = posts.id
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE bookmarks.user_id = :user_id AND posts.status = 'published'
                  ORDER BY bookmarks.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Fetch all posts liked by user
     *
     * @param int $userId
     * @return array
     */
    public function getLikedPosts(int $userId): array
    {
        $query = "SELECT 
                    likes.created_at AS liked_at,
                    posts.*,
                    users.username,
                    users.name,
                    users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count
                  FROM likes
                  INNER JOIN posts ON likes.post_id = posts.id
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE likes.user_id = :user_id AND posts.status = 'published'
                  ORDER BY likes.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Record or update user reading progress on a post
     *
     * @param int $userId
     * @param int $postId
     * @param int $progress 0-100 percentage
     * @return bool
     */
    public function recordProgress(int $userId, int $postId, int $progress = 10): bool
    {
        $progress = max(0, min(100, $progress));
        $query = "INSERT INTO reading_history (user_id, post_id, progress, last_read_at) 
                  VALUES (:user_id, :post_id, :progress, CURRENT_TIMESTAMP) 
                  ON DUPLICATE KEY UPDATE 
                    progress = GREATEST(progress, VALUES(progress)), 
                    last_read_at = CURRENT_TIMESTAMP";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':progress', $progress);
        return $this->db->execute();
    }

    /**
     * Get user reading progress on a specific post
     *
     * @param int $userId
     * @param int $postId
     * @return int 0-100
     */
    public function getProgress(int $userId, int $postId): int
    {
        $query = "SELECT progress FROM reading_history WHERE user_id = :user_id AND post_id = :post_id LIMIT 1";
        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':post_id', $postId);
        $row = $this->db->single();
        return (int)($row['progress'] ?? 0);
    }
}
