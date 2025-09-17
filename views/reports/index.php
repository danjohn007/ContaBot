<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reportes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>reports/fiscal" class="btn btn-outline-primary">
                <i class="fas fa-file-invoice me-2"></i>
                Reporte Fiscal
            </a>
            <button type="button" class="btn btn-outline-success" onclick="exportData()">
                <i class="fas fa-download me-2"></i>
                Exportar
            </button>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>reports" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="date_from" class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-4">
                <label for="date_to" class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo $date_to; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>
                    Generar Reporte
                </button>
            </div>
        </form>
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
                            Total Ingresos
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
                            Total Gastos
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
    <!-- Category Breakdown -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Desglose por Categorías</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($category_stats)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Ingresos</th>
                                <th>Gastos</th>
                                <th>Balance</th>
                                <th>Movimientos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_stats as $stat): ?>
                            <tr>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $stat['color']; ?>; color: white;">
                                        <?php echo htmlspecialchars($stat['name']); ?>
                                    </span>
                                </td>
                                <td class="text-success">
                                    $<?php echo number_format($stat['income'], 2); ?>
                                </td>
                                <td class="text-danger">
                                    $<?php echo number_format($stat['expenses'], 2); ?>
                                </td>
                                <td class="<?php echo ($stat['income'] - $stat['expenses']) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($stat['income'] - $stat['expenses'], 2); ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $stat['movement_count']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay datos para mostrar</h5>
                    <p class="text-muted">No se encontraron movimientos en el período seleccionado</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Classification Breakdown -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Por Clasificación</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Personal</span>
                        <strong class="text-danger">$<?php echo number_format($summary['personal_expenses'], 2); ?></strong>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo $summary['total_expenses'] > 0 ? ($summary['personal_expenses'] / $summary['total_expenses'] * 100) : 0; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Negocio</span>
                        <strong class="text-danger">$<?php echo number_format($summary['business_expenses'], 2); ?></strong>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: <?php echo $summary['total_expenses'] > 0 ? ($summary['business_expenses'] / $summary['total_expenses'] * 100) : 0; ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Fiscal</span>
                        <strong class="text-danger">$<?php echo number_format($summary['fiscal_expenses'], 2); ?></strong>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: <?php echo $summary['total_expenses'] > 0 ? ($summary['fiscal_expenses'] / $summary['total_expenses'] * 100) : 0; ?>%"></div>
                    </div>
                </div>
                
                <hr>
                <div class="text-center">
                    <small class="text-muted">
                        Período: <?php echo date('d/m/Y', strtotime($date_from)); ?> - <?php echo date('d/m/Y', strtotime($date_to)); ?>
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Acciones Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>movements/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>
                        Nuevo Movimiento
                    </a>
                    <a href="<?php echo BASE_URL; ?>categories" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-tags me-2"></i>
                        Gestionar Categorías
                    </a>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportData()">
                        <i class="fas fa-download me-2"></i>
                        Exportar Datos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportData() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    
    const exportUrl = `<?php echo BASE_URL; ?>reports/export?format=csv&date_from=${dateFrom}&date_to=${dateTo}`;
    window.open(exportUrl, '_blank');
}
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

.text-xs {
    font-size: 0.75rem;
}

.progress {
    height: 6px;
}
</style>