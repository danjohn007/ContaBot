<?php
/**
 * Application Configuration
 * ContaBot - Sistema Básico Contable
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/Mexico_City');

// Application settings
define('APP_NAME', 'ContaBot');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // change to 'production' in live environment

// Auto-detect base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script);
    
    // Clean path
    if ($path === '/' || $path === '\\') {
        $path = '';
    }
    
    return $protocol . $host . $path;
}

define('BASE_URL', getBaseUrl());
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Security settings
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
define('PASSWORD_MIN_LENGTH', 6);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_CAPTURISTA', 'capturista');
define('ROLE_CONSULTA', 'consulta');

// Transaction types
define('TRANSACTION_INCOME', 'income');
define('TRANSACTION_EXPENSE', 'expense');

// Transaction categories
define('CATEGORY_PERSONAL', 'personal');
define('CATEGORY_BUSINESS', 'business');
define('CATEGORY_FISCAL', 'fiscal');
define('CATEGORY_NON_FISCAL', 'non_fiscal');

// Invoice status
define('INVOICE_PENDING', 'pending');
define('INVOICE_INVOICED', 'invoiced');
define('INVOICE_NOT_APPLICABLE', 'not_applicable');

// Payment methods
define('PAYMENT_CASH', 'cash');
define('PAYMENT_CARD', 'card');
define('PAYMENT_TRANSFER', 'transfer');
define('PAYMENT_CHECK', 'check');

// Include database configuration
require_once __DIR__ . '/database.php';

// Auto-create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Utility functions
function redirect($url) {
    header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? ''
    ];
}

function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['user_role'];
    
    switch ($permission) {
        case 'delete':
            return $role === ROLE_ADMIN;
        case 'edit':
        case 'create':
            return in_array($role, [ROLE_ADMIN, ROLE_CAPTURISTA]);
        case 'view':
            return in_array($role, [ROLE_ADMIN, ROLE_CAPTURISTA, ROLE_CONSULTA]);
        default:
            return false;
    }
}

function formatMoney($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}