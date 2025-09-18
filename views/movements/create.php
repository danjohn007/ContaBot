<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Nuevo Movimiento</h1>
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
                    <i class="fas fa-plus-circle me-2"></i>
                    Registrar Nuevo Movimiento
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>movements/create" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipo de Movimiento *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="income">💰 Ingreso</option>
                                <option value="expense">💸 Gasto</option>
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
                                <option value="<?php echo $category['id']; ?>" data-color="<?php echo $category['color']; ?>">
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
                                       step="0.01" min="0.01" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback">
                                Por favor ingresa un monto válido.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="movement_date" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="movement_date" name="movement_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback">
                                Por favor selecciona una fecha.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="concept" class="form-label">Concepto *</label>
                        <input type="text" class="form-control" id="concept" name="concept" 
                               placeholder="Describe brevemente el movimiento" required maxlength="255">
                        <div class="invalid-feedback">
                            Por favor ingresa el concepto del movimiento.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Descripción detallada (opcional)"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="classification" class="form-label">Clasificación *</label>
                            <select class="form-select" id="classification" name="classification" required>
                                <option value="">Seleccionar clasificación</option>
                                <option value="personal">👤 Personal</option>
                                <option value="business">🏢 Negocio</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona una clasificación.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Método de Pago *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Seleccionar método</option>
                                <option value="cash">💵 Efectivo</option>
                                <option value="card">💳 Tarjeta</option>
                                <option value="transfer">🏦 Transferencia</option>
                                <option value="check">📃 Cheque</option>
                                <option value="other">❓ Otro</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona un método de pago.
                            </div>
                        </div>
                    </div>
                    
                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="receipt_file" class="form-label">Comprobante (Opcional)</label>
                        <div class="file-upload-area">
                            <input type="file" class="form-control" id="receipt_file" name="receipt_file" 
                                   accept="image/*,.pdf">
                            <div class="text-center mt-3">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">
                                    Arrastra un archivo aquí o haz clic para seleccionar
                                </p>
                                <small class="text-muted">
                                    Formatos soportados: JPG, PNG, GIF, PDF (Máximo 5MB)
                                </small>
                            </div>
                            <div class="file-preview mt-3"></div>
                        </div>
                    </div>
                    
                    <!-- Additional Options -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_billed" name="is_billed">
                            <label class="form-check-label" for="is_billed">
                                <i class="fas fa-file-invoice me-1"></i>
                                FACTURADO (Incluir en reporte fiscal)
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
                            Guardar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    
    // Update category color preview
    categorySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.color) {
            this.style.borderLeftColor = selectedOption.dataset.color;
            this.style.borderLeftWidth = '4px';
        } else {
            this.style.borderLeft = '';
        }
    });
    
    // Auto-suggest classification based on category
    categorySelect.addEventListener('change', function() {
        const categoryName = this.options[this.selectedIndex].text.toLowerCase();
        const classificationSelect = document.getElementById('classification');
        
        if (categoryName.includes('salario') || categoryName.includes('ingreso') || 
            categoryName.includes('venta') || categoryName.includes('ganancia')) {
            classificationSelect.value = 'business';
        } else if (categoryName.includes('oficina') || categoryName.includes('trabajo') || 
                   categoryName.includes('negocio')) {
            classificationSelect.value = 'business';
        } else {
            classificationSelect.value = 'personal';
        }
    });
    
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