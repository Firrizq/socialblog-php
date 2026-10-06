<?php

declare(strict_types=1);

/**
 * User Model
 * Handles database operations for authentication and user management.
 */
class User
{
    private Database $db;
    private string $table = 'users';

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Find a user by their email address
     *
     * @param string $email
     * @return array|false
     */
    public function findUserByEmail(string $email): array|false
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $this->db->bind(':email', $email);
        $row = $this->db->single();

        return $row ?: false;
    }

    /**
     * Find a user by their username
     *
     * @param string $username
     * @return array|false
     */
    public function findUserByUsername(string $username): array|false
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $this->db->bind(':username', $username);
        $row = $this->db->single();

        return $row ?: false;
    }

    /**
     * Register a new user into the database
     *
     * @param array $data Contains 'name', 'username', 'email', 'password'
     * @return bool
     */
    public function register(array $data): bool
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->db->query("INSERT INTO {$this->table} (name, username, email, password) VALUES (:name, :username, :email, :password)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $hashedPassword);

        return $this->db->execute();
    }

    /**
     * Verify user credentials for login
     *
     * @param string $email
     * @param string $password
     * @return array|false Returns user row on success, false on failure
     */
    public function login(string $email, string $password): array|false
    {
        $user = $this->findUserByEmail($email);

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Fetch user data by user ID
     *
     * @param int $userId
     * @return array|false
     */
    public function getUserById(int $userId): array|false
    {
        $this->db->query("SELECT id, name, username, email, bio, profile_picture, banner_picture, banner_position, location, profile_link, tipping_link, follower_count, following_count, created_at FROM {$this->table} WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $userId);
        $row = $this->db->single();

        return $row ?: false;
    }

    /**
     * Fetch user profile data by username
     *
     * @param string $username
     * @return array|false
     */
    public function getUserProfile(string $username): array|false
    {
        $this->db->query("SELECT id, name, username, email, bio, profile_picture, banner_picture, banner_position, location, profile_link, tipping_link, follower_count, following_count, created_at FROM {$this->table} WHERE username = :username LIMIT 1");
        $this->db->bind(':username', $username);
        $row = $this->db->single();

        return $row ?: false;
    }

    /**
     * Update user profile information
     *
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $fields = [
            'bio = :bio',
            'location = :location',
            'profile_link = :profile_link',
            'tipping_link = :tipping_link'
        ];

        if (array_key_exists('username', $data) && !empty($data['username'])) {
            $fields[] = 'username = :username';
        }

        if (array_key_exists('name', $data) && !empty($data['name'])) {
            $fields[] = 'name = :name';
        }

        if (array_key_exists('profile_picture', $data) && $data['profile_picture'] !== null) {
            $fields[] = 'profile_picture = :profile_picture';
        }

        if (array_key_exists('banner_picture', $data) && $data['banner_picture'] !== null) {
            $fields[] = 'banner_picture = :banner_picture';
        }

        if (array_key_exists('banner_position', $data)) {
            $fields[] = 'banner_position = :banner_position';
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :user_id";
        $this->db->query($sql);

        $this->db->bind(':bio', $data['bio'] ?? null);
        $this->db->bind(':location', $data['location'] ?? null);
        $this->db->bind(':profile_link', $data['profile_link'] ?? null);
        $this->db->bind(':tipping_link', $data['tipping_link'] ?? null);
        $this->db->bind(':user_id', $userId);

        if (array_key_exists('username', $data) && !empty($data['username'])) {
            $this->db->bind(':username', $data['username']);
        }

        if (array_key_exists('name', $data) && !empty($data['name'])) {
            $this->db->bind(':name', $data['name']);
        }

        if (array_key_exists('profile_picture', $data) && $data['profile_picture'] !== null) {
            $this->db->bind(':profile_picture', $data['profile_picture']);
        }

        if (array_key_exists('banner_picture', $data) && $data['banner_picture'] !== null) {
            $this->db->bind(':banner_picture', $data['banner_picture']);
        }

        if (array_key_exists('banner_position', $data)) {
            $this->db->bind(':banner_position', $data['banner_position']);
        }

        return $this->db->execute();
    }

    /**
     * Check if a username is already taken by a different user
     *
     * @param string $username
     * @param int $excludeUserId
     * @return bool
     */
    public function isUsernameTakenByOther(string $username, int $excludeUserId): bool
    {
        $this->db->query("SELECT id FROM {$this->table} WHERE LOWER(username) = LOWER(:username) AND id != :exclude_id LIMIT 1");
        $this->db->bind(':username', $username);
        $this->db->bind(':exclude_id', $excludeUserId);
        return (bool)$this->db->single();
    }

    /**
     * Remove user profile picture
     *
     * @param int $userId
     * @return bool
     */
    public function removeAvatar(int $userId): bool
    {
        $this->db->query("UPDATE {$this->table} SET profile_picture = NULL WHERE id = :user_id");
        $this->db->bind(':user_id', $userId);

        return $this->db->execute();
    }

    /**
     * Remove user profile banner picture
     *
     * @param int $userId
     * @return bool
     */
    public function removeBanner(int $userId): bool
    {
        $this->db->query("UPDATE {$this->table} SET banner_picture = NULL, banner_position = '50%' WHERE id = :user_id");
        $this->db->bind(':user_id', $userId);

        return $this->db->execute();
    }

    /**
     * Get suggested community writers ordered by follower count
     * Excludes self and any users currently followed by the active user.
     *
     * @param int $currentUserId
     * @return array
     */
    public function getSuggestedWriters(int $currentUserId): array
    {
        if ($currentUserId > 0) {
            $this->db->query("SELECT id, username, profile_picture, bio, follower_count 
                              FROM {$this->table} 
                              WHERE id != :current_user_id 
                                AND id NOT IN (
                                    SELECT target_id 
                                    FROM followings 
                                    WHERE user_id = :current_user_id AND target_id IS NOT NULL
                                )
                              ORDER BY follower_count DESC, created_at DESC 
                              LIMIT 4");
            $this->db->bind(':current_user_id', $currentUserId);
        } else {
            $this->db->query("SELECT id, username, profile_picture, bio, follower_count 
                              FROM {$this->table} 
                              ORDER BY follower_count DESC, created_at DESC 
                              LIMIT 4");
        }

        return $this->db->resultSet();
    }

    /**
     * Get users following a specific user
     *
     * @param int $userId
     * @param int|null $currentUserId
     * @return array
     */
    public function getFollowers(int $userId, ?int $currentUserId = null): array
    {
        $isFollowingSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM followings fl WHERE fl.user_id = " . (int)$currentUserId . " AND fl.target_id = u.id) AS is_following,"
            : "0 AS is_following,";

        $sql = "SELECT u.id, u.name, u.username, u.profile_picture, u.bio, u.follower_count,
                       {$isFollowingSelect}
                       f.created_at AS followed_at
                FROM {$this->table} u
                INNER JOIN followers f ON u.id = f.target_id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC";

        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Get users followed by a specific user
     *
     * @param int $userId
     * @param int|null $currentUserId
     * @return array
     */
    public function getFollowing(int $userId, ?int $currentUserId = null): array
    {
        $isFollowingSelect = $currentUserId
            ? "EXISTS(SELECT 1 FROM followings fl WHERE fl.user_id = " . (int)$currentUserId . " AND fl.target_id = u.id) AS is_following,"
            : "0 AS is_following,";

        $sql = "SELECT u.id, u.name, u.username, u.profile_picture, u.bio, u.follower_count,
                       {$isFollowingSelect}
                       f.created_at AS followed_at
                FROM {$this->table} u
                INNER JOIN followings f ON u.id = f.target_id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC";

        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Search authors/creators by keyword matching username, display name, or bio
     *
     * @param string $keyword
     * @param int|null $currentUserId
     * @param int $limit
     * @return array
     */
    public function searchAuthors(string $keyword, ?int $currentUserId = null, int $limit = 6): array
    {
        $keyword = trim($keyword);
        if (empty($keyword)) {
            return [];
        }

        $limitInt = max(1, min(50, (int)$limit));
        $isFollowingSelect = ($currentUserId !== null && $currentUserId > 0)
            ? "EXISTS(SELECT 1 FROM followings fl WHERE fl.user_id = " . (int)$currentUserId . " AND fl.target_id = u.id) AS is_following,"
            : "0 AS is_following,";

        $sql = "SELECT u.id, u.name, u.username, u.profile_picture, u.banner_picture, u.banner_position, u.bio, u.follower_count, u.following_count,
                       {$isFollowingSelect}
                       u.created_at
                FROM {$this->table} u
                WHERE (u.username LIKE :keyword OR u.name LIKE :keyword OR u.bio LIKE :keyword)
                ORDER BY 
                    CASE 
                        WHEN u.username = :exact_keyword THEN 1
                        WHEN u.name = :exact_keyword THEN 2
                        WHEN u.username LIKE :starts_keyword THEN 3
                        WHEN u.name LIKE :starts_keyword THEN 4
                        ELSE 5 
                    END,
                    u.follower_count DESC, 
                    u.created_at DESC
                LIMIT {$limitInt}";

        $this->db->query($sql);
        $this->db->bind(':keyword', "%{$keyword}%");
        $this->db->bind(':exact_keyword', $keyword);
        $this->db->bind(':starts_keyword', "{$keyword}%");

        return $this->db->resultSet();
    }
}



