<?php

declare(strict_types=1);

/**
 * Post Model
 * Handles database operations for blog/feed posts.
 */
class Post_model
{
    private Database $db;
    private string $table = 'posts';

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Fetch all posts joined with author's username from users table, ordered by newest first
     *
     * @return array
     */
    public function getFeedPosts(): array
    {
        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.email,
                    users.profile_picture
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.status = 'published'
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Create a new post in the database
     *
     * @param array $data ['user_id', 'title', 'content', 'status']
     * @return int|false
     */
    public function createPost(array $data): int|false
    {
        $status = $data['status'] ?? 'published';
        $coverImage = $data['cover_image'] ?? null;
        $postType = $data['post_type'] ?? 'story';
        $wordCount = str_word_count(strip_tags($data['content'] ?? ''));
        $readTime = max(1, (int)ceil($wordCount / 200));

        $query = "INSERT INTO {$this->table} (user_id, post_type, title, content, cover_image, status, read_time_minutes) 
                  VALUES (:user_id, :post_type, :title, :content, :cover_image, :status, :read_time_minutes)";

        $this->db->query($query);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':post_type', $postType);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':cover_image', $coverImage);
        $this->db->bind(':status', $status);
        $this->db->bind(':read_time_minutes', $readTime);

        if ($this->db->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Fetch all posts for a specific user, ordered by newest first
     *
     * @param int $user_id
     * @param bool $isOwner
     * @return array
     */
    public function getPostsByUser(int $user_id, bool $isOwner = false): array
    {
        $statusCondition = $isOwner ? "" : " AND posts.status = 'published'";
        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.email,
                    users.profile_picture
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.user_id = :user_id {$statusCondition}
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    /**
     * Fetch a single post by its ID, joined with author information
     *
     * @param int $id
     * @return array|false
     */
    public function getPostById(int $id): array|false
    {
        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.profile_picture,
                    users.email
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.id = :id
                  LIMIT 1";

        $this->db->query($query);
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        return $row ?: false;
    }

    /**
     * Fetch all posts bookmarked by a specific user, ordered by bookmark date newest first
     *
     * @param int $userId
     * @return array
     */
    public function getBookmarkedPosts(int $userId): array
    {
        $query = "SELECT posts.*, users.username, users.profile_picture, users.email, bookmarks.created_at AS bookmarked_at 
                  FROM posts 
                  INNER JOIN bookmarks ON posts.id = bookmarks.post_id 
                  INNER JOIN users ON posts.user_id = users.id 
                  WHERE bookmarks.user_id = :user_id 
                  ORDER BY bookmarks.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Fetch popular published stories ordered by like count and creation date
     *
     * @param int $limit
     * @return array
     */
    public function getPopularPosts(int $limit = 10): array
    {
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture 
                  FROM {$this->table} 
                  INNER JOIN users ON posts.user_id = users.id 
                  WHERE posts.status = 'published' AND posts.title IS NOT NULL AND posts.title != '' 
                  ORDER BY posts.like_count DESC, posts.created_at DESC 
                  LIMIT " . (int)$limit;

        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Fetch trending published posts for Explore feed
     *
     * @return array
     */
    public function getTrendingPosts(): array
    {
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture, users.email 
                  FROM {$this->table} 
                  INNER JOIN users ON posts.user_id = users.id 
                  WHERE posts.status = 'published' 
                  ORDER BY posts.like_count DESC, posts.created_at DESC";

        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Search published posts by keyword matching title, content, author username, or display name
     *
     * @param string $keyword
     * @return array
     */
    public function searchPosts(string $keyword): array
    {
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture 
                  FROM {$this->table} 
                  INNER JOIN users ON posts.user_id = users.id 
                  WHERE (posts.title LIKE :keyword OR posts.content LIKE :keyword OR users.username LIKE :keyword OR users.name LIKE :keyword) 
                  AND posts.status = 'published' 
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':keyword', "%{$keyword}%");
        return $this->db->resultSet();
    }

    /**
     * Delete post owned by a specific user
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function deletePost(int $postId, int $userId): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $this->db->query($query);
        $this->db->bind(':id', $postId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Fetch feed posts from authors the user follows
     *
     * @param int $userId
     * @return array
     */
    public function getFollowingFeedPosts(int $userId): array
    {
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture, users.email 
                  FROM {$this->table} 
                  INNER JOIN users ON posts.user_id = users.id 
                  INNER JOIN followings ON users.id = followings.target_id 
                  WHERE followings.user_id = :user_id AND posts.status = 'published' 
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Update an existing post owned by a specific user
     *
     * @param int $postId
     * @param int $userId
     * @param array $data ['title', 'content', 'status']
     * @return bool
     */
    public function updatePost(int $postId, int $userId, array $data): bool
    {
        $coverImage = $data['cover_image'] ?? null;
        $wordCount = str_word_count(strip_tags($data['content'] ?? ''));
        $readTime = max(1, (int)ceil($wordCount / 200));

        $query = "UPDATE {$this->table} SET title = :title, content = :content, cover_image = :cover_image, status = :status, read_time_minutes = :read_time_minutes WHERE id = :id AND user_id = :user_id";
        $this->db->query($query);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':cover_image', $coverImage);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':read_time_minutes', $readTime);
        $this->db->bind(':id', $postId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }
}


