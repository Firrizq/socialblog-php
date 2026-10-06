<?php

declare(strict_types=1);

require_once __DIR__ . '/Notification_model.php';

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
     * Generate a unique 12-digit numeric string for comments
     *
     * @return string
     */
    public function generateUid(): string
    {
        do {
            $uid = (string)random_int(100000000000, 999999999999);
            $this->db->query("SELECT id FROM {$this->table} WHERE uid = :uid LIMIT 1");
            $this->db->bind(':uid', $uid);
            $existing = $this->db->single();
        } while (!empty($existing));

        return $uid;
    }

    /**
     * Fetch a single comment by 12-digit UID, joined with user profile and post info
     *
     * @param string $uid
     * @return array|false
     */
    public function getCommentByUid(string $uid): array|false
    {
        $query = "SELECT 
                    comments.id,
                    comments.uid,
                    comments.post_id,
                    comments.user_id,
                    comments.parent_id,
                    comments.comment,
                    comments.like_count,
                    comments.reply_count,
                    comments.created_at,
                    users.username,
                    users.profile_picture,
                    posts.title AS post_title,
                    posts.uid AS post_uid,
                    posts.post_type AS post_type
                  FROM {$this->table}
                  INNER JOIN users ON comments.user_id = users.id
                  INNER JOIN posts ON comments.post_id = posts.id
                  WHERE comments.uid = :uid AND (comments.deleted_at IS NULL)
                  LIMIT 1";

        $this->db->query($query);
        $this->db->bind(':uid', $uid);
        $row = $this->db->single();

        return $row ?: false;
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
                    comments.uid,
                    comments.post_id,
                    comments.user_id,
                    comments.parent_id,
                    comments.comment,
                    comments.like_count,
                    comments.reply_count,
                    comments.created_at,
                    users.username,
                    users.profile_picture,
                    posts.title AS post_title,
                    posts.uid AS post_uid,
                    posts.post_type AS post_type
                  FROM {$this->table}
                  INNER JOIN users ON comments.user_id = users.id
                  INNER JOIN posts ON comments.post_id = posts.id
                  WHERE comments.id = :id AND (comments.deleted_at IS NULL)
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
                    comments.uid,
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
                  WHERE comments.parent_id = :parent_id AND (comments.deleted_at IS NULL)
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
                    comments.uid,
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
                  WHERE comments.post_id = :post_id AND (comments.deleted_at IS NULL)
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
     * @param array $data ['post_id', 'user_id', 'comment', 'parent_id' => null, 'uid' => optional]
     * @return bool
     */
    public function addComment(array $data): bool
    {
        $uid = !empty($data['uid']) ? (string)$data['uid'] : $this->generateUid();
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        $query = "INSERT INTO {$this->table} (uid, post_id, user_id, comment, parent_id) 
                  VALUES (:uid, :post_id, :user_id, :comment, :parent_id)";

        $this->db->query($query);
        $this->db->bind(':uid', $uid);
        $this->db->bind(':post_id', $data['post_id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':comment', $data['comment']);
        $this->db->bind(':parent_id', $parentId);

        $executed = $this->db->execute();

        if ($executed) {
            $postId = (int)$data['post_id'];
            
            // Increment the main post's comment count
            $this->db->query("UPDATE posts SET comment_count = comment_count + 1 WHERE id = :post_id");
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            // If it's a nested reply, increment the parent comment's reply count
            if ($parentId !== null) {
                $this->db->query("UPDATE {$this->table} SET reply_count = reply_count + 1 WHERE id = :parent_id");
                $this->db->bind(':parent_id', $parentId);
                $this->db->execute();
            }

            $notificationModel = new Notification_model();
            $actorId = (int)$data['user_id'];

            if ($parentId === null) {
                // Top-level comment: notify post owner
                $this->db->query("SELECT user_id FROM posts WHERE id = :post_id LIMIT 1");
                $this->db->bind(':post_id', $postId);
                $post = $this->db->single();
                if ($post && !empty($post['user_id'])) {
                    $notificationModel->addNotification((int)$post['user_id'], $actorId, 'comment', $postId);
                }
            } else {
                // Reply: notify parent comment owner
                $this->db->query("SELECT user_id FROM {$this->table} WHERE id = :parent_id LIMIT 1");
                $this->db->bind(':parent_id', $parentId);
                $parentComment = $this->db->single();
                if ($parentComment && !empty($parentComment['user_id'])) {
                    $notificationModel->addNotification((int)$parentComment['user_id'], $actorId, 'reply', $postId);
                }
            }
        }

        return $executed;
    }

    /**
     * Fetch all replies/comments authored by a specific user, joined with the replied post and author
     *
     * @param int $user_id
     * @return array
     */
    public function getRepliesByUser(int $user_id): array
    {
        $query = "SELECT 
                    comments.id,
                    comments.uid,
                    comments.post_id,
                    comments.user_id,
                    comments.parent_id,
                    comments.comment,
                    comments.like_count,
                    comments.reply_count,
                    comments.created_at,
                    replier.username AS replier_username,
                    replier.name AS replier_name,
                    replier.profile_picture AS replier_profile_picture,
                    posts.title AS post_title,
                    posts.uid AS post_uid,
                    posts.content AS post_content,
                    posts.cover_image AS post_cover_image,
                    posts.post_type AS post_type,
                    posts.created_at AS post_created_at,
                    author.id AS author_id,
                    author.username AS author_username,
                    author.name AS author_name,
                    author.profile_picture AS author_profile_picture
                  FROM {$this->table}
                  INNER JOIN users replier ON comments.user_id = replier.id
                  INNER JOIN posts ON comments.post_id = posts.id
                  INNER JOIN users author ON posts.user_id = author.id
                  WHERE comments.user_id = :user_id 
                    AND (comments.deleted_at IS NULL)
                    AND posts.status = 'published'
                  ORDER BY comments.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    /**
     * Soft delete a comment owned by a specific user or authored on a post owned by the user
     *
     * @param int $commentId
     * @param int $userId
     * @return bool
     */
    public function deleteComment(int $commentId, int $userId): bool
    {
        // 1. Fetch comment with post author info
        $query = "SELECT comments.id, comments.post_id, comments.user_id, comments.parent_id, posts.user_id AS post_author_id
                  FROM {$this->table}
                  INNER JOIN posts ON comments.post_id = posts.id
                  WHERE comments.id = :id AND comments.deleted_at IS NULL
                  LIMIT 1";
        $this->db->query($query);
        $this->db->bind(':id', $commentId);
        $comment = $this->db->single();

        if (!$comment) {
            return false;
        }

        // Must be comment author OR the post author
        if ((int)$comment['user_id'] !== $userId && (int)$comment['post_author_id'] !== $userId) {
            return false;
        }

        // 2. Perform soft delete
        $this->db->query("UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':id', $commentId);
        $executed = $this->db->execute();

        if ($executed) {
            $postId = (int)$comment['post_id'];
            // Decrement post comment count
            $this->db->query("UPDATE posts SET comment_count = GREATEST(comment_count - 1, 0) WHERE id = :post_id");
            $this->db->bind(':post_id', $postId);
            $this->db->execute();

            // If it had a parent comment, decrement parent's reply count
            if (!empty($comment['parent_id'])) {
                $this->db->query("UPDATE {$this->table} SET reply_count = GREATEST(reply_count - 1, 0) WHERE id = :parent_id");
                $this->db->bind(':parent_id', (int)$comment['parent_id']);
                $this->db->execute();
            }
        }

        return $executed;
    }
}
