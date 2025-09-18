<?php
/**
 * Movement Model
 * Sistema Básico Contable - ContaBot
 */

class Movement {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get movements for a user with pagination and filters
     */
    public function getUserMovements($userId, $filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT m.*, c.name as category_name, c.color as category_color
                  FROM movements m
                  LEFT JOIN categories c ON m.category_id = c.id
                  WHERE m.user_id = ?";
        
        $params = [$userId];
        
        // Apply filters
        if (!empty($filters['date_from'])) {
            $query .= " AND m.movement_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND m.movement_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['type'])) {
            $query .= " AND m.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $query .= " AND m.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['classification'])) {
            $query .= " AND m.classification = ?";
            $params[] = $filters['classification'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (m.concept LIKE ? OR m.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY m.movement_date DESC, m.created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind all parameters except LIMIT/OFFSET
        for ($i = 0; $i < count($params); $i++) {
            $stmt->bindValue($i + 1, $params[$i]);
        }
        
        // Bind LIMIT and OFFSET as integers
        $stmt->bindValue(count($params) + 1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get total count of movements for pagination
     */
    public function getUserMovementsCount($userId, $filters = []) {
        $query = "SELECT COUNT(*) as total FROM movements m WHERE m.user_id = ?";
        $params = [$userId];
        
        // Apply same filters as getUserMovements
        if (!empty($filters['date_from'])) {
            $query .= " AND m.movement_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND m.movement_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['type'])) {
            $query .= " AND m.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $query .= " AND m.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['classification'])) {
            $query .= " AND m.classification = ?";
            $params[] = $filters['classification'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (m.concept LIKE ? OR m.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch()['total'];
    }
    
    /**
     * Get movement by ID
     */
    public function getById($id, $userId = null) {
        $query = "SELECT m.*, c.name as category_name, c.color as category_color
                  FROM movements m
                  LEFT JOIN categories c ON m.category_id = c.id
                  WHERE m.id = ?";
        
        $params = [$id];
        
        if ($userId) {
            $query .= " AND m.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
    
    /**
     * Create a new movement
     */
    public function create($data) {
        $query = "INSERT INTO movements (
                    user_id, category_id, type, amount, concept, description,
                    movement_date, classification, payment_method, receipt_file, is_billed
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $params = [
            $data['user_id'],
            $data['category_id'],
            $data['type'],
            $data['amount'],
            $data['concept'],
            $data['description'] ?? null,
            $data['movement_date'],
            $data['classification'],
            $data['payment_method'],
            $data['receipt_file'] ?? null,
            $data['is_billed'] ?? false
        ];
        
        if ($stmt->execute($params)) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update movement
     */
    public function update($id, $userId, $data) {
        $query = "UPDATE movements SET 
                    category_id = ?, type = ?, amount = ?, concept = ?, description = ?,
                    movement_date = ?, classification = ?, payment_method = ?, 
                    receipt_file = ?, is_billed = ?, updated_at = CURRENT_TIMESTAMP
                  WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $params = [
            $data['category_id'],
            $data['type'],
            $data['amount'],
            $data['concept'],
            $data['description'] ?? null,
            $data['movement_date'],
            $data['classification'],
            $data['payment_method'],
            $data['receipt_file'] ?? null,
            $data['is_billed'] ?? false,
            $id,
            $userId
        ];
        
        return $stmt->execute($params);
    }
    
    /**
     * Delete movement
     */
    public function delete($id, $userId) {
        // Get movement info before deleting to handle file cleanup
        $movement = $this->getById($id, $userId);
        
        if (!$movement) {
            return ['success' => false, 'message' => 'Movimiento no encontrado'];
        }
        
        $query = "DELETE FROM movements WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$id, $userId])) {
            // Delete associated file if exists
            if ($movement['receipt_file'] && file_exists(UPLOAD_PATH . $movement['receipt_file'])) {
                unlink(UPLOAD_PATH . $movement['receipt_file']);
            }
            
            return ['success' => true, 'message' => 'Movimiento eliminado exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar el movimiento'];
    }
    
    /**
     * Get summary statistics
     */
    public function getSummaryStats($userId, $dateFrom = null, $dateTo = null) {
        $query = "SELECT 
                    type,
                    classification,
                    COUNT(*) as count,
                    SUM(amount) as total
                  FROM movements
                  WHERE user_id = ?";
        
        $params = [$userId];
        
        if ($dateFrom) {
            $query .= " AND movement_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $query .= " AND movement_date <= ?";
            $params[] = $dateTo;
        }
        
        $query .= " GROUP BY type, classification";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get monthly summary
     */
    public function getMonthlySummary($userId, $months = 12) {
        $query = "SELECT 
                    DATE_FORMAT(movement_date, '%Y-%m') as month,
                    type,
                    classification,
                    SUM(amount) as total
                  FROM movements
                  WHERE user_id = ? 
                  AND movement_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                  GROUP BY DATE_FORMAT(movement_date, '%Y-%m'), type, classification
                  ORDER BY month DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $months]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Upload receipt file
     */
    public function uploadReceiptFile($file, $movementId) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = MAX_UPLOAD_SIZE;
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF y PDF.'];
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 5MB.'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'receipt_' . $movementId . '_' . time() . '.' . $extension;
        $filepath = UPLOAD_PATH . $filename;
        
        // Create upload directory if it doesn't exist
        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
    
    /**
     * Update billing status
     */
    public function updateBillingStatus($id, $userId, $isBilled) {
        $query = "UPDATE movements SET is_billed = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$isBilled, $id, $userId]);
    }
}
?>