<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Movimientos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>movements/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Nuevo Movimiento
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-filter me-2"></i>
            Filtros
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>movements" class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label">Fecha Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo $filters['date_from'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Fecha Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo $filters['date_to'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Tipo</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Todos</option>
                    <option value="income" <?php echo ($filters['type'] ?? '') === 'income' ? 'selected' : ''; ?>>Ingresos</option>
                    <option value="expense" <?php echo ($filters['type'] ?? '') === 'expense' ? 'selected' : ''; ?>>Gastos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="category_id" class="form-label">Categoría</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                            <?php echo ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="classification" class="form-label">Clasificación</label>
                <select class="form-select" id="classification" name="classification">
                    <option value="">Todas</option>
                    <option value="personal" <?php echo ($filters['classification'] ?? '') === 'personal' ? 'selected' : ''; ?>>Personal</option>
                    <option value="business" <?php echo ($filters['classification'] ?? '') === 'business' ? 'selected' : ''; ?>>Negocio</option>
                    <option value="fiscal" <?php echo ($filters['classification'] ?? '') === 'fiscal' ? 'selected' : ''; ?>>Fiscal</option>
                    <option value="non_fiscal" <?php echo ($filters['classification'] ?? '') === 'non_fiscal' ? 'selected' : ''; ?>>No Fiscal</option>
                </select>
            </div>
            <div class="col-md-8">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Buscar en concepto o descripción..." 
                       value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>
                    Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>movements" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Movements Table -->
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">
            Lista de Movimientos
            <?php if ($pagination['total_count'] > 0): ?>
                <span class="badge bg-primary"><?php echo $pagination['total_count']; ?></span>
            <?php endif; ?>
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($movements)): ?>
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
                        <th>Comprobante</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td>
                            <strong><?php echo date('d/m/Y', strtotime($movement['movement_date'])); ?></strong>
                            <br><small class="text-muted"><?php echo date('H:i', strtotime($movement['created_at'])); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($movement['concept']); ?></strong>
                            <?php if (!empty($movement['description'])): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($movement['description']); ?></small>
                            <?php endif; ?>
                            <br><small class="text-muted">
                                <i class="fas fa-credit-card me-1"></i>
                                <?php 
                                $paymentMethods = [
                                    'cash' => 'Efectivo',
                                    'card' => 'Tarjeta',
                                    'transfer' => 'Transferencia',
                                    'check' => 'Cheque',
                                    'other' => 'Otro'
                                ];
                                echo $paymentMethods[$movement['payment_method']] ?? 'N/A';
                                ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge" style="background-color: <?php echo $movement['category_color']; ?>; color: white;">
                                <?php echo htmlspecialchars($movement['category_name']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $movement['classification'] === 'fiscal' ? 'warning' : 
                                    ($movement['classification'] === 'business' ? 'info' : 
                                    ($movement['classification'] === 'personal' ? 'success' : 'secondary')); 
                            ?>">
                                <?php 
                                $classifications = [
                                    'personal' => 'Personal',
                                    'business' => 'Negocio',
                                    'fiscal' => 'Fiscal',
                                    'non_fiscal' => 'No Fiscal'
                                ];
                                echo $classifications[$movement['classification']] ?? $movement['classification'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($movement['type'] === 'income'): ?>
                                <span class="text-success">
                                    <i class="fas fa-arrow-up"></i> Ingreso
                                </span>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="fas fa-arrow-down"></i> Gasto
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong class="<?php echo $movement['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                $<?php echo number_format($movement['amount'], 2); ?>
                            </strong>
                        </td>
                        <td>
                            <?php if ($movement['type'] === 'expense'): ?>
                                <button class="btn btn-sm btn-outline-<?php echo $movement['is_billed'] ? 'success' : 'warning'; ?>" 
                                        onclick="toggleBilling(<?php echo $movement['id']; ?>)">
                                    <i class="fas fa-<?php echo $movement['is_billed'] ? 'check' : 'clock'; ?>"></i>
                                    <?php echo $movement['is_billed'] ? 'Facturado' : 'Pendiente'; ?>
                                </button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($movement['receipt_file']): ?>
                                <a href="<?php echo BASE_URL; ?>movements/download/<?php echo $movement['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Descargar comprobante">
                                    <i class="fas fa-download"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?php echo BASE_URL; ?>movements/edit/<?php echo $movement['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteMovement(<?php echo $movement['id']; ?>)" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $pagination['current_page'] <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>movements?<?php echo http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])); ?>">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>movements?<?php echo http_build_query(array_merge($filters, ['page' => $i])); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>movements?<?php echo http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])); ?>">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="text-center">
            <small class="text-muted">
                Mostrando <?php echo (($pagination['current_page'] - 1) * $pagination['limit']) + 1; ?> 
                a <?php echo min($pagination['current_page'] * $pagination['limit'], $pagination['total_count']); ?> 
                de <?php echo $pagination['total_count']; ?> movimientos
            </small>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No se encontraron movimientos</h5>
            <?php if (empty($filters)): ?>
            <p class="text-muted">Comienza registrando tu primer movimiento</p>
            <a href="<?php echo BASE_URL; ?>movements/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Registrar Primer Movimiento
            </a>
            <?php else: ?>
            <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
            <a href="<?php echo BASE_URL; ?>movements" class="btn btn-outline-primary">
                <i class="fas fa-undo me-2"></i>
                Limpiar Filtros
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleBilling(movementId) {
    fetch(`<?php echo BASE_URL; ?>movements/toggleBilling/${movementId}`, {
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

function deleteMovement(movementId) {
    if (confirm('¿Está seguro de que desea eliminar este movimiento? Esta acción no se puede deshacer.')) {
        fetch(`<?php echo BASE_URL; ?>movements/delete/${movementId}?ajax=1`, {
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