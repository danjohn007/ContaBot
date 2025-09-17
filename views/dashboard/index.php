<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>movements/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Nuevo Movimiento
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Ingresos (Este Mes)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($summary['total_income'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-up fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Gastos (Este Mes)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($summary['total_expenses'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-down fa-2x text-danger"></i>
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
                            Balance
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($summary['balance'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-balance-scale fa-2x text-info"></i>
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
                            Gastos Fiscales
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($summary['fiscal_expenses'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Income vs Expenses Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Ingresos vs Gastos (Últimos 6 meses)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="monthlyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Receipts -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Resumen Rápido</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-gray-500">Comprobantes Pendientes</div>
                    <div class="h5 font-weight-bold"><?php echo $summary['pending_receipts']; ?></div>
                </div>
                <div class="mb-3">
                    <div class="small text-gray-500">Tipo de Usuario</div>
                    <div class="h6">
                        <span class="badge bg-<?php echo $user_type === 'business' ? 'primary' : 'success'; ?>">
                            <?php echo $user_type === 'business' ? 'Negocio' : 'Personal'; ?>
                        </span>
                    </div>
                </div>
                <div class="d-grid">
                    <a href="<?php echo BASE_URL; ?>reports" class="btn btn-primary">
                        <i class="fas fa-chart-bar me-2"></i>
                        Ver Reportes Completos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Movements -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Movimientos Recientes</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($recent_movements)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Categoría</th>
                        <th>Clasificación</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_movements as $movement): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($movement['movement_date'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($movement['concept']); ?></strong>
                            <?php if (!empty($movement['description'])): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($movement['description']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge" style="background-color: <?php echo $movement['category_color']; ?>">
                                <?php echo htmlspecialchars($movement['category_name']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $movement['classification'] === 'fiscal' ? 'warning' : 
                                    ($movement['classification'] === 'business' ? 'info' : 'secondary'); 
                            ?>">
                                <?php echo ucfirst($movement['classification']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($movement['type'] === 'income'): ?>
                                <span class="text-success"><i class="fas fa-arrow-up"></i> Ingreso</span>
                            <?php else: ?>
                                <span class="text-danger"><i class="fas fa-arrow-down"></i> Gasto</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong class="<?php echo $movement['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                $<?php echo number_format($movement['amount'], 2); ?>
                            </strong>
                        </td>
                        <td>
                            <?php if ($movement['type'] === 'expense'): ?>
                                <?php if ($movement['is_billed']): ?>
                                    <span class="badge bg-success">Facturado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Pendiente</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>movements" class="btn btn-outline-primary">
                Ver Todos los Movimientos
            </a>
        </div>
        <?php else: ?>
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay movimientos registrados</h5>
            <p class="text-muted">Comienza registrando tu primer ingreso o gasto</p>
            <a href="<?php echo BASE_URL; ?>movements/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Registrar Primer Movimiento
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Monthly Chart
const monthlyData = <?php echo json_encode($monthly_data); ?>;

const ctx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.labels,
        datasets: [{
            label: 'Ingresos',
            data: monthlyData.income,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.1,
            fill: true
        }, {
            label: 'Gastos',
            data: monthlyData.expenses,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<style>
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.chart-area {
    position: relative;
    height: 300px;
}
</style>