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
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Create a new post in the database
     *
     * @param array $data ['user_id', 'title', 'content']
     * @return bool
     */
    public function createPost(array $data): bool
    {
        $query = "INSERT INTO {$this->table} (user_id, title, content) 
                  VALUES (:user_id, :title, :content)";

        $this->db->query($query);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);

        return $this->db->execute();
    }

    /**
     * Fetch all posts for a specific user, ordered by newest first
     *
     * @param int $user_id
     * @return array
     */
    public function getPostsByUser(int $user_id): array
    {
        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.email,
                    users.profile_picture
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.user_id = :user_id
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
     * @return array
     */
    public function getPopularPosts(): array
    {
        $query = "SELECT posts.id, posts.title, posts.read_time_minutes, users.username 
                  FROM {$this->table} 
                  INNER JOIN users ON posts.user_id = users.id 
                  WHERE posts.status = 'published' AND posts.title IS NOT NULL AND posts.title != '' 
                  ORDER BY posts.like_count DESC, posts.created_at DESC 
                  LIMIT 4";

        $this->db->query($query);
        return $this->db->resultSet();
    }
}


