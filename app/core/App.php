<?php

declare(strict_types=1);

/**
 * Core Application Router Class
 * Parses the clean URL and executes the appropriate Controller and Method with parameters.
 * Format: /controller/method/param1/param2/...
 */
class App
{
    protected mixed $controller = 'Home';
    protected string $method = 'index';
    protected array $params = [];

    public function __construct()
    {
        $url = $this->parseURL();

        // 1. Determine Controller
        if (!empty($url[0])) {
            $controllerName = ucfirst($url[0]);
            $controllerPath = __DIR__ . '/../controllers/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                // If controller does not exist, return 404 HTTP status
                http_response_code(404);
                die("404 Not Found: Controller [{$controllerName}] not found.");
            }
        }

        // Require the resolved controller file
        require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller();

        // 2. Determine Method
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

        // 3. Determine Parameters
        $this->params = $url ? array_values($url) : [];

        // 4. Run Controller & Method with Params
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

        return [];
    }
}
