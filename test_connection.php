<?php
/**
 * Database Connection Test
 * ContaBot - Sistema Básico Contable
 */

// Include configuration
require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-database"></i> Test de Conexión</h4>
                    </div>
                    <div class="card-body">
                        <h5>Información del Sistema</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Aplicación:</strong></td>
                                <td><?php echo APP_NAME . ' v' . APP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong>URL Base:</strong></td>
                                <td><?php echo BASE_URL; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Directorio de Uploads:</strong></td>
                                <td><?php echo UPLOAD_PATH; ?></td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?php echo PHP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Timezone:</strong></td>
                                <td><?php echo date_default_timezone_get(); ?></td>
                            </tr>
                        </table>

                        <hr>

                        <h5>Test de Base de Datos</h5>
                        <?php
                        try {
                            $db = Database::getInstance();
                            if ($db->testConnection()) {
                                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ✅ Conexión a la base de datos exitosa</div>';
                                
                                // Test if tables exist
                                $conn = $db->getConnection();
                                $tables = ['users', 'categories', 'transactions', 'transaction_attachments'];
                                $existing_tables = [];
                                
                                foreach ($tables as $table) {
                                    try {
                                        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                                        if ($stmt->rowCount() > 0) {
                                            $existing_tables[] = $table;
                                        }
                                    } catch (Exception $e) {
                                        // Table doesn't exist
                                    }
                                }
                                
                                if (count($existing_tables) === count($tables)) {
                                    echo '<div class="alert alert-success"><i class="fas fa-table"></i> ✅ Todas las tablas existen</div>';
                                    
                                    // Test if admin user exists
                                    try {
                                        $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                                        $result = $stmt->fetch();
                                        if ($result['count'] > 0) {
                                            echo '<div class="alert alert-success"><i class="fas fa-user-shield"></i> ✅ Usuario administrador configurado</div>';
                                        } else {
                                            echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ⚠️ No se encontró usuario administrador</div>';
                                        }
                                    } catch (Exception $e) {
                                        echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ⚠️ Error verificando usuarios: ' . $e->getMessage() . '</div>';
                                    }
                                    
                                } else {
                                    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ⚠️ Faltan tablas: ' . implode(', ', array_diff($tables, $existing_tables)) . '</div>';
                                    echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Ejecuta el archivo database/schema.sql para crear las tablas</div>';
                                }
                                
                            } else {
                                echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ❌ Error en la conexión a la base de datos</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ❌ Error: ' . $e->getMessage() . '</div>';
                            echo '<div class="alert alert-info">
                                <strong>Pasos para configurar:</strong>
                                <ol>
                                    <li>Crear la base de datos MySQL</li>
                                    <li>Configurar las credenciales en config/database.php</li>
                                    <li>Ejecutar el archivo database/schema.sql</li>
                                </ol>
                            </div>';
                        }
                        ?>

                        <hr>

                        <h5>Test de Directorios</h5>
                        <?php
                        $directories = [
                            'uploads' => UPLOAD_PATH,
                            'assets/css' => __DIR__ . '/assets/css/',
                            'assets/js' => __DIR__ . '/assets/js/',
                            'views' => __DIR__ . '/views/',
                            'controllers' => __DIR__ . '/controllers/',
                            'models' => __DIR__ . '/models/'
                        ];
                        
                        foreach ($directories as $name => $path) {
                            if (is_dir($path)) {
                                $writable = is_writable($path) ? '(escribible)' : '(solo lectura)';
                                echo '<div class="alert alert-success"><i class="fas fa-folder"></i> ✅ ' . $name . ' ' . $writable . '</div>';
                            } else {
                                echo '<div class="alert alert-warning"><i class="fas fa-folder-open"></i> ⚠️ ' . $name . ' no existe</div>';
                            }
                        }
                        ?>

                        <hr>

                        <h5>Configuración PHP</h5>
                        <?php
                        $php_checks = [
                            'PDO MySQL' => extension_loaded('pdo_mysql'),
                            'GD Library' => extension_loaded('gd'),
                            'File Uploads' => ini_get('file_uploads'),
                            'Session Support' => extension_loaded('session'),
                        ];
                        
                        foreach ($php_checks as $check => $status) {
                            if ($status) {
                                echo '<div class="alert alert-success"><i class="fas fa-check"></i> ✅ ' . $check . '</div>';
                            } else {
                                echo '<div class="alert alert-danger"><i class="fas fa-times"></i> ❌ ' . $check . '</div>';
                            }
                        }
                        ?>

                        <div class="mt-4">
                            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                                <i class="fas fa-home"></i> Ir al Sistema
                            </a>
                            <button onclick="location.reload()" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Probar Nuevamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>