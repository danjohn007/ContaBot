<?php
/**
 * Authentication Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->db);
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
            $email = sanitizeInput($this->post('email'));
            $password = $this->post('password');
            $remember = $this->post('remember') === 'on';
            
            // Validation
            $errors = [];
            
            if (empty($email)) {
                $errors[] = 'El email es requerido';
            } elseif (!validateEmail($email)) {
                $errors[] = 'El email no es válido';
            }
            
            if (empty($password)) {
                $errors[] = 'La contraseña es requerida';
            }
            
            if (empty($errors)) {
                $user = $this->userModel->verifyCredentials($email, $password);
                
                if ($user) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Set remember cookie if requested
                    if ($remember) {
                        $token = bin2hex(random_bytes(16));
                        setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                    }
                    
                    $this->setFlash('success', 'Bienvenido, ' . $user['name']);
                    $this->redirect('dashboard');
                } else {
                    $errors[] = 'Credenciales inválidas';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Iniciar Sesión - ContaBot',
            'flash' => $this->getFlash()
        ];
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Show registration form
     */
    public function register() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }
        
        if ($this->isPost()) {
            $email = sanitizeInput($this->post('email'));
            $password = $this->post('password');
            $confirm_password = $this->post('confirm_password');
            $name = sanitizeInput($this->post('name'));
            $rfc = sanitizeInput($this->post('rfc'));
            $user_type = $this->post('user_type', 'personal');
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'El nombre es requerido';
            }
            
            if (empty($email)) {
                $errors[] = 'El email es requerido';
            } elseif (!validateEmail($email)) {
                $errors[] = 'El email no es válido';
            } elseif ($this->userModel->emailExists($email)) {
                $errors[] = 'El email ya está registrado';
            }
            
            if (empty($password)) {
                $errors[] = 'La contraseña es requerida';
            } elseif (strlen($password) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres';
            }
            
            if ($password !== $confirm_password) {
                $errors[] = 'Las contraseñas no coinciden';
            }
            
            if (!in_array($user_type, ['personal', 'business'])) {
                $errors[] = 'Tipo de usuario inválido';
            }
            
            // RFC validation (optional but if provided should be valid format)
            if (!empty($rfc) && !preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
                $errors[] = 'El RFC no tiene un formato válido';
            }
            
            if (empty($errors)) {
                if ($this->userModel->create($email, $password, $name, $rfc, $user_type)) {
                    $this->setFlash('success', 'Cuenta creada exitosamente. Ahora puedes iniciar sesión.');
                    $this->redirect('login');
                } else {
                    $errors[] = 'Error al crear la cuenta. Intenta nuevamente.';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Registrarse - ContaBot',
            'flash' => $this->getFlash()
        ];
        
        $this->view('auth/register', $data);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Clear session
        session_destroy();
        
        // Clear remember cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        $this->redirect('login');
    }
    
    /**
     * Show forgot password form
     */
    public function forgot() {
        if ($this->isPost()) {
            $email = sanitizeInput($this->post('email'));
            
            if (empty($email)) {
                $this->setFlash('error', 'El email es requerido');
            } elseif (!validateEmail($email)) {
                $this->setFlash('error', 'El email no es válido');
            } else {
                $token = $this->userModel->createPasswordResetToken($email);
                
                if ($token) {
                    // In a real application, you would send an email here
                    // For now, we'll just show a success message
                    $this->setFlash('success', 'Se ha enviado un enlace de recuperación a tu email.');
                    
                    // For testing purposes, we'll show the token
                    if (ini_get('display_errors')) {
                        $this->setFlash('info', 'Token de recuperación (solo para testing): ' . $token);
                    }
                } else {
                    $this->setFlash('error', 'Email no encontrado en el sistema');
                }
            }
        }
        
        $data = [
            'title' => 'Recuperar Contraseña - ContaBot',
            'flash' => $this->getFlash()
        ];
        
        $this->view('auth/forgot', $data);
    }
    
    /**
     * Reset password with token
     */
    public function reset() {
        $token = $this->get('token');
        
        if (empty($token)) {
            $this->setFlash('error', 'Token inválido');
            $this->redirect('login');
        }
        
        // Verify token
        $resetData = $this->userModel->verifyPasswordResetToken($token);
        if (!$resetData) {
            $this->setFlash('error', 'Token inválido o expirado');
            $this->redirect('login');
        }
        
        if ($this->isPost()) {
            $password = $this->post('password');
            $confirm_password = $this->post('confirm_password');
            
            $errors = [];
            
            if (empty($password)) {
                $errors[] = 'La contraseña es requerida';
            } elseif (strlen($password) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres';
            }
            
            if ($password !== $confirm_password) {
                $errors[] = 'Las contraseñas no coinciden';
            }
            
            if (empty($errors)) {
                if ($this->userModel->resetPasswordWithToken($token, $password)) {
                    $this->setFlash('success', 'Contraseña actualizada exitosamente. Ahora puedes iniciar sesión.');
                    $this->redirect('login');
                } else {
                    $errors[] = 'Error al actualizar la contraseña. Intenta nuevamente.';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Restablecer Contraseña - ContaBot',
            'token' => $token,
            'flash' => $this->getFlash()
        ];
        
        $this->view('auth/reset', $data);
    }
}
?>