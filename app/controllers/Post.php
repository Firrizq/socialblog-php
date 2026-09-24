<?php

declare(strict_types=1);

/**
 * Post Controller
 * Handles post authoring, publishing, single post detail viewing, and commenting.
 */
class Post extends Controller
{
    private object $postModel;
    private object $commentModel;

    public function __construct()
    {
        $this->postModel = $this->model('Post_model');
        $this->commentModel = $this->model('Comment_model');
    }

    /**
     * Default Post route
     * Redirects to create post or home
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
        // Enforce authentication for post creation
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $rawContent = trim($_POST['content'] ?? '');

            // Allow safe rich-text HTML tags from Quill while stripping dangerous tags (scripts, iframes, etc.)
            $allowedTags = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6><blockquote><pre><code><ol><ul><li><a><span>';
            $content = strip_tags($rawContent, $allowedTags);

            $data = [
                'title' => 'Create New Post - Blogggle',
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
            'title' => 'Create New Post - Blogggle',
            'post_title' => '',
            'content' => '',
            'error' => ''
        ];

        $this->view('post/create', $data);
    }

    /**
     * Create a short-form note (Substack / Twitter style)
     * POST /post/createNote
     */
    public function createNote(): void
    {
        // Enforce authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $rawContent = trim($_POST['content'] ?? '');

            if (!empty($rawContent)) {
                // Sanitize plain text and convert line breaks
                $content = nl2br(htmlspecialchars($rawContent, ENT_QUOTES, 'UTF-8'));

                $this->postModel->createPost([
                    'user_id' => (int)$_SESSION['user_id'],
                    'title' => null,
                    'content' => $content
                ]);
            }
        }

        header('Location: ' . BASEURL . '/home');
        exit;
    }

    /**
     * Single Post Detail Page
     * GET /post/detail/{id}
     *
     * @param string|int $id
     */
    public function detail(string|int $id = 0): void
    {
        $id = (int)$id;

        if ($id <= 0) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $post = $this->postModel->getPostById($id);

        if (!$post) {
            http_response_code(404);
            $data = [
                'title' => 'Post Not Found - Blogggle',
                'post' => null,
                'comments' => []
            ];
            $this->view('post/detail', $data);
            return;
        }

        $comments = $this->commentModel->getCommentsByPostId($id);

        $isLiked = false;
        $isBookmarked = false;
        if (!empty($_SESSION['user_id'])) {
            $interactionModel = $this->model('Interaction_model');
            $isLiked = $interactionModel->isLiked((int)$_SESSION['user_id'], $id);
            $isBookmarked = $interactionModel->isBookmarked((int)$_SESSION['user_id'], $id);
        }

        $data = [
            'title' => ($post['title'] ?? 'Story') . ' - Blogggle',
            'post' => $post,
            'comments' => $comments,
            'is_liked' => $isLiked,
            'is_bookmarked' => $isBookmarked
        ];

        $this->view('post/detail', $data);
    }

    /**
     * Add a comment or reply to a post
     * POST /post/comment/{post_id}
     *
     * @param string|int $post_id
     */
    public function comment(string|int $post_id = 0): void
    {
        $post_id = (int)$post_id;

        // Ensure user is authenticated
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $post_id > 0) {
            $commentText = trim($_POST['comment'] ?? '');
            $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $redirectTo = trim($_POST['redirect_to'] ?? '');
            
            // Plain text only for comments
            $commentText = strip_tags($commentText);

            if (!empty($commentText)) {
                $this->commentModel->addComment([
                    'post_id' => $post_id,
                    'user_id' => (int)$_SESSION['user_id'],
                    'comment' => $commentText,
                    'parent_id' => $parentId
                ]);
            }

            // Redirect back to specific thread view if requested
            if (!empty($redirectTo)) {
                header('Location: ' . $redirectTo);
                exit;
            }
        }

        header('Location: ' . BASEURL . '/post/detail/' . $post_id);
        exit;
    }

    /**
     * Single Comment / Thread Focus View
     * GET /post/commentDetail/{id}
     *
     * @param string|int $id
     */
    public function commentDetail(string|int $id = 0): void
    {
        $id = (int)$id;

        if ($id <= 0) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $comment = $this->commentModel->getCommentById($id);

        if (!$comment) {
            http_response_code(404);
            $data = [
                'title' => 'Thread Not Found - Blogggle',
                'comment' => null,
                'post' => null,
                'parent_comment' => null,
                'replies' => []
            ];
            $this->view('post/comment_detail', $data);
            return;
        }

        // Fetch the parent story
        $post = $this->postModel->getPostById((int)$comment['post_id']);

        // Fetch direct child replies to this focused comment
        $replies = $this->commentModel->getRepliesByCommentId($id);

        // If this comment itself is a reply, fetch parent comment for conversation context
        $parentComment = null;
        if (!empty($comment['parent_id'])) {
            $parentComment = $this->commentModel->getCommentById((int)$comment['parent_id']);
        }

        $data = [
            'title' => 'Thread by @' . htmlspecialchars($comment['username']) . ' - Blogggle',
            'comment' => $comment,
            'post' => $post,
            'parent_comment' => $parentComment,
            'replies' => $replies
        ];

        $this->view('post/comment_detail', $data);
    }

    /**
     * Delete a story owned by the authenticated user
     * POST /post/delete/{id}
     *
     * @param string|int $id
     */
    public function delete(string|int $id = 0): void
    {
        // Enforce authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        // Enforce POST method
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $this->postModel->deletePost((int)$id, (int)$_SESSION['user_id']);

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer)) {
            // If the user deleted the post from its own detail page, redirect to /profile to avoid 404
            if (str_contains($referer, '/post/detail/' . (int)$id)) {
                header('Location: ' . BASEURL . '/profile');
            } else {
                header('Location: ' . $referer);
            }
        } else {
            header('Location: ' . BASEURL . '/profile');
        }
        exit;
    }
}
