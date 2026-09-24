<?php

declare(strict_types=1);

/**
 * Bookmarks Controller
 * Displays the list of saved/bookmarked posts for the authenticated user.
 */
class Bookmarks extends Controller
{
    private object $postModel;
    private object $interactionModel;

    public function __construct()
    {
        $this->postModel = $this->model('Post_model');
        $this->interactionModel = $this->model('Interaction_model');
    }

    /**
     * Display all bookmarked posts of the logged-in user
     */
    public function index(): void
    {
        // Enforce authentication
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $posts = $this->postModel->getBookmarkedPosts($userId);

        $likedPosts = $this->interactionModel->getUserLikedPostIds($userId);
        $bookmarkedPosts = $this->interactionModel->getUserBookmarkedPostIds($userId);

        $data = [
            'title' => 'Bookmarks - Blogggle',
            'posts' => $posts,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts
        ];

        $this->view('bookmarks/index', $data);
    }
}
