<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Categoría</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo BASE_URL; ?>categories" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver a Categorías
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-edit me-2"></i>
                    Editar Categoría
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>categories/edit/<?php echo $category['id']; ?>" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Ej: Alimentación, Transporte, Servicios..." required maxlength="255"
                               value="<?php echo htmlspecialchars($category['name']); ?>">
                        <div class="invalid-feedback">
                            Por favor ingresa el nombre de la categoría.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción (Opcional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Describe qué tipo de gastos o ingresos incluye esta categoría..."><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="color" class="form-label">Color de la Categoría</label>
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <input type="color" class="form-control form-control-color" id="color" name="color" 
                                       value="<?php echo $category['color']; ?>" title="Elegir color">
                            </div>
                            <div class="col-md-6">
                                <div class="color-preview p-3 rounded text-center" id="colorPreview">
                                    <strong>Vista Previa</strong>
                                    <br><small><?php echo htmlspecialchars($category['name']); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">
                            El color ayuda a identificar visualmente la categoría en reportes y gráficas.
                        </div>
                    </div>
                    
                    <!-- Color Presets -->
                    <div class="mb-4">
                        <label class="form-label">Colores Sugeridos</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn color-preset" data-color="#28a745" style="background-color: #28a745; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn color-preset" data-color="#007bff" style="background-color: #007bff; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn color-preset" data-color="#ffc107" style="background-color: #ffc107; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn color-preset" data-color="#dc3545" style="background-color: #dc3545; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn color-preset" data-color="#6f42c1" style="background-color: #6f42c1; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn color-preset" data-color="#fd7e14" style="background-color: #fd7e14; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn color-preset" data-color="#20c997" style="background-color: #20c997; width: 40px; height: 40px; border-radius: 50%;"></button>
                            <button type="button" class="btn color-preset" data-color="#17a2b8" style="background-color: #17a2b8; width: 40px; height: 40px; border-radius: 50%;"></button>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo BASE_URL; ?>categories" class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" data-loading>
                            <i class="fas fa-save me-2"></i>
                            Actualizar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorPreview = document.getElementById('colorPreview');
    const colorPresets = document.querySelectorAll('.color-preset');
    
    // Update preview when color changes
    function updateColorPreview() {
        const color = colorInput.value;
        colorPreview.style.backgroundColor = color;
        colorPreview.style.color = getContrastColor(color);
    }
    
    // Get contrast color for better text readability
    function getContrastColor(hexColor) {
        // Convert hex to RGB
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);
        
        // Calculate brightness
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        
        return brightness > 128 ? '#000000' : '#ffffff';
    }
    
    // Handle color input change
    colorInput.addEventListener('change', updateColorPreview);
    
    // Handle color preset clicks
    colorPresets.forEach(preset => {
        preset.addEventListener('click', function(e) {
            e.preventDefault();
            const color = this.dataset.color;
            colorInput.value = color;
            updateColorPreview();
        });
    });
    
    // Initialize preview
    updateColorPreview();
});
</script>