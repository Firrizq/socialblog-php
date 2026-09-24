<?php

declare(strict_types=1);

/**
 * Default Home Controller
 * Displays the main timeline feed of posts.
 */
class Home extends Controller
{
    private object $postModel;

    public function __construct()
    {
        $this->postModel = $this->model('Post_model');
    }

    public function index(): void
    {
        $posts = $this->postModel->getFeedPosts();

        $likedPosts = [];
        $bookmarkedPosts = [];
        if (!empty($_SESSION['user_id'])) {
            $interactionModel = $this->model('Interaction_model');
            $likedPosts = $interactionModel->getUserLikedPostIds((int)$_SESSION['user_id']);
            $bookmarkedPosts = $interactionModel->getUserBookmarkedPostIds((int)$_SESSION['user_id']);
        }

        $data = [
            'title' => 'Timeline Feed - Blogggle',
            'posts' => $posts,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts
        ];

        $this->view('home/index', $data);
    }
}
