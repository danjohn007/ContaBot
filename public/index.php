<?php
/**
 * Main Router - Sistema Básico Contable ContaBot
 * Handles all URL routing for the application
 */

// Include configuration
require_once '../config/config.php';

// Get the URL from the request
$url = '';
if (isset($_GET['url'])) {
    $url = $_GET['url'];
} else {
    // For PHP built-in server, parse the REQUEST_URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $url = trim(parse_url($requestUri, PHP_URL_PATH), '/');
}

$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);

// Split URL into parts
$urlParts = $url ? explode('/', $url) : ['dashboard'];

// Extract controller and action
$controllerName = !empty($urlParts[0]) ? $urlParts[0] : 'dashboard';
$action = isset($urlParts[1]) ? $urlParts[1] : 'index';
$params = array_slice($urlParts, 2);

// Map routes to controllers
$routes = [
    '' => 'DashboardController',
    'dashboard' => 'DashboardController',
    'login' => 'AuthController',
    'register' => 'AuthController',
    'logout' => 'AuthController',
    'profile' => 'UserController',
    'movements' => 'MovementController',
    'categories' => 'CategoryController',
    'reports' => 'ReportController',
    'earnings' => 'EarningsController',
    'account' => 'AccountController',
    'test' => 'TestController',
    'superadmin' => 'SuperAdminController'
];

// Get controller class name
$controllerClass = isset($routes[$controllerName]) ? $routes[$controllerName] : 'DashboardController';

// For specific routes, map the route name to the method name
$methodMappings = [
    'login' => 'login',
    'register' => 'register', 
    'logout' => 'logout'
];

// If this is a mapped route and action is 'index', use the mapping
if (isset($methodMappings[$controllerName]) && $action === 'index') {
    $action = $methodMappings[$controllerName];
}

// Check if controller file exists
$controllerFile = '../controllers/' . $controllerClass . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo "404 - Page not found";
    exit();
}

// Include and instantiate controller
require_once $controllerFile;

if (!class_exists($controllerClass)) {
    http_response_code(500);
    echo "500 - Controller not found";
    exit();
}

$controller = new $controllerClass();

// Check if method exists
$method = $action;
if (!method_exists($controller, $method)) {
    // Try converting hyphenated action to camelCase
    $camelCaseMethod = lcfirst(str_replace('-', '', ucwords($action, '-')));
    if (method_exists($controller, $camelCaseMethod)) {
        $method = $camelCaseMethod;
    } else {
        $method = 'index';
    }
}

// Call the controller method
try {
    call_user_func_array([$controller, $method], $params);
} catch (Exception $e) {
    http_response_code(500);
    echo "500 - Internal Server Error";
    if (ini_get('display_errors')) {
        echo "<br>Error: " . $e->getMessage();
    }
}
?>