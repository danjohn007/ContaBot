<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2 text-primary"></i>
        Sistema de Lealtad
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Referidos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['total_referrals']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Referentes Activos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['active_referrers']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Comisiones Pagadas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats['paid_commissions'] ?? 0, 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Comisiones Pendientes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats['pending_commissions'] ?? 0, 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Options -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Buscar Referencias</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo BASE_URL; ?>superadmin/loyalty">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Buscar por nombre o email..."
                               value="<?php echo htmlspecialchars($current_search ?? ''); ?>">
                        <?php if (isset($current_status) && $current_status !== 'all'): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($current_status); ?>">
                        <?php endif; ?>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($current_search)): ?>
                            <a href="<?php echo BASE_URL; ?>superadmin/loyalty<?php echo isset($current_status) && $current_status !== 'all' ? '?status=' . urlencode($current_status) : ''; ?>" 
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
                    <a href="<?php echo BASE_URL; ?>superadmin/loyalty?status=all<?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>" 
                       class="btn <?php echo ($current_status ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        Todos
                    </a>
                    <a href="<?php echo BASE_URL; ?>superadmin/loyalty?status=pending<?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>" 
                       class="btn <?php echo ($current_status ?? '') === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        Pendientes
                    </a>
                    <a href="<?php echo BASE_URL; ?>superadmin/loyalty?status=paid<?php echo !empty($current_search) ? '&search=' . urlencode($current_search) : ''; ?>" 
                       class="btn <?php echo ($current_status ?? '') === 'paid' ? 'btn-success' : 'btn-outline-success'; ?>">
                        Pagadas
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
                <p class="mb-0">Total de referencias: <strong><?php echo number_format($stats['total_referrals'] ?? 0); ?></strong></p>
                <p class="mb-0">Mostrando página <?php echo $current_page; ?> de <?php echo $total_pages; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Referrals Management -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>
            Gestión de Referencias y Comisiones
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($referrals)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Referente</th>
                        <th>Usuario Referido</th>
                        <th>Código</th>
                        <th>Comisión</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $referral): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($referral['referrer_name']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($referral['referrer_email']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($referral['referred_name']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($referral['referred_email']); ?></small>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($referral['referral_code']); ?></code>
                        </td>
                        <td>
                            <strong class="text-success">$<?php echo number_format($referral['commission_amount'], 2); ?></strong>
                        </td>
                        <td>
                            <?php
                            $statusClass = $referral['commission_status'] === 'paid' ? 'success' : 'warning';
                            $statusText = $referral['commission_status'] === 'paid' ? 'Pagada' : 'Pendiente';
                            ?>
                            <span class="badge bg-<?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                            <?php if ($referral['payment_date']): ?>
                            <br><small class="text-muted">
                                Pagado: <?php echo date('d/m/Y', strtotime($referral['payment_date'])); ?>
                            </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($referral['registered_at'])); ?>
                        </td>
                        <td>
                            <?php if ($referral['commission_status'] === 'pending'): ?>
                            <button type="button" class="btn btn-sm btn-success" 
                                    onclick="openPaymentModal(
                                        <?php echo $referral['id']; ?>, 
                                        '<?php echo htmlspecialchars($referral['referrer_name']); ?>',
                                        <?php echo $referral['commission_amount']; ?>
                                    )">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                Pagar
                            </button>
                            <?php else: ?>
                            <span class="text-success small">
                                <i class="fas fa-check-circle me-1"></i>
                                Pagado
                            </span>
                            <?php if ($referral['evidence_file']): ?>
                            <br><a href="<?php echo BASE_URL; ?>public/uploads/<?php echo $referral['evidence_file']; ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-file me-1"></i>
                                Ver Evidencia
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center">
                <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Anterior</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Siguiente</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="text-center py-4">
            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay referencias registradas</h5>
            <p class="text-muted">Las referencias aparecerán aquí cuando los usuarios compartan sus enlaces.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Registrar Pago de Comisión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>superadmin/pay-commission" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="referral_registration_id" name="referral_registration_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Usuario:</label>
                        <span id="referrer_name" class="form-control-plaintext"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Monto a Pagar *</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Seleccionar método</option>
                            <option value="transfer">Transferencia Bancaria</option>
                            <option value="paypal">PayPal</option>
                            <option value="cash">Efectivo</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="evidence_file" class="form-label">Evidencia de Pago</label>
                        <input type="file" class="form-control" id="evidence_file" name="evidence_file" 
                               accept="image/*,.pdf">
                        <div class="form-text">Sube una imagen o PDF como comprobante del pago.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPaymentModal(referralId, referrerName, amount) {
    document.getElementById('referral_registration_id').value = referralId;
    document.getElementById('referrer_name').textContent = referrerName;
    document.getElementById('amount').value = amount;
    
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}
</script>