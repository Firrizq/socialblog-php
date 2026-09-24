<?php

declare(strict_types=1);

/**
 * Notification Model
 * Handles database operations for user activity notifications.
 */
class Notification_model
{
    private Database $db;
    private string $table = 'notifications';

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get the total unread notifications count for a user
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        $this->db->query("UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Fetch all notifications for a user joined with actor information, ordered by newest first
     *
     * @param int $userId
     * @return array
     */
    public function getUserNotifications(int $userId): array
    {
        $query = "SELECT n.*, u.username as actor_username, u.profile_picture as actor_profile_picture 
                  FROM {$this->table} n 
                  INNER JOIN users u ON n.actor_id = u.id 
                  WHERE n.user_id = :user_id 
                  ORDER BY n.created_at DESC";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Insert a new notification into the database
     * Always ignores/returns early if $userId === $actorId (users shouldn't notify themselves)
     *
     * @param int $userId
     * @param int $actorId
     * @param string $type
     * @param int|null $referenceId
     * @return void
     */
    public function addNotification(int $userId, int $actorId, string $type, ?int $referenceId = null): void
    {
        if ($userId === $actorId) {
            return;
        }

        $query = "INSERT INTO {$this->table} (user_id, actor_id, type, reference_id, is_read) 
                  VALUES (:user_id, :actor_id, :type, :reference_id, 0)";

        $this->db->query($query);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':actor_id', $actorId);
        $this->db->bind(':type', $type);
        $this->db->bind(':reference_id', $referenceId);
        $this->db->execute();
    }
}
