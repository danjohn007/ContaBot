<?php
/**
 * Simple Router for ContaBot
 * ContaBot - Sistema Básico Contable
 */

class Router {
    private $routes = [];
    private $currentRoute = '';
    
    public function __construct() {
        $this->parseUrl();
    }
    
    /**
     * Parse current URL
     */
    private function parseUrl() {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove base path if exists
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($url, $basePath) === 0) {
            $url = substr($url, strlen($basePath));
        }
        
        // Remove query string
        $url = strtok($url, '?');
        
        // Clean URL
        $url = trim($url, '/');
        
        $this->currentRoute = $url;
    }
    
    /**
     * Add route
     */
    public function add($pattern, $controller, $action) {
        $this->routes[$pattern] = [
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    /**
     * Route the current request
     */
    public function route() {
        // Default route
        if (empty($this->currentRoute)) {
            $this->currentRoute = 'dashboard/index';
        }
        
        // Check for exact match first
        if (isset($this->routes[$this->currentRoute])) {
            return $this->dispatch($this->routes[$this->currentRoute]);
        }
        
        // Check for pattern matches
        foreach ($this->routes as $pattern => $route) {
            if ($this->matchPattern($pattern, $this->currentRoute)) {
                return $this->dispatch($route);
            }
        }
        
        // Try to match controller/action pattern
        $parts = explode('/', $this->currentRoute);
        
        if (count($parts) >= 1) {
            $controller = $parts[0];
            $action = $parts[1] ?? 'index';
            $params = array_slice($parts, 2);
            
            return $this->dispatch([
                'controller' => $controller,
                'action' => $action,
                'params' => $params
            ]);
        }
        
        // 404 - Not found
        $this->handle404();
    }
    
    /**
     * Match pattern with wildcards
     */
    private function matchPattern($pattern, $url) {
        // Convert pattern to regex
        $pattern = str_replace('*', '([^/]+)', $pattern);
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        
        return preg_match($pattern, $url);
    }
    
    /**
     * Dispatch to controller
     */
    private function dispatch($route) {
        $controllerName = ucfirst($route['controller']) . 'Controller';
        $actionName = $route['action'];
        $params = $route['params'] ?? [];
        
        $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            $this->handle404("Controller not found: $controllerName");
            return;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            $this->handle404("Controller class not found: $controllerName");
            return;
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $actionName)) {
            $this->handle404("Action not found: $actionName in $controllerName");
            return;
        }
        
        // Call the action
        call_user_func_array([$controller, $actionName], $params);
    }
    
    /**
     * Handle 404 errors
     */
    private function handle404($message = 'Page not found') {
        http_response_code(404);
        
        // Try to load 404 controller
        $controllerFile = __DIR__ . '/controllers/ErrorController.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controller = new ErrorController();
            $controller->error404($message);
        } else {
            // Fallback 404 page
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>404 - Page Not Found</title>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            </head>
            <body class='bg-light'>
                <div class='container mt-5'>
                    <div class='row justify-content-center'>
                        <div class='col-md-6 text-center'>
                            <h1 class='display-1'>404</h1>
                            <h2>Página no encontrada</h2>
                            <p class='text-muted'>$message</p>
                            <a href='" . BASE_URL . "' class='btn btn-primary'>Volver al inicio</a>
                        </div>
                    </div>
                </div>
            </body>
            </html>";
        }
    }
    
    /**
     * Get current route
     */
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
}