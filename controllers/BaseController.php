<?php
/**
 * Base Controller
 * Sistema Básico Contable - ContaBot
 */

class BaseController {
    protected $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Load a view file
     */
    protected function view($viewName, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewFile = '../views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View not found: " . $viewName;
        }
    }
    
    /**
     * Load view with layout
     */
    protected function viewWithLayout($viewName, $data = []) {
        $data['content_view'] = $viewName;
        $this->view('layout/main', $data);
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect($url) {
        $redirectUrl = BASE_URL . $url;
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Get POST data
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    /**
     * Get GET data
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get and clear flash messages
     */
    protected function getFlash() {
        $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
        unset($_SESSION['flash']);
        return $flash;
    }
}
?>