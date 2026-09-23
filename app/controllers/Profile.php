<?php

declare(strict_types=1);

/**
 * Profile Controller
 * Displays user profile header, biography, statistics, and authored stories.
 */
class Profile extends Controller
{
    private object $userModel;
    private object $postModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->postModel = $this->model('Post_model');
    }

    /**
     * Default profile route
     * Redirects authenticated user to their own profile, or guests to login
     */
    public function index(): void
    {
        if (!empty($_SESSION['username'])) {
            header('Location: ' . BASEURL . '/profile/user/' . urlencode($_SESSION['username']));
            exit;
        }

        header('Location: ' . BASEURL . '/auth');
        exit;
    }

    /**
     * Display a specific user's public profile and published stories
     *
     * @param string $username
     */
    public function user(string $username = ''): void
    {
        $username = trim($username);

        if (empty($username)) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $profileUser = $this->userModel->getUserProfile($username);

        if (!$profileUser) {
            http_response_code(404);
            $data = [
                'title' => 'User Not Found - EmeraldInk',
                'username' => $username,
                'profile_user' => null,
                'posts' => []
            ];
            $this->view('profile/index', $data);
            return;
        }

        $posts = $this->postModel->getPostsByUser((int)$profileUser['id']);

        $isFollowing = false;
        $likedPosts = [];
        $bookmarkedPosts = [];
        if (!empty($_SESSION['user_id'])) {
            $interactionModel = $this->model('Interaction_model');
            $isFollowing = $interactionModel->isFollowing((int)$_SESSION['user_id'], (int)$profileUser['id']);
            $likedPosts = $interactionModel->getUserLikedPostIds((int)$_SESSION['user_id']);
            $bookmarkedPosts = $interactionModel->getUserBookmarkedPostIds((int)$_SESSION['user_id']);
        }

        $data = [
            'title' => '@' . $profileUser['username'] . ' - Profile | EmeraldInk',
            'profile_user' => $profileUser,
            'posts' => $posts,
            'is_following' => $isFollowing,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts
        ];

        $this->view('profile/index', $data);
    }
}
