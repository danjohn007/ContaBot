<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt text-primary"></i> Dashboard
            </h1>
            <div>
                <span class="text-muted">Bienvenido, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></span>
                <span class="badge bg-primary ms-2"><?php echo ucfirst($user['role']); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <!-- Monthly Income -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card bg-gradient-success text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-label">Ingresos del Mes</div>
                        <div class="stats-number"><?php echo formatMoney($monthly_summary['total_income']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-up stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Expenses -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card bg-gradient-danger text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-label">Gastos del Mes</div>
                        <div class="stats-number"><?php echo formatMoney($monthly_summary['total_expenses']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-down stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Balance -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-label">Balance del Mes</div>
                        <div class="stats-number"><?php echo formatMoney($monthly_summary['balance']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-balance-scale stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fiscal Expenses -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card" style="background: linear-gradient(135deg, #6f42c1, #5a32a8);">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="stats-label">Gastos Fiscales</div>
                        <div class="stats-number"><?php echo formatMoney($fiscal_summary['fiscal_expenses']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-start-primary">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt text-warning"></i> Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo BASE_URL; ?>/transactions/create" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Nueva Transacción
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo BASE_URL; ?>/reports" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar"></i> Ver Reportes
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo BASE_URL; ?>/transactions?status=pending" class="btn btn-warning w-100">
                            <i class="fas fa-clock"></i> Pendientes
                            <?php if ($fiscal_summary['pending_invoice_count'] > 0): ?>
                                <span class="badge bg-light text-dark ms-1"><?php echo $fiscal_summary['pending_invoice_count']; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?php echo BASE_URL; ?>/categories" class="btn btn-secondary w-100">
                            <i class="fas fa-tags"></i> Categorías
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Charts Column -->
    <div class="col-lg-8">
        <!-- Monthly Trend Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line text-primary"></i> Tendencia Mensual <?php echo date('Y'); ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Category Breakdown -->
        <?php if (!empty($category_breakdown)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie text-info"></i> Gastos por Categoría (Este Mes)
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar Column -->
    <div class="col-lg-4">
        <!-- Recent Transactions -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history text-primary"></i> Transacciones Recientes
                </h5>
                <a href="<?php echo BASE_URL; ?>/transactions" class="btn btn-sm btn-outline-primary">
                    Ver todas
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recent_transactions)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <?php foreach (array_slice($recent_transactions, 0, 8) as $transaction): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">
                                                <?php if ($transaction['type'] === 'income'): ?>
                                                    <i class="fas fa-arrow-up text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-arrow-down text-danger"></i>
                                                <?php endif; ?>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-truncate" style="max-width: 120px;">
                                                    <?php echo htmlspecialchars($transaction['concept']); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo formatDate($transaction['transaction_date']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <span class="fw-bold <?php echo $transaction['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo formatMoney($transaction['amount']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No hay transacciones recientes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pending Invoices -->
        <?php if (!empty($pending_invoices)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Facturas Pendientes
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach (array_slice($pending_invoices, 0, 5) as $invoice): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="text-truncate" style="max-width: 150px;">
                                        <?php echo htmlspecialchars($invoice['concept']); ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo formatDate($invoice['transaction_date']); ?>
                                    </small>
                                </td>
                                <td class="text-end pe-3">
                                    <?php echo formatMoney($invoice['amount']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Year Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt text-info"></i> Resumen Anual <?php echo date('Y'); ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="h5 text-success mb-1"><?php echo formatMoney($yearly_summary['total_income']); ?></div>
                        <small class="text-muted">Ingresos</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-danger mb-1"><?php echo formatMoney($yearly_summary['total_expenses']); ?></div>
                        <small class="text-muted">Gastos</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <div class="h4 <?php echo $yearly_summary['balance'] >= 0 ? 'text-success' : 'text-danger'; ?> mb-1">
                        <?php echo formatMoney($yearly_summary['balance']); ?>
                    </div>
                    <small class="text-muted">Balance Total</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script>
// Monthly Trend Chart
const monthlyData = <?php echo json_encode($monthly_data); ?>;
const balanceTrend = <?php echo json_encode($balance_trend); ?>;

if (monthlyData && monthlyData.length > 0) {
    const ctx1 = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month_name || 'N/A'),
            datasets: [{
                label: 'Ingresos',
                data: monthlyData.map(item => parseFloat(item.income) || 0),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Gastos',
                data: monthlyData.map(item => parseFloat(item.expenses) || 0),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
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
            }
        }
    });
}

// Category Chart
<?php if (!empty($category_breakdown)): ?>
const categoryData = <?php echo json_encode($category_breakdown); ?>;
if (categoryData && categoryData.length > 0) {
    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category_name || 'Sin categoría'),
            datasets: [{
                data: categoryData.map(item => parseFloat(item.total_amount) || 0),
                backgroundColor: categoryData.map(item => item.category_color || '#007bff')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>