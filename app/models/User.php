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
     * @param array $data Contains 'username', 'email', 'password'
     * @return bool
     */
    public function register(array $data): bool
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->db->query("INSERT INTO {$this->table} (username, email, password) VALUES (:username, :email, :password)");
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
     * Fetch user profile data by username
     *
     * @param string $username
     * @return array|false
     */
    public function getUserProfile(string $username): array|false
    {
        $this->db->query("SELECT id, username, bio, profile_picture, banner_picture, location, profile_link, tipping_link, follower_count, following_count, created_at FROM {$this->table} WHERE username = :username LIMIT 1");
        $this->db->bind(':username', $username);
        $row = $this->db->single();

        return $row ?: false;
    }
}
