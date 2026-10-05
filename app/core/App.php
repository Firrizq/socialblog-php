<?php

declare(strict_types=1);

/**
 * Core Application Router Class
 * Parses clean URLs and executes appropriate Controller, Method and Parameters.
 * 
 * Supports both standard MVC patterns:
 *   /controller/method/param1/...
 * 
 * And Twitter/Medium vanity patterns:
 *   /{username}                              -> Profile@index($username)
 *   /{username}/note/{uid}                   -> Post@detail($uid)
 *   /{username}/story/{uid}                  -> Post@detail($uid)
 *   /{username}/comment/{uid}                -> Post@comment_detail($uid)
 */
class App
{
    protected mixed $controller = 'Home';
    protected string $method = 'index';
    protected array $params = [];

    public function __construct()
    {
        if (!headers_sent()) {
            header("Cross-Origin-Opener-Policy: same-origin");
            header("Cross-Origin-Embedder-Policy: credentialless");
        }

        $url = $this->parseURL();

        // 1. Determine Controller & Route Type
        if (!empty($url[0])) {
            $controllerName = ucfirst($url[0]);
            $controllerPath = __DIR__ . '/../controllers/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                // Standard MVC route: /controller/method/param1/...
                $this->controller = $controllerName;
                unset($url[0]);

                require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
                $this->controller = new $this->controller();

                if (isset($url[1])) {
                    if (method_exists($this->controller, $url[1])) {
                        $this->method = $url[1];
                        unset($url[1]);
                    } else {
                        http_response_code(404);
                        $controllerClass = get_class($this->controller);
                        die("404 Not Found: Method [{$url[1]}] not found in Controller [{$controllerClass}].");
                    }
                }

                $this->params = $url ? array_values($url) : [];
            } else {
                // Twitter / Medium Vanity Routing
                $username = ltrim($url[0], '@');
                $action = isset($url[1]) ? strtolower($url[1]) : '';
                $uid = $url[2] ?? '';

                if ($action === 'note' || $action === 'story') {
                    // Map /{username}/note/{uid} or /{username}/story/{uid} -> Post@detail($uid)
                    $this->controller = 'Post';
                    $this->method = 'detail';
                    $this->params = [$uid];
                } elseif ($action === 'comment') {
                    // Map /{username}/comment/{uid} -> Post@comment_detail($uid)
                    $this->controller = 'Post';
                    $this->method = 'comment_detail';
                    $this->params = [$uid];
                } else {
                    // Map /{username} -> Profile@index($username)
                    $this->controller = 'Profile';
                    $this->method = 'index';
                    $this->params = [$username];
                }

                require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
                $this->controller = new $this->controller();
            }
        } else {
            // Default Root Route (Home@index)
            require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
            $this->controller = new $this->controller();
        }

        // Execute Controller & Method with Params
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Parse and sanitize the incoming URL query parameter
     * 
     * @return array
     */
    public function parseURL(): array
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        // Support PHP built-in web server or direct REQUEST_URI routing
        if (isset($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
            $path = trim($path, '/');
            if (!empty($path)) {
                $path = filter_var($path, FILTER_SANITIZE_URL);
                return explode('/', $path);
            }
        }

        return [];
    }
}
