<?php

declare(strict_types=1);

/**
 * Auth Controller
 * Handles user registration, authentication (login), and session termination (logout).
 */
class Auth extends Controller
{
    private object $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Display Login page
     * GET /auth or /auth/index
     */
    public function index(): void
    {
        // If already authenticated and not explicitly adding an account, redirect to home
        if (isset($_SESSION['active_user_id']) && !isset($_GET['add']) && !isset($_GET['add_account'])) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $data = [
            'title' => !empty($_SESSION['accounts']) ? 'Add Account - Blogggle' : 'Sign In - Blogggle',
            'error' => '',
            'success' => $_SESSION['flash_success'] ?? '',
            'email' => '',
            'is_add_account' => isset($_GET['add']) || isset($_GET['add_account']) || !empty($_SESSION['accounts'])
        ];

        // Clear one-time flash message
        unset($_SESSION['flash_success']);

        $this->view('auth/login', $data);
    }

    /**
     * Display Add Existing Account page
     * GET /auth/add
     */
    public function add(): void
    {
        $data = [
            'title' => 'Add Account - Blogggle',
            'error' => '',
            'success' => '',
            'email' => '',
            'is_add_account' => true
        ];

        $this->view('auth/login', $data);
    }

    /**
     * Handle User Registration
     * GET /auth/register (view) | POST /auth/register (process)
     */
    public function register(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $data = [
                'title' => 'Register - Blogggle',
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'error' => ''
            ];

            // Form validation
            if (empty($name) || empty($username) || empty($email) || empty($password)) {
                $data['error'] = 'Please fill in all required fields.';
                $this->view('auth/register', $data);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'Please provide a valid email address.';
                $this->view('auth/register', $data);
                return;
            }

            if (strlen($password) < 6) {
                $data['error'] = 'Password must be at least 6 characters long.';
                $this->view('auth/register', $data);
                return;
            }

            if ($password !== $confirmPassword) {
                $data['error'] = 'Passwords do not match.';
                $this->view('auth/register', $data);
                return;
            }

            // Check for reserved system route usernames
            $reservedUsernames = [
                'home', 'post', 'auth', 'explore', 'history', 'bookmarks', 
                'notifications', 'action', 'upload', 'profile', 'repost', 
                'api', 'admin', 'root', 'user', 'users', 'login', 'logout', 
                'register', 'dashboard', 'settings', 'help', 'search', 'public'
            ];
            if (in_array(strtolower($username), $reservedUsernames, true)) {
                $data['error'] = 'That username is reserved for system routes. Please choose a different one.';
                $this->view('auth/register', $data);
                return;
            }

            // Check if email or username already exists
            if ($this->userModel->findUserByEmail($email)) {
                $data['error'] = 'An account with that email already exists.';
                $this->view('auth/register', $data);
                return;
            }

            if ($this->userModel->findUserByUsername($username)) {
                $data['error'] = 'That username is already taken.';
                $this->view('auth/register', $data);
                return;
            }

            // Create new user
            $registered = $this->userModel->register([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);

            if ($registered) {
                $_SESSION['flash_success'] = 'Account created successfully! Please login.';
                header('Location: ' . BASEURL . '/auth');
                exit;
            } else {
                $data['error'] = 'Failed to create account. Please try again.';
                $this->view('auth/register', $data);
                return;
            }
        }

        // GET Request: Render form
        $data = [
            'title' => 'Register - Blogggle',
            'name' => '',
            'username' => '',
            'email' => '',
            'error' => ''
        ];

        $this->view('auth/register', $data);
    }

    /**
     * Handle User Login submission
     * POST /auth/login
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $data = [
                'title' => !empty($_SESSION['accounts']) ? 'Add Account - Blogggle' : 'Sign In - Blogggle',
                'email' => $email,
                'error' => '',
                'success' => '',
                'is_add_account' => !empty($_SESSION['accounts'])
            ];

            if (empty($email) || empty($password)) {
                $data['error'] = 'Please enter both email and password.';
                $this->view('auth/login', $data);
                return;
            }

            // Verify user credentials
            $user = $this->userModel->login($email, $password);

            if ($user) {
                $userId = (int)$user['id'];

                // Initialize accounts array if not already present
                if (!isset($_SESSION['accounts']) || !is_array($_SESSION['accounts'])) {
                    $_SESSION['accounts'] = [];
                }

                $avatar = !empty($user['profile_picture']) ? $user['profile_picture'] : null;

                // STRICTLY assign fresh DB user data to its dedicated session key without merging
                $_SESSION['accounts'][$userId] = [
                    'id' => $userId,
                    'name' => $user['name'] ?? $user['username'],
                    'username' => $user['username'],
                    'email' => $user['email'] ?? '',
                    'profile_picture' => $avatar,
                    'avatar' => $avatar
                ];

                // Set as active user
                $_SESSION['active_user_id'] = $userId;

                // Sync top-level session variables for system-wide compatibility
                Controller::initSession();

                header('Location: ' . BASEURL . '/home');
                exit;
            } else {
                $data['error'] = 'Invalid email or password.';
                $this->view('auth/login', $data);
                return;
            }
        }

        // If accessed directly via GET:
        if (isset($_SESSION['active_user_id']) && !isset($_GET['add']) && !isset($_GET['add_account'])) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        header('Location: ' . BASEURL . '/auth' . (isset($_GET['add']) ? '?add=1' : ''));
        exit;
    }

    /**
     * Switch active account
     * GET /auth/switchAccount/{target_user_id}
     *
     * @param string|int $targetUserId
     */
    public function switchAccount(string|int $targetUserId = 0): void
    {
        Controller::initSession();

        $targetId = (int)$targetUserId;

        // Check if target user ID exists in $_SESSION['accounts']
        if ($targetId > 0 && (isset($_SESSION['accounts'][$targetId]) || isset($_SESSION['accounts'][(string)$targetId]))) {
            $_SESSION['active_user_id'] = $targetId;
            Controller::initSession();
        }

        header('Location: ' . BASEURL . '/home');
        exit;
    }

    /**
     * Alias for switchAccount
     * GET /auth/switch/{target_user_id}
     *
     * @param string|int $targetUserId
     */
    public function switch(string|int $targetUserId = 0): void
    {
        $this->switchAccount($targetUserId);
    }

    /**
     * Log the active user out of the multi-account session
     * GET /auth/logout
     */
    public function logout(): void
    {
        Controller::initSession();

        $activeId = (int)($_SESSION['active_user_id'] ?? 0);

        // Unset the current active_user_id from $_SESSION['accounts']
        if ($activeId > 0) {
            unset($_SESSION['accounts'][$activeId], $_SESSION['accounts'][(string)$activeId]);
        }

        // If there are still other accounts left in the array, switch to the first available
        if (!empty($_SESSION['accounts'])) {
            $firstKey = array_key_first($_SESSION['accounts']);
            $_SESSION['active_user_id'] = (int)($_SESSION['accounts'][$firstKey]['id'] ?? $firstKey);
            Controller::initSession();

            header('Location: ' . BASEURL . '/home');
            exit;
        }

        // If no accounts are left, completely destroy session
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header('Location: ' . BASEURL . '/auth');
        exit;
    }

    /**
     * Completely log out of all accounts
     * GET /auth/logoutAll
     */
    public function logoutAll(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header('Location: ' . BASEURL . '/auth');
        exit;
    }
}
