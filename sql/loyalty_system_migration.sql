-- Loyalty System Migration Script
-- Sistema Básico Contable - ContaBot
-- This script adds the missing referral system tables and updates user schema

-- Add phone field to users table (replace RFC functionality)
ALTER TABLE users ADD COLUMN phone VARCHAR(15) NULL AFTER rfc;
ALTER TABLE users ADD COLUMN referred_by VARCHAR(255) NULL AFTER phone;

-- Create referral_links table
CREATE TABLE IF NOT EXISTS referral_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referral_code VARCHAR(50) NOT NULL UNIQUE,
    commission_rate DECIMAL(5,2) DEFAULT 10.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_referral_code (referral_code),
    INDEX idx_user_id (user_id)
);

-- Create referral_registrations table
CREATE TABLE IF NOT EXISTS referral_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_user_id INT NOT NULL,
    referral_code VARCHAR(50) NOT NULL,
    commission_amount DECIMAL(8,2) NOT NULL,
    commission_status VARCHAR(20) DEFAULT 'pending',
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_referrer_id (referrer_id),
    INDEX idx_referred_user_id (referred_user_id),
    INDEX idx_commission_status (commission_status)
);

-- Create commission_payments table
CREATE TABLE IF NOT EXISTS commission_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referral_registration_id INT NOT NULL,
    amount DECIMAL(8,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    evidence_file VARCHAR(255) NULL,
    notes TEXT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referral_registration_id) REFERENCES referral_registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_payment_date (payment_date)
);

-- Create user_accounts table for multi-user functionality
CREATE TABLE IF NOT EXISTS user_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_user_id INT NOT NULL,
    child_user_id INT NOT NULL,
    access_level VARCHAR(20) DEFAULT 'basic',
    can_create_movements TINYINT(1) DEFAULT 1,
    can_view_reports TINYINT(1) DEFAULT 1,
    can_edit_movements TINYINT(1) DEFAULT 0,
    can_delete_movements TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (child_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_account_relationship (parent_user_id, child_user_id),
    INDEX idx_parent_user (parent_user_id),
    INDEX idx_child_user (child_user_id)
);

-- Add unique constraints for email and phone
ALTER TABLE users ADD CONSTRAINT unique_email UNIQUE (email);
ALTER TABLE users ADD CONSTRAINT unique_phone UNIQUE (phone);