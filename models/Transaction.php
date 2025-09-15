<?php
/**
 * Transaction Model
 * ContaBot - Sistema Básico Contable
 */

require_once 'BaseModel.php';

class Transaction extends BaseModel {
    protected $table = 'transactions';
    
    /**
     * Get transactions with category and user info
     */
    public function getTransactionsWithDetails($conditions = [], $limit = null, $offset = 0) {
        $sql = "
            SELECT 
                t.*,
                c.name as category_name,
                c.type as category_type,
                c.is_fiscal,
                c.color as category_color,
                sc.name as subcategory_name,
                u.full_name as created_by_name,
                (SELECT COUNT(*) FROM transaction_attachments ta WHERE ta.transaction_id = t.id) as attachment_count
            FROM {$this->table} t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN subcategories sc ON t.subcategory_id = sc.id
            LEFT JOIN users u ON t.created_by = u.id
        ";
        
        $params = [];
        $whereClauses = [];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                if (strpos($field, '.') === false) {
                    $field = 't.' . $field;
                }
                $whereClauses[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get transaction summary for a period
     */
    public function getSummary($startDate = null, $endDate = null, $userId = null) {
        $sql = "
            SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance,
                COUNT(*) as total_transactions,
                COUNT(CASE WHEN type = 'income' THEN 1 END) as income_count,
                COUNT(CASE WHEN type = 'expense' THEN 1 END) as expense_count
            FROM {$this->table} t
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($startDate) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $endDate;
        }
        
        if ($userId) {
            $sql .= " AND t.user_id = ?";
            $params[] = $userId;
        }
        
        return $this->queryOne($sql, $params);
    }
    
    /**
     * Get fiscal summary
     */
    public function getFiscalSummary($year = null, $userId = null) {
        $year = $year ?? date('Y');
        
        $sql = "
            SELECT 
                SUM(CASE WHEN t.type = 'expense' AND c.is_fiscal = 1 THEN t.amount ELSE 0 END) as fiscal_expenses,
                SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as total_income,
                COUNT(CASE WHEN t.type = 'expense' AND c.is_fiscal = 1 THEN 1 END) as fiscal_expense_count,
                COUNT(CASE WHEN t.invoice_status = 'invoiced' THEN 1 END) as invoiced_count,
                COUNT(CASE WHEN t.invoice_status = 'pending' THEN 1 END) as pending_invoice_count
            FROM {$this->table} t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE YEAR(t.transaction_date) = ?
        ";
        
        $params = [$year];
        
        if ($userId) {
            $sql .= " AND t.user_id = ?";
            $params[] = $userId;
        }
        
        return $this->queryOne($sql, $params);
    }
    
    /**
     * Get monthly summary
     */
    public function getMonthlySummary($year = null, $userId = null) {
        $year = $year ?? date('Y');
        
        $sql = "
            SELECT 
                MONTH(transaction_date) as month,
                MONTHNAME(transaction_date) as month_name,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses,
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance,
                COUNT(*) as transaction_count
            FROM {$this->table}
            WHERE YEAR(transaction_date) = ?
        ";
        
        $params = [$year];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " GROUP BY MONTH(transaction_date), MONTHNAME(transaction_date) ORDER BY MONTH(transaction_date)";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get transactions by category
     */
    public function getByCategory($startDate = null, $endDate = null, $userId = null) {
        $sql = "
            SELECT 
                c.name as category_name,
                c.color as category_color,
                c.type as category_type,
                SUM(t.amount) as total_amount,
                COUNT(t.id) as transaction_count,
                AVG(t.amount) as avg_amount
            FROM {$this->table} t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.type = 'expense'
        ";
        
        $params = [];
        
        if ($startDate) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $endDate;
        }
        
        if ($userId) {
            $sql .= " AND t.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " GROUP BY c.id, c.name, c.color, c.type ORDER BY total_amount DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Search transactions
     */
    public function search($searchTerm, $filters = []) {
        $sql = "
            SELECT 
                t.*,
                c.name as category_name,
                sc.name as subcategory_name,
                u.full_name as created_by_name
            FROM {$this->table} t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN subcategories sc ON t.subcategory_id = sc.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE (
                t.concept LIKE ? OR 
                t.description LIKE ? OR 
                t.reference_number LIKE ? OR
                c.name LIKE ?
            )
        ";
        
        $searchParam = '%' . $searchTerm . '%';
        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        
        // Apply filters
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND t.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get recent transactions
     */
    public function getRecent($limit = 10, $userId = null) {
        $conditions = [];
        if ($userId) {
            $conditions['user_id'] = $userId;
        }
        
        return $this->getTransactionsWithDetails($conditions, $limit);
    }
    
    /**
     * Get pending invoices
     */
    public function getPendingInvoices($userId = null) {
        $conditions = ['invoice_status' => INVOICE_PENDING];
        if ($userId) {
            $conditions['user_id'] = $userId;
        }
        
        return $this->getTransactionsWithDetails($conditions);
    }
}