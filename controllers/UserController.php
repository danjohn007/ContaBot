<?php
/**
 * User Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class UserController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        requireLogin();
        $this->userModel = new User($this->db);
    }
    
    /**
     * Default index method - redirect to profile
     */
    public function index() {
        $this->profile();
    }
    
    /**
     * Show user profile
     */
    public function profile() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $this->setFlash('error', 'Usuario no encontrado.');
            $this->redirect('dashboard');
        }
        
        if ($this->isPost()) {
            $name = sanitizeInput($this->post('name'));
            $rfc = sanitizeInput($this->post('rfc'));
            $user_type = $this->post('user_type');
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'El nombre es requerido.';
            }
            
            if (!in_array($user_type, ['personal', 'business'])) {
                $errors[] = 'Tipo de usuario inválido.';
            }
            
            // RFC validation (optional but if provided should be valid format)
            if (!empty($rfc) && !preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
                $errors[] = 'El RFC no tiene un formato válido.';
            }
            
            if (empty($errors)) {
                if ($this->userModel->updateProfile($userId, $name, $rfc, $user_type)) {
                    // Update session data
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_type'] = $user_type;
                    
                    $this->setFlash('success', 'Perfil actualizado exitosamente.');
                    $this->redirect('profile');
                } else {
                    $errors[] = 'Error al actualizar el perfil. Intenta nuevamente.';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Mi Perfil - ContaBot',
            'user' => $user,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('user/profile', $data);
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $userId = $_SESSION['user_id'];
        
        if ($this->isPost()) {
            $currentPassword = $this->post('current_password');
            $newPassword = $this->post('new_password');
            $confirmPassword = $this->post('confirm_password');
            
            // Validation
            $errors = [];
            
            if (empty($currentPassword)) {
                $errors[] = 'La contraseña actual es requerida.';
            }
            
            if (empty($newPassword)) {
                $errors[] = 'La nueva contraseña es requerida.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Las contraseñas no coinciden.';
            }
            
            if (empty($errors)) {
                // Verify current password
                $user = $this->userModel->findById($userId);
                
                if (!password_verify($currentPassword, $user['password'])) {
                    $errors[] = 'La contraseña actual es incorrecta.';
                } else {
                    if ($this->userModel->updatePassword($userId, $newPassword)) {
                        $this->setFlash('success', 'Contraseña actualizada exitosamente.');
                        $this->redirect('profile');
                    } else {
                        $errors[] = 'Error al actualizar la contraseña. Intenta nuevamente.';
                    }
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Cambiar Contraseña - ContaBot',
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('user/change-password', $data);
    }
}
?>