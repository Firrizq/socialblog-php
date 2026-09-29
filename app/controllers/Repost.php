<?php

declare(strict_types=1);

/**
 * Repost Controller
 * Handles toggling reposts / retweets for stories and notes via AJAX.
 */
class Repost extends Controller
{
    private object $repostModel;

    public function __construct()
    {
        $this->repostModel = $this->model('Repost_model');
    }

    /**
     * Default route - toggles repost if post ID provided
     * POST /repost/{post_id}
     *
     * @param string|int $postId
     */
    public function index(string|int $postId = 0): void
    {
        $this->toggle($postId);
    }

    /**
     * Toggle repost on a post
     * POST /repost/toggle/{post_id}
     *
     * @param string|int $postId
     */
    public function toggle(string|int $postId = 0): void
    {
        // 1. Session check
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized. Please log in to repost stories.',
                'is_reposted' => false,
                'repost_count' => 0
            ]);
            exit;
        }

        // 2. HTTP method check
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Method Not Allowed. POST is required.',
                'is_reposted' => false,
                'repost_count' => 0
            ]);
            exit;
        }

        $postId = (int)$postId;
        if ($postId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid post ID',
                'is_reposted' => false,
                'repost_count' => 0
            ]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $result = $this->repostModel->toggleRepost($userId, $postId);

        if (($result['status'] ?? '') === 'error') {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => $result['message'] ?? 'Post not found',
                'is_reposted' => false,
                'repost_count' => 0
            ]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'is_reposted' => (bool)$result['is_reposted'],
            'repost_count' => (int)$result['repost_count'],
            'count' => (int)$result['repost_count'],
            'action' => $result['action'] ?? ($result['is_reposted'] ? 'reposted' : 'unreposted')
        ]);
        exit;
    }
}
