<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2 text-primary"></i>
        Gestión de Cuentas
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>earnings" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>
                Volver a Ganancias
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i>
                Agregar Usuario
            </button>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Gestión de Usuarios:</strong> Puedes agregar usuarios adicionales a tu cuenta para que puedan registrar movimientos en tu perfil. Cada usuario puede tener diferentes permisos de acceso.
</div>

<!-- Child Users Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Usuarios Asociados a tu Cuenta
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($child_users)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Nivel de Acceso</th>
                        <th>Permisos</th>
                        <th>Estado</th>
                        <th>Fecha Agregado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($child_users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                            $accessLevelClass = $user['access_level'] === 'full' ? 'success' : 'info';
                            $accessLevelText = $user['access_level'] === 'full' ? 'Completo' : 'Básico';
                            ?>
                            <span class="badge bg-<?php echo $accessLevelClass; ?>"><?php echo $accessLevelText; ?></span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <?php if ($user['can_create_movements']): ?>
                                <small><i class="fas fa-check text-success me-1"></i> Crear Movimientos</small>
                                <?php endif; ?>
                                <?php if ($user['can_view_reports']): ?>
                                <small><i class="fas fa-check text-success me-1"></i> Ver Reportes</small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $statusClass = match($user['account_status']) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?>">
                                <?php echo ucfirst($user['account_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editUser(<?php echo $user['child_user_id']; ?>, '<?php echo $user['access_level']; ?>', <?php echo $user['can_create_movements'] ? 'true' : 'false'; ?>, <?php echo $user['can_view_reports'] ? 'true' : 'false'; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="removeUser(<?php echo $user['child_user_id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay usuarios asociados</h5>
            <p class="text-muted">Agrega usuarios para que puedan colaborar en tu cuenta.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i>
                Agregar Primer Usuario
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Agregar Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>earnings/add-user">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="access_level" class="form-label">Nivel de Acceso</label>
                        <select class="form-select" id="access_level" name="access_level">
                            <option value="basic">Básico</option>
                            <option value="full">Completo</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permisos</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_create_movements" 
                                   name="can_create_movements" checked>
                            <label class="form-check-label" for="can_create_movements">
                                Puede crear movimientos
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_view_reports" 
                                   name="can_view_reports" checked>
                            <label class="form-check-label" for="can_view_reports">
                                Puede ver reportes
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Agregar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Permisos de Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>earnings/update-permissions">
                <div class="modal-body">
                    <input type="hidden" id="edit_child_user_id" name="child_user_id">
                    
                    <div class="mb-3">
                        <label for="edit_access_level" class="form-label">Nivel de Acceso</label>
                        <select class="form-select" id="edit_access_level" name="access_level">
                            <option value="basic">Básico</option>
                            <option value="full">Completo</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permisos</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_can_create_movements" 
                                   name="can_create_movements">
                            <label class="form-check-label" for="edit_can_create_movements">
                                Puede crear movimientos
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_can_view_reports" 
                                   name="can_view_reports">
                            <label class="form-check-label" for="edit_can_view_reports">
                                Puede ver reportes
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Permisos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(userId, accessLevel, canCreateMovements, canViewReports) {
    document.getElementById('edit_child_user_id').value = userId;
    document.getElementById('edit_access_level').value = accessLevel;
    document.getElementById('edit_can_create_movements').checked = canCreateMovements;
    document.getElementById('edit_can_view_reports').checked = canViewReports;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function removeUser(userId, userName) {
    if (confirm(`¿Estás seguro de que quieres remover a ${userName} de tu cuenta?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>earnings/remove-user';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'child_user_id';
        input.value = userId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>