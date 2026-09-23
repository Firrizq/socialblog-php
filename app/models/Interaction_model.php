<?php

declare(strict_types=1);

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

        return [
            'status' => $status
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
        return array_column($rows, 'post_id');
    }
}
