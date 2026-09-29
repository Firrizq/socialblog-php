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
     * @param int|null $currentUserId
     * @return array
     */
    public function getFeedPosts(?int $currentUserId = null): array
    {
        $isRepostedSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM reposts WHERE reposts.post_id = posts.id AND reposts.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.email,
                    users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    {$isRepostedSelect}
                    NULL AS repost_user_id,
                    NULL AS repost_username,
                    NULL AS repost_name
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.status = 'published'
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Generate a unique 12-digit numeric string for posts
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
     * Create a new post in the database
     *
     * @param array $data ['user_id', 'title', 'content', 'status', 'uid' => optional]
     * @return int|false
     */
    public function createPost(array $data): int|false
    {
        $status = $data['status'] ?? 'published';
        $coverImage = $data['cover_image'] ?? null;
        $postType = $data['post_type'] ?? 'story';
        $wordCount = str_word_count(strip_tags($data['content'] ?? ''));
        $readTime = max(1, (int)ceil($wordCount / 200));
        $uid = !empty($data['uid']) ? (string)$data['uid'] : $this->generateUid();

        $query = "INSERT INTO {$this->table} (uid, user_id, post_type, title, content, cover_image, status, read_time_minutes) 
                  VALUES (:uid, :user_id, :post_type, :title, :content, :cover_image, :status, :read_time_minutes)";

        $this->db->query($query);
        $this->db->bind(':uid', $uid);
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
     * @param int|null $currentUserId
     * @return array
     */
    public function getPostsByUser(int $user_id, bool $isOwner = false, ?int $currentUserId = null): array
    {
        $statusCondition = $isOwner ? "" : " AND posts.status = 'published'";
        $isRepostedSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM reposts WHERE reposts.post_id = posts.id AND reposts.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.email,
                    users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    {$isRepostedSelect}
                    NULL AS repost_user_id,
                    NULL AS repost_username,
                    NULL AS repost_name
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.user_id = :user_id {$statusCondition}
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    /**
     * Fetch a single post by its 12-digit UID, joined with author information
     *
     * @param string $uid
     * @param int|null $currentUserId
     * @return array|false
     */
    public function getPostByUid(string $uid, ?int $currentUserId = null): array|false
    {
        $isRepostedSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM reposts WHERE reposts.post_id = posts.id AND reposts.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.profile_picture,
                    users.email,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    {$isRepostedSelect}
                    NULL AS repost_user_id,
                    NULL AS repost_username,
                    NULL AS repost_name
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.uid = :uid
                  LIMIT 1";

        $this->db->query($query);
        $this->db->bind(':uid', $uid);
        $row = $this->db->single();

        return $row ?: false;
    }

    /**
     * Fetch a single post by its ID, joined with author information
     *
     * @param int $id
     * @param int|null $currentUserId
     * @return array|false
     */
    public function getPostById(int $id, ?int $currentUserId = null): array|false
    {
        $isRepostedSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM reposts WHERE reposts.post_id = posts.id AND reposts.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.profile_picture,
                    users.email,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    {$isRepostedSelect}
                    NULL AS repost_user_id,
                    NULL AS repost_username,
                    NULL AS repost_name
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
        $query = "SELECT posts.*, users.username, users.profile_picture, users.email, bookmarks.created_at AS bookmarked_at,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count 
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
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count 
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
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture, users.email,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count 
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
        $query = "SELECT posts.*, users.username, users.name, users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count 
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
     * Fetch feed posts from authors the user follows, plus stories reposted by authors they follow
     *
     * @param int $userId
     * @return array
     */
    public function getFollowingFeedPosts(int $userId): array
    {
        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.name,
                    users.profile_picture,
                    users.email,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    EXISTS(SELECT 1 FROM reposts r WHERE r.post_id = posts.id AND r.user_id = :user_id1) AS is_reposted,
                    NULL AS repost_user_id,
                    NULL AS repost_username,
                    NULL AS repost_name,
                    posts.created_at AS timeline_time
                  FROM {$this->table} 
                  INNER JOIN users ON posts.user_id = users.id 
                  INNER JOIN followings ON users.id = followings.target_id 
                  WHERE followings.user_id = :user_id2 AND posts.status = 'published' 

                  UNION ALL

                  SELECT 
                    posts.*,
                    author.username,
                    author.name,
                    author.profile_picture,
                    author.email,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    EXISTS(SELECT 1 FROM reposts r WHERE r.post_id = posts.id AND r.user_id = :user_id3) AS is_reposted,
                    reposter.id AS repost_user_id,
                    reposter.username AS repost_username,
                    reposter.name AS repost_name,
                    reposts.created_at AS timeline_time
                  FROM reposts
                  INNER JOIN followings ON reposts.user_id = followings.target_id
                  INNER JOIN users reposter ON reposts.user_id = reposter.id
                  INNER JOIN posts ON reposts.post_id = posts.id
                  INNER JOIN users author ON posts.user_id = author.id
                  WHERE followings.user_id = :user_id4 AND posts.status = 'published'
                  ORDER BY timeline_time DESC";

        $this->db->query($query);
        $this->db->bind(':user_id1', $userId);
        $this->db->bind(':user_id2', $userId);
        $this->db->bind(':user_id3', $userId);
        $this->db->bind(':user_id4', $userId);
        return $this->db->resultSet();
    }

    /**
     * Fetch all posts reposted by a specific user, ordered by repost date newest first
     *
     * @param int $userId
     * @param int|null $currentUserId
     * @return array
     */
    public function getRepostedPostsByUser(int $userId, ?int $currentUserId = null): array
    {
        $isRepostedSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM reposts r2 WHERE r2.post_id = posts.id AND r2.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT 
                    posts.*,
                    author.username,
                    author.name,
                    author.profile_picture,
                    author.email,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    {$isRepostedSelect}
                    reposter.id AS repost_user_id,
                    reposter.username AS repost_username,
                    reposter.name AS repost_name,
                    reposts.created_at AS timeline_time
                  FROM reposts
                  INNER JOIN users reposter ON reposts.user_id = reposter.id
                  INNER JOIN posts ON reposts.post_id = posts.id
                  INNER JOIN users author ON posts.user_id = author.id
                  WHERE reposts.user_id = :user_id AND posts.status = 'published'
                  ORDER BY reposts.created_at DESC";

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

    /**
     * Fetch all posts by a specific user that contain media attachments (cover images or embedded images)
     *
     * @param int $user_id
     * @param bool $isOwner
     * @param int|null $currentUserId
     * @return array
     */
    public function getMediaPostsByUser(int $user_id, bool $isOwner = false, ?int $currentUserId = null): array
    {
        $statusCondition = $isOwner ? "" : " AND posts.status = 'published'";
        $isRepostedSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM reposts WHERE reposts.post_id = posts.id AND reposts.user_id = " . (int)$currentUserId . ") AS is_reposted,"
            : "0 AS is_reposted,";

        $query = "SELECT 
                    posts.*,
                    users.username,
                    users.name,
                    users.email,
                    users.profile_picture,
                    (SELECT COUNT(*) FROM bookmarks WHERE post_id = posts.id) AS bookmark_count,
                    (SELECT COUNT(*) FROM reposts WHERE post_id = posts.id) AS repost_count,
                    {$isRepostedSelect}
                    NULL AS repost_user_id,
                    NULL AS repost_username,
                    NULL AS repost_name
                  FROM {$this->table}
                  INNER JOIN users ON posts.user_id = users.id
                  WHERE posts.user_id = :user_id {$statusCondition}
                    AND (
                        (posts.cover_image IS NOT NULL AND posts.cover_image != '' AND posts.cover_image != '[]' AND posts.cover_image != 'null')
                        OR posts.content LIKE '%<img%'
                    )
                  ORDER BY posts.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }
}



