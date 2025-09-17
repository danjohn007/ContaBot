<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Categorías</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>categories/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Nueva Categoría
            </a>
            <?php if (empty($categories)): ?>
            <a href="<?php echo BASE_URL; ?>categories/createDefaults" class="btn btn-outline-secondary">
                <i class="fas fa-magic me-2"></i>
                Crear Por Defecto
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($categories)): ?>
<!-- Categories Grid -->
<div class="row">
    <?php foreach ($categories as $category): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow-sm h-100 category-card" data-category-id="<?php echo $category['id']; ?>">
            <div class="card-header" style="background-color: <?php echo $category['color']; ?>; color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>categories/edit/<?php echo $category['id']; ?>">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a>
                            </li>
                            <?php if ($category['movement_count'] == 0): ?>
                            <li>
                                <a class="dropdown-item text-danger" href="#" 
                                   onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($category['description'])): ?>
                <p class="card-text text-muted mb-3">
                    <?php echo htmlspecialchars($category['description']); ?>
                </p>
                <?php endif; ?>
                
                <div class="row text-center">
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $category['movement_count']; ?></div>
                            <div class="stat-label">Movimientos</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-value text-success">$<?php echo number_format($category['total_income'], 0); ?></div>
                            <div class="stat-label">Ingresos</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-value text-danger">$<?php echo number_format($category['total_expenses'], 0); ?></div>
                            <div class="stat-label">Gastos</div>
                        </div>
                    </div>
                </div>
                
                <?php if ($category['movement_count'] > 0): ?>
                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>movements?category_id=<?php echo $category['id']; ?>" 
                       class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-list me-2"></i>
                        Ver Movimientos
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($category['movement_count'] == 0): ?>
            <div class="card-footer bg-light text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Sin movimientos - Puede eliminarse
                </small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Summary Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-chart-pie me-2"></i>
                    Resumen de Categorías
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo count($categories); ?></div>
                            <div class="stat-label">Total Categorías</div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php echo array_sum(array_column($categories, 'movement_count')); ?>
                            </div>
                            <div class="stat-label">Total Movimientos</div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="stat-item">
                            <div class="stat-value text-success">
                                $<?php echo number_format(array_sum(array_column($categories, 'total_income')), 2); ?>
                            </div>
                            <div class="stat-label">Total Ingresos</div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="stat-item">
                            <div class="stat-value text-danger">
                                $<?php echo number_format(array_sum(array_column($categories, 'total_expenses')), 2); ?>
                            </div>
                            <div class="stat-label">Total Gastos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Empty State -->
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-tags fa-4x text-muted mb-4"></i>
                <h4 class="text-muted mb-3">No tienes categorías</h4>
                <p class="text-muted mb-4">
                    Las categorías te ayudan a organizar y clasificar tus ingresos y gastos. 
                    Puedes crear categorías personalizadas o usar las categorías por defecto.
                </p>
                <div class="d-grid gap-2 d-md-block">
                    <a href="<?php echo BASE_URL; ?>categories/create" class="btn btn-primary me-md-2">
                        <i class="fas fa-plus me-2"></i>
                        Crear Mi Primera Categoría
                    </a>
                    <a href="<?php echo BASE_URL; ?>categories/createDefaults" class="btn btn-outline-secondary">
                        <i class="fas fa-magic me-2"></i>
                        Usar Categorías Por Defecto
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.category-card {
    transition: all 0.3s ease;
    border: none;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.stat-item {
    padding: 0.5rem 0;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: bold;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-header .dropdown-toggle {
    border: 1px solid rgba(255,255,255,0.3);
}

.card-header .dropdown-toggle:hover {
    background-color: rgba(255,255,255,0.1);
}
</style>

<script>
function deleteCategory(categoryId) {
    if (confirm('¿Está seguro de que desea eliminar esta categoría? Esta acción no se puede deshacer.')) {
        fetch(`<?php echo BASE_URL; ?>categories/delete/${categoryId}?ajax=1`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ContaBot.showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                ContaBot.showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            ContaBot.showNotification('Error de conexión', 'danger');
        });
    }
}
</script>