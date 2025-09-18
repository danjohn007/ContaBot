<?php
/**
 * UserAccount Model
 * Sistema Básico Contable - ContaBot
 */

class UserAccount {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Add a child user to a parent account
     */
    public function addChildUser($parentUserId, $childUserId, $accessLevel = 'basic', $canCreateMovements = true, $canViewReports = true, $canEditMovements = false, $canDeleteMovements = false) {
        $query = "INSERT INTO user_accounts (parent_user_id, child_user_id, access_level, can_create_movements, can_view_reports, can_edit_movements, can_delete_movements) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$parentUserId, $childUserId, $accessLevel, $canCreateMovements, $canViewReports, $canEditMovements, $canDeleteMovements]);
    }
    
    /**
     * Get child users for a parent account
     */
    public function getChildUsers($parentUserId) {
        $query = "SELECT ua.*, u.name, u.email, u.account_status
                 FROM user_accounts ua 
                 JOIN users u ON ua.child_user_id = u.id 
                 WHERE ua.parent_user_id = ? 
                 ORDER BY u.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$parentUserId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Check if user has access to another user's account
     */
    public function hasAccess($parentUserId, $childUserId) {
        $query = "SELECT id FROM user_accounts WHERE parent_user_id = ? AND child_user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$parentUserId, $childUserId]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get user permissions for a specific account
     */
    public function getUserPermissions($parentUserId, $childUserId) {
        $query = "SELECT * FROM user_accounts WHERE parent_user_id = ? AND child_user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$parentUserId, $childUserId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Update user permissions
     */
    public function updateUserPermissions($parentUserId, $childUserId, $accessLevel, $canCreateMovements, $canViewReports, $canEditMovements = false, $canDeleteMovements = false) {
        $query = "UPDATE user_accounts 
                 SET access_level = ?, can_create_movements = ?, can_view_reports = ?, can_edit_movements = ?, can_delete_movements = ?, updated_at = CURRENT_TIMESTAMP 
                 WHERE parent_user_id = ? AND child_user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$accessLevel, $canCreateMovements, $canViewReports, $canEditMovements, $canDeleteMovements, $parentUserId, $childUserId]);
    }
    
    /**
     * Remove child user from parent account
     */
    public function removeChildUser($parentUserId, $childUserId) {
        $query = "DELETE FROM user_accounts WHERE parent_user_id = ? AND child_user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$parentUserId, $childUserId]);
    }
    
    /**
     * Get parent users for a child user
     */
    public function getParentUsers($childUserId) {
        $query = "SELECT ua.*, u.name as parent_name, u.email as parent_email
                 FROM user_accounts ua 
                 JOIN users u ON ua.parent_user_id = u.id 
                 WHERE ua.child_user_id = ? 
                 ORDER BY u.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$childUserId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Check if user can create movements in an account
     */
    public function canCreateMovements($parentUserId, $childUserId) {
        $permissions = $this->getUserPermissions($parentUserId, $childUserId);
        return $permissions && $permissions['can_create_movements'];
    }
    
    /**
     * Check if user can view reports for an account
     */
    public function canViewReports($parentUserId, $childUserId) {
        $permissions = $this->getUserPermissions($parentUserId, $childUserId);
        return $permissions && $permissions['can_view_reports'];
    }
    
    /**
     * Check if user can edit movements in an account
     */
    public function canEditMovements($parentUserId, $childUserId) {
        $permissions = $this->getUserPermissions($parentUserId, $childUserId);
        return $permissions && $permissions['can_edit_movements'];
    }
    
    /**
     * Check if user can delete movements in an account
     */
    public function canDeleteMovements($parentUserId, $childUserId) {
        $permissions = $this->getUserPermissions($parentUserId, $childUserId);
        return $permissions && $permissions['can_delete_movements'];
    }
}
?>