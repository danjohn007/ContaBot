<?php
/**
 * Account Management Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class AccountController extends BaseController {
    private $userAccountModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        requireLogin();
        
        $this->userAccountModel = new UserAccount($this->db);
        $this->userModel = new User($this->db);
    }
    
    /**
     * Account Management Dashboard
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Get child users (users added to this account)
        $childUsers = $this->userAccountModel->getChildUsers($userId);
        
        // Get parent users (accounts this user belongs to)
        $parentUsers = $this->userAccountModel->getParentUsers($userId);
        
        $data = [
            'title' => 'Gestión de Cuenta - ContaBot',
            'child_users' => $childUsers,
            'parent_users' => $parentUsers,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('account/index', $data);
    }
    
    /**
     * Add new user to account
     */
    public function addUser() {
        if (!$this->isPost()) {
            $this->redirect('account');
        }
        
        $parentUserId = $_SESSION['user_id'];
        $email = sanitizeInput($this->post('email'));
        $password = $this->post('password');
        $name = sanitizeInput($this->post('name'));
        $phone = sanitizeInput($this->post('phone'));
        $accessLevel = $this->post('access_level', 'basic');
        $canCreateMovements = $this->post('can_create_movements') === 'on';
        $canViewReports = $this->post('can_view_reports') === 'on';
        $canEditMovements = $this->post('can_edit_movements') === 'on';
        $canDeleteMovements = $this->post('can_delete_movements') === 'on';
        
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
        
        if (empty($phone)) {
            $errors[] = 'El número de teléfono es requerido';
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = 'El número de teléfono debe tener exactamente 10 dígitos';
        } elseif ($this->userModel->phoneExists($phone)) {
            $errors[] = 'El número de teléfono ya está registrado';
        }
        
        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!in_array($accessLevel, ['basic', 'advanced'])) {
            $errors[] = 'Nivel de acceso inválido';
        }
        
        if (empty($errors)) {
            try {
                $this->db->beginTransaction();
                
                // Create the user with 'active' status since it's being added by an existing user
                $query = "INSERT INTO users (email, password, name, phone, user_type, account_status, billing_status) VALUES (?, ?, ?, ?, 'personal', 'active', 'paid')";
                $stmt = $this->db->prepare($query);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                if ($stmt->execute([$email, $hashedPassword, $name, $phone])) {
                    $childUserId = $this->db->lastInsertId();
                    
                    // Add the relationship
                    if ($this->userAccountModel->addChildUser($parentUserId, $childUserId, $accessLevel, $canCreateMovements, $canViewReports, $canEditMovements, $canDeleteMovements)) {
                        $this->db->commit();
                        $this->setFlash('success', 'Usuario agregado exitosamente a tu cuenta');
                    } else {
                        $this->db->rollBack();
                        $errors[] = 'Error al establecer la relación de cuenta';
                    }
                } else {
                    $this->db->rollBack();
                    $errors[] = 'Error al crear el usuario';
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                $errors[] = 'Error en la base de datos: ' . $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
        }
        
        $this->redirect('account');
    }
    
    /**
     * Update user permissions
     */
    public function updatePermissions() {
        if (!$this->isPost()) {
            $this->redirect('account');
        }
        
        $parentUserId = $_SESSION['user_id'];
        $childUserId = (int) $this->post('child_user_id');
        $accessLevel = $this->post('access_level', 'basic');
        $canCreateMovements = $this->post('can_create_movements') === 'on';
        $canViewReports = $this->post('can_view_reports') === 'on';
        $canEditMovements = $this->post('can_edit_movements') === 'on';
        $canDeleteMovements = $this->post('can_delete_movements') === 'on';
        
        if (!$childUserId) {
            $this->setFlash('error', 'Usuario inválido');
            $this->redirect('account');
        }
        
        if ($this->userAccountModel->updateUserPermissions($parentUserId, $childUserId, $accessLevel, $canCreateMovements, $canViewReports, $canEditMovements, $canDeleteMovements)) {
            $this->setFlash('success', 'Permisos actualizados exitosamente');
        } else {
            $this->setFlash('error', 'Error al actualizar permisos');
        }
        
        $this->redirect('account');
    }
    
    /**
     * Remove user from account
     */
    public function removeUser() {
        if (!$this->isPost()) {
            $this->redirect('account');
        }
        
        $parentUserId = $_SESSION['user_id'];
        $childUserId = (int) $this->post('child_user_id');
        
        if (!$childUserId) {
            $this->setFlash('error', 'Usuario inválido');
            $this->redirect('account');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Remove the relationship
            if ($this->userAccountModel->removeChildUser($parentUserId, $childUserId)) {
                // Optionally, you might want to deactivate the user account entirely
                // or leave it active for them to manage independently
                
                $this->db->commit();
                $this->setFlash('success', 'Usuario removido de tu cuenta exitosamente');
            } else {
                $this->db->rollBack();
                $this->setFlash('error', 'Error al remover usuario de la cuenta');
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', 'Error en la base de datos: ' . $e->getMessage());
        }
        
        $this->redirect('account');
    }
}
?>