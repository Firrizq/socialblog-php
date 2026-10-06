<?php

declare(strict_types=1);

/**
 * Explore Controller
 * Handles search and content discovery across stories and community authors.
 */
class Explore extends Controller
{
    private object $postModel;
    private object $interactionModel;

    public function __construct()
    {
        $this->postModel = $this->model('Post_model');
        $this->interactionModel = $this->model('Interaction_model');
    }

    /**
     * Search and Explore feed
     * GET /explore?q=keyword
     */
    public function index(): void
    {
        $keyword = trim($_GET['q'] ?? '');

        if (!empty($keyword)) {
            $posts = $this->postModel->searchPosts($keyword);
            $pageTitle = 'Search: ' . $keyword . ' - Blogggle';
        } else {
            $posts = $this->postModel->getTrendingPosts();
            $pageTitle = 'Explore - Blogggle';
        }

        $likedPosts = [];
        $bookmarkedPosts = [];
        $repostedPosts = [];
        if (!empty($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $likedPosts = $this->interactionModel->getUserLikedPostIds($userId);
            $bookmarkedPosts = $this->interactionModel->getUserBookmarkedPostIds($userId);
            $repostedPosts = $this->interactionModel->getUserRepostedPostIds($userId);
        }

        $data = [
            'title' => $pageTitle,
            'keyword' => $keyword,
            'posts' => $posts,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts,
            'reposted_posts' => $repostedPosts
        ];

        $this->view('explore/index', $data);
    }

    /**
     * View posts filtered by hashtag
     * GET /explore/tag/{tagName}
     *
     * @param string $tagName
     */
    public function tag(string $tagName = ''): void
    {
        $tagName = trim($tagName);
        if (empty($tagName)) {
            header('Location: ' . BASEURL . '/explore');
            exit;
        }

        $likedPosts = [];
        $bookmarkedPosts = [];
        $repostedPosts = [];
        $userId = null;
        if (!empty($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $likedPosts = $this->interactionModel->getUserLikedPostIds($userId);
            $bookmarkedPosts = $this->interactionModel->getUserBookmarkedPostIds($userId);
            $repostedPosts = $this->interactionModel->getUserRepostedPostIds($userId);
        }

        $tagModel = $this->model('Tag_model');
        $posts = $tagModel->getPostsByTag($tagName, $userId);

        $data = [
            'title' => "#{$tagName} - Blogggle",
            'tag_name' => $tagName,
            'posts' => $posts,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts,
            'reposted_posts' => $repostedPosts
        ];

        $this->view('explore/tag', $data);
    }
}
