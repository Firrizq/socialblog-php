<?php

declare(strict_types=1);

/**
 * Comment Model
 * Handles database operations for post comments and threaded discussions.
 */
class Comment_model
{
    private Database $db;
    private string $table = 'comments';

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Fetch a single comment by ID, joined with user profile and post info
     *
     * @param int $id
     * @return array|false
     */
    public function getCommentById(int $id): array|false
    {
        $query = "SELECT 
                    comments.id,
                    comments.post_id,
                    comments.user_id,
                    comments.parent_id,
                    comments.comment,
                    comments.like_count,
                    comments.reply_count,
                    comments.created_at,
                    users.username,
                    users.profile_picture,
                    posts.title AS post_title
                  FROM {$this->table}
                  INNER JOIN users ON comments.user_id = users.id
                  INNER JOIN posts ON comments.post_id = posts.id
                  WHERE comments.id = :id
                  LIMIT 1";

        $this->db->query($query);
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        return $row ?: false;
    }

    /**
     * Fetch direct replies for a specific parent comment
     *
     * @param int $parent_id
     * @return array
     */
    public function getRepliesByCommentId(int $parent_id): array
    {
        $query = "SELECT 
                    comments.id,
                    comments.post_id,
                    comments.user_id,
                    comments.parent_id,
                    comments.comment,
                    comments.like_count,
                    comments.reply_count,
                    comments.created_at,
                    users.username,
                    users.profile_picture
                  FROM {$this->table}
                  INNER JOIN users ON comments.user_id = users.id
                  WHERE comments.parent_id = :parent_id
                  ORDER BY comments.created_at ASC";

        $this->db->query($query);
        $this->db->bind(':parent_id', $parent_id);
        return $this->db->resultSet();
    }

    /**
     * Fetch all comments for a post and organize into a hierarchical tree array
     * Top-level comments (parent_id is null) contain an array of 'replies'.
     *
     * @param int $post_id
     * @return array
     */
    public function getCommentsByPostId(int $post_id): array
    {
        $query = "SELECT 
                    comments.id,
                    comments.post_id,
                    comments.user_id,
                    comments.parent_id,
                    comments.comment,
                    comments.like_count,
                    comments.reply_count,
                    comments.created_at,
                    users.username,
                    users.profile_picture
                  FROM {$this->table}
                  INNER JOIN users ON comments.user_id = users.id
                  WHERE comments.post_id = :post_id
                  ORDER BY comments.created_at ASC";

        $this->db->query($query);
        $this->db->bind(':post_id', $post_id);
        $rawComments = $this->db->resultSet();

        return $this->buildCommentTree($rawComments);
    }

    /**
     * Helper to organize flat comments into parent-child tree structure
     *
     * @param array $comments
     * @return array
     */
    public function buildCommentTree(array $comments): array
    {
        $commentMap = [];
        $tree = [];

        // 1. Index all comments by ID and initialize empty replies array
        foreach ($comments as $comment) {
            $comment['replies'] = [];
            $commentMap[$comment['id']] = $comment;
        }

        // 2. Assign child replies to their parents
        foreach ($commentMap as $id => &$comment) {
            if (!empty($comment['parent_id']) && isset($commentMap[$comment['parent_id']])) {
                $commentMap[$comment['parent_id']]['replies'][] = &$comment;
            } else {
                $tree[] = &$comment;
            }
        }
        unset($comment);

        // Sort root discussions newest first
        usort($tree, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return $tree;
    }

    /**
     * Insert a new comment or reply into the database
     *
     * @param array $data ['post_id', 'user_id', 'comment', 'parent_id' => null]
     * @return bool
     */
    public function addComment(array $data): bool
    {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        $query = "INSERT INTO {$this->table} (post_id, user_id, comment, parent_id) 
                  VALUES (:post_id, :user_id, :comment, :parent_id)";

        $this->db->query($query);
        $this->db->bind(':post_id', $data['post_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':comment', $data['comment']);
        $this->db->bind(':parent_id', $parentId);

        return $this->db->execute();
    }
}
