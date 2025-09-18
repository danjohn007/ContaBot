<?php
/**
 * Account Management View
 * Sistema Básico Contable - ContaBot
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users-cog me-2"></i>
                    Gestión de Cuenta
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-2"></i>
                    Agregar Usuario
                </button>
            </div>
        </div>
    </div>

    <?php if (isset($flash['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $flash['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($flash['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $flash['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Users in my account -->
        <div class="col-12 <?php echo !empty($parent_users) ? 'col-lg-8' : ''; ?> mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Usuarios en mi Cuenta
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($child_users)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tienes usuarios agregados a tu cuenta.</p>
                        <p class="text-muted">Haz clic en "Agregar Usuario" para comenzar.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Contacto</th>
                                    <th>Nivel de Acceso</th>
                                    <th>Permisos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($child_users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                        <br>
                                        <small class="text-muted">Agregado: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            <i class="fas fa-envelope text-muted me-1"></i>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </div>
                                        <?php if (!empty($user['phone'])): ?>
                                        <div>
                                            <i class="fas fa-phone text-muted me-1"></i>
                                            <?php echo htmlspecialchars($user['phone']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['access_level'] === 'advanced' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['access_level']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if ($user['can_create_movements']): ?>
                                            <span class="badge bg-info">Crear</span>
                                            <?php endif; ?>
                                            <?php if ($user['can_edit_movements']): ?>
                                            <span class="badge bg-warning">Editar</span>
                                            <?php endif; ?>
                                            <?php if ($user['can_delete_movements']): ?>
                                            <span class="badge bg-danger">Eliminar</span>
                                            <?php endif; ?>
                                            <?php if ($user['can_view_reports']): ?>
                                            <span class="badge bg-success">Reportes</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['account_status'] === 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($user['account_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal<?php echo $user['child_user_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#removeUserModal<?php echo $user['child_user_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Accounts I belong to -->
        <?php if (!empty($parent_users)): ?>
        <div class="col-12 col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-friends me-2"></i>
                        Cuentas a las que Pertenezco
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($parent_users as $parent): ?>
                    <div class="card mb-2">
                        <div class="card-body py-2">
                            <strong><?php echo htmlspecialchars($parent['parent_name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($parent['parent_email']); ?></small>
                            <div class="mt-1">
                                <span class="badge bg-secondary"><?php echo ucfirst($parent['access_level']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Agregar Usuario a mi Cuenta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>account/add-user">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Teléfono WhatsApp</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   placeholder="5512345678" maxlength="10" pattern="[0-9]{10}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="access_level" class="form-label">Nivel de Acceso</label>
                            <select class="form-select" id="access_level" name="access_level">
                                <option value="basic">Básico</option>
                                <option value="advanced">Avanzado</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Permisos</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="can_create_movements" name="can_create_movements" checked>
                                <label class="form-check-label" for="can_create_movements">
                                    Puede crear movimientos
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="can_view_reports" name="can_view_reports" checked>
                                <label class="form-check-label" for="can_view_reports">
                                    Puede ver reportes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="can_edit_movements" name="can_edit_movements">
                                <label class="form-check-label" for="can_edit_movements">
                                    Puede editar movimientos
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="can_delete_movements" name="can_delete_movements">
                                <label class="form-check-label" for="can_delete_movements">
                                    Puede eliminar movimientos
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Agregar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modals -->
<?php foreach ($child_users as $user): ?>
<div class="modal fade" id="editUserModal<?php echo $user['child_user_id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Editar Permisos: <?php echo htmlspecialchars($user['name']); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>account/update-permissions">
                <input type="hidden" name="child_user_id" value="<?php echo $user['child_user_id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="access_level" class="form-label">Nivel de Acceso</label>
                        <select class="form-select" name="access_level">
                            <option value="basic" <?php echo $user['access_level'] === 'basic' ? 'selected' : ''; ?>>Básico</option>
                            <option value="advanced" <?php echo $user['access_level'] === 'advanced' ? 'selected' : ''; ?>>Avanzado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permisos</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="can_create_movements" 
                                   <?php echo $user['can_create_movements'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Puede crear movimientos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="can_view_reports" 
                                   <?php echo $user['can_view_reports'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Puede ver reportes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="can_edit_movements" 
                                   <?php echo $user['can_edit_movements'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Puede editar movimientos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="can_delete_movements" 
                                   <?php echo $user['can_delete_movements'] ? 'checked' : ''; ?>>
                            <label class="form-check-label">Puede eliminar movimientos</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove User Modal -->
<div class="modal fade" id="removeUserModal<?php echo $user['child_user_id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas remover a <strong><?php echo htmlspecialchars($user['name']); ?></strong> de tu cuenta?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?php echo BASE_URL; ?>account/remove-user" class="d-inline">
                    <input type="hidden" name="child_user_id" value="<?php echo $user['child_user_id']; ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Remover Usuario
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Phone number validation for add user form
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            if (e.target.value.length !== 10) {
                e.target.setCustomValidity('El número debe tener exactamente 10 dígitos');
            } else {
                e.target.setCustomValidity('');
            }
        });
    }
});
</script>