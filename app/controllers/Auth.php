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
        // If already authenticated, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $data = [
            'title' => 'Sign In - Blogggle',
            'error' => '',
            'success' => $_SESSION['flash_success'] ?? '',
            'email' => ''
        ];

        // Clear one-time flash message
        unset($_SESSION['flash_success']);

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
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $data = [
                'title' => 'Login - Social Blog',
                'email' => $email,
                'error' => '',
                'success' => ''
            ];

            if (empty($email) || empty($password)) {
                $data['error'] = 'Please enter both email and password.';
                $this->view('auth/login', $data);
                return;
            }

            // Verify user credentials
            $user = $this->userModel->login($email, $password);

            if ($user) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile_picture'] = $user['profile_picture'] ?? null;

                header('Location: ' . BASEURL . '/home');
                exit;
            } else {
                $data['error'] = 'Invalid email or password.';
                $this->view('auth/login', $data);
                return;
            }
        }

        // If accessed directly via GET, forward to index
        header('Location: ' . BASEURL . '/auth');
        exit;
    }

    /**
     * Log the user out and destroy session
     * GET /auth/logout
     */
    public function logout(): void
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
