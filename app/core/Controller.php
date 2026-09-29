<?php

declare(strict_types=1);

/**
 * Base Controller Class
 * Provides helpers for rendering views and instantiating models.
 */
class Controller
{
    /**
     * Synchronize and validate multi-account session state
     */
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Backward compatibility: Migrate legacy single-account session to multi-account schema
        if (isset($_SESSION['user_id']) && (empty($_SESSION['accounts']) || !is_array($_SESSION['accounts']))) {
            $legacyId = (int)$_SESSION['user_id'];
            $_SESSION['accounts'] = [
                $legacyId => [
                    'id' => $legacyId,
                    'name' => $_SESSION['name'] ?? $_SESSION['username'] ?? 'User',
                    'username' => $_SESSION['username'] ?? 'user',
                    'email' => $_SESSION['email'] ?? '',
                    'profile_picture' => $_SESSION['profile_picture'] ?? null
                ]
            ];
            $_SESSION['active_user_id'] = $legacyId;
        }

        if (!isset($_SESSION['accounts']) || !is_array($_SESSION['accounts'])) {
            $_SESSION['accounts'] = [];
        }

        // Validate and resolve the active account
        if (!empty($_SESSION['accounts'])) {
            $activeId = $_SESSION['active_user_id'] ?? null;
            if (!$activeId || (!isset($_SESSION['accounts'][$activeId]) && !isset($_SESSION['accounts'][(string)$activeId]) && !isset($_SESSION['accounts'][(int)$activeId]))) {
                $firstKey = array_key_first($_SESSION['accounts']);
                $activeId = (int)($_SESSION['accounts'][$firstKey]['id'] ?? $firstKey);
                $_SESSION['active_user_id'] = $activeId;
            }

            // AUTO-SYNC: Refresh account data from DB to eliminate any corrupted/polluted session data
            if (class_exists('Database')) {
                try {
                    require_once __DIR__ . '/../models/User.php';
                    $userModel = new User();
                    foreach ($_SESSION['accounts'] as $accKey => $accVal) {
                        $accId = (int)($accVal['id'] ?? $accKey);
                        if ($accId > 0) {
                            $freshUser = $userModel->getUserById($accId);
                            if ($freshUser) {
                                $avatar = !empty($freshUser['profile_picture']) ? $freshUser['profile_picture'] : null;
                                $_SESSION['accounts'][$accId] = [
                                    'id' => (int)$freshUser['id'],
                                    'name' => $freshUser['name'] ?? $freshUser['username'],
                                    'username' => $freshUser['username'],
                                    'email' => $freshUser['email'] ?? '',
                                    'profile_picture' => $avatar,
                                    'avatar' => $avatar
                                ];
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback to existing session data if database is temporarily unavailable
                }
            }

            // Sync active account data across top-level $_SESSION for backward compatibility
            $activeUser = $_SESSION['accounts'][$activeId] ?? $_SESSION['accounts'][(string)$activeId] ?? $_SESSION['accounts'][(int)$activeId] ?? null;
            if ($activeUser) {
                $avatar = !empty($activeUser['avatar']) ? $activeUser['avatar'] : (!empty($activeUser['profile_picture']) ? $activeUser['profile_picture'] : null);

                $_SESSION['user_id'] = (int)$activeUser['id'];
                $_SESSION['name'] = $activeUser['name'] ?? $activeUser['username'];
                $_SESSION['username'] = $activeUser['username'];
                $_SESSION['email'] = $activeUser['email'] ?? '';
                $_SESSION['profile_picture'] = $avatar;
                $_SESSION['avatar'] = $avatar;
                $_SESSION['user'] = $activeUser;
            }
        } else {
            unset(
                $_SESSION['active_user_id'],
                $_SESSION['user_id'],
                $_SESSION['name'],
                $_SESSION['username'],
                $_SESSION['email'],
                $_SESSION['profile_picture'],
                $_SESSION['user']
            );
        }
    }

    /**
     * Get the active account data from session
     *
     * @return array|null
     */
    public static function getActiveAccount(): ?array
    {
        self::initSession();
        $activeId = $_SESSION['active_user_id'] ?? null;
        if ($activeId && isset($_SESSION['accounts'])) {
            return $_SESSION['accounts'][$activeId] ?? $_SESSION['accounts'][(string)$activeId] ?? $_SESSION['accounts'][(int)$activeId] ?? null;
        }
        return null;
    }

    /**
     * Render a view file with optional data passed in
     * 
     * @param string $view Relative view path without extension (e.g., 'home/index' or 'posts/detail')
     * @param array $data Associative array of data to expose to the view
     */
    public function view(string $view, array $data = []): void
    {
        self::initSession();

        $activeAccount = self::getActiveAccount();

        // Always make multi-account data available to views
        if (!isset($data['active_user_id'])) {
            $data['active_user_id'] = $_SESSION['active_user_id'] ?? null;
        }
        if (!isset($data['accounts'])) {
            $data['accounts'] = $_SESSION['accounts'] ?? [];
        }
        if (!isset($data['currentUser']) && $activeAccount) {
            $data['currentUser'] = $activeAccount;
        }

        // Only default $data['user'] if not explicitly set (e.g. Profile views specify $data['profile_user'] or target $data['user'])
        if (!isset($data['user']) && $activeAccount) {
            $data['user'] = $activeAccount;
        }

        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewFile)) {
            // Extract data array so keys become direct variable names in the view ($data['title'] becomes $title and $data['title'])
            extract($data);
            require_once $viewFile;
        } else {
            http_response_code(404);
            die("View file [{$view}] does not exist at {$viewFile}");
        }
    }

    /**
     * Instantiate and return a Model class
     * 
     * @param string $model Model class name (e.g., 'Post_model' or 'User')
     * @return object
     */
    public function model(string $model): object
    {
        // Check for direct name, then _model suffix (e.g., Post -> Post_model)
        $candidates = [
            $model,
            $model . '_model',
            $model . 'Model'
        ];

        foreach ($candidates as $className) {
            $modelFile = __DIR__ . '/../models/' . $className . '.php';
            if (file_exists($modelFile)) {
                require_once $modelFile;
                if (class_exists($className)) {
                    return new $className();
                }
            }
        }

        die("Model file for [{$model}] does not exist in app/models/");
    }
}
