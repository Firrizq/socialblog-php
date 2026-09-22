<?php

declare(strict_types=1);

/**
 * Front Controller
 * Single entry point for all incoming HTTP requests.
 */

// Start user session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Application Configuration
require_once __DIR__ . '/../app/config/config.php';

// Load Core Framework Classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/App.php';

// Instantiate Core Application Router
$app = new App();
