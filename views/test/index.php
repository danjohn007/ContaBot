<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Sistema Básico Contable - ContaBot
                        </h3>
                        <p class="mb-0">Verificación del Sistema</p>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert <?php echo $overall_status ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                    <i class="fas <?php echo $overall_status ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                                    <strong>Estado General:</strong> 
                                    <?php echo $overall_status ? 'Sistema listo para usar' : 'Se encontraron problemas que requieren atención'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mb-3">Pruebas del Sistema</h4>
                        
                        <div class="row">
                            <?php foreach ($tests as $testKey => $test): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas <?php echo $test['status'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?> me-2"></i>
                                            <?php echo $test['name']; ?>
                                        </h6>
                                        <p class="card-text text-muted mb-0"><?php echo $test['message']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Información del Sistema</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>URL Base:</strong></td>
                                        <td><?php echo BASE_URL; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Directorio Raíz:</strong></td>
                                        <td><?php echo ROOT_PATH; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Directorio de Uploads:</strong></td>
                                        <td><?php echo UPLOAD_PATH; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Límite de Upload:</strong></td>
                                        <td><?php echo (MAX_UPLOAD_SIZE / 1024 / 1024); ?> MB</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Zona Horaria:</strong></td>
                                        <td><?php echo date_default_timezone_get(); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="button" class="btn btn-warning me-md-2" onclick="initializeDatabase()">
                                        <i class="fas fa-database me-2"></i>
                                        Inicializar Base de Datos
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                                        <i class="fas fa-home me-2"></i>
                                        Ir al Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function initializeDatabase() {
            if (confirm('¿Está seguro de que desea inicializar la base de datos? Esto creará las tablas y datos de ejemplo.')) {
                fetch('<?php echo BASE_URL; ?>test/database', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Base de datos inicializada correctamente');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error de conexión: ' + error);
                });
            }
        }
    </script>
</body>
</html>