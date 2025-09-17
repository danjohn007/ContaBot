<?php
/**
 * Category Model
 * Sistema Básico Contable - ContaBot
 */

class Category {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all categories for a user
     */
    public function getUserCategories($userId) {
        $query = "SELECT * FROM categories WHERE user_id = ? ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get category by ID
     */
    public function getById($id, $userId = null) {
        $query = "SELECT * FROM categories WHERE id = ?";
        $params = [$id];
        
        if ($userId) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
    
    /**
     * Create a new category
     */
    public function create($userId, $name, $description = null, $color = '#007bff') {
        $query = "INSERT INTO categories (user_id, name, description, color) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$userId, $name, $description, $color])) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update category
     */
    public function update($id, $userId, $name, $description = null, $color = '#007bff') {
        $query = "UPDATE categories SET name = ?, description = ?, color = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$name, $description, $color, $id, $userId]);
    }
    
    /**
     * Delete category
     */
    public function delete($id, $userId) {
        // First check if category has movements
        $checkQuery = "SELECT COUNT(*) as count FROM movements WHERE category_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar la categoría porque tiene movimientos asociados'];
        }
        
        $query = "DELETE FROM categories WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$id, $userId])) {
            return ['success' => true, 'message' => 'Categoría eliminada exitosamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar la categoría'];
    }
    
    /**
     * Check if category name exists for user
     */
    public function nameExists($userId, $name, $excludeId = null) {
        $query = "SELECT id FROM categories WHERE user_id = ? AND name = ?";
        $params = [$userId, $name];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Create default categories for new user
     */
    public function createDefaultCategories($userId) {
        $defaultCategories = [
            ['name' => 'Alimentación', 'description' => 'Gastos en comida y bebidas', 'color' => '#28a745'],
            ['name' => 'Transporte', 'description' => 'Gastos de transporte y combustible', 'color' => '#007bff'],
            ['name' => 'Servicios', 'description' => 'Servicios públicos y suscripciones', 'color' => '#ffc107'],
            ['name' => 'Salud', 'description' => 'Gastos médicos y medicamentos', 'color' => '#dc3545'],
            ['name' => 'Entretenimiento', 'description' => 'Gastos de ocio y entretenimiento', 'color' => '#fd7e14'],
            ['name' => 'Ingresos', 'description' => 'Ingresos generales', 'color' => '#17a2b8']
        ];
        
        foreach ($defaultCategories as $category) {
            $this->create($userId, $category['name'], $category['description'], $category['color']);
        }
    }
    
    /**
     * Get categories with movement count
     */
    public function getCategoriesWithStats($userId) {
        $query = "SELECT c.*, 
                         COUNT(m.id) as movement_count,
                         COALESCE(SUM(CASE WHEN m.type = 'expense' THEN m.amount ELSE 0 END), 0) as total_expenses,
                         COALESCE(SUM(CASE WHEN m.type = 'income' THEN m.amount ELSE 0 END), 0) as total_income
                  FROM categories c
                  LEFT JOIN movements m ON c.id = m.category_id
                  WHERE c.user_id = ?
                  GROUP BY c.id
                  ORDER BY c.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
}
?>