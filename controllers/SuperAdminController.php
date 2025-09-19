<?php
/**
 * SuperAdmin Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class SuperAdminController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        requireLogin();
        
        // Check if user is SuperAdmin
        if ($_SESSION['user_type'] !== 'superadmin') {
            $this->setFlash('error', 'Acceso denegado. Se requieren permisos de SuperAdmin.');
            $this->redirect('dashboard');
        }
        
        $this->userModel = new User($this->db);
    }
    
    /**
     * Get database-compatible date format function
     */
    private function getDateFormatFilter($column, $format) {
        // Check if we're using SQLite
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            // SQLite uses strftime
            return "strftime('$format', $column)";
        } else {
            // MySQL uses DATE_FORMAT
            return "DATE_FORMAT($column, '$format')";
        }
    }
    
    /**
     * Get database-compatible date subtraction
     */
    private function getDateSubtractFilter($months) {
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            // SQLite date subtraction
            return "date('now', '-$months months')";
        } else {
            // MySQL date subtraction
            return "DATE_SUB(CURDATE(), INTERVAL $months MONTH)";
        }
    }
    
    /**
     * SuperAdmin Dashboard
     */
    public function index() {
        $stats = $this->userModel->getFinancialStats();
        $pendingUsers = $this->userModel->getPendingUsers(10);
        $overdueUsers = $this->userModel->getOverdueUsers();
        
        $data = [
            'title' => 'Panel SuperAdmin - ContaBot',
            'stats' => $stats,
            'pending_users' => $pendingUsers,
            'overdue_users' => $overdueUsers,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('superadmin/dashboard', $data);
    }
    
    /**
     * Pending Users Management
     */
    public function pendingUsers() {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get pending users with pagination
        $query = "SELECT u.*, sp.name as plan_name, sp.price as plan_price 
                 FROM users u
                 LEFT JOIN subscription_plans sp ON u.subscription_plan = sp.type
                 WHERE u.account_status = 'pending' AND u.user_type != 'superadmin'
                 ORDER BY u.created_at DESC
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $pendingUsers = $stmt->fetchAll();
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM users WHERE account_status = 'pending' AND user_type != 'superadmin'";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute();
        $totalUsers = $countStmt->fetch()['total'];
        
        // Get subscription plans
        $plansQuery = "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY price ASC";
        $plansStmt = $this->db->prepare($plansQuery);
        $plansStmt->execute();
        $plans = $plansStmt->fetchAll();
        
        $data = [
            'title' => 'Usuarios Pendientes - SuperAdmin',
            'pending_users' => $pendingUsers,
            'plans' => $plans,
            'current_page' => $page,
            'total_pages' => ceil($totalUsers / $limit),
            'total_users' => $totalUsers,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('superadmin/pending-users', $data);
    }
    
    /**
     * Approve User
     */
    public function approveUser() {
        if (!$this->isPost()) {
            $this->redirect('superadmin/pending-users');
        }
        
        $userId = $this->post('user_id');
        $planType = $this->post('plan_type');
        
        if (!$userId || !$planType) {
            $this->setFlash('error', 'Datos incompletos para aprobar usuario');
            $this->redirect('superadmin/pending-users');
        }
        
        try {
            $this->userModel->approveUser($userId, $planType, $_SESSION['user_id']);
            $this->setFlash('success', 'Usuario aprobado exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al aprobar usuario: ' . $e->getMessage());
        }
        
        $this->redirect('superadmin/pending-users');
    }
    
    /**
     * Financial Dashboard
     */
    public function financial() {
        $stats = $this->userModel->getFinancialStats();
        
        // Get revenue by month (last 12 months) - using database-agnostic functions
        $dateFormat = $this->getDateFormatFilter('payment_date', '%Y-%m');
        $dateSubtract = $this->getDateSubtractFilter(12);
        
        $revenueQuery = "SELECT 
                        $dateFormat as month,
                        SUM(amount) as revenue,
                        COUNT(*) as payments
                        FROM billing_history 
                        WHERE payment_status = 'paid' 
                        AND payment_date >= $dateSubtract
                        GROUP BY $dateFormat
                        ORDER BY month";
        
        $revenueStmt = $this->db->prepare($revenueQuery);
        $revenueStmt->execute();
        $monthlyRevenue = $revenueStmt->fetchAll();
        
        // Get recent payments
        $paymentsQuery = "SELECT bh.*, u.name as user_name, u.email as user_email, sp.name as plan_name
                         FROM billing_history bh
                         INNER JOIN users u ON bh.user_id = u.id
                         INNER JOIN subscription_plans sp ON bh.plan_id = sp.id
                         WHERE bh.payment_status = 'paid'
                         ORDER BY bh.payment_date DESC
                         LIMIT 10";
        
        $paymentsStmt = $this->db->prepare($paymentsQuery);
        $paymentsStmt->execute();
        $recentPayments = $paymentsStmt->fetchAll();
        
        $data = [
            'title' => 'Dashboard Financiero - SuperAdmin',
            'stats' => $stats,
            'monthly_revenue' => $monthlyRevenue,
            'recent_payments' => $recentPayments,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('superadmin/financial', $data);
    }
    
    /**
     * User Management
     */
    public function users() {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE u.user_type != 'superadmin'";
        $params = [];
        
        if ($status !== 'all') {
            $whereClause .= " AND u.account_status = ?";
            $params[] = $status;
        }
        
        // Get users with pagination
        $query = "SELECT u.*, sp.name as plan_name, sp.price as plan_price,
                         approver.name as approved_by_name
                 FROM users u
                 LEFT JOIN subscription_plans sp ON u.subscription_plan = sp.type
                 LEFT JOIN users approver ON u.approved_by = approver.id
                 $whereClause
                 ORDER BY u.created_at DESC
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM users u $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $i => $param) {
            $countStmt->bindValue($i + 1, $param);
        }
        $countStmt->execute();
        $totalUsers = $countStmt->fetch()['total'];
        
        $data = [
            'title' => 'Gestión de Usuarios - SuperAdmin',
            'users' => $users,
            'current_page' => $page,
            'total_pages' => ceil($totalUsers / $limit),
            'total_users' => $totalUsers,
            'current_status' => $status,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('superadmin/users', $data);
    }
    
    /**
     * Suspend User
     */
    public function suspendUser() {
        if (!$this->isPost()) {
            $this->redirect('superadmin/users');
        }
        
        $userId = $this->post('user_id');
        $reason = $this->post('reason', 'Suspendido por administrador');
        
        if (!$userId) {
            $this->setFlash('error', 'ID de usuario requerido');
            $this->redirect('superadmin/users');
        }
        
        try {
            $this->userModel->suspendUser($userId, $reason);
            $this->setFlash('success', 'Usuario suspendido exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al suspender usuario: ' . $e->getMessage());
        }
        
        $this->redirect('superadmin/users');
    }
    
    /**
     * Payment Registration Module
     */
    public function payments() {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $status = isset($_GET['status']) ? $_GET['status'] : 'active';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get active users with their payment information
        $whereClause = "WHERE u.account_status = ? AND u.user_type != 'superadmin'";
        $params = [$status];
        
        $query = "SELECT u.*, sp.name as plan_name, sp.price as plan_price,
                         pending_bh.payment_status, pending_bh.payment_date, pending_bh.amount as pending_amount,
                         pending_bh.billing_period_start, pending_bh.billing_period_end, pending_bh.id as billing_id,
                         recent_bh.payment_date as last_payment_date,
                         CASE 
                            WHEN pending_bh.id IS NOT NULL THEN 'pending'
                            WHEN u.next_payment_date > CURRENT_TIMESTAMP THEN 'paid'
                            ELSE 'overdue'
                         END as payment_display_status
                 FROM users u
                 LEFT JOIN subscription_plans sp ON u.subscription_plan = sp.type
                 LEFT JOIN billing_history pending_bh ON u.id = pending_bh.user_id AND pending_bh.payment_status = 'pending'
                 LEFT JOIN (
                     SELECT user_id, MAX(payment_date) as payment_date
                     FROM billing_history 
                     WHERE payment_status = 'paid' 
                     GROUP BY user_id
                 ) recent_bh ON u.id = recent_bh.user_id
                 $whereClause
                 ORDER BY u.name ASC
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM users u $whereClause";
        $countStmt = $this->db->prepare($countQuery);
        foreach ($params as $i => $param) {
            $countStmt->bindValue($i + 1, $param);
        }
        $countStmt->execute();
        $totalUsers = $countStmt->fetch()['total'];
        
        // Get payment methods for the form
        $paymentMethods = [
            'transfer' => 'Transferencia Bancaria',
            'card' => 'Tarjeta de Crédito/Débito',
            'paypal' => 'PayPal',
            'cash' => 'Efectivo',
            'check' => 'Cheque',
            'other' => 'Otro'
        ];
        
        $data = [
            'title' => 'Registro de Pagos - SuperAdmin',
            'users' => $users,
            'payment_methods' => $paymentMethods,
            'current_page' => $page,
            'total_pages' => ceil($totalUsers / $limit),
            'total_users' => $totalUsers,
            'current_status' => $status,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('superadmin/payments', $data);
    }
    
    /**
     * Process Payment Registration
     */
    public function registerPayment() {
        if (!$this->isPost()) {
            $this->redirect('superadmin/payments');
        }
        
        $billingId = $this->post('billing_id');
        $paymentMethod = $this->post('payment_method');
        $transactionId = $this->post('transaction_id');
        $notes = $this->post('notes');
        $paymentDate = $this->post('payment_date');
        
        if (!$billingId || !$paymentMethod || !$paymentDate) {
            $this->setFlash('error', 'Todos los campos obligatorios deben ser completados');
            $this->redirect('superadmin/payments');
        }
        
        try {
            $this->userModel->registerPayment($billingId, $paymentMethod, $transactionId, $notes, $paymentDate);
            $this->setFlash('success', 'Pago registrado exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al registrar pago: ' . $e->getMessage());
        }
        
        $this->redirect('superadmin/payments');
    }
    
    /**
     * Process Advance Payment Registration
     */
    public function advancePayment() {
        if (!$this->isPost()) {
            $this->redirect('superadmin/payments');
        }
        
        $billingId = $this->post('billing_id');
        $paymentMethod = $this->post('payment_method');
        $transactionId = $this->post('transaction_id');
        $notes = $this->post('notes');
        $paymentDate = $this->post('payment_date');
        
        if (!$billingId || !$paymentMethod || !$paymentDate) {
            $this->setFlash('error', 'Todos los campos obligatorios deben ser completados');
            $this->redirect('superadmin/payments');
        }
        
        try {
            $this->userModel->registerAdvancePayment($billingId, $paymentMethod, $transactionId, $notes, $paymentDate);
            $this->setFlash('success', 'Adelanto de pago registrado exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al registrar adelanto de pago: ' . $e->getMessage());
        }
        
        $this->redirect('superadmin/payments');
    }
    
    /**
     * Loyalty System Management
     */
    public function loyalty() {
        $referralModel = new Referral($this->db);
        
        // Get pagination parameters
        $page = max(1, (int) $this->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get all referrals with pagination
        $referrals = $referralModel->getAllReferrals($limit, $offset);
        
        // Get summary stats
        $stats = $this->getReferralStats();
        
        $data = [
            'title' => 'Sistema de Lealtad - SuperAdmin',
            'referrals' => $referrals,
            'stats' => $stats,
            'current_page' => $page,
            'total_pages' => ceil($stats['total_referrals'] / $limit),
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('superadmin/loyalty', $data);
    }
    
    /**
     * Update commission rate for a user
     */
    public function updateCommission() {
        if (!$this->isPost()) {
            $this->redirect('superadmin/loyalty');
        }
        
        $userId = $this->post('user_id');
        $commissionRate = (float) $this->post('commission_rate');
        
        if (!$userId || $commissionRate < 0 || $commissionRate > 100) {
            $this->setFlash('error', 'Datos inválidos para actualizar comisión');
            $this->redirect('superadmin/loyalty');
        }
        
        try {
            $referralModel = new Referral($this->db);
            $referralModel->updateCommissionRate($userId, $commissionRate);
            $this->setFlash('success', 'Tasa de comisión actualizada exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al actualizar comisión: ' . $e->getMessage());
        }
        
        $this->redirect('superadmin/loyalty');
    }
    
    /**
     * Record commission payment
     */
    public function payCommission() {
        if (!$this->isPost()) {
            $this->redirect('superadmin/loyalty');
        }
        
        $referralRegistrationId = $this->post('referral_registration_id');
        $amount = (float) $this->post('amount');
        $paymentMethod = $this->post('payment_method');
        $notes = $this->post('notes');
        
        // Handle file upload
        $evidenceFile = null;
        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
            $evidenceFile = $this->handleFileUpload($_FILES['evidence_file'], 'commission_evidence');
        }
        
        if (!$referralRegistrationId || $amount <= 0) {
            $this->setFlash('error', 'Datos inválidos para registrar pago');
            $this->redirect('superadmin/loyalty');
        }
        
        try {
            $referralModel = new Referral($this->db);
            $referralModel->recordCommissionPayment(
                $referralRegistrationId, 
                $amount, 
                $paymentMethod, 
                $_SESSION['user_id'],
                $evidenceFile, 
                $notes
            );
            $this->setFlash('success', 'Pago de comisión registrado exitosamente');
        } catch (Exception $e) {
            $this->setFlash('error', 'Error al registrar pago: ' . $e->getMessage());
        }
        
        $this->redirect('superadmin/loyalty');
    }
    
    /**
     * Get referral system statistics
     */
    private function getReferralStats() {
        $query = "SELECT 
                    COUNT(*) as total_referrals,
                    COUNT(DISTINCT referrer_id) as active_referrers,
                    SUM(commission_amount) as total_commissions,
                    SUM(CASE WHEN commission_status = 'paid' THEN commission_amount ELSE 0 END) as paid_commissions,
                    SUM(CASE WHEN commission_status = 'pending' THEN commission_amount ELSE 0 END) as pending_commissions
                 FROM referral_registrations";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Handle file upload for commission evidence
     */
    private function handleFileUpload($file, $prefix = 'upload') {
        $uploadDir = UPLOAD_PATH . 'commission_evidence/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = $prefix . '_' . time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'commission_evidence/' . $fileName;
        }
        
        return null;
    }
    
    /**
     * Auto-suspend overdue users (for cron job)
     */
    public function autoSuspend() {
        $overdueUsers = $this->userModel->getOverdueUsers();
        $suspendedCount = 0;
        
        foreach ($overdueUsers as $user) {
            // Check if payment is overdue by more than 7 days
            $overdueDate = strtotime($user['next_payment_date']);
            $gracePeriod = strtotime('-7 days');
            
            if ($overdueDate < $gracePeriod) {
                try {
                    $this->userModel->suspendUser($user['id'], 'Auto-suspended: Payment overdue');
                    $suspendedCount++;
                } catch (Exception $e) {
                    error_log("Failed to auto-suspend user {$user['id']}: " . $e->getMessage());
                }
            }
        }
        
        // Return JSON response for API calls
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->json(['suspended_count' => $suspendedCount, 'message' => "Auto-suspended $suspendedCount users"]);
        } else {
            $this->setFlash('info', "Se suspendieron automáticamente $suspendedCount usuarios con pagos vencidos");
            $this->redirect('superadmin');
        }
    }
}
?>