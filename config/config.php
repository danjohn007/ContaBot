<?php
/**
 * Main Configuration File
 * Sistema Básico Contable - ContaBot
 */

// Session configuration - start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Base URL auto-detection
function getBaseUrl() {
    if (!isset($_SERVER['HTTP_HOST'])) {
        // CLI or testing environment
        return 'http://localhost:8000/';
    }
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = str_replace(basename($script), '', $script);
    return $protocol . '://' . $host . $path;
}

// Constants
define('BASE_URL', getBaseUrl());
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Database
require_once ROOT_PATH . '/config/database.php';

// Timezone
date_default_timezone_set('America/Mexico_City');

// Include models and controllers
function autoload($className) {
    $paths = [
        ROOT_PATH . '/models/' . $className . '.php',
        ROOT_PATH . '/controllers/' . $className . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
}

spl_autoload_register('autoload');

// Security functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login');
        exit();
    }
}
?>