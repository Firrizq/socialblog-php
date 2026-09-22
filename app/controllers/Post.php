<?php

declare(strict_types=1);

/**
 * Post Controller
 * Handles authoring and publishing blog posts.
 * Protected: Requires authenticated user session.
 */
class Post extends Controller
{
    private object $postModel;

    public function __construct()
    {
        // Enforce authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $this->postModel = $this->model('Post_model');
    }

    /**
     * Default Post route
     * Redirects to create post
     */
    public function index(): void
    {
        header('Location: ' . BASEURL . '/post/create');
        exit;
    }

    /**
     * Create a new post
     * GET /post/create (form view) | POST /post/create (save to DB)
     */
    public function create(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $rawContent = trim($_POST['content'] ?? '');

            // Allow safe rich-text HTML tags from Quill while stripping dangerous tags (scripts, iframes, etc.)
            $allowedTags = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6><blockquote><pre><code><ol><ul><li><a><span>';
            $content = strip_tags($rawContent, $allowedTags);

            $data = [
                'title' => 'Create New Post - Social Blog',
                'post_title' => $title,
                'content' => $content,
                'error' => ''
            ];

            // Validation
            if (empty($title) || empty(strip_tags($content))) {
                $data['error'] = 'Please provide both a title and content for your post.';
                $this->view('post/create', $data);
                return;
            }

            // Save post
            $created = $this->postModel->createPost([
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'content' => $content
            ]);

            if ($created) {
                header('Location: ' . BASEURL . '/home');
                exit;
            } else {
                $data['error'] = 'Failed to publish post. Please try again.';
                $this->view('post/create', $data);
                return;
            }
        }

        // GET request: show creation view
        $data = [
            'title' => 'Create New Post - Social Blog',
            'post_title' => '',
            'content' => '',
            'error' => ''
        ];

        $this->view('post/create', $data);
    }
}
