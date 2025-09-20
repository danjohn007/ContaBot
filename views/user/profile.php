<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mi Perfil</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo BASE_URL; ?>profile/changePassword" class="btn btn-outline-primary">
            <i class="fas fa-key me-2"></i>
            Cambiar Contraseña
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user-edit me-2"></i>
                    Información Personal
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>profile" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre Completo *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="Tu nombre completo" required
                                       value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                            <div class="invalid-feedback">
                                Por favor ingresa tu nombre completo.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="form-text">
                                El email no se puede cambiar. Contacta al administrador si necesitas cambiarlo.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_type" class="form-label">Tipo de Usuario *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="personal" <?php echo $user['user_type'] === 'personal' ? 'selected' : ''; ?>>
                                        👤 Personal
                                    </option>
                                    <option value="business" <?php echo $user['user_type'] === 'business' ? 'selected' : ''; ?>>
                                        🏢 Negocio
                                    </option>
                                </select>
                            </div>
                            <div class="form-text">
                                Define si usas el sistema para finanzas personales o de negocio.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="rfc" class="form-label">RFC (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control" id="rfc" name="rfc" 
                                       placeholder="XAXX010101000" maxlength="13" 
                                       style="text-transform: uppercase;"
                                       value="<?php echo htmlspecialchars($user['rfc'] ?? ''); ?>">
                            </div>
                            <div class="form-text">
                                Solo si manejas facturas fiscales o tienes actividad empresarial.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Teléfono (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="5551234567" maxlength="15" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-text">
                                Número de teléfono para contacto (solo números).
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Account Information -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Registro</label>
                            <div class="form-control-plaintext">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Última Actualización</label>
                            <div class="form-control-plaintext">
                                <i class="fas fa-clock me-2 text-muted"></i>
                                <?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado de la Cuenta</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <i class="fas fa-<?php echo $user['status'] === 'active' ? 'check' : 'pause'; ?> me-1"></i>
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" data-loading>
                            <i class="fas fa-save me-2"></i>
                            Actualizar Perfil
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Additional Info Card -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle me-2"></i>
                    Información de Uso
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-value" id="categoriesCount">-</div>
                            <div class="stat-label">Categorías Creadas</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-value" id="movementsCount">-</div>
                            <div class="stat-label">Total Movimientos</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-value" id="currentBalance">-</div>
                            <div class="stat-label">Balance Actual</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-item {
    padding: 1rem 0;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1.2;
    color: #667eea;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}

.form-control-plaintext {
    display: flex;
    align-items: center;
    min-height: calc(1.5em + 0.75rem + 2px);
    padding: 0.375rem 0;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding-left: 0.75rem;
}
</style>

<script>
// RFC format validation
document.getElementById('rfc').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Load usage statistics
document.addEventListener('DOMContentLoaded', function() {
    // You could implement AJAX calls here to load statistics
    // For now, we'll just show placeholders
    document.getElementById('categoriesCount').textContent = '...';
    document.getElementById('movementsCount').textContent = '...';
    document.getElementById('currentBalance').textContent = '...';
});
</script>