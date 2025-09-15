<?php
/**
 * Authentication Controller
 * ContaBot - Sistema Básico Contable
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Show login form
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }
        
        if ($this->isPost()) {
            $this->handleLogin();
        } else {
            $this->render('auth/login', [
                'title' => 'Iniciar Sesión',
                'csrf_token' => generateCSRFToken()
            ]);
        }
    }
    
    /**
     * Handle login form submission
     */
    private function handleLogin() {
        $username = sanitizeInput($this->getPost('username'));
        $password = $this->getPost('password');
        $remember = $this->getPost('remember') === '1';
        
        // Validate CSRF
        $this->validateCSRF();
        
        // Validate required fields
        $errors = $this->validateRequired($_POST, ['username', 'password']);
        
        if (!empty($errors)) {
            $this->render('auth/login', [
                'title' => 'Iniciar Sesión',
                'errors' => $errors,
                'csrf_token' => generateCSRFToken(),
                'old_input' => $_POST
            ]);
            return;
        }
        
        // Attempt authentication
        $user = $this->userModel->authenticate($username, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['login_time'] = time();
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                // In a real application, you'd store this token in the database
            }
            
            // Log successful login
            error_log("User login: " . $user['username'] . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
            // Redirect to dashboard
            $this->setFlash('success', 'Bienvenido, ' . $user['full_name']);
            $this->redirect('dashboard');
            
        } else {
            // Authentication failed
            $this->render('auth/login', [
                'title' => 'Iniciar Sesión',
                'errors' => ['Credenciales inválidas'],
                'csrf_token' => generateCSRFToken(),
                'old_input' => ['username' => $username]
            ]);
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isLoggedIn()) {
            // Log logout
            error_log("User logout: " . ($_SESSION['username'] ?? 'unknown'));
            
            // Clear remember me cookie
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/');
            }
            
            // Destroy session
            session_destroy();
            session_start();
            
            $this->setFlash('info', 'Sesión cerrada correctamente');
        }
        
        $this->redirect('auth/login');
    }
    
    /**
     * Show registration form (admin only)
     */
    public function register() {
        $this->requirePermission('create');
        
        if ($this->isPost()) {
            $this->handleRegister();
        } else {
            $this->render('auth/register', [
                'title' => 'Registrar Usuario',
                'csrf_token' => generateCSRFToken()
            ]);
        }
    }
    
    /**
     * Handle registration form submission
     */
    private function handleRegister() {
        $this->validateCSRF();
        
        $data = [
            'username' => sanitizeInput($this->getPost('username')),
            'email' => sanitizeInput($this->getPost('email')),
            'full_name' => sanitizeInput($this->getPost('full_name')),
            'role' => sanitizeInput($this->getPost('role')),
            'password' => $this->getPost('password'),
            'password_confirm' => $this->getPost('password_confirm')
        ];
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['username', 'email', 'full_name', 'role', 'password']);
        
        // Validate password confirmation
        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        // Validate password length
        if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        }
        
        // Check if username exists
        if ($this->userModel->usernameExists($data['username'])) {
            $errors[] = 'El nombre de usuario ya existe';
        }
        
        // Check if email exists
        if ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'El email ya está registrado';
        }
        
        // Validate role
        if (!in_array($data['role'], [ROLE_ADMIN, ROLE_CAPTURISTA, ROLE_CONSULTA])) {
            $errors[] = 'Rol inválido';
        }
        
        if (!empty($errors)) {
            $this->render('auth/register', [
                'title' => 'Registrar Usuario',
                'errors' => $errors,
                'csrf_token' => generateCSRFToken(),
                'old_input' => $data
            ]);
            return;
        }
        
        // Remove password confirmation from data
        unset($data['password_confirm']);
        
        // Create user
        $userId = $this->userModel->createUser($data);
        
        if ($userId) {
            $this->setFlash('success', 'Usuario registrado correctamente');
            $this->redirect('users');
        } else {
            $this->render('auth/register', [
                'title' => 'Registrar Usuario',
                'errors' => ['Error al registrar el usuario'],
                'csrf_token' => generateCSRFToken(),
                'old_input' => $data
            ]);
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $this->requireLogin();
        
        if ($this->isPost()) {
            $this->handleChangePassword();
        } else {
            $this->render('auth/change_password', [
                'title' => 'Cambiar Contraseña',
                'csrf_token' => generateCSRFToken()
            ]);
        }
    }
    
    /**
     * Handle change password form
     */
    private function handleChangePassword() {
        $this->validateCSRF();
        
        $currentPassword = $this->getPost('current_password');
        $newPassword = $this->getPost('new_password');
        $confirmPassword = $this->getPost('confirm_password');
        
        $errors = [];
        
        // Validate current password
        $user = $this->userModel->find($_SESSION['user_id']);
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $errors[] = 'La contraseña actual es incorrecta';
        }
        
        // Validate new password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'La nueva contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        if (!empty($errors)) {
            $this->render('auth/change_password', [
                'title' => 'Cambiar Contraseña',
                'errors' => $errors,
                'csrf_token' => generateCSRFToken()
            ]);
            return;
        }
        
        // Update password
        if ($this->userModel->updatePassword($_SESSION['user_id'], $newPassword)) {
            $this->setFlash('success', 'Contraseña actualizada correctamente');
            $this->redirect('dashboard');
        } else {
            $this->render('auth/change_password', [
                'title' => 'Cambiar Contraseña',
                'errors' => ['Error al actualizar la contraseña'],
                'csrf_token' => generateCSRFToken()
            ]);
        }
    }
}