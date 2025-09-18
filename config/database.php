<?php
/**
 * Database Configuration
 * Sistema Básico Contable - ContaBot
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'contabot_db';
    private $username = 'root';
    private $password = '';
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
                    user_type VARCHAR(20) DEFAULT 'personal',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'active'
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
            
            // Insert test user (password is 'password123')
            $pdo->exec("
                INSERT INTO users (email, password, name, rfc, user_type) VALUES
                ('test@contabot.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuario Test', 'TEST010101ABC', 'personal')
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
