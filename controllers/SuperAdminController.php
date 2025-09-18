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