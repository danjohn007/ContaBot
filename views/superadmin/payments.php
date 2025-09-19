<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-credit-card me-2 text-success"></i>
        Registro de Pagos
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

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Registro de Pagos:</strong> Aquí puedes registrar los pagos recibidos de usuarios aprobados con planes de pago pendientes.
</div>

<!-- Filter Options -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Filtrar por Estado</h6>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="<?php echo BASE_URL; ?>superadmin/payments?status=active" 
                       class="btn <?php echo $current_status === 'active' ? 'btn-success' : 'btn-outline-success'; ?>">
                        Usuarios Activos
                    </a>
                    <a href="<?php echo BASE_URL; ?>superadmin/payments?status=suspended" 
                       class="btn <?php echo $current_status === 'suspended' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        Suspendidos
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
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

<!-- Payment Registration Table -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Usuarios con Pagos Pendientes</h6>
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
                        <th>Plan</th>
                        <th>Monto Pendiente</th>
                        <th>Período de Facturación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                            <?php if ($user['rfc']): ?>
                            <br><small class="text-muted">RFC: <?php echo htmlspecialchars($user['rfc']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['plan_name']): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($user['plan_name']); ?></span>
                                <br><small class="text-muted">$<?php echo number_format($user['plan_price'], 2); ?></small>
                            <?php else: ?>
                                <span class="text-muted">Sin plan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['pending_amount']): ?>
                                <span class="text-danger fw-bold">$<?php echo number_format($user['pending_amount'], 2); ?></span>
                            <?php elseif ($user['plan_price'] > 0): ?>
                                <span class="text-success">Sin pagos pendientes</span>
                            <?php else: ?>
                                <span class="text-muted">Plan gratuito</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['billing_period_start'] && $user['billing_period_end']): ?>
                                <small>
                                    <?php echo date('d/m/Y', strtotime($user['billing_period_start'])); ?> -
                                    <?php echo date('d/m/Y', strtotime($user['billing_period_end'])); ?>
                                </small>
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
                            <?php if ($user['billing_id'] && $user['payment_status'] === 'pending'): ?>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" 
                                        id="payment-btn-<?php echo $user['billing_id']; ?>"
                                        onclick="registerPayment(
                                            <?php echo $user['billing_id']; ?>, 
                                            '<?php echo htmlspecialchars($user['name']); ?>', 
                                            <?php echo $user['pending_amount']; ?>
                                        )">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Registrar Pago
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="advancePayment(
                                            <?php echo $user['billing_id']; ?>, 
                                            '<?php echo htmlspecialchars($user['name']); ?>', 
                                            <?php echo $user['pending_amount']; ?>
                                        )">
                                    <i class="fas fa-fast-forward me-1"></i>
                                    Adelantar Pago
                                </button>
                            </div>
                            <?php elseif ($user['plan_price'] > 0): ?>
                                <span class="text-success small">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Al día
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">Plan gratuito</span>
                            <?php endif; ?>
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
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/payments?page=<?php echo $current_page - 1; ?>&status=<?php echo $current_status; ?>">
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
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/payments?page=<?php echo $i; ?>&status=<?php echo $current_status; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>superadmin/payments?page=<?php echo $current_page + 1; ?>&status=<?php echo $current_status; ?>">
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

