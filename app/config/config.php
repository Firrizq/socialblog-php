<?php

/**
 * Application Configuration
 * Social Blogging Platform (PHP Native MVC)
 */

// Application URL Root
// Automatically detects protocol, host/port (e.g., localhost:8080), and subdirectory
if (!defined('BASEURL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
    define('BASEURL', rtrim($protocol . $host . $basePath, '/'));
}

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'ccit_socialblog');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');
