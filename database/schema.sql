-- ContaBot Database Schema
-- Sistema Básico Contable

CREATE DATABASE IF NOT EXISTS contabot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE contabot;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'capturista', 'consulta') NOT NULL DEFAULT 'capturista',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('personal', 'business', 'fiscal', 'non_fiscal') NOT NULL,
    is_fiscal BOOLEAN DEFAULT FALSE,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Subcategories table
CREATE TABLE subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Transactions table (income and expenses)
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    concept VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    subcategory_id INT,
    payment_method ENUM('cash', 'card', 'transfer', 'check', 'other') NOT NULL DEFAULT 'cash',
    transaction_date DATE NOT NULL,
    invoice_status ENUM('pending', 'invoiced', 'not_applicable') DEFAULT 'not_applicable',
    fiscal_year YEAR GENERATED ALWAYS AS (YEAR(transaction_date)) STORED,
    fiscal_month TINYINT GENERATED ALWAYS AS (MONTH(transaction_date)) STORED,
    reference_number VARCHAR(50),
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_type (type),
    INDEX idx_category (category_id)
);

-- Transaction attachments/evidence
CREATE TABLE transaction_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Audit log for tracking changes
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
);

-- User sessions (optional, for better session management)
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@contabot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Sistema', 'admin');

-- Insert default categories
INSERT INTO categories (name, type, is_fiscal, description, color) VALUES 
('Alimentación', 'personal', FALSE, 'Gastos en comida y bebidas', '#28a745'),
('Transporte', 'personal', FALSE, 'Gastos de transporte personal', '#17a2b8'),
('Salud', 'personal', TRUE, 'Gastos médicos deducibles', '#dc3545'),
('Educación', 'personal', TRUE, 'Gastos educativos deducibles', '#6f42c1'),
('Oficina', 'business', TRUE, 'Gastos de oficina', '#fd7e14'),
('Marketing', 'business', TRUE, 'Gastos de marketing y publicidad', '#e83e8c'),
('Servicios', 'business', TRUE, 'Servicios profesionales', '#20c997'),
('Equipos', 'business', TRUE, 'Compra de equipos y herramientas', '#6c757d'),
('Ventas', 'business', FALSE, 'Ingresos por ventas', '#007bff'),
('Otros Ingresos', 'personal', FALSE, 'Otros ingresos personales', '#ffc107');

-- Insert subcategories
INSERT INTO subcategories (category_id, name, description) VALUES 
(1, 'Restaurantes', 'Comidas en restaurantes'),
(1, 'Supermercado', 'Compras de supermercado'),
(2, 'Combustible', 'Gasolina y combustible'),
(2, 'Transporte Público', 'Autobús, metro, taxi'),
(3, 'Consultas Médicas', 'Consultas con doctores'),
(3, 'Medicamentos', 'Compra de medicamentos'),
(5, 'Papelería', 'Material de oficina'),
(5, 'Software', 'Licencias de software'),
(6, 'Publicidad Online', 'Facebook Ads, Google Ads'),
(6, 'Material Promocional', 'Folletos, tarjetas');

-- Insert sample transactions
INSERT INTO transactions (user_id, type, amount, concept, description, category_id, subcategory_id, payment_method, transaction_date, invoice_status, created_by) VALUES 
(1, 'expense', 1500.00, 'Compra de laptop', 'Laptop para trabajo', 8, NULL, 'card', '2024-01-15', 'invoiced', 1),
(1, 'expense', 350.50, 'Comida restaurante', 'Almuerzo de negocios', 1, 1, 'card', '2024-01-16', 'pending', 1),
(1, 'income', 5000.00, 'Venta de servicios', 'Consultoría web', 9, NULL, 'transfer', '2024-01-17', 'invoiced', 1),
(1, 'expense', 800.00, 'Gasolina', 'Combustible del mes', 2, 3, 'cash', '2024-01-18', 'not_applicable', 1),
(1, 'expense', 1200.00, 'Consulta médica', 'Revisión general', 3, 5, 'card', '2024-01-19', 'invoiced', 1),
(1, 'income', 3500.00, 'Freelance', 'Proyecto de diseño', 10, NULL, 'transfer', '2024-01-20', 'pending', 1),
(1, 'expense', 250.75, 'Papelería', 'Material de oficina', 5, 7, 'cash', '2024-01-21', 'invoiced', 1),
(1, 'expense', 2500.00, 'Publicidad Facebook', 'Campaña promocional', 6, 9, 'card', '2024-01-22', 'invoiced', 1);

-- Create view for transaction summary
CREATE VIEW transaction_summary AS
SELECT 
    t.id,
    t.type,
    t.amount,
    t.concept,
    t.transaction_date,
    t.invoice_status,
    c.name as category_name,
    c.type as category_type,
    c.is_fiscal,
    sc.name as subcategory_name,
    u.full_name as created_by_name
FROM transactions t
LEFT JOIN categories c ON t.category_id = c.id
LEFT JOIN subcategories sc ON t.subcategory_id = sc.id
LEFT JOIN users u ON t.created_by = u.id
ORDER BY t.transaction_date DESC;

-- Create view for fiscal summary
CREATE VIEW fiscal_summary AS
SELECT 
    fiscal_year,
    fiscal_month,
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
    SUM(CASE WHEN type = 'expense' AND c.is_fiscal = TRUE THEN amount ELSE 0 END) as fiscal_expenses,
    COUNT(*) as total_transactions
FROM transactions t
LEFT JOIN categories c ON t.category_id = c.id
GROUP BY fiscal_year, fiscal_month
ORDER BY fiscal_year DESC, fiscal_month DESC;