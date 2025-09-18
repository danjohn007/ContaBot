<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-crown me-2 text-warning"></i>
        Panel SuperAdmin
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>superadmin/pending-users" class="btn btn-warning">
                <i class="fas fa-user-clock me-2"></i>
                Aprobar Usuarios
                <?php if ($stats['pending_users'] > 0): ?>
                <span class="badge bg-danger ms-1"><?php echo $stats['pending_users']; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>superadmin/financial" class="btn btn-success">
                <i class="fas fa-chart-line me-2"></i>
                Dashboard Financiero
            </a>
            <a href="<?php echo BASE_URL; ?>superadmin/loyalty" class="btn btn-primary">
                <i class="fas fa-users me-2"></i>
                Sistema de Lealtad
            </a>
            <a href="<?php echo BASE_URL; ?>superadmin/users" class="btn btn-info">
                <i class="fas fa-users me-2"></i>
                Gestionar Usuarios
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
                            Usuarios Activos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['total_users']); ?>
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
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pendientes de Aprobación
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['pending_users']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock fa-2x text-gray-300"></i>
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
                            Ingresos Este Mes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats['monthly_revenue'], 2); ?>
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
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Pagos Pendientes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats['outstanding_payments'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pending Users -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-clock me-2"></i>
                    Usuarios Pendientes de Aprobación
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_users)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pending_users, 0, 5) as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo ucfirst($user['user_type']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>superadmin/pending-users" class="btn btn-sm btn-primary">
                                        <i class="fas fa-check"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?php echo BASE_URL; ?>superadmin/pending-users" class="btn btn-primary">
                        Ver Todos los Pendientes
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">No hay usuarios pendientes</h5>
                    <p class="text-muted">Todos los usuarios han sido procesados</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Users by Plan -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>
                    Distribución por Plan
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['users_by_plan'])): ?>
                <div class="row">
                    <?php foreach ($stats['users_by_plan'] as $planData): ?>
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo ucfirst($planData['subscription_plan'] ?? 'Sin plan'); ?></strong>
                            </div>
                            <div>
                                <span class="badge bg-primary"><?php echo $planData['count']; ?> usuarios</span>
                            </div>
                        </div>
                        <div class="progress mt-1" style="height: 8px;">
                            <?php 
                            $percentage = ($planData['count'] / $stats['total_users']) * 100;
                            ?>
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay datos de planes</h5>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Users Alert -->
<?php if (!empty($overdue_users)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Usuarios con Pagos Vencidos
            </h5>
            <p>Hay <strong><?php echo count($overdue_users); ?> usuario(s)</strong> con pagos vencidos que requieren atención.</p>
            <hr>
            <div class="d-flex">
                <a href="<?php echo BASE_URL; ?>superadmin/users?status=suspended" class="btn btn-outline-danger me-2">
                    Ver Usuarios Vencidos
                </a>
                <form method="POST" action="<?php echo BASE_URL; ?>superadmin/auto-suspend" class="d-inline">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Suspender automáticamente usuarios con más de 7 días de retraso?')">
                        <i class="fas fa-ban me-2"></i>
                        Auto-Suspender
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}
.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}
</style>