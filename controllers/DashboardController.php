<?php
/**
 * Dashboard Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class DashboardController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        requireLogin();
    }
    
    /**
     * Dashboard main page
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $userType = $_SESSION['user_type'];
        
        // Get summary data
        $summaryData = $this->getSummaryData($userId);
        $recentMovements = $this->getRecentMovements($userId);
        $monthlyData = $this->getMonthlyData($userId);
        
        $data = [
            'title' => 'Dashboard - ContaBot',
            'user_name' => $_SESSION['user_name'],
            'user_type' => $userType,
            'summary' => $summaryData,
            'recent_movements' => $recentMovements,
            'monthly_data' => $monthlyData,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('dashboard/index', $data);
    }
    
    /**
     * Get summary data for dashboard
     */
    private function getSummaryData($userId) {
        $currentMonth = date('Y-m');
        
        // Total income this month
        $incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total 
                       FROM movements 
                       WHERE user_id = ? AND type = 'income' 
                       AND DATE_FORMAT(movement_date, '%Y-%m') = ?";
        $incomeStmt = $this->db->prepare($incomeQuery);
        $incomeStmt->execute([$userId, $currentMonth]);
        $totalIncome = $incomeStmt->fetch()['total'];
        
        // Total expenses this month
        $expenseQuery = "SELECT COALESCE(SUM(amount), 0) as total 
                        FROM movements 
                        WHERE user_id = ? AND type = 'expense' 
                        AND DATE_FORMAT(movement_date, '%Y-%m') = ?";
        $expenseStmt = $this->db->prepare($expenseQuery);
        $expenseStmt->execute([$userId, $currentMonth]);
        $totalExpenses = $expenseStmt->fetch()['total'];
        
        // Balance
        $balance = $totalIncome - $totalExpenses;
        
        // Fiscal expenses this month
        $fiscalQuery = "SELECT COALESCE(SUM(amount), 0) as total 
                       FROM movements 
                       WHERE user_id = ? AND type = 'expense' 
                       AND classification = 'fiscal'
                       AND DATE_FORMAT(movement_date, '%Y-%m') = ?";
        $fiscalStmt = $this->db->prepare($fiscalQuery);
        $fiscalStmt->execute([$userId, $currentMonth]);
        $fiscalExpenses = $fiscalStmt->fetch()['total'];
        
        // Pending receipts
        $pendingQuery = "SELECT COUNT(*) as total 
                        FROM movements 
                        WHERE user_id = ? AND type = 'expense' 
                        AND is_billed = FALSE
                        AND DATE_FORMAT(movement_date, '%Y-%m') = ?";
        $pendingStmt = $this->db->prepare($pendingQuery);
        $pendingStmt->execute([$userId, $currentMonth]);
        $pendingReceipts = $pendingStmt->fetch()['total'];
        
        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'balance' => $balance,
            'fiscal_expenses' => $fiscalExpenses,
            'pending_receipts' => $pendingReceipts
        ];
    }
    
    /**
     * Get recent movements
     */
    private function getRecentMovements($userId, $limit = 5) {
        $query = "SELECT m.*, c.name as category_name, c.color as category_color
                 FROM movements m
                 LEFT JOIN categories c ON m.category_id = c.id
                 WHERE m.user_id = ?
                 ORDER BY m.movement_date DESC, m.created_at DESC
                 LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $userId);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get monthly data for charts
     */
    private function getMonthlyData($userId) {
        $query = "SELECT 
                    DATE_FORMAT(movement_date, '%Y-%m') as month,
                    type,
                    SUM(amount) as total
                 FROM movements 
                 WHERE user_id = ? 
                 AND movement_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY DATE_FORMAT(movement_date, '%Y-%m'), type
                 ORDER BY month";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        
        // Format data for charts
        $monthlyData = [
            'labels' => [],
            'income' => [],
            'expenses' => []
        ];
        
        $months = [];
        foreach ($results as $row) {
            if (!in_array($row['month'], $months)) {
                $months[] = $row['month'];
                $monthlyData['labels'][] = date('M Y', strtotime($row['month'] . '-01'));
                $monthlyData['income'][] = 0;
                $monthlyData['expenses'][] = 0;
            }
            
            $monthIndex = array_search($row['month'], $months);
            
            if ($row['type'] === 'income') {
                $monthlyData['income'][$monthIndex] = (float)$row['total'];
            } else {
                $monthlyData['expenses'][$monthIndex] = (float)$row['total'];
            }
        }
        
        return $monthlyData;
    }
}
?>