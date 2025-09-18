<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .brand-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
    </style>
</head>
<body class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card register-card border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="brand-logo">
                                <i class="fas fa-calculator text-white fa-lg"></i>
                            </div>
                            <h2 class="card-title mb-1">Registrarse</h2>
                            <p class="text-muted">Crear nueva cuenta en ContaBot</p>
                        </div>
                        
                        <?php if (isset($flash['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $flash['error']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?php echo BASE_URL; ?>register">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nombre Completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               placeholder="Tu nombre completo" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="tu@email.com" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Mínimo 6 caracteres" required minlength="6">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Repetir contraseña" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="user_type" class="form-label">Tipo de Usuario</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                        <select class="form-select" id="user_type" name="user_type" required>
                                            <option value="personal">Personal</option>
                                            <option value="business">Negocio</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="rfc" class="form-label">RFC (Opcional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control" id="rfc" name="rfc" 
                                               placeholder="XAXX010101000" maxlength="13" 
                                               style="text-transform: uppercase;">
                                    </div>
                                    <div class="form-text">Solo si manejas facturas fiscales</div>
                                </div>
                            </div>
                            
                            <?php if (!empty($referral_code)): ?>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-users me-2"></i>
                                        <strong>¡Registro por referido!</strong> Has sido invitado a unirte a ContaBot.
                                    </div>
                                    <input type="hidden" name="ref" value="<?php echo htmlspecialchars($referral_code); ?>">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Crear Cuenta
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0">¿Ya tienes cuenta?</p>
                            <a href="<?php echo BASE_URL; ?>login" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Iniciar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // RFC format validation
        document.getElementById('rfc').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('blur', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            
            if (password !== confirm) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>