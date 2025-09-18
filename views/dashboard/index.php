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
                <h6 class="m-0 font-weight-bold">Ingresos vs Gastos (Últimos 12 meses)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="monthlyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Expenses Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold">Gastos por Categoría (Mes Actual)</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($category_data['labels'])): ?>
                <div class="chart-pie pt-4">
                    <canvas id="categoryChart" width="400" height="300"></canvas>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No hay gastos este mes</h6>
                    <p class="text-muted small">Los gastos aparecerán aquí cuando registres movimientos</p>
                </div>
                <?php endif; ?>
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
// Chart initialization with fallback
function initializeCharts() {
    // Monthly Chart
    const monthlyData = <?php echo json_encode($monthly_data); ?>;
    
    try {
        if (typeof Chart !== 'undefined') {
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
        } else {
            // Fallback when Chart.js is not available
            createFallbackLineChart('monthlyChart', monthlyData);
        }
    } catch (error) {
        console.log('Chart.js fallback for monthly chart');
        createFallbackLineChart('monthlyChart', monthlyData);
    }
    
    // Category Chart
    const categoryData = <?php echo json_encode($category_data); ?>;
    
    if (categoryData.labels && categoryData.labels.length > 0) {
        try {
            if (typeof Chart !== 'undefined') {
                const ctxCategory = document.getElementById('categoryChart').getContext('2d');
                const categoryChart = new Chart(ctxCategory, {
                    type: 'doughnut',
                    data: {
                        labels: categoryData.labels,
                        datasets: [{
                            data: categoryData.data,
                            colors: categoryData.colors,
                            backgroundColor: categoryData.colors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': $' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                createFallbackDoughnutChart('categoryChart', categoryData);
            }
        } catch (error) {
            console.log('Chart.js fallback for category chart');
            createFallbackDoughnutChart('categoryChart', categoryData);
        }
    }
}

// Fallback chart functions
function createFallbackLineChart(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    const container = canvas.parentElement;
    
    const chartDiv = document.createElement('div');
    chartDiv.className = 'chart-fallback';
    chartDiv.style.cssText = `
        width: 100%; 
        height: 400px; 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        border: 2px solid #dee2e6;
        font-family: 'Segoe UI', sans-serif;
        padding: 20px;
    `;
    
    if (data.labels && data.labels.length > 0) {
        // Title
        const title = document.createElement('h6');
        title.textContent = 'Ingresos vs Gastos (Últimos 12 meses)';
        title.style.cssText = 'color: #495057; margin-bottom: 20px; font-weight: 600; text-align: center;';
        chartDiv.appendChild(title);
        
        // Chart area
        const chartArea = document.createElement('div');
        chartArea.style.cssText = 'width: 100%; background: white; border-radius: 6px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
        
        // Legend
        const legend = document.createElement('div');
        legend.style.cssText = 'display: flex; justify-content: center; gap: 20px; margin-bottom: 15px;';
        
        const datasets = [
            { label: 'Ingresos', data: data.income, color: '#28a745' },
            { label: 'Gastos', data: data.expenses, color: '#dc3545' }
        ];
        
        datasets.forEach(dataset => {
            const legendItem = document.createElement('div');
            legendItem.style.cssText = 'display: flex; align-items: center; gap: 8px; font-size: 14px;';
            
            const colorBox = document.createElement('div');
            colorBox.style.cssText = `width: 16px; height: 16px; background: ${dataset.color}; border-radius: 2px;`;
            
            const label = document.createElement('span');
            label.textContent = dataset.label;
            label.style.fontWeight = '500';
            
            legendItem.appendChild(colorBox);
            legendItem.appendChild(label);
            legend.appendChild(legendItem);
        });
        
        chartArea.appendChild(legend);
        
        // Simple bar representation
        const barsContainer = document.createElement('div');
        barsContainer.style.cssText = 'display: flex; align-items: end; height: 200px; gap: 3px; padding: 0 10px; overflow-x: auto;';
        
        const maxValue = Math.max(...datasets.flatMap(d => d.data));
        
        data.labels.forEach((label, index) => {
            const barGroup = document.createElement('div');
            barGroup.style.cssText = 'min-width: 60px; display: flex; flex-direction: column; align-items: center; gap: 5px;';
            
            const barsWrapper = document.createElement('div');
            barsWrapper.style.cssText = 'display: flex; gap: 2px; align-items: end; height: 160px;';
            
            datasets.forEach(dataset => {
                const value = dataset.data[index] || 0;
                const height = maxValue > 0 ? Math.max((value / maxValue) * 150, 3) : 3;
                
                const bar = document.createElement('div');
                bar.style.cssText = `
                    width: 18px; 
                    height: ${height}px; 
                    background: ${dataset.color}; 
                    border-radius: 2px 2px 0 0;
                    position: relative;
                    cursor: pointer;
                `;
                bar.title = `${dataset.label}: $${value.toLocaleString()}`;
                
                barsWrapper.appendChild(bar);
            });
            
            const labelDiv = document.createElement('div');
            labelDiv.textContent = label;
            labelDiv.style.cssText = 'font-size: 10px; color: #6c757d; text-align: center; font-weight: 500; max-width: 60px; word-wrap: break-word;';
            
            barGroup.appendChild(barsWrapper);
            barGroup.appendChild(labelDiv);
            barsContainer.appendChild(barGroup);
        });
        
        chartArea.appendChild(barsContainer);
        chartDiv.appendChild(chartArea);
    } else {
        const noData = document.createElement('div');
        noData.textContent = 'No hay datos para mostrar';
        noData.style.cssText = 'color: #6c757d; font-style: italic;';
        chartDiv.appendChild(noData);
    }
    
    canvas.style.display = 'none';
    container.appendChild(chartDiv);
}

function createFallbackDoughnutChart(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    const container = canvas.parentElement;
    
    const chartDiv = document.createElement('div');
    chartDiv.className = 'chart-fallback';
    chartDiv.style.cssText = `
        width: 100%; 
        height: 400px; 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        border: 2px solid #dee2e6;
        font-family: 'Segoe UI', sans-serif;
        padding: 20px;
    `;
    
    if (data.labels && data.labels.length > 0) {
        // Title
        const title = document.createElement('h6');
        title.textContent = 'Gastos por Categoría (Mes Actual)';
        title.style.cssText = 'color: #495057; margin-bottom: 20px; font-weight: 600; text-align: center;';
        chartDiv.appendChild(title);
        
        // Chart area
        const chartArea = document.createElement('div');
        chartArea.style.cssText = 'width: 100%; background: white; border-radius: 6px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
        
        // Legend as list
        const legend = document.createElement('div');
        legend.style.cssText = 'display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; width: 100%;';
        
        data.labels.forEach((label, index) => {
            const value = data.data[index];
            const color = data.colors ? data.colors[index] : '#007bff';
            
            const legendItem = document.createElement('div');
            legendItem.style.cssText = 'display: flex; align-items: center; gap: 12px; font-size: 14px; padding: 12px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid ' + color + ';';
            
            const colorBox = document.createElement('div');
            colorBox.style.cssText = `width: 20px; height: 20px; background: ${color}; border-radius: 50%; flex-shrink: 0;`;
            
            const labelText = document.createElement('span');
            labelText.textContent = label;
            labelText.style.cssText = 'font-weight: 500; flex-grow: 1;';
            
            const valueText = document.createElement('span');
            valueText.textContent = `$${value.toLocaleString()}`;
            valueText.style.cssText = 'color: #495057; font-weight: 600; font-size: 16px;';
            
            legendItem.appendChild(colorBox);
            legendItem.appendChild(labelText);
            legendItem.appendChild(valueText);
            legend.appendChild(legendItem);
        });
        
        chartArea.appendChild(legend);
        chartDiv.appendChild(chartArea);
    } else {
        const noData = document.createElement('div');
        noData.textContent = 'No hay datos para mostrar';
        noData.style.cssText = 'color: #6c757d; font-style: italic;';
        chartDiv.appendChild(noData);
    }
    
    canvas.style.display = 'none';
    container.appendChild(chartDiv);
}

// Initialize charts when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCharts);
} else {
    initializeCharts();
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
.chart-area {
    position: relative;
    height: 300px;
}
.chart-fallback {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>