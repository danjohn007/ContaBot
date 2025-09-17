<?php
/**
 * Test Controller
 * Sistema Básico Contable - ContaBot
 * Tests database connection and system configuration
 */

require_once 'BaseController.php';

class TestController extends BaseController {
    
    public function index() {
        $tests = [];
        
        // Test 1: Database connection
        $database = new Database();
        $tests['database'] = [
            'name' => 'Database Connection',
            'status' => $database->testConnection(),
            'message' => $database->testConnection() ? 'Connection successful' : 'Connection failed'
        ];
        
        // Test 2: Base URL configuration
        $tests['base_url'] = [
            'name' => 'Base URL Configuration',
            'status' => !empty(BASE_URL),
            'message' => 'Base URL: ' . BASE_URL
        ];
        
        // Test 3: Upload directory
        $tests['upload_dir'] = [
            'name' => 'Upload Directory',
            'status' => is_dir(UPLOAD_PATH) && is_writable(UPLOAD_PATH),
            'message' => is_dir(UPLOAD_PATH) ? 
                (is_writable(UPLOAD_PATH) ? 'Upload directory is writable' : 'Upload directory exists but not writable') :
                'Upload directory does not exist'
        ];
        
        // Test 4: PHP version
        $tests['php_version'] = [
            'name' => 'PHP Version',
            'status' => version_compare(PHP_VERSION, '7.0.0', '>='),
            'message' => 'PHP Version: ' . PHP_VERSION
        ];
        
        // Test 5: Required extensions
        $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'fileinfo'];
        $missing_extensions = [];
        
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing_extensions[] = $ext;
            }
        }
        
        $tests['extensions'] = [
            'name' => 'Required PHP Extensions',
            'status' => empty($missing_extensions),
            'message' => empty($missing_extensions) ? 
                'All required extensions are loaded' : 
                'Missing extensions: ' . implode(', ', $missing_extensions)
        ];
        
        // Test 6: Session functionality
        $tests['sessions'] = [
            'name' => 'Session Functionality',
            'status' => session_status() === PHP_SESSION_ACTIVE,
            'message' => session_status() === PHP_SESSION_ACTIVE ? 
                'Sessions are working' : 
                'Sessions are not working'
        ];
        
        $data = [
            'title' => 'System Tests - ContaBot',
            'tests' => $tests,
            'overall_status' => $this->getOverallStatus($tests)
        ];
        
        $this->view('test/index', $data);
    }
    
    public function database() {
        try {
            // Try to execute the schema
            $schema = file_get_contents('../sql/schema.sql');
            $sample_data = file_get_contents('../sql/sample_data.sql');
            
            if (!$schema || !$sample_data) {
                $this->json(['success' => false, 'message' => 'SQL files not found']);
                return;
            }
            
            // Remove CREATE DATABASE statement for safety
            $schema = preg_replace('/CREATE DATABASE[^;]+;/', '', $schema);
            $schema = preg_replace('/USE[^;]+;/', '', $schema);
            $sample_data = preg_replace('/USE[^;]+;/', '', $sample_data);
            
            // Execute schema
            $this->db->exec($schema);
            
            // Execute sample data
            $this->db->exec($sample_data);
            
            $this->json(['success' => true, 'message' => 'Database initialized successfully']);
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function getOverallStatus($tests) {
        foreach ($tests as $test) {
            if (!$test['status']) {
                return false;
            }
        }
        return true;
    }
}
?>