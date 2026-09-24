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
                'title' => 'User Not Found - Blogggle',
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
            'title' => '@' . $profileUser['username'] . ' - Profile | Blogggle',
            'profile_user' => $profileUser,
            'posts' => $posts,
            'is_following' => $isFollowing,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts
        ];

        $this->view('profile/index', $data);
    }

    /**
     * Display the Edit Profile form
     */
    public function edit(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $user = $this->userModel->getUserProfile($_SESSION['username']);
        if (!$user) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_error']);

        $data = [
            'title' => 'Edit Profile - Blogggle',
            'user' => $user,
            'error' => $error
        ];

        $this->view('profile/edit', $data);
    }

    /**
     * Handle Profile Update submission
     */
    public function update(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
            $_SESSION['flash_error'] = 'Upload failed: Total file size exceeds server limits.';
            header('Location: ' . BASEURL . '/profile/edit');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/profile/edit');
            exit;
        }

        // Check individual file upload errors
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_OK && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash_error'] = 'Banner upload failed. Max size is 10MB.';
            header('Location: ' . BASEURL . '/profile/edit');
            exit;
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_OK && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash_error'] = 'Avatar upload failed. Max size is 10MB.';
            header('Location: ' . BASEURL . '/profile/edit');
            exit;
        }

        $uploadBasePath = dirname(__DIR__, 2) . '/public';
        $avatarDir = $uploadBasePath . '/uploads/avatars/';
        $bannerDir = $uploadBasePath . '/uploads/banners/';

        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }
        if (!is_dir($bannerDir)) {
            mkdir($bannerDir, 0755, true);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $updateData = [
            'bio' => strip_tags(trim($_POST['bio'] ?? '')),
            'location' => strip_tags(trim($_POST['location'] ?? '')),
            'profile_link' => filter_var(trim($_POST['profile_link'] ?? ''), FILTER_SANITIZE_URL),
            'tipping_link' => filter_var(trim($_POST['tipping_link'] ?? ''), FILTER_SANITIZE_URL),
        ];

        // Process Avatar Upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['avatar']['tmp_name'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpName);
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

            if (in_array($mime, $allowedMimes, true) && in_array($ext, $allowedExtensions, true)) {
                $filename = 'avatar_' . $_SESSION['user_id'] . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($tmpName, $avatarDir . $filename)) {
                    $updateData['profile_picture'] = '/uploads/avatars/' . $filename;
                }
            }
        }

        // Process Banner Upload
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['banner']['tmp_name'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpName);
            $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));

            if (in_array($mime, $allowedMimes, true) && in_array($ext, $allowedExtensions, true)) {
                $filename = 'banner_' . $_SESSION['user_id'] . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($tmpName, $bannerDir . $filename)) {
                    $updateData['banner_picture'] = '/uploads/banners/' . $filename;
                }
            }
        }

        if (isset($updateData['profile_picture'])) {
            $_SESSION['profile_picture'] = $updateData['profile_picture'];
        }

        $this->userModel->updateProfile((int)$_SESSION['user_id'], $updateData);

        header('Location: ' . BASEURL . '/profile');
        exit;
    }

    /**
     * Remove the current user's profile picture
     */
    public function removeAvatar(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $user = $this->userModel->getUserProfile($_SESSION['username']);

        if (!empty($user['profile_picture'])) {
            $filePath = dirname(__DIR__, 2) . '/public' . $user['profile_picture'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        $this->userModel->removeAvatar((int)$_SESSION['user_id']);
        unset($_SESSION['profile_picture']);

        header('Location: ' . BASEURL . '/profile/edit');
        exit;
    }
}


