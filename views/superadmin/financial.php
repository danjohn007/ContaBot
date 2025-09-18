<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line me-2 text-success"></i>
        Dashboard Financiero
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>superadmin" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Volver al Panel
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>
                Imprimir Reporte
            </button>
        </div>
    </div>
</div>

<!-- Financial Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Ingresos Totales
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?>
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
                            Pagos Este Mes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['monthly_payments'] ?? 0); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                            Usuarios de Pago
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['paying_users'] ?? 0); ?>
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
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Promedio por Pago
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats['average_payment'] ?? 0, 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Revenue Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-area me-2"></i>
                    Ingresos por Mes (Últimos 12 meses)
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($monthly_revenue)): ?>
                <div class="chart-area">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No hay datos de ingresos disponibles</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Payment Methods -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-credit-card me-2"></i>
                    Métodos de Pago
                </h6>
            </div>
            <div class="card-body">
                <div class="small mb-1">Transferencia Bancaria</div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 65%"></div>
                </div>
                <div class="small mb-1">Tarjeta de Crédito</div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 25%"></div>
                </div>
                <div class="small mb-1">PayPal</div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 10%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Pagos Recientes
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_payments)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Plan</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_payments as $payment): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($payment['user_name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($payment['user_email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($payment['plan_name']); ?></td>
                                <td>
                                    <span class="h6 mb-0 text-success">
                                        $<?php echo number_format($payment['amount'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No hay pagos recientes</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<?php if (!empty($monthly_revenue)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Revenue Chart
    const ctx = document.getElementById('monthlyRevenueChart');
    if (ctx) {
        const monthlyData = <?php echo json_encode($monthly_revenue); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Ingresos',
                    data: monthlyData.map(item => parseFloat(item.revenue)),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.text-gray-500 {
    color: #858796 !important;
}
</style>