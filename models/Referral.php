<?php
/**
 * Referral Model
 * Sistema Básico Contable - ContaBot
 */

class Referral {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Generate a unique referral code for a user
     */
    public function generateReferralLink($userId, $commissionRate = 10.00) {
        // Generate unique referral code
        do {
            $referralCode = 'REF_' . strtoupper(bin2hex(random_bytes(8)));
            $exists = $this->checkReferralCodeExists($referralCode);
        } while ($exists);
        
        $query = "INSERT INTO referral_links (user_id, referral_code, commission_rate) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$userId, $referralCode, $commissionRate])) {
            return $referralCode;
        }
        
        return false;
    }
    
    /**
     * Check if referral code exists
     */
    private function checkReferralCodeExists($referralCode) {
        $query = "SELECT id FROM referral_links WHERE referral_code = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$referralCode]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get referral link by user ID
     */
    public function getReferralLinkByUserId($userId) {
        $query = "SELECT * FROM referral_links WHERE user_id = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get referral link by code
     */
    public function getReferralLinkByCode($referralCode) {
        $query = "SELECT * FROM referral_links WHERE referral_code = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$referralCode]);
        
        return $stmt->fetch();
    }
    
    /**
     * Update commission rate for a user
     */
    public function updateCommissionRate($userId, $commissionRate) {
        $query = "UPDATE referral_links SET commission_rate = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$commissionRate, $userId]);
    }
    
    /**
     * Register a referral when someone signs up with a referral code
     */
    public function registerReferral($referralCode, $newUserId) {
        $referralLink = $this->getReferralLinkByCode($referralCode);
        
        if (!$referralLink) {
            return false;
        }
        
        $query = "INSERT INTO referral_registrations (referrer_id, referred_user_id, referral_code, commission_amount) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $referralLink['user_id'],
            $newUserId,
            $referralCode,
            $referralLink['commission_rate']
        ]);
    }
    
    /**
     * Get referrals for a user (people they referred)
     */
    public function getUserReferrals($userId) {
        $query = "SELECT rr.*, u.name as referred_name, u.email as referred_email, u.created_at as registration_date
                 FROM referral_registrations rr 
                 JOIN users u ON rr.referred_user_id = u.id 
                 WHERE rr.referrer_id = ? 
                 ORDER BY rr.registered_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get earnings summary for a user
     */
    public function getUserEarnings($userId) {
        $query = "SELECT 
                    COUNT(*) as total_referrals,
                    SUM(commission_amount) as total_earned,
                    SUM(CASE WHEN commission_status = 'paid' THEN commission_amount ELSE 0 END) as total_paid,
                    SUM(CASE WHEN commission_status = 'pending' THEN commission_amount ELSE 0 END) as total_pending
                 FROM referral_registrations 
                 WHERE referrer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get earnings by month for charts
     */
    public function getUserEarningsByMonth($userId, $months = 12) {
        if ($this->conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $dateFormat = "strftime('%Y-%m', registered_at)";
            $dateFilter = "date('now', '-$months months')";
        } else {
            $dateFormat = "DATE_FORMAT(registered_at, '%Y-%m')";
            $dateFilter = "DATE_SUB(CURDATE(), INTERVAL $months MONTH)";
        }
        
        $query = "SELECT 
                    $dateFormat as month,
                    COUNT(*) as referrals,
                    SUM(commission_amount) as earnings
                 FROM referral_registrations 
                 WHERE referrer_id = ? AND registered_at >= $dateFilter
                 GROUP BY $dateFormat
                 ORDER BY month";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all referrals for SuperAdmin (commission management)
     */
    public function getAllReferrals($limit = 50, $offset = 0) {
        $query = "SELECT rr.*, 
                    u1.name as referrer_name, u1.email as referrer_email,
                    u2.name as referred_name, u2.email as referred_email,
                    cp.amount as payment_amount, cp.payment_date, cp.evidence_file
                 FROM referral_registrations rr 
                 JOIN users u1 ON rr.referrer_id = u1.id 
                 JOIN users u2 ON rr.referred_user_id = u2.id 
                 LEFT JOIN commission_payments cp ON rr.id = cp.referral_registration_id
                 ORDER BY rr.registered_at DESC 
                 LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Record commission payment
     */
    public function recordCommissionPayment($referralRegistrationId, $amount, $paymentMethod, $createdBy, $evidenceFile = null, $notes = null) {
        $this->conn->beginTransaction();
        
        try {
            // Insert payment record
            $query = "INSERT INTO commission_payments (user_id, referral_registration_id, amount, payment_method, evidence_file, notes, created_by) 
                     SELECT referrer_id, ?, ?, ?, ?, ?, ? 
                     FROM referral_registrations 
                     WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$referralRegistrationId, $amount, $paymentMethod, $evidenceFile, $notes, $createdBy, $referralRegistrationId]);
            
            // Update referral registration status
            $query = "UPDATE referral_registrations SET commission_status = 'paid' WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$referralRegistrationId]);
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Get commission payments for a user
     */
    public function getUserCommissionPayments($userId) {
        $query = "SELECT cp.*, rr.referred_user_id, u.name as referred_name 
                 FROM commission_payments cp 
                 JOIN referral_registrations rr ON cp.referral_registration_id = rr.id 
                 JOIN users u ON rr.referred_user_id = u.id 
                 WHERE cp.user_id = ? 
                 ORDER BY cp.payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
}
?>