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
     * Get database-compatible date function for start of month
     */
    private function getStartOfMonthFunction() {
        if ($this->conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            return "date('now', 'start of month')";
        } else {
            return "DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        }
    }
    
    /**
     * Create a new user
     */
    public function create($email, $password, $name, $phone = null, $user_type = 'personal', $referralCode = null) {
        $query = "INSERT INTO users (email, password, name, phone, user_type, account_status, billing_status, referred_by) VALUES (?, ?, ?, ?, ?, 'pending', 'pending', ?)";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if ($stmt->execute([$email, $hashed_password, $name, $phone, $user_type, $referralCode])) {
            $userId = $this->conn->lastInsertId();
            
            // If there's a referral code, register the referral
            if ($referralCode) {
                $referralModel = new Referral($this->conn);
                $referralModel->registerReferral($referralCode, $userId);
            }
            
            // Generate referral link for the new user
            $referralModel = new Referral($this->conn);
            $referralModel->generateReferralLink($userId);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Find user by email - including pending users for SuperAdmin
     */
    public function findByEmail($email, $includePending = false) {
        if ($includePending) {
            $query = "SELECT * FROM users WHERE email = ?";
        } else {
            $query = "SELECT * FROM users WHERE email = ? AND account_status = 'active'";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        return $stmt->fetch();
    }
    
    /**
     * Find user by ID - including pending users for SuperAdmin
     */
    public function findById($id, $includePending = false) {
        if ($includePending) {
            $query = "SELECT * FROM users WHERE id = ?";
        } else {
            $query = "SELECT * FROM users WHERE id = ? AND account_status = 'active'";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Verify user credentials - allow SuperAdmin to access pending accounts
     */
    public function verifyCredentials($email, $password) {
        // First try with active users
        $user = $this->findByEmail($email);
        
        // If not found and not superadmin, try with pending users
        if (!$user) {
            $user = $this->findByEmail($email, true);
            
            // Only allow login for SuperAdmin or active accounts
            if ($user && $user['user_type'] !== 'superadmin' && $user['account_status'] !== 'active') {
                return false;
            }
        }
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get pending users for approval
     */
    public function getPendingUsers($limit = 50) {
        $query = "SELECT u.*, sp.name as plan_name, sp.price as plan_price 
                 FROM users u
                 LEFT JOIN subscription_plans sp ON u.subscription_plan = sp.type
                 WHERE u.account_status = 'pending' AND u.user_type != 'superadmin'
                 ORDER BY u.created_at DESC
                 LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Approve user and set subscription
     */
    public function approveUser($userId, $planType, $approvedBy) {
        $this->conn->beginTransaction();
        
        try {
            // Get plan details
            $planQuery = "SELECT * FROM subscription_plans WHERE type = ? AND is_active = 1";
            $planStmt = $this->conn->prepare($planQuery);
            $planStmt->execute([$planType]);
            $plan = $planStmt->fetch();
            
            if (!$plan) {
                throw new Exception("Plan no encontrado");
            }
            
            $startDate = date('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime("+{$plan['duration_months']} months"));
            $nextPaymentDate = ($plan['price'] > 0) ? $endDate : null;
            $billingStatus = ($plan['price'] > 0) ? 'pending' : 'paid';
            
            // Update user
            $userQuery = "UPDATE users SET 
                         account_status = 'active',
                         subscription_plan = ?,
                         subscription_start_date = ?,
                         subscription_end_date = ?,
                         billing_status = ?,
                         next_payment_date = ?,
                         approved_by = ?,
                         approved_at = CURRENT_TIMESTAMP,
                         updated_at = CURRENT_TIMESTAMP
                         WHERE id = ?";
            
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->execute([
                $planType, $startDate, $endDate, $billingStatus, 
                $nextPaymentDate, $approvedBy, $userId
            ]);
            
            // Create billing record if paid plan
            if ($plan['price'] > 0) {
                $billingQuery = "INSERT INTO billing_history (user_id, plan_id, amount, billing_period_start, billing_period_end, payment_status) 
                                VALUES (?, ?, ?, ?, ?, 'pending')";
                $billingStmt = $this->conn->prepare($billingQuery);
                $billingStmt->execute([$userId, $plan['id'], $plan['price'], $startDate, $endDate]);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Get users with overdue payments
     */
    public function getOverdueUsers() {
        $query = "SELECT u.*, sp.name as plan_name, sp.price as plan_price,
                         bh.amount as outstanding_amount, bh.billing_period_end
                 FROM users u
                 INNER JOIN subscription_plans sp ON u.subscription_plan = sp.type
                 LEFT JOIN billing_history bh ON u.id = bh.user_id AND bh.payment_status = 'pending'
                 WHERE u.account_status = 'active' 
                 AND u.billing_status = 'overdue'
                 AND u.next_payment_date < CURRENT_TIMESTAMP
                 ORDER BY u.next_payment_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Suspend user account for non-payment
     */
    public function suspendUser($userId, $reason = 'Non-payment') {
        $query = "UPDATE users SET 
                 account_status = 'suspended',
                 billing_status = 'overdue',
                 updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Register payment for a billing record
     */
    public function registerPayment($billingId, $paymentMethod, $transactionId = null, $notes = null, $paymentDate = null) {
        if (!$paymentDate) {
            $paymentDate = date('Y-m-d H:i:s');
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Update billing record
            $query = "UPDATE billing_history SET 
                     payment_status = 'paid',
                     payment_method = ?,
                     transaction_id = ?,
                     notes = ?,
                     payment_date = ?,
                     updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$paymentMethod, $transactionId, $notes, $paymentDate, $billingId]);
            
            // Get user_id and plan details from billing record
            $billingQuery = "SELECT bh.user_id, bh.billing_period_end, sp.type, sp.duration_months
                            FROM billing_history bh
                            INNER JOIN subscription_plans sp ON bh.plan_id = sp.id
                            WHERE bh.id = ?";
            $billingStmt = $this->conn->prepare($billingQuery);
            $billingStmt->execute([$billingId]);
            $billingInfo = $billingStmt->fetch();
            
            if ($billingInfo) {
                // Calculate next payment date
                $nextPaymentDate = date('Y-m-d H:i:s', strtotime($billingInfo['billing_period_end'] . ' +' . $billingInfo['duration_months'] . ' months'));
                
                // Update user billing status and next payment date
                $userQuery = "UPDATE users SET 
                             billing_status = 'paid',
                             last_payment_date = ?,
                             next_payment_date = ?,
                             updated_at = CURRENT_TIMESTAMP
                             WHERE id = ?";
                
                $userStmt = $this->conn->prepare($userQuery);
                $userStmt->execute([$paymentDate, $nextPaymentDate, $billingInfo['user_id']]);
                
                // Create next billing period record
                $nextBillingStart = $billingInfo['billing_period_end'];
                $nextBillingEnd = date('Y-m-d', strtotime($nextPaymentDate . ' -1 day'));
                
                // Get plan price for next billing period
                $planQuery = "SELECT id, price FROM subscription_plans WHERE type = ?";
                $planStmt = $this->conn->prepare($planQuery);
                $planStmt->execute([$billingInfo['type']]);
                $plan = $planStmt->fetch();
                
                if ($plan && $plan['price'] > 0) {
                    $nextBillingQuery = "INSERT INTO billing_history (user_id, plan_id, amount, billing_period_start, billing_period_end, payment_status) 
                                        VALUES (?, ?, ?, ?, ?, 'pending')";
                    $nextBillingStmt = $this->conn->prepare($nextBillingQuery);
                    $nextBillingStmt->execute([
                        $billingInfo['user_id'], 
                        $plan['id'], 
                        $plan['price'], 
                        $nextBillingStart, 
                        $nextBillingEnd
                    ]);
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Register advance payment for next billing period
     */
    public function registerAdvancePayment($billingId, $paymentMethod, $transactionId = null, $notes = null, $paymentDate = null) {
        if (!$paymentDate) {
            $paymentDate = date('Y-m-d H:i:s');
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Get current billing record details
            $billingQuery = "SELECT bh.user_id, bh.billing_period_end, sp.type, sp.duration_months, sp.id as plan_id, sp.price
                            FROM billing_history bh
                            INNER JOIN subscription_plans sp ON bh.plan_id = sp.id
                            WHERE bh.id = ?";
            $billingStmt = $this->conn->prepare($billingQuery);
            $billingStmt->execute([$billingId]);
            $billingInfo = $billingStmt->fetch();
            
            if (!$billingInfo) {
                throw new Exception("Registro de facturación no encontrado");
            }
            
            // Calculate next billing period dates
            $nextBillingStart = $billingInfo['billing_period_end'];
            $nextBillingEnd = date('Y-m-d', strtotime($nextBillingStart . ' +' . $billingInfo['duration_months'] . ' months -1 day'));
            
            // Create advance payment billing record
            $advanceBillingQuery = "INSERT INTO billing_history (user_id, plan_id, amount, billing_period_start, billing_period_end, payment_status, payment_method, transaction_id, notes, payment_date) 
                                   VALUES (?, ?, ?, ?, ?, 'paid', ?, ?, ?, ?)";
            $advanceBillingStmt = $this->conn->prepare($advanceBillingQuery);
            $advanceBillingStmt->execute([
                $billingInfo['user_id'], 
                $billingInfo['plan_id'], 
                $billingInfo['price'], 
                $nextBillingStart, 
                $nextBillingEnd,
                $paymentMethod,
                $transactionId,
                'Adelanto de pago - ' . ($notes ?? ''),
                $paymentDate
            ]);
            
            // Update user's next payment date to the period after the advance payment
            $futurePaymentDate = date('Y-m-d H:i:s', strtotime($nextBillingEnd . ' +' . $billingInfo['duration_months'] . ' months'));
            
            $userQuery = "UPDATE users SET 
                         last_payment_date = ?,
                         next_payment_date = ?,
                         updated_at = CURRENT_TIMESTAMP
                         WHERE id = ?";
            
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->execute([$paymentDate, $futurePaymentDate, $billingInfo['user_id']]);
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Create advance payment for user without pending billing
     */
    public function createAdvancePayment($userId, $paymentMethod, $transactionId = null, $notes = null, $paymentDate = null) {
        if (!$paymentDate) {
            $paymentDate = date('Y-m-d H:i:s');
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Get user's current plan information
            $userQuery = "SELECT u.*, sp.id as plan_id, sp.type, sp.duration_months, sp.price
                         FROM users u
                         INNER JOIN subscription_plans sp ON u.subscription_plan = sp.type
                         WHERE u.id = ?";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->execute([$userId]);
            $userInfo = $userStmt->fetch();
            
            if (!$userInfo) {
                throw new Exception("Usuario o plan no encontrado");
            }
            
            // Calculate billing period dates based on user's next payment date
            $nextBillingStart = $userInfo['next_payment_date'] ? date('Y-m-d', strtotime($userInfo['next_payment_date'])) : date('Y-m-d');
            $nextBillingEnd = date('Y-m-d', strtotime($nextBillingStart . ' +' . $userInfo['duration_months'] . ' months -1 day'));
            
            // Create advance payment billing record
            $advanceBillingQuery = "INSERT INTO billing_history (user_id, plan_id, amount, billing_period_start, billing_period_end, payment_status, payment_method, transaction_id, notes, payment_date) 
                                   VALUES (?, ?, ?, ?, ?, 'paid', ?, ?, ?, ?)";
            $advanceBillingStmt = $this->conn->prepare($advanceBillingQuery);
            $advanceBillingStmt->execute([
                $userId, 
                $userInfo['plan_id'], 
                $userInfo['price'], 
                $nextBillingStart, 
                $nextBillingEnd,
                $paymentMethod,
                $transactionId,
                'Adelanto de pago - ' . ($notes ?? ''),
                $paymentDate
            ]);
            
            // Update user's next payment date to the period after the advance payment
            $futurePaymentDate = date('Y-m-d H:i:s', strtotime($nextBillingEnd . ' +' . $userInfo['duration_months'] . ' months'));
            
            $userUpdateQuery = "UPDATE users SET 
                               last_payment_date = ?,
                               next_payment_date = ?,
                               updated_at = CURRENT_TIMESTAMP
                               WHERE id = ?";
            
            $userUpdateStmt = $this->conn->prepare($userUpdateQuery);
            $userUpdateStmt->execute([$paymentDate, $futurePaymentDate, $userId]);
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Get financial dashboard data for SuperAdmin
     */
    public function getFinancialStats() {
        $stats = [];
        
        // Total active users
        $query = "SELECT COUNT(*) as total FROM users WHERE account_status = 'active' AND user_type != 'superadmin'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch()['total'];
        
        // Pending approvals
        $query = "SELECT COUNT(*) as total FROM users WHERE account_status = 'pending' AND user_type != 'superadmin'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending_users'] = $stmt->fetch()['total'];
        
        // Monthly revenue
        $startOfMonth = $this->getStartOfMonthFunction();
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM billing_history 
                 WHERE payment_status = 'paid' 
                 AND payment_date >= $startOfMonth";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['monthly_revenue'] = $stmt->fetch()['total'];
        
        // Outstanding payments
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM billing_history 
                 WHERE payment_status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['outstanding_payments'] = $stmt->fetch()['total'];
        
        // Users by plan
        $query = "SELECT subscription_plan, COUNT(*) as count 
                 FROM users 
                 WHERE account_status = 'active' AND user_type != 'superadmin' 
                 GROUP BY subscription_plan";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['users_by_plan'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($id, $name, $rfc = null, $user_type = null, $phone = null) {
        $query = "UPDATE users SET name = ?, rfc = ?, user_type = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$name, $rfc, $user_type, $phone, $id]);
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
     * Check if phone exists
     */
    public function phoneExists($phone, $exclude_id = null) {
        if (empty($phone)) {
            return false;
        }
        
        $query = "SELECT id FROM users WHERE phone = ?";
        $params = [$phone];
        
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