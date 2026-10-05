<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Tag_model.php';

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

        // Handle post_max_size overflow gracefully
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $postMax = ini_get('post_max_size') ?: 'unknown';
            $data = [
                'title' => 'Create New Post - Blogggle',
                'post_title' => '',
                'content' => '',
                'error' => "The uploaded file exceeds the server post limit (post_max_size: {$postMax}). Please choose a smaller file."
            ];
            $this->view('post/create', $data);
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $rawContent = trim($_POST['content'] ?? '');

            // Allow safe rich-text HTML tags from Quill while stripping dangerous tags (scripts, iframes, etc.)
            $allowedTags = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6><blockquote><pre><code><ol><ul><li><a><span><img>';
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

            $uploadError = null;
            $coverImage = $this->handleUploadedCover($uploadError);

            if ($uploadError !== null) {
                $data['error'] = $uploadError;
                $this->view('post/create', $data);
                return;
            }

            $status = ($_POST['action'] ?? 'publish') === 'draft' ? 'draft' : 'published';

            // Save post
            $newPostId = $this->postModel->createPost([
                'user_id' => $_SESSION['user_id'],
                'post_type' => 'story',
                'title' => $title,
                'content' => $content,
                'cover_image' => $coverImage,
                'status' => $status
            ]);

            if ($newPostId) {
                (new Tag_model())->processTags((int)$newPostId, $content);
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
     * Alias for create to support /post/store route
     */
    public function store(): void
    {
        $this->create();
    }

    /**
     * Alias for edit to support /post/update/{id} route
     */
    public function update(string|int $id = 0): void
    {
        $this->edit($id);
    }

    /**
     * Edit an existing story or draft
     * GET /post/edit/{id} | POST /post/edit/{id}
     *
     * @param string|int $id
     */
    public function edit(string|int $id = 0): void
    {
        // Enforce authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $id = (int)$id;
        $post = $this->postModel->getPostById($id);

        if (!$post || (int)$post['user_id'] !== (int)$_SESSION['user_id']) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $isNote = (($post['post_type'] ?? '') === 'note');

        // Handle post_max_size overflow gracefully
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $postMax = ini_get('post_max_size') ?: 'unknown';
            $viewName = $isNote ? 'post/edit_note' : 'post/edit';
            $viewTitle = $isNote ? 'Edit Note' : 'Edit Story';
            $this->view($viewName, [
                'title' => $viewTitle,
                'post' => $post,
                'error' => "The uploaded file exceeds the server post limit (post_max_size: {$postMax}). Please choose a smaller file."
            ]);
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $rawContent = trim($_POST['content'] ?? '');
            $coverImage = trim($_POST['cover_image'] ?? '');
            $status = ($_POST['action'] ?? 'publish') === 'draft' ? 'draft' : 'published';

            if ($isNote) {
                $title = null;
                // Reverse <br> to newlines, strip any stray HTML, then re-apply nl2br for safe saving
                $cleanText = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $rawContent));
                $content = nl2br(htmlspecialchars($cleanText, ENT_QUOTES, 'UTF-8'));
            } else {
                $title = trim($_POST['title'] ?? '');
                $allowedTags = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6><blockquote><pre><code><ol><ul><li><a><span><img>';
                $content = strip_tags($rawContent, $allowedTags);
            }

            $uploadError = null;
            $uploadedCover = $this->handleUploadedCover($uploadError);

            if ($uploadError !== null) {
                $viewName = $isNote ? 'post/edit_note' : 'post/edit';
                $viewTitle = $isNote ? 'Edit Note' : 'Edit Story';
                $this->view($viewName, [
                    'title' => $viewTitle,
                    'post' => $post,
                    'error' => $uploadError
                ]);
                return;
            }

            $finalStoryCover = $post['cover_image'] ?? null;
            if ($uploadedCover !== null) {
                $finalStoryCover = $uploadedCover;
            } elseif (isset($_POST['existing_cover'])) {
                $finalStoryCover = !empty($_POST['existing_cover']) ? trim($_POST['existing_cover']) : null;
            }

            $this->postModel->updatePost($id, (int)$_SESSION['user_id'], [
                'title' => $title,
                'content' => $content,
                'cover_image' => $isNote ? ($coverImage ?: null) : $finalStoryCover,
                'status' => $status
            ]);

            (new Tag_model())->processTags($id, $content);

            if ($status === 'published') {
                $postType = strtolower($post['post_type'] ?? 'story');
                if (!empty($post['uid']) && !empty($post['username'])) {
                    header('Location: ' . BASEURL . '/' . urlencode($post['username']) . '/' . $postType . '/' . $post['uid']);
                } else {
                    header('Location: ' . BASEURL . '/post/detail/' . $id);
                }
            } else {
                header('Location: ' . BASEURL . '/profile');
            }
            exit;
        }

        $viewName = $isNote ? 'post/edit_note' : 'post/edit';
        $viewTitle = $isNote ? 'Edit Note' : 'Edit Story';

        $this->view($viewName, [
            'title' => $viewTitle,
            'post' => $post,
            'error' => ''
        ]);
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
            $coverImage = trim($_POST['cover_image'] ?? '');

            if (!empty($rawContent) || !empty($coverImage)) {
                // Sanitize plain text and convert line breaks
                $content = nl2br(htmlspecialchars($rawContent, ENT_QUOTES, 'UTF-8'));

                $status = ($_POST['action'] ?? 'publish') === 'draft' ? 'draft' : 'published';

                $newPostId = $this->postModel->createPost([
                    'user_id' => (int)$_SESSION['user_id'],
                    'post_type' => 'note',
                    'title' => null,
                    'content' => $content,
                    'cover_image' => $coverImage ?: null,
                    'status' => $status
                ]);

                if ($newPostId) {
                    (new Tag_model())->processTags((int)$newPostId, $content);
                }
            }
        }

        header('Location: ' . BASEURL . '/home');
        exit;
    }

    /**
     * Single Post Detail Page
     * GET /{username}/{note-or-story}/{uid} or /post/detail/{uid}
     *
     * @param string|int $uid
     */
    public function detail(string|int $uid = ''): void
    {
        $uid = trim((string)$uid);

        if (empty($uid)) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        // Fetch post by 12-digit UID
        $post = $this->postModel->getPostByUid($uid);

        // Fallback for legacy numeric IDs
        if (!$post && is_numeric($uid)) {
            $post = $this->postModel->getPostById((int)$uid);
        }

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

        $postId = (int)$post['id'];
        $comments = $this->commentModel->getCommentsByPostId($postId);

        $isLiked = false;
        $isBookmarked = false;
        $isReposted = false;
        $readingProgress = 0;
        $currentUserId = $_SESSION['active_user_id'] ?? $_SESSION['user_id'] ?? null;
        if (!empty($currentUserId)) {
            $interactionModel = $this->model('Interaction_model');
            $isLiked = $interactionModel->isLiked((int)$currentUserId, $postId);
            $isBookmarked = $interactionModel->isBookmarked((int)$currentUserId, $postId);
            $isReposted = $interactionModel->isReposted((int)$currentUserId, $postId);
            // Record reading history on view
            $historyModel = $this->model('History_model');
            $readingProgress = $historyModel->getProgress((int)$currentUserId, $postId);
            if ($readingProgress < 10) {
                $historyModel->recordProgress((int)$currentUserId, $postId, 10);
                $readingProgress = 10;
            }
        }

        $data = [
            'title' => ($post['title'] ?? 'Story') . ' - Blogggle',
            'post' => $post,
            'comments' => $comments,
            'is_liked' => $isLiked,
            'is_bookmarked' => $isBookmarked,
            'is_reposted' => $isReposted,
            'reading_progress' => $readingProgress
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

        $post = $this->postModel->getPostById($post_id);
        if ($post && !empty($post['username']) && !empty($post['uid'])) {
            $postType = strtolower($post['post_type'] ?? 'story');
            header('Location: ' . BASEURL . '/' . $post['username'] . '/' . $postType . '/' . $post['uid']);
        } else {
            header('Location: ' . BASEURL . '/home');
        }
        exit;
    }

    /**
     * Single Comment / Thread Focus View
     * GET /{username}/comment/{uid} or /post/comment_detail/{uid}
     *
     * @param string|int $uid
     */
    public function comment_detail(string|int $uid = ''): void
    {
        $uid = trim((string)$uid);

        if (empty($uid)) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        // Fetch comment by 12-digit UID
        $comment = $this->commentModel->getCommentByUid($uid);

        // Fallback for legacy numeric IDs
        if (!$comment && is_numeric($uid)) {
            $comment = $this->commentModel->getCommentById((int)$uid);
        }

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
        $replies = $this->commentModel->getRepliesByCommentId((int)$comment['id']);

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
     * Backward-compatible alias for commentDetail
     *
     * @param string|int $uid
     */
    public function commentDetail(string|int $uid = ''): void
    {
        $this->comment_detail($uid);
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

        $post = $this->postModel->getPostById((int)$id);
        $this->postModel->deletePost((int)$id, (int)$_SESSION['user_id']);

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer)) {
            // If the user deleted the post from its own detail page, redirect to profile to avoid 404
            $isOnDetailPage = str_contains($referer, '/post/detail/' . (int)$id) || (!empty($post['uid']) && str_contains($referer, '/' . $post['uid']));
            if ($isOnDetailPage) {
                header('Location: ' . BASEURL . '/' . (!empty($post['username']) ? urlencode($post['username']) : 'profile'));
            } else {
                header('Location: ' . $referer);
            }
        } else {
            header('Location: ' . BASEURL . '/profile');
        }
        exit;
    }

    /**
     * Helper to process single cover image or video upload from $_FILES['images'] or $_FILES['media']
     */
    private function handleUploadedCover(?string &$uploadError = null): ?string
    {
        $fileInfo = null;

        if (!empty($_FILES['images']['name'])) {
            if (is_array($_FILES['images']['name'])) {
                if (!empty($_FILES['images']['name'][0])) {
                    $fileInfo = [
                        'name' => $_FILES['images']['name'][0],
                        'tmp_name' => $_FILES['images']['tmp_name'][0],
                        'error' => $_FILES['images']['error'][0],
                        'size' => $_FILES['images']['size'][0]
                    ];
                }
            } else {
                $fileInfo = $_FILES['images'];
            }
        } elseif (!empty($_FILES['media']['name'])) {
            $fileInfo = $_FILES['media'];
        }

        if (!$fileInfo || empty($fileInfo['name'])) {
            return null;
        }

        // Handle upload errors
        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            $uploadError = match ($fileInfo['error']) {
                UPLOAD_ERR_INI_SIZE => 'Uploaded file exceeds the upload_max_filesize directive in php.ini (' . (ini_get('upload_max_filesize') ?: 'unknown') . ').',
                UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeds the MAX_FILE_SIZE directive in the form.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => null,
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by a PHP extension.',
                default => 'Upload error code: ' . $fileInfo['error']
            };
            return null;
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $fileName = $fileInfo['name'];
        $fileTmp = $fileInfo['tmp_name'];
        $fileSize = (int)$fileInfo['size'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedVideoExts = ['mp4', 'webm', 'ogg', 'mov'];
        $allowedExts = array_merge($allowedImageExts, $allowedVideoExts);

        if (!in_array($ext, $allowedExts, true)) {
            $uploadError = 'Invalid file extension: .' . $ext . '. Allowed: JPG, PNG, GIF, WEBP, MP4, WEBM, OGG, MOV.';
            return null;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $fileTmp) : mime_content_type($fileTmp);
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-m4v'];
        $allowedMimes = array_merge($allowedImageMimes, $allowedVideoMimes);

        if (!in_array($mimeType, $allowedMimes, true)) {
            $uploadError = 'Invalid file type (' . htmlspecialchars((string)$mimeType) . '). Please upload a valid image or video (e.g. video/mp4, video/webm, video/quicktime).';
            return null;
        }

        $isVideo = in_array($ext, $allowedVideoExts, true) || in_array($mimeType, $allowedVideoMimes, true);
        $maxSizeBytes = 200000000; // 200MB limit constraint

        if ($fileSize > $maxSizeBytes) {
            $uploadedMb = round($fileSize / (1024 * 1024), 1);
            $uploadError = "File is too large ({$uploadedMb}MB). Maximum allowed upload size is 200MB.";
            return null;
        }

        $subFolder = $isVideo ? 'videos' : 'images';
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/' . $subFolder;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = 'cover_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;

        if (@move_uploaded_file($fileTmp, $dest)) {
            return '/uploads/' . $subFolder . '/' . $filename;
        }

        $uploadError = 'Failed to save uploaded file to destination. Check folder permissions.';
        return null;
    }
}
