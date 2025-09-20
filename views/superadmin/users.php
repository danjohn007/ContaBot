<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2 text-info"></i>
        Gestión de Usuarios
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>superadmin" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>
                Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Filter Options -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Buscar Usuarios</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo BASE_URL; ?>superadmin/users">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Buscar por nombre, email o teléfono..."
                               value="<?php echo htmlspecialchars($current_search ?? ''); ?>">
                        <?php if (isset($current_status) && $current_status !== 'all'): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($current_status); ?>">
                        <?php endif; ?>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($current_search)): ?>
                            <a href="<?php echo BASE_URL; ?>superadmin/users<?php echo isset($current_status) && $current_status !== 'all' ? '?status=' . urlencode($current_status) : ''; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Filtrar por Estado</h6>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="<?php echo BASE_URL; ?>superadmin/users?status=all<?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>" 
                       class="btn <?php echo $current_status === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        Todos
                    </a>
                    <a href="<?php echo BASE_URL; ?>superadmin/users?status=active<?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>" 
                       class="btn <?php echo $current_status === 'active' ? 'btn-success' : 'btn-outline-success'; ?>">
                        Activos
                    </a>
                    <a href="<?php echo BASE_URL; ?>superadmin/users?status=pending<?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>" 
                       class="btn <?php echo $current_status === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        Pendientes
                    </a>
                    <a href="<?php echo BASE_URL; ?>superadmin/users?status=suspended<?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>" 
                       class="btn <?php echo $current_status === 'suspended' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        Suspendidos
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Resumen</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">Total de usuarios: <strong><?php echo number_format($total_users); ?></strong></p>
                <p class="mb-0">Mostrando página <?php echo $current_page; ?> de <?php echo $total_pages; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Lista de Usuarios</h6>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No se encontraron usuarios con los criterios seleccionados.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Plan</th>
                        <th>Comisión</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Aprobado Por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                            <?php if ($user['rfc']): ?>
                            <br><small class="text-muted">RFC: <?php echo htmlspecialchars($user['rfc']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo ucfirst($user['user_type']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['plan_name']): ?>
                                <?php echo htmlspecialchars($user['plan_name']); ?>
                                <?php if ($user['plan_price']): ?>
                                <br><small class="text-muted">$<?php echo number_format($user['plan_price'], 2); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Sin plan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['commission_rate'] !== null): ?>
                                <span class="badge bg-info" style="cursor: pointer;" 
                                      onclick="editCommission(<?php echo $user['id']; ?>, <?php echo $user['commission_rate']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                    <?php echo number_format($user['commission_rate'], 1); ?>%
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusClass = '';
                            switch ($user['account_status']) {
                                case 'active':
                                    $statusClass = 'bg-success';
                                    break;
                                case 'pending':
                                    $statusClass = 'bg-warning';
                                    break;
                                case 'suspended':
                                    $statusClass = 'bg-danger';
                                    break;
                                default:
                                    $statusClass = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst($user['account_status']); ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></small>
                        </td>
                        <td>
                            <?php if ($user['approved_by_name']): ?>
                                <small><?php echo htmlspecialchars($user['approved_by_name']); ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if ($user['account_status'] === 'active'): ?>
                                <button type="button" class="btn btn-outline-warning" 
                                        onclick="suspendUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <?php elseif ($user['account_status'] === 'suspended'): ?>
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="reactivateUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                    <i class="fas fa-play"></i>
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($user['commission_rate'] !== null): ?>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="editCommission(<?php echo $user['id']; ?>, <?php echo $user['commission_rate']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                    <i class="fas fa-percentage"></i>
                                </button>
                                <?php endif; ?>
                                
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Paginación de usuarios">
            <ul class="pagination justify-content-center">
                <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/users?page=<?php echo $current_page - 1; ?>&status=<?php echo $current_status; ?><?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>">
                        Anterior
                    </a>
                </li>
                <?php endif; ?>
                
                <?php
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/users?page=<?php echo $i; ?>&status=<?php echo $current_status; ?><?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/users?page=<?php echo $current_page + 1; ?>&status=<?php echo $current_status; ?><?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>">
                        Siguiente
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Suspend User Modal -->
<div class="modal fade" id="suspendUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suspender Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="suspendUserForm" method="POST" action="<?php echo BASE_URL; ?>superadmin/suspend-user">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="suspendUserId">
                    <p>¿Está seguro de que desea suspender al usuario <strong id="suspendUserName"></strong>?</p>
                    <div class="mb-3">
                        <label for="suspendReason" class="form-label">Motivo de suspensión:</label>
                        <textarea class="form-control" id="suspendReason" name="reason" rows="3" 
                                  placeholder="Ingrese el motivo de la suspensión"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Suspender</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reactivate User Modal -->
<div class="modal fade" id="reactivateUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reactivar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reactivateUserForm" method="POST" action="<?php echo BASE_URL; ?>superadmin/reactivate-user">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="reactivateUserId">
                    <p>¿Está seguro de que desea reactivar al usuario <strong id="reactivateUserName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Reactivar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Commission Modal -->
<div class="modal fade" id="editCommissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-percentage me-2"></i>
                    Editar Comisión
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCommissionForm" method="POST" action="<?php echo BASE_URL; ?>superadmin/update-user-commission">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editCommissionUserId">
                    <p>Editar porcentaje de comisión para <strong id="editCommissionUserName"></strong></p>
                    <div class="mb-3">
                        <label for="editCommissionRate" class="form-label">Porcentaje de Comisión</label>
                        <div class="input-group">
                            <input type="number" class="form-control" 
                                   id="editCommissionRate" 
                                   name="commission_rate" 
                                   min="0" 
                                   max="100" 
                                   step="0.01" 
                                   required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">
                            Ingrese un valor entre 0% y 100%
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function suspendUser(userId, userName) {
    document.getElementById('suspendUserId').value = userId;
    document.getElementById('suspendUserName').textContent = userName;
    new bootstrap.Modal(document.getElementById('suspendUserModal')).show();
}

function reactivateUser(userId, userName) {
    document.getElementById('reactivateUserId').value = userId;
    document.getElementById('reactivateUserName').textContent = userName;
    new bootstrap.Modal(document.getElementById('reactivateUserModal')).show();
}

function editCommission(userId, currentRate, userName) {
    document.getElementById('editCommissionUserId').value = userId;
    document.getElementById('editCommissionUserName').textContent = userName;
    document.getElementById('editCommissionRate').value = currentRate;
    new bootstrap.Modal(document.getElementById('editCommissionModal')).show();
}

function viewUserDetails(userId) {
    // This could open a detailed view modal or redirect to a details page
    window.location.href = '<?php echo BASE_URL; ?>superadmin/user-details/' + userId;
}
</script>