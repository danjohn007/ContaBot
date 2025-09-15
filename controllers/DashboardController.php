<?php
/**
 * Dashboard Controller
 * ContaBot - Sistema Básico Contable
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Transaction.php';

class DashboardController extends BaseController {
    private $userModel;
    private $transactionModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->transactionModel = new Transaction();
    }
    
    /**
     * Dashboard main page
     */
    public function index() {
        $this->requireLogin();
        
        $user = getCurrentUser();
        $userId = $user['id'];
        
        // Get current month summary
        $currentMonth = date('Y-m-01');
        $nextMonth = date('Y-m-01', strtotime('+1 month'));
        
        $monthlySummary = $this->transactionModel->getSummary(
            $currentMonth,
            $nextMonth,
            $userId
        );
        
        // Get current year summary
        $currentYear = date('Y-01-01');
        $nextYear = date('Y-01-01', strtotime('+1 year'));
        
        $yearlySummary = $this->transactionModel->getSummary(
            $currentYear,
            $nextYear,
            $userId
        );
        
        // Get fiscal summary for current year
        $fiscalSummary = $this->transactionModel->getFiscalSummary(
            date('Y'),
            $userId
        );
        
        // Get recent transactions
        $recentTransactions = $this->transactionModel->getRecent(10, $userId);
        
        // Get pending invoices
        $pendingInvoices = $this->transactionModel->getPendingInvoices($userId);
        
        // Get monthly data for chart
        $monthlyData = $this->transactionModel->getMonthlySummary(date('Y'), $userId);
        
        // Get category breakdown
        $categoryBreakdown = $this->transactionModel->getByCategory(
            $currentMonth,
            $nextMonth,
            $userId
        );
        
        // Calculate balance trend (last 6 months)
        $balanceTrend = $this->getBalanceTrend($userId);
        
        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'monthly_summary' => $monthlySummary ?: [
                'total_income' => 0,
                'total_expenses' => 0,
                'balance' => 0,
                'total_transactions' => 0
            ],
            'yearly_summary' => $yearlySummary ?: [
                'total_income' => 0,
                'total_expenses' => 0,
                'balance' => 0,
                'total_transactions' => 0
            ],
            'fiscal_summary' => $fiscalSummary ?: [
                'fiscal_expenses' => 0,
                'total_income' => 0,
                'fiscal_expense_count' => 0,
                'invoiced_count' => 0,
                'pending_invoice_count' => 0
            ],
            'recent_transactions' => $recentTransactions ?: [],
            'pending_invoices' => $pendingInvoices ?: [],
            'monthly_data' => $monthlyData ?: [],
            'category_breakdown' => $categoryBreakdown ?: [],
            'balance_trend' => $balanceTrend
        ]);
    }
    
    /**
     * Get balance trend for the last 6 months
     */
    private function getBalanceTrend($userId) {
        $trend = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-$i month"));
            $nextMonth = date('Y-m-01', strtotime("-$i month +1 month"));
            
            $summary = $this->transactionModel->getSummary($date, $nextMonth, $userId);
            
            $trend[] = [
                'month' => date('M Y', strtotime($date)),
                'income' => $summary['total_income'] ?? 0,
                'expenses' => $summary['total_expenses'] ?? 0,
                'balance' => $summary['balance'] ?? 0
            ];
        }
        
        return $trend;
    }
    
    /**
     * Get dashboard data via AJAX
     */
    public function getData() {
        $this->requireLogin();
        
        $type = $this->getGet('type');
        $period = $this->getGet('period', 'month');
        $userId = getCurrentUser()['id'];
        
        switch ($type) {
            case 'summary':
                $data = $this->getSummaryData($period, $userId);
                break;
            case 'chart':
                $data = $this->getChartData($period, $userId);
                break;
            case 'recent':
                $data = $this->transactionModel->getRecent(
                    $this->getGet('limit', 10),
                    $userId
                );
                break;
            default:
                $data = ['error' => 'Invalid data type'];
        }
        
        $this->json($data);
    }
    
    /**
     * Get summary data for a period
     */
    private function getSummaryData($period, $userId) {
        switch ($period) {
            case 'week':
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $endDate = date('Y-m-d', strtotime('sunday this week'));
                break;
            case 'month':
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                break;
            case 'year':
                $startDate = date('Y-01-01');
                $endDate = date('Y-12-31');
                break;
            default:
                $startDate = null;
                $endDate = null;
        }
        
        return $this->transactionModel->getSummary($startDate, $endDate, $userId);
    }
    
    /**
     * Get chart data for a period
     */
    private function getChartData($period, $userId) {
        switch ($period) {
            case 'month':
                // Daily data for current month
                $data = $this->getDailyData($userId);
                break;
            case 'year':
                // Monthly data for current year
                $data = $this->transactionModel->getMonthlySummary(date('Y'), $userId);
                break;
            default:
                $data = [];
        }
        
        return $data;
    }
    
    /**
     * Get daily data for current month
     */
    private function getDailyData($userId) {
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        
        $sql = "
            SELECT 
                DATE(transaction_date) as date,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses,
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance
            FROM transactions
            WHERE transaction_date BETWEEN ? AND ?
            AND user_id = ?
            GROUP BY DATE(transaction_date)
            ORDER BY transaction_date
        ";
        
        return $this->transactionModel->query($sql, [$startDate, $endDate, $userId]);
    }
    
    /**
     * Export dashboard data
     */
    public function export() {
        $this->requireLogin();
        
        $format = $this->getGet('format', 'csv');
        $type = $this->getGet('type', 'summary');
        $userId = getCurrentUser()['id'];
        
        switch ($type) {
            case 'summary':
                $data = $this->getSummaryData('year', $userId);
                $filename = 'resumen_' . date('Y') . '.' . $format;
                break;
            case 'transactions':
                $data = $this->transactionModel->getRecent(1000, $userId);
                $filename = 'transacciones_' . date('Y-m-d') . '.' . $format;
                break;
            default:
                $this->json(['error' => 'Invalid export type'], 400);
                return;
        }
        
        if ($format === 'csv') {
            $this->exportCSV($data, $filename);
        } else {
            $this->json(['error' => 'Unsupported format'], 400);
        }
    }
    
    /**
     * Export data as CSV
     */
    private function exportCSV($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($data)) {
            // Headers
            fputcsv($output, array_keys($data[0]));
            
            // Data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}