<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reporte Fiscal <?php echo $year; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>reports" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Volver a Reportes
            </a>
            <!-- Year selector -->
            <form method="GET" action="<?php echo BASE_URL; ?>reports/fiscal" class="d-inline-block ms-2">
                <select name="year" class="form-select d-inline-block" style="width: auto;" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Ingresos Facturados
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($fiscal_summary['taxable_income'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
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
                            Gastos Deducibles
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($fiscal_summary['deductible_expenses'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt fa-2x text-danger"></i>
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
                            Balance Fiscal
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($fiscal_summary['taxable_income'] - $fiscal_summary['deductible_expenses'], 2); ?>
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
                            Total Movimientos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $fiscal_summary['total_movements']; ?>
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

<!-- Movements Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Movimientos Fiscales <?php echo $year; ?></h6>
            </div>
            <div class="card-body">
                <?php if (!empty($fiscal_movements)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Categoría</th>
                                <th>Monto</th>
                                <th>Facturado</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fiscal_movements as $movement): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($movement['movement_date'])); ?></td>
                                <td>
                                    <?php if ($movement['type'] === 'income'): ?>
                                        <span class="badge bg-success">Ingreso</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Gasto</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($movement['concept']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $movement['category_color'] ?? '#6c757d'; ?>; color: white;">
                                        <?php echo htmlspecialchars($movement['category_name']); ?>
                                    </span>
                                </td>
                                <td class="<?php echo $movement['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($movement['amount'], 2); ?>
                                </td>
                                <td>
                                    <?php if ($movement['is_billed']): ?>
                                        <span class="badge bg-success">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($movement['receipt_file'])): ?>
                                        <a href="<?php echo BASE_URL; ?>public/uploads/<?php echo $movement['receipt_file']; ?>" target="_blank">
                                            <i class="fas fa-file-alt text-primary"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay movimientos fiscales registrados</h5>
                    <p class="text-muted">No se encontraron movimientos marcados como facturados para el año <?php echo $year; ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
</style>