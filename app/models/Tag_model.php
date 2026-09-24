<?php

declare(strict_types=1);

/**
 * Tag Model
 * Handles hashtag extraction, storage, and tag-filtered post feeds.
 */
class Tag_model
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Parse content for hashtags and associate them with a post
     *
     * @param int $postId
     * @param string $content
     * @return void
     */
    public function processTags(int $postId, string $content): void
    {
        preg_match_all('/(?<!&)#([a-zA-Z_][a-zA-Z0-9_]*)/', $content, $matches);
        if (empty($matches[1])) {
            return;
        }

        $uniqueTags = array_unique($matches[1]);

        foreach ($uniqueTags as $tag) {
            $tagName = trim($tag);
            if (empty($tagName)) {
                continue;
            }

            // Check if tag exists
            $this->db->query("SELECT id FROM tags WHERE name = :name LIMIT 1");
            $this->db->bind(':name', $tagName);
            $row = $this->db->single();

            if ($row) {
                $tagId = (int)$row['id'];
            } else {
                $this->db->query("INSERT INTO tags (name) VALUES (:name)");
                $this->db->bind(':name', $tagName);
                $this->db->execute();
                $tagId = (int)$this->db->lastInsertId();
            }

            if ($tagId > 0) {
                $this->db->query("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)");
                $this->db->bind(':post_id', $postId);
                $this->db->bind(':tag_id', $tagId);
                $this->db->execute();
            }
        }
    }

    /**
     * Retrieve published posts associated with a specific tag
     *
     * @param string $tagName
     * @return array
     */
    public function getPostsByTag(string $tagName): array
    {
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture 
                  FROM posts 
                  INNER JOIN users ON posts.user_id = users.id 
                  INNER JOIN post_tags ON posts.id = post_tags.post_id 
                  INNER JOIN tags ON post_tags.tag_id = tags.id 
                  WHERE tags.name = :name AND posts.status = 'published' 
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':name', $tagName);
        return $this->db->resultSet();
    }
}
