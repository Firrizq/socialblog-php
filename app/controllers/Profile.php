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
    private object $commentModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->postModel = $this->model('Post_model');
        $this->commentModel = $this->model('Comment_model');
    }

    /**
     * Display a user's public profile and authored stories
     * Supports:
     *   /{username} -> Profile@index($username)
     *   /profile    -> Redirects to /{active_username} or /auth
     *
     * @param string $username
     */
    public function index(string $username = ''): void
    {
        $username = trim($username);

        // If no username is provided in URL (/profile)
        if (empty($username)) {
            $activeUserId = $_SESSION['active_user_id'] ?? $_SESSION['user_id'] ?? null;
            if (!empty($activeUserId)) {
                $sessionUser = $_SESSION['accounts'][$activeUserId] ?? null;
                $activeUsername = $sessionUser['username'] ?? $_SESSION['username'] ?? null;
                if (!empty($activeUsername)) {
                    $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
                    header('Location: ' . BASEURL . '/' . urlencode($activeUsername) . $queryString);
                    exit;
                }
            }

            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        // Clean username from leading '@'
        $username = ltrim($username, '@');

        // Fetch user profile by string username
        $profileUser = $this->userModel->getUserProfile($username);

        // If not found, attempt fallback by integer ID if numeric
        if (!$profileUser && is_numeric($username)) {
            $profileUser = $this->userModel->getUserById((int)$username);
        }

        if (!$profileUser) {
            http_response_code(404);
            $data = [
                'title' => 'User Not Found - Blogggle',
                'username' => $username,
                'profile_user' => null,
                'user' => null,
                'is_owner' => false,
                'posts' => [],
                'replies' => [],
                'media_posts' => [],
                'active_tab' => 'posts'
            ];
            $this->view('profile/index', $data);
            return;
        }

        // Determine if logged-in active_user_id owns this profile to show "Edit profile"
        $activeUserId = $_SESSION['active_user_id'] ?? $_SESSION['user_id'] ?? null;
        $isOwner = (!empty($activeUserId) && (int)$activeUserId === (int)$profileUser['id']);
        $currentUserId = !empty($activeUserId) ? (int)$activeUserId : null;

        $tab = $_GET['tab'] ?? 'posts';
        $allowedTabs = ['posts', 'replies', 'reposts', 'media'];
        if ($isOwner) {
            $allowedTabs[] = 'drafts';
        }
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'posts';
        }

        $posts = [];
        $replies = [];
        $mediaPosts = [];
        $drafts = [];

        if ($tab === 'reposts') {
            $posts = $this->postModel->getRepostedPostsByUser((int)$profileUser['id'], $currentUserId);
        } elseif ($tab === 'replies') {
            $replies = $this->commentModel->getRepliesByUser((int)$profileUser['id']);
        } elseif ($tab === 'media') {
            $mediaPosts = $this->postModel->getMediaPostsByUser((int)$profileUser['id'], $isOwner, $currentUserId);
        } elseif ($tab === 'drafts' && $isOwner) {
            $drafts = $this->postModel->getDraftsByUser((int)$profileUser['id']);
        } else {
            $posts = $this->postModel->getPostsByUser((int)$profileUser['id'], $isOwner, $currentUserId);
        }

        $isFollowing = false;
        $likedPosts = [];
        $bookmarkedPosts = [];
        $repostedPosts = [];
        if ($currentUserId) {
            $interactionModel = $this->model('Interaction_model');
            $isFollowing = $interactionModel->isFollowing($currentUserId, (int)$profileUser['id']);
            $likedPosts = $interactionModel->getUserLikedPostIds($currentUserId);
            $bookmarkedPosts = $interactionModel->getUserBookmarkedPostIds($currentUserId);
            $repostedPosts = $interactionModel->getUserRepostedPostIds($currentUserId);
        }

        $data = [
            'title' => ($profileUser['name'] ?? $profileUser['username']) . ' (@' . $profileUser['username'] . ') - Profile | Blogggle',
            'profile_user' => $profileUser,
            'user' => $profileUser,
            'is_owner' => $isOwner,
            'is_following' => $isFollowing,
            'posts' => $posts,
            'replies' => $replies,
            'media_posts' => $mediaPosts,
            'drafts' => $drafts,
            'active_tab' => $tab,
            'liked_posts' => $likedPosts,
            'bookmarked_posts' => $bookmarkedPosts,
            'reposted_posts' => $repostedPosts
        ];

        $this->view('profile/index', $data);
    }

    /**
     * Backward-compatible alias for /profile/user/{username}
     */
    public function user(string $username = ''): void
    {
        $this->index($username);
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

        $submittedName = strip_tags(trim($_POST['name'] ?? ''));

        $updateData = [
            'bio' => strip_tags(trim($_POST['bio'] ?? '')),
            'location' => strip_tags(trim($_POST['location'] ?? '')),
            'profile_link' => filter_var(trim($_POST['profile_link'] ?? ''), FILTER_SANITIZE_URL),
            'tipping_link' => filter_var(trim($_POST['tipping_link'] ?? ''), FILTER_SANITIZE_URL),
        ];

        if (!empty($submittedName)) {
            $updateData['name'] = $submittedName;
        }

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

        $uid = (int)$_SESSION['user_id'];
        if (!empty($updateData['name'])) {
            $_SESSION['name'] = $updateData['name'];
            if (isset($_SESSION['accounts'][$uid])) {
                $_SESSION['accounts'][$uid]['name'] = $updateData['name'];
            }
        }

        if (isset($updateData['profile_picture'])) {
            $_SESSION['profile_picture'] = $updateData['profile_picture'];
            $_SESSION['avatar'] = $updateData['profile_picture'];
            if (isset($_SESSION['accounts'][$uid])) {
                $_SESSION['accounts'][$uid]['profile_picture'] = $updateData['profile_picture'];
                $_SESSION['accounts'][$uid]['avatar'] = $updateData['profile_picture'];
            }
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
        unset($_SESSION['profile_picture'], $_SESSION['avatar']);
        $uid = (int)$_SESSION['user_id'];
        if (isset($_SESSION['accounts'][$uid])) {
            $_SESSION['accounts'][$uid]['profile_picture'] = null;
            $_SESSION['accounts'][$uid]['avatar'] = null;
        }

        header('Location: ' . BASEURL . '/profile/edit');
        exit;
    }

    /**
     * Remove the current user's banner picture
     */
    public function removeBanner(): void
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $user = $this->userModel->getUserProfile($_SESSION['username']);

        if (!empty($user['banner_picture'])) {
            $filePath = dirname(__DIR__, 2) . '/public' . $user['banner_picture'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        $this->userModel->removeBanner((int)$_SESSION['user_id']);
        header('Location: ' . BASEURL . '/profile/edit');
        exit;
    }

    /**
     * Return Profile Hover Card Partial
     * Supports:
     *   /profile/hoverCard/{username}
     *   /profile/hovercard/{username}
     *
     * @param string $username
     */
    public function hoverCard(string $username = ''): void
    {
        $username = trim(ltrim($username, '@'));

        if (empty($username)) {
            http_response_code(400);
            echo '<div class="p-3 text-xs text-on-surface-variant">User not specified</div>';
            return;
        }

        $profileUser = $this->userModel->getUserProfile($username);
        if (!$profileUser && is_numeric($username)) {
            $profileUser = $this->userModel->getUserById((int)$username);
        }

        if (!$profileUser) {
            http_response_code(404);
            echo '<div class="p-3 text-xs text-on-surface-variant">User not found</div>';
            return;
        }

        $activeUserId = $_SESSION['active_user_id'] ?? $_SESSION['user_id'] ?? null;
        $currentUserId = !empty($activeUserId) ? (int)$activeUserId : null;
        $isOwner = ($currentUserId && (int)$currentUserId === (int)$profileUser['id']);

        $isFollowing = false;
        if ($currentUserId && !$isOwner) {
            $interactionModel = $this->model('Interaction_model');
            $isFollowing = $interactionModel->isFollowing($currentUserId, (int)$profileUser['id']);
        }

        $data = [
            'user' => $profileUser,
            'is_following' => $isFollowing,
            'is_owner' => $isOwner,
            'current_user_id' => $currentUserId
        ];

        // Support JSON response if requested
        if ((isset($_GET['format']) && $_GET['format'] === 'json') || 
            (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'user' => $profileUser,
                'is_following' => $isFollowing,
                'is_owner' => $isOwner
            ]);
            return;
        }

        // Render HTML Partial
        header('Content-Type: text/html; charset=utf-8');
        $this->view('components/hover_card', $data);
        exit;
    }

    /**
     * Get JSON list of followers for a user
     * GET /profile/followers/{username}
     *
     * @param string $username
     */
    public function followers(string $username = ''): void
    {
        $username = ltrim(trim($username), '@');
        if (empty($username)) {
            $username = $_SESSION['username'] ?? '';
        }

        $profileUser = $this->userModel->getUserProfile($username);
        if (!$profileUser && is_numeric($username)) {
            $profileUser = $this->userModel->getUserById((int)$username);
        }

        if (!$profileUser) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'User not found', 'users' => []]);
            exit;
        }

        $currentUserId = $_SESSION['active_user_id'] ?? $_SESSION['user_id'] ?? null;
        $users = $this->userModel->getFollowers((int)$profileUser['id'], $currentUserId ? (int)$currentUserId : null);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => (int)$profileUser['id'],
                'username' => $profileUser['username'],
                'name' => $profileUser['name'] ?? $profileUser['username']
            ],
            'users' => $users
        ]);
        exit;
    }

    /**
     * Get JSON list of following for a user
     * GET /profile/following/{username}
     *
     * @param string $username
     */
    public function following(string $username = ''): void
    {
        $username = ltrim(trim($username), '@');
        if (empty($username)) {
            $username = $_SESSION['username'] ?? '';
        }

        $profileUser = $this->userModel->getUserProfile($username);
        if (!$profileUser && is_numeric($username)) {
            $profileUser = $this->userModel->getUserById((int)$username);
        }

        if (!$profileUser) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'User not found', 'users' => []]);
            exit;
        }

        $currentUserId = $_SESSION['active_user_id'] ?? $_SESSION['user_id'] ?? null;
        $users = $this->userModel->getFollowing((int)$profileUser['id'], $currentUserId ? (int)$currentUserId : null);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => (int)$profileUser['id'],
                'username' => $profileUser['username'],
                'name' => $profileUser['name'] ?? $profileUser['username']
            ],
            'users' => $users
        ]);
        exit;
    }
}

