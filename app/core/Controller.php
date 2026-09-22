<?php

declare(strict_types=1);

/**
 * Base Controller Class
 * Provides helpers for rendering views and instantiating models.
 */
class Controller
{
    /**
     * Render a view file with optional data passed in
     * 
     * @param string $view Relative view path without extension (e.g., 'home/index' or 'posts/detail')
     * @param array $data Associative array of data to expose to the view
     */
    public function view(string $view, array $data = []): void
    {
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
        $modelFile = __DIR__ . '/../models/' . $model . '.php';

        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }

        die("Model file [{$model}] does not exist at {$modelFile}");
    }
}
