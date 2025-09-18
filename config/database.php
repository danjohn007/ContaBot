<?php
/**
 * Database Configuration
 * Sistema Básico Contable - ContaBot
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'ejercito_contabot';
    private $username = 'ejercito_contabot';
    private $password = 'Danjohn007!';
    private $charset = 'utf8mb4';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        // Check if we should use SQLite for testing
        if (getenv('USE_SQLITE_TEST') === 'true' || !$this->canConnectToMySQL()) {
            return $this->getSQLiteConnection();
        }
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            // Fallback to SQLite if MySQL fails
            return $this->getSQLiteConnection();
        }
        
        return $this->conn;
    }
    
    private function canConnectToMySQL() {
        try {
            $dsn = "mysql:host=" . $this->host . ";charset=" . $this->charset;
            $testConn = new PDO($dsn, $this->username, $this->password);
            $testConn = null; // Close connection
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    private function getSQLiteConnection() {
        try {
            $dbFile = '/tmp/contabot_test.db';
            // Create test database if it doesn't exist
            if (!file_exists($dbFile)) {
                $this->createTestDatabase($dbFile);
            }
            
            $this->conn = new PDO("sqlite:$dbFile");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->conn;
        } catch(PDOException $e) {
            echo "SQLite connection error: " . $e->getMessage();
            return null;
        }
    }
    
    private function createTestDatabase($dbFile) {
        try {
            $pdo = new PDO("sqlite:$dbFile");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables
            $pdo->exec("
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
                    referred_by VARCHAR(32) DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'active',
                    FOREIGN KEY (approved_by) REFERENCES users(id)
                )
            ");
            
            $pdo->exec("
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
                )
            ");
            
            $pdo->exec("
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
                )
            ");
            
            $pdo->exec("
                CREATE TABLE categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    color VARCHAR(7) DEFAULT '#007bff',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            
            $pdo->exec("
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
                )
            ");
            
            // Loyalty system tables
            $pdo->exec("
                CREATE TABLE referral_links (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    referral_code VARCHAR(32) UNIQUE NOT NULL,
                    commission_rate DECIMAL(5,2) DEFAULT 10.00,
                    is_active BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            
            $pdo->exec("
                CREATE TABLE referral_registrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    referrer_id INTEGER NOT NULL,
                    referred_user_id INTEGER NOT NULL,
                    referral_code VARCHAR(32) NOT NULL,
                    commission_amount DECIMAL(10,2) DEFAULT 0.00,
                    commission_status VARCHAR(20) DEFAULT 'pending' CHECK(commission_status IN ('pending', 'paid', 'cancelled')),
                    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            
            $pdo->exec("
                CREATE TABLE commission_payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    referral_registration_id INTEGER NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    payment_method VARCHAR(50),
                    evidence_file VARCHAR(255),
                    notes TEXT,
                    created_by INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (referral_registration_id) REFERENCES referral_registrations(id) ON DELETE CASCADE,
                    FOREIGN KEY (created_by) REFERENCES users(id)
                )
            ");
            
            $pdo->exec("
                CREATE TABLE user_accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    parent_user_id INTEGER NOT NULL,
                    child_user_id INTEGER NOT NULL,
                    access_level VARCHAR(20) DEFAULT 'basic' CHECK(access_level IN ('basic', 'full')),
                    can_create_movements BOOLEAN DEFAULT 1,
                    can_view_reports BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (parent_user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (child_user_id) REFERENCES users(id) ON DELETE CASCADE,
                    UNIQUE(parent_user_id, child_user_id)
                )
            ");
            
            // Insert SuperAdmin user and test user (password is 'password123')
            $pdo->exec("
                INSERT INTO users (email, password, name, rfc, user_type, account_status, billing_status) VALUES
                ('superadmin@contabot.com', '\$2y\$10\$CClHJt6yDwwNNLK8Ap965.870hQ.VPzrhAnLDJaA7d04ZpaAq3kWm', 'Super Administrador', 'ADMIN010101ABC', 'superadmin', 'active', 'paid'),
                ('test@contabot.com', '\$2y\$10\$CClHJt6yDwwNNLK8Ap965.870hQ.VPzrhAnLDJaA7d04ZpaAq3kWm', 'Usuario Test', 'TEST010101ABC', 'personal', 'pending', 'pending')
            ");
            
            // Insert subscription plans
            $pdo->exec("
                INSERT INTO subscription_plans (name, type, price, duration_months, description, features) VALUES
                ('Plan Mensual', 'monthly', 299.00, 1, 'Acceso completo al sistema por un mes', '[\"Dashboard completo\", \"Movimientos ilimitados\", \"Reportes fiscales\", \"Soporte básico\"]'),
                ('Plan Anual', 'annual', 2990.00, 12, 'Acceso completo al sistema por un año (2 meses gratis)', '[\"Dashboard completo\", \"Movimientos ilimitados\", \"Reportes fiscales\", \"Soporte prioritario\", \"2 meses gratis\"]'),
                ('Plan Cortesía', 'courtesy', 0.00, 1, 'Acceso gratuito de cortesía por un mes', '[\"Dashboard básico\", \"Movimientos limitados\", \"Reportes básicos\"]')
            ");
            
            // Insert test categories
            $pdo->exec("
                INSERT INTO categories (user_id, name, description, color) VALUES
                (1, 'Alimentación', 'Gastos en comida y bebidas', '#28a745'),
                (1, 'Transporte', 'Gastos de transporte', '#007bff'),
                (1, 'Servicios', 'Servicios públicos', '#ffc107'),
                (1, 'Salario', 'Ingresos por trabajo', '#17a2b8')
            ");
            
            // Insert test movements for current month and previous months
            $currentMonth = date('Y-m');
            $lastMonth = date('Y-m', strtotime('-1 month'));
            $twoMonthsAgo = date('Y-m', strtotime('-2 months'));
            
            $movements = [
                // Current month
                [1, 4, 'income', 5000.00, 'Salario', 'Salario mensual', $currentMonth . '-01', 'personal', 'transfer', 0],
                [1, 1, 'expense', 800.00, 'Supermercado', 'Compras del mes', $currentMonth . '-05', 'personal', 'card', 0],
                [1, 2, 'expense', 300.00, 'Transportation', 'Transporte público', $currentMonth . '-10', 'personal', 'cash', 0],
                [1, 3, 'expense', 150.00, 'Internet', 'Servicio de internet', $currentMonth . '-15', 'personal', 'transfer', 0],
                
                // Last month
                [1, 4, 'income', 5000.00, 'Salario', 'Salario mensual anterior', $lastMonth . '-01', 'personal', 'transfer', 0],
                [1, 1, 'expense', 750.00, 'Supermercado', 'Compras del mes anterior', $lastMonth . '-05', 'personal', 'card', 0],
                [1, 2, 'expense', 280.00, 'Transportation', 'Transporte mes anterior', $lastMonth . '-10', 'personal', 'cash', 0],
                
                // Two months ago
                [1, 4, 'income', 4800.00, 'Salario', 'Salario hace dos meses', $twoMonthsAgo . '-01', 'personal', 'transfer', 0],
                [1, 1, 'expense', 900.00, 'Supermercado', 'Compras hace dos meses', $twoMonthsAgo . '-05', 'personal', 'card', 0]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO movements (user_id, category_id, type, amount, concept, description, movement_date, classification, payment_method, is_billed) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($movements as $movement) {
                $stmt->execute($movement);
            }
            
            // Insert initial referral links for existing users
            $pdo->exec("
                INSERT INTO referral_links (user_id, referral_code, commission_rate) VALUES
                (1, 'SUPER_" . bin2hex(random_bytes(8)) . "', 10.00)
            ");
            
        } catch (Exception $e) {
            error_log("Error creating test database: " . $e->getMessage());
        }
    }
    
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return $conn !== null;
        } catch(Exception $e) {
            return false;
        }
    }
}
?>
