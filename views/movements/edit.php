<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Movimiento</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo BASE_URL; ?>movements" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver a Movimientos
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-edit me-2"></i>
                    Editar Movimiento
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>movements/edit/<?php echo $movement['id']; ?>" 
                      enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipo de Movimiento *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="income" <?php echo $movement['type'] === 'income' ? 'selected' : ''; ?>>💰 Ingreso</option>
                                <option value="expense" <?php echo $movement['type'] === 'expense' ? 'selected' : ''; ?>>💸 Gasto</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona el tipo de movimiento.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Categoría *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        data-color="<?php echo $category['color']; ?>"
                                        <?php echo $movement['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona una categoría.
                            </div>
                            <div class="form-text">
                                <a href="<?php echo BASE_URL; ?>categories/create" target="_blank">
                                    <i class="fas fa-plus me-1"></i>
                                    Crear nueva categoría
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Monto *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control currency-input" id="amount" name="amount" 
                                       step="0.01" min="0.01" placeholder="0.00" required
                                       value="<?php echo $movement['amount']; ?>">
                            </div>
                            <div class="invalid-feedback">
                                Por favor ingresa un monto válido.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="movement_date" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="movement_date" name="movement_date" 
                                   value="<?php echo $movement['movement_date']; ?>" required>
                            <div class="invalid-feedback">
                                Por favor selecciona una fecha.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="concept" class="form-label">Concepto *</label>
                        <input type="text" class="form-control" id="concept" name="concept" 
                               placeholder="Describe brevemente el movimiento" required maxlength="255"
                               value="<?php echo htmlspecialchars($movement['concept']); ?>">
                        <div class="invalid-feedback">
                            Por favor ingresa el concepto del movimiento.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Descripción detallada (opcional)"><?php echo htmlspecialchars($movement['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="classification" class="form-label">Clasificación *</label>
                            <select class="form-select" id="classification" name="classification" required>
                                <option value="">Seleccionar clasificación</option>
                                <option value="personal" <?php echo $movement['classification'] === 'personal' ? 'selected' : ''; ?>>👤 Personal</option>
                                <option value="business" <?php echo $movement['classification'] === 'business' ? 'selected' : ''; ?>>🏢 Negocio</option>
                                <option value="fiscal" <?php echo $movement['classification'] === 'fiscal' ? 'selected' : ''; ?>>📋 Fiscal</option>
                                <option value="non_fiscal" <?php echo $movement['classification'] === 'non_fiscal' ? 'selected' : ''; ?>>📄 No Fiscal</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona una clasificación.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Método de Pago *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Seleccionar método</option>
                                <option value="cash" <?php echo $movement['payment_method'] === 'cash' ? 'selected' : ''; ?>>💵 Efectivo</option>
                                <option value="card" <?php echo $movement['payment_method'] === 'card' ? 'selected' : ''; ?>>💳 Tarjeta</option>
                                <option value="transfer" <?php echo $movement['payment_method'] === 'transfer' ? 'selected' : ''; ?>>🏦 Transferencia</option>
                                <option value="check" <?php echo $movement['payment_method'] === 'check' ? 'selected' : ''; ?>>📃 Cheque</option>
                                <option value="other" <?php echo $movement['payment_method'] === 'other' ? 'selected' : ''; ?>>❓ Otro</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona un método de pago.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current File Display -->
                    <?php if ($movement['receipt_file']): ?>
                    <div class="mb-3">
                        <label class="form-label">Comprobante Actual</label>
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-alt me-2"></i>
                                <strong><?php echo basename($movement['receipt_file']); ?></strong>
                            </div>
                            <div>
                                <a href="<?php echo BASE_URL; ?>movements/download/<?php echo $movement['id']; ?>" 
                                   class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="receipt_file" class="form-label">
                            <?php echo $movement['receipt_file'] ? 'Cambiar Comprobante (Opcional)' : 'Comprobante (Opcional)'; ?>
                        </label>
                        <div class="file-upload-area">
                            <input type="file" class="form-control" id="receipt_file" name="receipt_file" 
                                   accept="image/*,.pdf">
                            <div class="text-center mt-3">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">
                                    <?php echo $movement['receipt_file'] ? 
                                        'Arrastra un archivo aquí para reemplazar el actual' : 
                                        'Arrastra un archivo aquí o haz clic para seleccionar'; ?>
                                </p>
                                <small class="text-muted">
                                    Formatos soportados: JPG, PNG, GIF, PDF (Máximo 5MB)
                                </small>
                            </div>
                            <div class="file-preview mt-3"></div>
                        </div>
                    </div>
                    
                    <!-- Additional Options -->
                    <div class="mb-3" id="billing-options" style="<?php echo $movement['type'] === 'expense' ? 'display: block;' : 'display: none;'; ?>">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_billed" name="is_billed"
                                   <?php echo $movement['is_billed'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_billed">
                                <i class="fas fa-file-invoice me-1"></i>
                                Marcar como facturado
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo BASE_URL; ?>movements" class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" data-loading>
                            <i class="fas fa-save me-2"></i>
                            Actualizar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const billingOptions = document.getElementById('billing-options');
    const categorySelect = document.getElementById('category_id');
    
    // Show/hide billing options based on type
    typeSelect.addEventListener('change', function() {
        if (this.value === 'expense') {
            billingOptions.style.display = 'block';
        } else {
            billingOptions.style.display = 'none';
            document.getElementById('is_billed').checked = false;
        }
    });
    
    // Update category color preview
    function updateCategoryColor() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        if (selectedOption.dataset.color) {
            categorySelect.style.borderLeftColor = selectedOption.dataset.color;
            categorySelect.style.borderLeftWidth = '4px';
        } else {
            categorySelect.style.borderLeft = '';
        }
    }
    
    categorySelect.addEventListener('change', updateCategoryColor);
    
    // Initialize category color
    updateCategoryColor();
    
    // Amount formatting
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('input', function() {
        // Remove any non-numeric characters except decimal point
        let value = this.value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Limit to 2 decimal places
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        this.value = value;
    });
});
</script>