<?php
/**
 * User Model
 * ContaBot - Sistema Básico Contable
 */

require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    
    /**
     * Find user by username
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? AND active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create user with hashed password
     */
    public function createUser($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        // Set default values
        $data['active'] = $data['active'] ?? true;
        $data['role'] = $data['role'] ?? ROLE_CAPTURISTA;
        
        return $this->create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password_hash' => $passwordHash]);
    }
    
    /**
     * Get active users by role
     */
    public function getUsersByRole($role) {
        return $this->findAll(['role' => $role, 'active' => 1], 'full_name ASC');
    }
    
    /**
     * Get all active users
     */
    public function getActiveUsers() {
        return $this->findAll(['active' => 1], 'full_name ASC');
    }
    
    /**
     * Deactivate user (soft delete)
     */
    public function deactivateUser($userId) {
        return $this->update($userId, ['active' => 0]);
    }
    
    /**
     * Activate user
     */
    public function activateUser($userId) {
        return $this->update($userId, ['active' => 1]);
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Check if email exists
     */ 
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get user stats
     */
    public function getUserStats($userId) {
        $sql = "
            SELECT 
                COUNT(t.id) as total_transactions,
                SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as total_income,
                SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) as total_expenses,
                COUNT(CASE WHEN t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as transactions_last_30_days
            FROM users u
            LEFT JOIN transactions t ON u.id = t.user_id
            WHERE u.id = ?
            GROUP BY u.id
        ";
        
        return $this->queryOne($sql, [$userId]);
    }
}