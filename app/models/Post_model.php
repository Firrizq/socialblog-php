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
                    posts.id,
                    posts.user_id,
                    posts.title,
                    posts.content,
                    posts.created_at,
                    users.username,
                    users.email
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
}
