<?php
/**
 * User Model
 * Sistema Básico Contable - ContaBot
 */

class User {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create a new user
     */
    public function create($email, $password, $name, $rfc = null, $user_type = 'personal') {
        $query = "INSERT INTO users (email, password, name, rfc, user_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        return $stmt->execute([$email, $hashed_password, $name, $rfc, $user_type]);
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = ? AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        return $stmt->fetch();
    }
    
    /**
     * Find user by ID
     */
    public function findById($id) {
        $query = "SELECT * FROM users WHERE id = ? AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Verify user credentials
     */
    public function verifyCredentials($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($id, $name, $rfc = null, $user_type = null) {
        $query = "UPDATE users SET name = ?, rfc = ?, user_type = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$name, $rfc, $user_type, $id]);
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $new_password) {
        $query = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        return $stmt->execute([$hashed_password, $id]);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Create password reset token
     */
    public function createPasswordResetToken($email) {
        // First check if user exists
        if (!$this->findByEmail($email)) {
            return false;
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete existing tokens for this email
        $deleteQuery = "DELETE FROM password_resets WHERE email = ?";
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->execute([$email]);
        
        // Insert new token
        $query = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$email, $token, $expires_at])) {
            return $token;
        }
        
        return false;
    }
    
    /**
     * Verify password reset token
     */
    public function verifyPasswordResetToken($token) {
        $query = "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$token]);
        
        return $stmt->fetch();
    }
    
    /**
     * Reset password with token
     */
    public function resetPasswordWithToken($token, $new_password) {
        $resetData = $this->verifyPasswordResetToken($token);
        
        if (!$resetData) {
            return false;
        }
        
        $user = $this->findByEmail($resetData['email']);
        if (!$user) {
            return false;
        }
        
        // Update password
        $success = $this->updatePassword($user['id'], $new_password);
        
        if ($success) {
            // Delete used token
            $deleteQuery = "DELETE FROM password_resets WHERE token = ?";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->execute([$token]);
        }
        
        return $success;
    }
}
?>