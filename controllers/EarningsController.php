<?php
/**
 * Earnings Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class EarningsController extends BaseController {
    private $referralModel;
    private $userAccountModel;
    
    public function __construct() {
        parent::__construct();
        requireLogin();
        
        $this->referralModel = new Referral($this->db);
        $this->userAccountModel = new UserAccount($this->db);
    }
    
    /**
     * Earnings Dashboard
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Get user's referral link
        $referralLink = $this->referralModel->getReferralLinkByUserId($userId);
        
        // If no referral link exists, create one
        if (!$referralLink) {
            $this->referralModel->generateReferralLink($userId);
            $referralLink = $this->referralModel->getReferralLinkByUserId($userId);
        }
        
        // Get earnings summary
        $earnings = $this->referralModel->getUserEarnings($userId);
        
        // Get referrals list
        $referrals = $this->referralModel->getUserReferrals($userId);
        
        // Get earnings by month for chart
        $earningsByMonth = $this->referralModel->getUserEarningsByMonth($userId, 12);
        
        // Get commission payments
        $commissionPayments = $this->referralModel->getUserCommissionPayments($userId);
        
        // Generate referral URL
        $referralUrl = BASE_URL . 'register?ref=' . $referralLink['referral_code'];
        
        $data = [
            'title' => 'Mis Ganancias - ContaBot',
            'referral_link' => $referralLink,
            'referral_url' => $referralUrl,
            'earnings' => $earnings,
            'referrals' => $referrals,
            'earnings_by_month' => $earningsByMonth,
            'commission_payments' => $commissionPayments,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('earnings/dashboard', $data);
    }
    
    /**
     * Generate new referral link
     */
    public function generateNewLink() {
        if (!$this->isPost()) {
            $this->redirect('earnings');
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // Deactivate current link
            $currentLink = $this->referralModel->getReferralLinkByUserId($userId);
            if ($currentLink) {
                $query = "UPDATE referral_links SET is_active = 0 WHERE user_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$userId]);
            }
            
            // Generate new link
            $newCode = $this->referralModel->generateReferralLink($userId);
            
            if ($newCode) {
                $this->setFlash('success', 'Nuevo enlace de referido generado exitosamente');
            } else {
                $this->setFlash('error', 'Error al generar nuevo enlace');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        $this->redirect('earnings');
    }
    
    /**
     * Manage user accounts (sub-users)
     */
    public function accounts() {
        $userId = $_SESSION['user_id'];
        
        // Get child users
        $childUsers = $this->userAccountModel->getChildUsers($userId);
        
        $data = [
            'title' => 'Gestión de Cuentas - ContaBot',
            'child_users' => $childUsers,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('earnings/accounts', $data);
    }
    
    /**
     * Add new user to account
     */
    public function addUser() {
        if (!$this->isPost()) {
            $this->redirect('earnings/accounts');
        }
        
        $parentUserId = $_SESSION['user_id'];
        $email = sanitizeInput($this->post('email'));
        $password = $this->post('password');
        $name = sanitizeInput($this->post('name'));
        $accessLevel = $this->post('access_level', 'basic');
        $canCreateMovements = $this->post('can_create_movements') === 'on';
        $canViewReports = $this->post('can_view_reports') === 'on';
        
        // Validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'El nombre es requerido';
        }
        
        if (empty($email)) {
            $errors[] = 'El email es requerido';
        } elseif (!validateEmail($email)) {
            $errors[] = 'El email no es válido';
        }
        
        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (empty($errors)) {
            try {
                $userModel = new User($this->db);
                
                // Check if email already exists
                if ($userModel->emailExists($email)) {
                    $this->setFlash('error', 'El email ya está registrado');
                    $this->redirect('earnings/accounts');
                }
                
                // Create user
                if ($userModel->create($email, $password, $name, null, 'personal')) {
                    $newUserId = $this->db->lastInsertId();
                    
                    // Add to user accounts
                    $this->userAccountModel->addChildUser(
                        $parentUserId, 
                        $newUserId, 
                        $accessLevel, 
                        $canCreateMovements, 
                        $canViewReports
                    );
                    
                    $this->setFlash('success', 'Usuario agregado exitosamente');
                } else {
                    $this->setFlash('error', 'Error al crear usuario');
                }
            } catch (Exception $e) {
                $this->setFlash('error', 'Error: ' . $e->getMessage());
            }
        } else {
            $this->setFlash('error', implode('<br>', $errors));
        }
        
        $this->redirect('earnings/accounts');
    }
    
    /**
     * Update user permissions
     */
    public function updatePermissions() {
        if (!$this->isPost()) {
            $this->redirect('earnings/accounts');
        }
        
        $parentUserId = $_SESSION['user_id'];
        $childUserId = $this->post('child_user_id');
        $accessLevel = $this->post('access_level', 'basic');
        $canCreateMovements = $this->post('can_create_movements') === 'on';
        $canViewReports = $this->post('can_view_reports') === 'on';
        
        if (!$childUserId) {
            $this->setFlash('error', 'Usuario inválido');
            $this->redirect('earnings/accounts');
        }
        
        try {
            $this->userAccountModel->updateUserPermissions(
                $parentUserId,
                $childUserId,
                $accessLevel,
                $canCreateMovements,
                $canViewReports
            );
            
            $this->setFlash('success', 'Permisos actualizados exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        $this->redirect('earnings/accounts');
    }
    
    /**
     * Remove user from account
     */
    public function removeUser() {
        if (!$this->isPost()) {
            $this->redirect('earnings/accounts');
        }
        
        $parentUserId = $_SESSION['user_id'];
        $childUserId = $this->post('child_user_id');
        
        if (!$childUserId) {
            $this->setFlash('error', 'Usuario inválido');
            $this->redirect('earnings/accounts');
        }
        
        try {
            $this->userAccountModel->removeChildUser($parentUserId, $childUserId);
            $this->setFlash('success', 'Usuario removido exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        $this->redirect('earnings/accounts');
    }
}
?>