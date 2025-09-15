<?php
/**
 * Main Entry Point
 * ContaBot - Sistema Básico Contable
 */

// Include configuration
require_once 'config/config.php';

// Include router
require_once 'router.php';

// Include base classes
require_once 'controllers/BaseController.php';
require_once 'models/BaseModel.php';

// Initialize router
$router = new Router();

// Define routes
$router->add('', 'dashboard', 'index');
$router->add('login', 'auth', 'login');
$router->add('logout', 'auth', 'logout');
$router->add('dashboard', 'dashboard', 'index');
$router->add('transactions', 'transaction', 'index');
$router->add('transactions/create', 'transaction', 'create');
$router->add('transactions/edit/*', 'transaction', 'edit');
$router->add('transactions/delete/*', 'transaction', 'delete');
$router->add('transactions/view/*', 'transaction', 'view');
$router->add('categories', 'category', 'index');
$router->add('reports', 'report', 'index');
$router->add('reports/fiscal', 'report', 'fiscal');
$router->add('reports/monthly', 'report', 'monthly');
$router->add('users', 'user', 'index');
$router->add('profile', 'user', 'profile');
$router->add('api/*', 'api', 'handle');

// Start routing
try {
    $router->route();
} catch (Exception $e) {
    // Log error
    error_log("Router error: " . $e->getMessage());
    
    // Show error page
    http_response_code(500);
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error - " . APP_NAME . "</title>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-light'>
        <div class='container mt-5'>
            <div class='row justify-content-center'>
                <div class='col-md-6 text-center'>
                    <h1 class='display-4 text-danger'>Error</h1>
                    <p class='text-muted'>Ha ocurrido un error interno del servidor.</p>
                    <a href='" . BASE_URL . "' class='btn btn-primary'>Volver al inicio</a>
                </div>
            </div>
        </div>
    </body>
    </html>";
}