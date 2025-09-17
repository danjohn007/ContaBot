-- Sistema Básico Contable - ContaBot
-- Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS contabot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE contabot_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    rfc VARCHAR(13) NULL,
    user_type ENUM('personal', 'business') DEFAULT 'personal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Movements table (income and expenses)
CREATE TABLE movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    concept VARCHAR(255) NOT NULL,
    description TEXT NULL,
    movement_date DATE NOT NULL,
    classification ENUM('personal', 'business', 'fiscal', 'non_fiscal') NOT NULL,
    payment_method ENUM('cash', 'card', 'transfer', 'check', 'other') DEFAULT 'cash',
    receipt_file VARCHAR(255) NULL,
    is_billed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Password reset tokens
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
);

-- Create indexes for better performance
CREATE INDEX idx_movements_user_date ON movements(user_id, movement_date);
CREATE INDEX idx_movements_type ON movements(type);
CREATE INDEX idx_movements_classification ON movements(classification);
CREATE INDEX idx_categories_user ON categories(user_id);