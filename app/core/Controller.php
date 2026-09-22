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
