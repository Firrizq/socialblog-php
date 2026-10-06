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
        $extractedTags = !empty($matches[1]) ? array_unique($matches[1]) : [];
        $currentTagIds = [];

        foreach ($extractedTags as $tag) {
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
                $currentTagIds[] = $tagId;
            }
        }

        // Delete associations for tags no longer present in this post
        if (!empty($currentTagIds)) {
            $inClause = implode(',', array_map('intval', $currentTagIds));
            $this->db->query("DELETE FROM post_tags WHERE post_id = :post_id AND tag_id NOT IN ({$inClause})");
            $this->db->bind(':post_id', $postId);
            $this->db->execute();
        } else {
            // No tags present in content, remove all tag associations for this post
            $this->db->query("DELETE FROM post_tags WHERE post_id = :post_id");
            $this->db->bind(':post_id', $postId);
            $this->db->execute();
        }
    }

    /**
     * Retrieve published posts associated with a specific tag
     *
     * @param string $tagName
     * @param int|null $currentUserId
     * @return array
     */
    public function getPostsByTag(string $tagName, ?int $currentUserId = null): array
    {
        $isRepostedSubquery = $currentUserId !== null
            ? "EXISTS(SELECT 1 FROM reposts WHERE reposts.post_id = posts.id AND reposts.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT posts.*, users.username, users.name, users.profile_picture,
                  {$isRepostedSubquery}
                  (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                  (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count
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
