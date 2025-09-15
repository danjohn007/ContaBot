<?php
/**
 * Base Controller Class
 * ContaBot - Sistema Básico Contable
 */

class BaseController {
    protected $data = [];
    
    public function __construct() {
        // Initialize any common controller logic
    }
    
    /**
     * Render view with data
     */
    protected function render($view, $data = []) {
        // Merge controller data with passed data
        $data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($data);
        
        // Include view file
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            $this->error404("View not found: $view");
        }
    }
    
    /**
     * Render JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        redirect($url);
    }
    
    /**
     * Show 404 error
     */
    protected function error404($message = 'Page not found') {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
        exit;
    }
    
    /**
     * Show 403 error
     */
    protected function error403($message = 'Access denied') {
        http_response_code(403);
        $this->render('errors/403', ['message' => $message]);
        exit;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!validateCSRFToken($token)) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
    }
    
    /**
     * Check if user is logged in
     */
    protected function requireLogin() {
        if (!isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => 'Authentication required'], 401);
            } else {
                $this->redirect('auth/login');
            }
        }
    }
    
    /**
     * Check user permission
     */
    protected function requirePermission($permission) {
        $this->requireLogin();
        
        if (!hasPermission($permission)) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => 'Insufficient permissions'], 403);
            } else {
                $this->error403('No tienes permisos para realizar esta acción');
            }
        }
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get request method
     */
    protected function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost() {
        return $this->getRequestMethod() === 'POST';
    }
    
    /**
     * Check if request is GET
     */
    protected function isGet() {
        return $this->getRequestMethod() === 'GET';
    }
    
    /**
     * Get POST data
     */
    protected function getPost($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     */
    protected function getGet($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $fields) {
        $errors = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "El campo $field es requerido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash messages
     */
    protected function getFlash() {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Upload file
     */
    protected function uploadFile($file, $allowedTypes = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $allowedTypes = $allowedTypes ?? ALLOWED_EXTENSIONS;
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedTypes)) {
            return false;
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }
        
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = UPLOAD_PATH . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'filename' => $filename,
                'original_name' => $file['name'],
                'path' => $filepath,
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }
        
        return false;
    }
}