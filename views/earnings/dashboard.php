<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line me-2 text-success"></i>
        Mis Ganancias
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>earnings/accounts" class="btn btn-primary">
                <i class="fas fa-users me-2"></i>
                Gestionar Cuentas
            </a>
        </div>
    </div>
</div>

<!-- Earnings Statistics -->
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
                            <?php echo number_format($earnings['total_referrals'] ?? 0); ?>
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
                            Total Ganado
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($earnings['total_earned'] ?? 0, 2); ?>
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
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Pagado
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($earnings['total_paid'] ?? 0, 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            Pendiente
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($earnings['total_pending'] ?? 0, 2); ?>
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

<!-- Referral Link -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-link me-2"></i>
                    Tu Enlace de Referido
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" id="referralUrl" 
                                   value="<?php echo htmlspecialchars($referral_url); ?>" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="copyReferralUrl()">
                                <i class="fas fa-copy me-1"></i>
                                Copiar
                            </button>
                        </div>
                        <div class="form-text">
                            Comparte este enlace para ganar comisiones del <?php echo $referral_link['commission_rate']; ?>% 
                            por cada nuevo usuario que se registre.
                        </div>
                    </div>
                    <div class="col-md-4">
                        <form method="POST" action="<?php echo BASE_URL; ?>earnings/generate-new-link" class="d-inline">
                            <button type="submit" class="btn btn-secondary" 
                                    onclick="return confirm('¿Estás seguro de generar un nuevo enlace? El anterior dejará de funcionar.')">
                                <i class="fas fa-sync me-1"></i>
                                Generar Nuevo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-area me-2"></i>
                    Ganancias por Mes
                </h6>
            </div>
            <div class="card-body">
                <canvas id="earningsChart" width="100%" height="50"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>
                    Estado de Comisiones
                </h6>
            </div>
            <div class="card-body">
                <canvas id="commissionsChart" width="100%" height="50"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Referrals -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-users me-2"></i>
                    Usuarios Referidos Recientes
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($referrals)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Comisión</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($referrals, 0, 10) as $referral): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($referral['referred_name']); ?></td>
                                <td><?php echo htmlspecialchars($referral['referred_email']); ?></td>
                                <td>
                                    <strong class="text-success">
                                        $<?php echo number_format($referral['commission_amount'], 2); ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = $referral['commission_status'] === 'paid' ? 'success' : 'warning';
                                    $statusText = $referral['commission_status'] === 'paid' ? 'Pagada' : 'Pendiente';
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($referral['registration_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No tienes referidos aún</h5>
                    <p class="text-muted">Comparte tu enlace para empezar a ganar comisiones.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-receipt me-2"></i>
                    Pagos Recibidos
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($commission_payments)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($commission_payments, 0, 5) as $payment): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 text-success">$<?php echo number_format($payment['amount'], 2); ?></h6>
                            <small><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($payment['referred_name']); ?></p>
                        <small class="text-muted"><?php echo htmlspecialchars($payment['payment_method']); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-money-bill-wave fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No hay pagos aún</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function copyReferralUrl() {
    const input = document.getElementById('referralUrl');
    input.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = input.nextElementSibling;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check me-1"></i> ¡Copiado!';
    button.classList.remove('btn-outline-primary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-primary');
    }, 2000);
}

// Earnings Chart
const earningsData = <?php echo json_encode($earnings_by_month); ?>;
const earningsLabels = earningsData.map(item => {
    const date = new Date(item.month + '-01');
    return date.toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
});
const earningsValues = earningsData.map(item => parseFloat(item.earnings || 0));

const earningsCtx = document.getElementById('earningsChart').getContext('2d');
new Chart(earningsCtx, {
    type: 'line',
    data: {
        labels: earningsLabels,
        datasets: [{
            label: 'Ganancias',
            data: earningsValues,
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            borderColor: 'rgba(28, 200, 138, 1)',
            borderWidth: 2,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(2);
                    }
                }
            }
        }
    }
});

// Commissions Pie Chart
const commissionsCtx = document.getElementById('commissionsChart').getContext('2d');
new Chart(commissionsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pagadas', 'Pendientes'],
        datasets: [{
            data: [
                <?php echo $earnings['total_paid'] ?? 0; ?>,
                <?php echo $earnings['total_pending'] ?? 0; ?>
            ],
            backgroundColor: [
                'rgba(28, 200, 138, 0.8)',
                'rgba(255, 193, 7, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>