<!-- Register Payment Modal -->
<div class="modal fade" id="registerPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-money-bill-wave me-2 text-success"></i>
                    Registrar Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="registerPaymentForm" method="POST" action="<?php echo BASE_URL; ?>superadmin/register-payment">
                <div class="modal-body">
                    <input type="hidden" name="billing_id" id="paymentBillingId">
                    
                    <div class="alert alert-info">
                        <strong>Usuario:</strong> <span id="paymentUserName"></span><br>
                        <strong>Monto:</strong> $<span id="paymentAmount"></span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">
                                    <i class="fas fa-credit-card me-1"></i>
                                    Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="paymentMethod" name="payment_method" required>
                                    <option value="">Seleccionar método</option>
                                    <?php foreach ($payment_methods as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paymentDate" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Fecha de Pago <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" id="paymentDate" name="payment_date" 
                                       value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transactionId" class="form-label">
                            <i class="fas fa-hashtag me-1"></i>
                            ID de Transacción / Referencia
                        </label>
                        <input type="text" class="form-control" id="transactionId" name="transaction_id" 
                               placeholder="Número de referencia, transferencia, etc.">
                        <div class="form-text">Opcional: Cualquier referencia o ID de la transacción</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentNotes" class="form-label">
                            <i class="fas fa-sticky-note me-1"></i>
                            Notas Adicionales
                        </label>
                        <textarea class="form-control" id="paymentNotes" name="notes" rows="3" 
                                  placeholder="Cualquier observación adicional sobre el pago"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>
                        Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Advance Payment Modal -->
<div class="modal fade" id="advancePaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-fast-forward me-2 text-warning"></i>
                    Adelantar Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="advancePaymentForm" method="POST" action="<?php echo BASE_URL; ?>superadmin/advance-payment">
                <div class="modal-body">
                    <input type="hidden" name="billing_id" id="advancePaymentBillingId">
                    
                    <div class="alert alert-warning">
                        <strong>Usuario:</strong> <span id="advancePaymentUserName"></span><br>
                        <strong>Monto del próximo pago:</strong> $<span id="advancePaymentAmount"></span><br>
                        <strong>Nota:</strong> Este pago se aplicará al siguiente período de facturación.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advancePaymentMethod" class="form-label">
                                    <i class="fas fa-credit-card me-1"></i>
                                    Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="advancePaymentMethod" name="payment_method" required>
                                    <option value="">Seleccionar método</option>
                                    <?php foreach ($payment_methods as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advancePaymentDate" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Fecha de Pago <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" id="advancePaymentDate" name="payment_date" 
                                       value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="advanceTransactionId" class="form-label">
                            <i class="fas fa-hashtag me-1"></i>
                            ID de Transacción / Referencia
                        </label>
                        <input type="text" class="form-control" id="advanceTransactionId" name="transaction_id" 
                               placeholder="Número de referencia, transferencia, etc.">
                        <div class="form-text">Opcional: Cualquier referencia o ID de la transacción</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="advancePaymentNotes" class="form-label">
                            <i class="fas fa-sticky-note me-1"></i>
                            Notas Adicionales
                        </label>
                        <textarea class="form-control" id="advancePaymentNotes" name="notes" rows="3" 
                                  placeholder="Cualquier observación adicional sobre el adelanto de pago"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-fast-forward me-1"></i>
                        Adelantar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function registerPayment(billingId, userName, amount) {
    document.getElementById('paymentBillingId').value = billingId;
    document.getElementById('paymentUserName').textContent = userName;
    document.getElementById('paymentAmount').textContent = parseFloat(amount).toFixed(2);
    
    // Reset form
    document.getElementById('registerPaymentForm').reset();
    document.getElementById('paymentDate').value = new Date().toISOString().slice(0, 16);
    
    new bootstrap.Modal(document.getElementById('registerPaymentModal')).show();
}

function advancePayment(billingId, userName, amount) {
    document.getElementById('advancePaymentBillingId').value = billingId;
    document.getElementById('advancePaymentUserName').textContent = userName;
    document.getElementById('advancePaymentAmount').textContent = parseFloat(amount).toFixed(2);
    
    // Reset form
    document.getElementById('advancePaymentForm').reset();
    document.getElementById('advancePaymentDate').value = new Date().toISOString().slice(0, 16);
    
    new bootstrap.Modal(document.getElementById('advancePaymentModal')).show();
}

// Form validation for regular payment
document.getElementById('registerPaymentForm').addEventListener('submit', function(e) {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const paymentDate = document.getElementById('paymentDate').value;
    
    if (!paymentMethod || !paymentDate) {
        e.preventDefault();
        alert('Por favor complete todos los campos obligatorios.');
        return false;
    }
    
    // Disable the payment button to prevent multiple submissions
    const billingId = document.getElementById('paymentBillingId').value;
    const paymentBtn = document.getElementById('payment-btn-' + billingId);
    if (paymentBtn) {
        paymentBtn.disabled = true;
        paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';
    }
});

// Form validation for advance payment
document.getElementById('advancePaymentForm').addEventListener('submit', function(e) {
    const paymentMethod = document.getElementById('advancePaymentMethod').value;
    const paymentDate = document.getElementById('advancePaymentDate').value;
    
    if (!paymentMethod || !paymentDate) {
        e.preventDefault();
        alert('Por favor complete todos los campos obligatorios.');
        return false;
    }
});
</script>