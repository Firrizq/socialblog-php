<?php

declare(strict_types=1);

/**
 * Notifications Controller
 * Displays and manages user notifications.
 */
class Notifications extends Controller
{
    private object $notificationModel;

    public function __construct()
    {
        $this->notificationModel = $this->model('Notification_model');
    }

    /**
     * Display all notifications for the authenticated user and mark them as read
     */
    public function index(): void
    {
        // Enforce authentication
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $notifications = $this->notificationModel->getUserNotifications($userId);

        // Mark all as read so the badge count clears
        $this->notificationModel->markAllAsRead($userId);

        $data = [
            'title' => 'Notifications - Blogggle',
            'notifications' => $notifications
        ];

        $this->view('notifications/index', $data);
    }
}
