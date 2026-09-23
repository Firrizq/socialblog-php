<?php

declare(strict_types=1);

/**
 * Action Controller
 * Handles AJAX/fetch requests for user interactions: Likes, Bookmarks, and Follows.
 * Returns structured JSON responses.
 */
class Action extends Controller
{
    private object $interactionModel;

    public function __construct()
    {
        $this->interactionModel = $this->model('Interaction_model');
    }

    /**
     * Toggle like on a post
     * POST /action/like/{post_id}
     *
     * @param string|int $postId
     */
    public function like(string|int $postId = 0): void
    {
        $this->requireAuth();
        $this->requirePostMethod();

        $postId = (int)$postId;
        if ($postId <= 0) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid post ID'
            ], 400);
        }

        $userId = (int)$_SESSION['user_id'];
        $result = $this->interactionModel->toggleLike($userId, $postId);

        $this->jsonResponse(array_merge([
            'success' => true,
            'post_id' => $postId
        ], $result));
    }

    /**
     * Toggle bookmark on a post
     * POST /action/bookmark/{post_id}
     *
     * @param string|int $postId
     */
    public function bookmark(string|int $postId = 0): void
    {
        $this->requireAuth();
        $this->requirePostMethod();

        $postId = (int)$postId;
        if ($postId <= 0) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid post ID'
            ], 400);
        }

        $userId = (int)$_SESSION['user_id'];
        $result = $this->interactionModel->toggleBookmark($userId, $postId);

        $this->jsonResponse(array_merge([
            'success' => true,
            'post_id' => $postId
        ], $result));
    }

    /**
     * Toggle follow on a user
     * POST /action/follow/{user_id}
     *
     * @param string|int $userId
     */
    public function follow(string|int $userId = 0): void
    {
        $this->requireAuth();
        $this->requirePostMethod();

        $targetUserId = (int)$userId;
        if ($targetUserId <= 0) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid user ID'
            ], 400);
        }

        $followerId = (int)$_SESSION['user_id'];
        $result = $this->interactionModel->toggleFollow($followerId, $targetUserId);

        if (($result['status'] ?? '') === 'error') {
            $this->jsonResponse(array_merge([
                'success' => false
            ], $result), 400);
        }

        $this->jsonResponse(array_merge([
            'success' => true,
            'user_id' => $targetUserId
        ], $result));
    }

    /**
     * Helper to verify authentication
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized. Please log in to perform this action.'
            ], 401);
        }
    }

    /**
     * Helper to enforce POST HTTP method
     */
    private function requirePostMethod(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Method Not Allowed. POST is required.'
            ], 405);
        }
    }

    /**
     * Output JSON response with appropriate headers and HTTP status code
     *
     * @param array $data
     * @param int $statusCode
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
