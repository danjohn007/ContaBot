-- ContaBot Database Schema Upgrade
-- Adds SuperAdmin functionality and billing system

-- Drop existing database if using fresh install
-- For production, use ALTER TABLE statements instead

-- If you want to keep existing data, use these ALTER statements:
-- ALTER TABLE users ADD COLUMN account_status VARCHAR(20) DEFAULT 'pending' CHECK(account_status IN ('pending', 'active', 'suspended', 'cancelled'));
-- ALTER TABLE users ADD COLUMN subscription_plan VARCHAR(20) DEFAULT NULL CHECK(subscription_plan IN ('monthly', 'annual', 'courtesy'));
-- ALTER TABLE users ADD COLUMN subscription_start_date DATETIME DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN subscription_end_date DATETIME DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN billing_status VARCHAR(20) DEFAULT 'pending' CHECK(billing_status IN ('pending', 'paid', 'overdue', 'cancelled'));
-- ALTER TABLE users ADD COLUMN last_payment_date DATETIME DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN next_payment_date DATETIME DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN approved_by INTEGER DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN approved_at DATETIME DEFAULT NULL;
-- ALTER TABLE users ADD CONSTRAINT fk_approved_by FOREIGN KEY (approved_by) REFERENCES users(id);

-- For fresh install, recreate tables with new schema:

DROP TABLE IF EXISTS billing_history;
DROP TABLE IF EXISTS subscription_plans;
DROP TABLE IF EXISTS movements;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Enhanced Users table with billing and subscription support
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    rfc VARCHAR(13),
    user_type VARCHAR(20) DEFAULT 'personal' CHECK(user_type IN ('personal', 'business', 'superadmin')),
    account_status VARCHAR(20) DEFAULT 'pending' CHECK(account_status IN ('pending', 'active', 'suspended', 'cancelled')),
    subscription_plan VARCHAR(20) DEFAULT NULL CHECK(subscription_plan IN ('monthly', 'annual', 'courtesy')),
    subscription_start_date DATETIME DEFAULT NULL,
    subscription_end_date DATETIME DEFAULT NULL,
    billing_status VARCHAR(20) DEFAULT 'pending' CHECK(billing_status IN ('pending', 'paid', 'overdue', 'cancelled')),
    last_payment_date DATETIME DEFAULT NULL,
    next_payment_date DATETIME DEFAULT NULL,
    approved_by INTEGER DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'active',
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Subscription Plans table
CREATE TABLE subscription_plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    type VARCHAR(20) NOT NULL CHECK(type IN ('monthly', 'annual', 'courtesy')),
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    duration_months INTEGER NOT NULL,
    description TEXT,
    features JSON,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Billing History table
CREATE TABLE billing_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    plan_id INTEGER NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'pending' CHECK(payment_status IN ('pending', 'paid', 'failed', 'cancelled')),
    payment_date DATETIME DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    transaction_id VARCHAR(255) DEFAULT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
);

-- Categories table (unchanged)
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Movements table (unchanged)
CREATE TABLE movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    type VARCHAR(10) NOT NULL CHECK(type IN ('income', 'expense')),
    amount DECIMAL(10,2) NOT NULL,
    concept VARCHAR(255) NOT NULL,
    description TEXT,
    movement_date DATE NOT NULL,
    classification VARCHAR(20) NOT NULL CHECK(classification IN ('personal', 'business', 'fiscal', 'non_fiscal')),
    payment_method VARCHAR(20) DEFAULT 'cash' CHECK(payment_method IN ('cash', 'card', 'transfer', 'check', 'other')),
    receipt_file VARCHAR(255),
    is_billed BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Insert SuperAdmin user and test user (password is 'password123')
INSERT INTO users (email, password, name, rfc, user_type, account_status, billing_status) VALUES
('superadmin@contabot.com', '$2y$10$CClHJt6yDwwNNLK8Ap965.870hQ.VPzrhAnLDJaA7d04ZpaAq3kWm', 'Super Administrador', 'ADMIN010101ABC', 'superadmin', 'active', 'paid'),
('test@contabot.com', '$2y$10$CClHJt6yDwwNNLK8Ap965.870hQ.VPzrhAnLDJaA7d04ZpaAq3kWm', 'Usuario Test', 'TEST010101ABC', 'personal', 'pending', 'pending');

-- Insert subscription plans
INSERT INTO subscription_plans (name, type, price, duration_months, description, features) VALUES
('Plan Mensual', 'monthly', 299.00, 1, 'Acceso completo al sistema por un mes', '["Dashboard completo", "Movimientos ilimitados", "Reportes fiscales", "Soporte básico"]'),
('Plan Anual', 'annual', 2990.00, 12, 'Acceso completo al sistema por un año (2 meses gratis)', '["Dashboard completo", "Movimientos ilimitados", "Reportes fiscales", "Soporte prioritario", "2 meses gratis"]'),
('Plan Cortesía', 'courtesy', 0.00, 1, 'Acceso gratuito de cortesía por un mes', '["Dashboard básico", "Movimientos limitados", "Reportes básicos"]');

-- Insert test categories (only for SuperAdmin to have some categories)
INSERT INTO categories (user_id, name, description, color) VALUES
(1, 'Administración', 'Gastos administrativos del sistema', '#28a745'),
(1, 'Marketing', 'Gastos de marketing y publicidad', '#007bff'),
(1, 'Infraestructura', 'Gastos de infraestructura y tecnología', '#ffc107'),
(1, 'Ingresos Sistema', 'Ingresos por suscripciones', '#17a2b8');

-- Insert some test movements for SuperAdmin
INSERT INTO movements (user_id, category_id, type, amount, concept, description, movement_date, classification, payment_method, is_billed) VALUES
(1, 4, 'income', 5000.00, 'Suscripciones Mensuales', 'Ingresos por suscripciones del mes actual', date('now', 'start of month'), 'business', 'transfer', 0),
(1, 1, 'expense', 800.00, 'Hosting y Dominios', 'Gastos de infraestructura mensual', date('now', 'start of month', '+5 days'), 'business', 'card', 0),
(1, 2, 'expense', 300.00, 'Publicidad Online', 'Campaña de Google Ads', date('now', 'start of month', '+10 days'), 'business', 'card', 0),
(1, 3, 'expense', 150.00, 'Servicios Cloud', 'AWS y servicios cloud', date('now', 'start of month', '+15 days'), 'business', 'transfer', 0);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_account_status ON users(account_status);
CREATE INDEX idx_users_billing_status ON users(billing_status);
CREATE INDEX idx_billing_history_user_id ON billing_history(user_id);
CREATE INDEX idx_billing_history_payment_status ON billing_history(payment_status);
CREATE INDEX idx_movements_user_id ON movements(user_id);
CREATE INDEX idx_movements_date ON movements(movement_date);
CREATE INDEX idx_categories_user_id ON categories(user_id);