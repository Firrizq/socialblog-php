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
        $feedType = $_GET['feed'] ?? 'for-you';
        $currentUserId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if ($feedType === 'following' && $currentUserId) {
            $posts = $this->postModel->getFollowingFeedPosts($currentUserId);
        } else {
            $posts = $this->postModel->getFeedPosts($currentUserId);
        }

        $likedPosts = [];
        $bookmarkedPosts = [];
        $repostedPosts = [];
        if ($currentUserId) {
            $interactionModel = $this->model('Interaction_model');
            $likedPosts = $interactionModel->getUserLikedPostIds($currentUserId);
            $bookmarkedPosts = $interactionModel->getUserBookmarkedPostIds($currentUserId);
            $repostedPosts = $interactionModel->getUserRepostedPostIds($currentUserId);
        }

        $data = [
            'title' => 'Timeline Feed - Blogggle',
            'feed_type' => $feedType,
            'posts' => $posts,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts,
            'reposted_posts' => $repostedPosts
        ];

        $this->view('home/index', $data);
    }
}
