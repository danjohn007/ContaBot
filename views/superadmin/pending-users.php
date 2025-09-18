<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-clock me-2 text-warning"></i>
        Usuarios Pendientes de Aprobación
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>superadmin" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Volver al Panel
            </a>
        </div>
    </div>
</div>

<?php if (!empty($pending_users)): ?>
<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Lista de Usuarios Pendientes (<?php echo $total_users; ?> total)
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>RFC</th>
                        <th>Tipo</th>
                        <th>Fecha de Registro</th>
                        <th>Plan Solicitado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <div class="avatar-initial rounded-circle bg-primary">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    <br>
                                    <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($user['rfc']): ?>
                                <code><?php echo htmlspecialchars($user['rfc']); ?></code>
                            <?php else: ?>
                                <span class="text-muted">No proporcionado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $user['user_type'] === 'business' ? 'info' : 'secondary'; ?>">
                                <i class="fas fa-<?php echo $user['user_type'] === 'business' ? 'building' : 'user'; ?> me-1"></i>
                                <?php echo ucfirst($user['user_type']); ?>
                            </span>
                        </td>
                        <td>
                            <div>
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo date('H:i', strtotime($user['created_at'])); ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <?php if ($user['subscription_plan']): ?>
                                <span class="badge bg-warning">
                                    <?php echo ucfirst($user['subscription_plan']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">No seleccionado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveModal<?php echo $user['id']; ?>">
                                    <i class="fas fa-check me-1"></i>
                                    Aprobar
                                </button>
                                <button type="button" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times me-1"></i>
                                    Rechazar
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Approval Modal -->
                    <div class="modal fade" id="approveModal<?php echo $user['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        Aprobar Usuario
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="<?php echo BASE_URL; ?>superadmin/approve-user">
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        
                                        <div class="alert alert-info">
                                            <strong>Usuario:</strong> <?php echo htmlspecialchars($user['name']); ?><br>
                                            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
                                            <strong>Tipo:</strong> <?php echo ucfirst($user['user_type']); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="plan_type<?php echo $user['id']; ?>" class="form-label">
                                                <i class="fas fa-credit-card me-1"></i>
                                                Seleccionar Plan de Suscripción *
                                            </label>
                                            <select class="form-select" id="plan_type<?php echo $user['id']; ?>" name="plan_type" required>
                                                <option value="">Seleccione un plan...</option>
                                                <?php foreach ($plans as $plan): ?>
                                                <option value="<?php echo $plan['type']; ?>" 
                                                        <?php echo ($user['subscription_plan'] === $plan['type']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($plan['name']); ?> - 
                                                    <?php if ($plan['price'] > 0): ?>
                                                        $<?php echo number_format($plan['price'], 2); ?>/<?php echo $plan['duration_months']; ?> mes(es)
                                                    <?php else: ?>
                                                        Gratis
                                                    <?php endif; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">
                                                El usuario será activado con el plan seleccionado
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Detalles del Plan</label>
                                            <div id="planDetails<?php echo $user['id']; ?>" class="border rounded p-3 bg-light">
                                                <small class="text-muted">Seleccione un plan para ver los detalles</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check me-1"></i>
                                            Aprobar Usuario
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Navegación de páginas">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/pending-users?page=<?php echo $current_page - 1; ?>">
                        Anterior
                    </a>
                </li>
                
                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/pending-users?page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/pending-users?page=<?php echo $current_page + 1; ?>">
                        Siguiente
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="card shadow">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
        <h3 class="text-muted">No hay usuarios pendientes</h3>
        <p class="text-muted mb-4">Todos los usuarios registrados han sido procesados.</p>
        <a href="<?php echo BASE_URL; ?>superadmin" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver al Panel
        </a>
    </div>
</div>
<?php endif; ?>

<script>
// Update plan details when plan is selected
document.addEventListener('DOMContentLoaded', function() {
    const plans = <?php echo json_encode($plans); ?>;
    
    document.querySelectorAll('select[name="plan_type"]').forEach(select => {
        const userId = select.id.replace('plan_type', '');
        const detailsDiv = document.getElementById('planDetails' + userId);
        
        select.addEventListener('change', function() {
            const selectedPlan = plans.find(plan => plan.type === this.value);
            
            if (selectedPlan) {
                const features = JSON.parse(selectedPlan.features || '[]');
                detailsDiv.innerHTML = `
                    <h6>${selectedPlan.name}</h6>
                    <p class="mb-2"><strong>Precio:</strong> $${parseFloat(selectedPlan.price).toFixed(2)}</p>
                    <p class="mb-2"><strong>Duración:</strong> ${selectedPlan.duration_months} mes(es)</p>
                    <p class="mb-2"><strong>Descripción:</strong> ${selectedPlan.description}</p>
                    <div class="mb-0">
                        <strong>Características:</strong>
                        <ul class="mt-1 mb-0">
                            ${features.map(feature => `<li>${feature}</li>`).join('')}
                        </ul>
                    </div>
                `;
            } else {
                detailsDiv.innerHTML = '<small class="text-muted">Seleccione un plan para ver los detalles</small>';
            }
        });
    });
});
</script>

<style>
.avatar {
    width: 40px;
    height: 40px;
}

.avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
}

.table td {
    vertical-align: middle;
}
</style>