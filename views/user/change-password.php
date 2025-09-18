<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-key me-2"></i>
        Cambiar Contraseña
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo BASE_URL; ?>profile" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver al Perfil
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-shield-alt me-2"></i>
                    Seguridad de la Cuenta
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Requisitos de seguridad:</strong>
                    <ul class="mb-0 mt-2">
                        <li>La contraseña debe tener al menos 6 caracteres</li>
                        <li>Se recomienda usar una combinación de letras, números y símbolos</li>
                        <li>No compartas tu contraseña con nadie</li>
                    </ul>
                </div>

                <form method="POST" action="<?php echo BASE_URL; ?>profile/changePassword" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                   placeholder="Ingresa tu contraseña actual" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye" id="current_password_icon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Por favor ingresa tu contraseña actual.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Ingresa tu nueva contraseña" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            La nueva contraseña debe tener al menos 6 caracteres.
                        </div>
                        <div class="form-text">
                            <div id="password_strength" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirma tu nueva contraseña" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye" id="confirm_password_icon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Por favor confirma tu nueva contraseña.
                        </div>
                        <div id="password_match" class="form-text"></div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Security Tips -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-lightbulb me-2"></i>
                    Consejos de Seguridad
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">
                            <i class="fas fa-check me-1"></i>
                            Recomendado:
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Usar combinaciones únicas</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Incluir números y símbolos</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Cambiar periódicamente</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>No reutilizar contraseñas</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger">
                            <i class="fas fa-times me-1"></i>
                            Evitar:
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-times-circle text-danger me-2"></i>Información personal</li>
                            <li><i class="fas fa-times-circle text-danger me-2"></i>Fechas de nacimiento</li>
                            <li><i class="fas fa-times-circle text-danger me-2"></i>Palabras del diccionario</li>
                            <li><i class="fas fa-times-circle text-danger me-2"></i>Secuencias simples</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) {
        strength++;
    } else {
        feedback.push('Al menos 8 caracteres');
    }
    
    if (/[a-z]/.test(password)) {
        strength++;
    } else {
        feedback.push('Letras minúsculas');
    }
    
    if (/[A-Z]/.test(password)) {
        strength++;
    } else {
        feedback.push('Letras mayúsculas');
    }
    
    if (/[0-9]/.test(password)) {
        strength++;
    } else {
        feedback.push('Números');
    }
    
    if (/[^A-Za-z0-9]/.test(password)) {
        strength++;
    } else {
        feedback.push('Símbolos especiales');
    }
    
    const strengthDiv = document.getElementById('password_strength');
    let strengthText = '';
    let strengthClass = '';
    
    if (strength <= 2) {
        strengthText = 'Débil';
        strengthClass = 'text-danger';
    } else if (strength <= 3) {
        strengthText = 'Moderada';
        strengthClass = 'text-warning';
    } else if (strength <= 4) {
        strengthText = 'Fuerte';
        strengthClass = 'text-success';
    } else {
        strengthText = 'Muy Fuerte';
        strengthClass = 'text-success';
    }
    
    strengthDiv.innerHTML = `<strong class="${strengthClass}">Fortaleza: ${strengthText}</strong>`;
    if (feedback.length > 0) {
        strengthDiv.innerHTML += `<br><small class="text-muted">Agregar: ${feedback.join(', ')}</small>`;
    }
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchDiv = document.getElementById('password_match');
    
    if (confirmPassword === '') {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (newPassword === confirmPassword) {
        matchDiv.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>Las contraseñas coinciden</small>';
    } else {
        matchDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>Las contraseñas no coinciden</small>';
    }
}

// Event listeners
document.getElementById('new_password').addEventListener('input', function() {
    checkPasswordStrength(this.value);
    checkPasswordMatch();
});

document.getElementById('confirm_password').addEventListener('input', function() {
    checkPasswordMatch();
});

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        const forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (newPassword !== confirmPassword) {
                    event.preventDefault();
                    event.stopPropagation();
                    document.getElementById('confirm_password').setCustomValidity('Las contraseñas no coinciden');
                } else {
                    document.getElementById('confirm_password').setCustomValidity('');
                }
                
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>