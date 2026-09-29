<?php

declare(strict_types=1);

/**
 * History Controller
 * Hub for Reading History, Bookmarks, and Liked posts.
 */
class History extends Controller
{
    private object $historyModel;
    private object $interactionModel;

    public function __construct()
    {
        $this->historyModel = $this->model('History_model');
        $this->interactionModel = $this->model('Interaction_model');
    }

    /**
     * Display the Library / History page
     */
    public function index(): void
    {
        // Enforce authentication
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        // 1. Fetch all 3 datasets
        $readingHistory = $this->historyModel->getReadingHistory($userId);
        $bookmarkedPosts = $this->historyModel->getBookmarkedPosts($userId);
        $likedPosts = $this->historyModel->getLikedPosts($userId);

        // 2. Split reading history into finished and unfinished
        $finishedStories = array_values(array_filter($readingHistory, function ($post) {
            return (int)($post['progress'] ?? 0) >= 90;
        }));

        $unfinishedStories = array_values(array_filter($readingHistory, function ($post) {
            return (int)($post['progress'] ?? 0) < 90;
        }));

        // Interaction IDs for UI states
        $userLikedIds = $this->interactionModel->getUserLikedPostIds($userId);
        $userBookmarkedIds = $this->interactionModel->getUserBookmarkedPostIds($userId);
        $userRepostedIds = $this->interactionModel->getUserRepostedPostIds($userId);

        $activeTab = $_GET['tab'] ?? 'history';
        if (!in_array($activeTab, ['history', 'bookmarks', 'likes'], true)) {
            $activeTab = 'history';
        }

        $data = [
            'title' => 'Library & History - Blogggle',
            'active_tab' => $activeTab,
            'reading_history' => $readingHistory,
            'unfinished_stories' => $unfinishedStories,
            'finished_stories' => $finishedStories,
            'bookmarked_posts_list' => $bookmarkedPosts,
            'liked_posts_list' => $likedPosts,
            'liked_posts' => $userLikedIds,
            'bookmarked_posts' => $userBookmarkedIds,
            'reposted_posts' => $userRepostedIds
        ];

        $this->view('history/index', $data);
    }

    /**
     * AJAX endpoint to record reading progress
     * POST /history/progress/{post_id}
     *
     * @param string|int $postId
     */
    public function progress(string|int $postId = 0): void
    {
        if (empty($_SESSION['user_id']) || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }

        $postId = (int)$postId;
        $userId = (int)$_SESSION['user_id'];
        
        $input = json_decode(file_get_contents('php://input'), true);
        $progress = (int)($input['progress'] ?? $_POST['progress'] ?? 0);

        if ($postId > 0 && $progress >= 0) {
            $this->historyModel->recordProgress($userId, $postId, $progress);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'progress' => $progress]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
}
