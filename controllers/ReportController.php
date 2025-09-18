<?php
/**
 * Report Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class ReportController extends BaseController {
    private $movementModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        requireLogin();
        $this->movementModel = new Movement($this->db);
        $this->categoryModel = new Category($this->db);
    }
    
    /**
     * Main reports dashboard
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Get date range (default to current month)
        $dateFrom = $this->get('date_from', date('Y-m-01'));
        $dateTo = $this->get('date_to', date('Y-m-t'));
        
        // Get summary statistics
        $summaryStats = $this->movementModel->getSummaryStats($userId, $dateFrom, $dateTo);
        
        // Process statistics
        $summary = [
            'total_income' => 0,
            'total_expenses' => 0,
            'fiscal_expenses' => 0,
            'personal_expenses' => 0,
            'business_expenses' => 0,
            'balance' => 0
        ];
        
        foreach ($summaryStats as $stat) {
            if ($stat['type'] === 'income') {
                $summary['total_income'] += $stat['total'];
            } else {
                $summary['total_expenses'] += $stat['total'];
                
                switch ($stat['classification']) {
                    case 'fiscal':
                        $summary['fiscal_expenses'] += $stat['total'];
                        break;
                    case 'personal':
                        $summary['personal_expenses'] += $stat['total'];
                        break;
                    case 'business':
                        $summary['business_expenses'] += $stat['total'];
                        break;
                }
            }
        }
        
        $summary['balance'] = $summary['total_income'] - $summary['total_expenses'];
        
        // Get category breakdown
        $categoryStats = $this->getCategoryBreakdown($userId, $dateFrom, $dateTo);
        
        // Get monthly trend
        $monthlyTrend = $this->movementModel->getMonthlySummary($userId, 6);
        
        $data = [
            'title' => 'Reportes - ContaBot',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => $summary,
            'category_stats' => $categoryStats,
            'monthly_trend' => $monthlyTrend,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('reports/index', $data);
    }
    
    /**
     * Generate fiscal report
     */
    public function fiscal() {
        $userId = $_SESSION['user_id'];
        
        // Get fiscal year (default to current year)
        $year = $this->get('year', date('Y'));
        $dateFrom = $year . '-01-01';
        $dateTo = $year . '-12-31';
        
        // Get fiscal movements (billed movements)
        $fiscalMovements = $this->movementModel->getUserMovements($userId, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'is_billed' => true
        ], 1, 1000); // Get all billed movements
        
        // Calculate fiscal summary
        $fiscalSummary = [
            'deductible_expenses' => 0,
            'taxable_income' => 0,
            'total_movements' => count($fiscalMovements),
            'billed_count' => 0,
            'pending_count' => 0
        ];
        
        foreach ($fiscalMovements as $movement) {
            if ($movement['type'] === 'income') {
                $fiscalSummary['taxable_income'] += $movement['amount'];
            } else {
                $fiscalSummary['deductible_expenses'] += $movement['amount'];
            }
            
            if ($movement['is_billed']) {
                $fiscalSummary['billed_count']++;
            } else {
                $fiscalSummary['pending_count']++;
            }
        }
        
        $data = [
            'title' => 'Reporte Fiscal - ContaBot',
            'year' => $year,
            'fiscal_summary' => $fiscalSummary,
            'fiscal_movements' => $fiscalMovements,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('reports/fiscal', $data);
    }
    
    /**
     * Export data to CSV
     */
    public function export() {
        $userId = $_SESSION['user_id'];
        $format = $this->get('format', 'csv');
        
        // Get date range
        $dateFrom = $this->get('date_from', date('Y-m-01'));
        $dateTo = $this->get('date_to', date('Y-m-t'));
        
        // Get movements
        $movements = $this->movementModel->getUserMovements($userId, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ], 1, 10000); // Get all movements in range
        
        if ($format === 'csv') {
            $this->exportToCsv($movements, $dateFrom, $dateTo);
        } else {
            $this->setFlash('error', 'Formato de exportación no soportado.');
            $this->redirect('reports');
        }
    }
    
    /**
     * Export movements to CSV
     */
    private function exportToCsv($movements, $dateFrom, $dateTo) {
        $fileName = 'movimientos_' . $dateFrom . '_' . $dateTo . '.csv';
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, [
            'Fecha',
            'Tipo',
            'Concepto',
            'Descripción',
            'Categoría',
            'Clasificación',
            'Método de Pago',
            'Monto',
            'Facturado',
            'Comprobante'
        ]);
        
        // CSV Data
        foreach ($movements as $movement) {
            fputcsv($output, [
                $movement['movement_date'],
                $movement['type'] === 'income' ? 'Ingreso' : 'Gasto',
                $movement['concept'],
                $movement['description'] ?? '',
                $movement['category_name'],
                ucfirst($movement['classification']),
                ucfirst($movement['payment_method']),
                $movement['amount'],
                $movement['is_billed'] ? 'Sí' : 'No',
                $movement['receipt_file'] ? 'Sí' : 'No'
            ]);
        }
        
        fclose($output);
        exit();
    }
    
    /**
     * Get category breakdown for the given period
     */
    private function getCategoryBreakdown($userId, $dateFrom, $dateTo) {
        $query = "SELECT c.name, c.color, 
                         SUM(CASE WHEN m.type = 'income' THEN m.amount ELSE 0 END) as income,
                         SUM(CASE WHEN m.type = 'expense' THEN m.amount ELSE 0 END) as expenses,
                         COUNT(m.id) as movement_count
                  FROM categories c
                  LEFT JOIN movements m ON c.id = m.category_id 
                                        AND m.movement_date >= ? 
                                        AND m.movement_date <= ?
                  WHERE c.user_id = ?
                  GROUP BY c.id, c.name, c.color
                  HAVING movement_count > 0
                  ORDER BY expenses DESC, income DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFrom, $dateTo, $userId]);
        
        return $stmt->fetchAll();
    }
}
?>