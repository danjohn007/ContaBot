<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'ContaBot'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/app.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-0">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-calculator me-2"></i>
                            ContaBot
                        </h4>
                        <small class="text-white-50">Sistema Básico Contable</small>
                    </div>
                    
                    <ul class="nav flex-column px-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['REQUEST_URI']) === '' || basename($_SERVER['REQUEST_URI']) === 'dashboard' ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'superadmin'): ?>
                        <!-- SuperAdmin Menu -->
                        <li class="nav-item mt-3">
                            <div class="text-white-50 small text-uppercase px-3 mb-2">SuperAdmin</div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'superadmin') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>superadmin">
                                <i class="fas fa-crown me-2"></i>
                                Panel Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>superadmin/pending-users">
                                <i class="fas fa-user-clock me-2"></i>
                                Usuarios Pendientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>superadmin/financial">
                                <i class="fas fa-chart-line me-2"></i>
                                Dashboard Financiero
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>superadmin/users">
                                <i class="fas fa-users me-2"></i>
                                Gestionar Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>superadmin/payments">
                                <i class="fas fa-credit-card me-2"></i>
                                Registro de Pagos
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <div class="text-white-50 small text-uppercase px-3 mb-2">Usuario Regular</div>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'movements') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>movements">
                                <i class="fas fa-exchange-alt me-2"></i>
                                Movimientos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'categories') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>categories">
                                <i class="fas fa-tags me-2"></i>
                                Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'reports') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>reports">
                                <i class="fas fa-chart-pie me-2"></i>
                                Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'profile') !== false ? 'active' : ''; ?>" 
                               href="<?php echo BASE_URL; ?>profile">
                                <i class="fas fa-user me-2"></i>
                                Perfil
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>logout">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-auto p-3">
                        <div class="text-center">
                            <small class="text-white-50">
                                Usuario: <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'N/A'; ?><br>
                                Tipo: <?php echo isset($_SESSION['user_type']) ? ucfirst($_SESSION['user_type']) : 'N/A'; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="pt-3">
                    <!-- Flash messages -->
                    <?php if (isset($flash['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $flash['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($flash['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $flash['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($flash['warning'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $flash['warning']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($flash['info'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo $flash['info']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Content -->
                    <?php 
                    if (isset($content_view)) {
                        $viewFile = '../views/' . $content_view . '.php';
                        if (file_exists($viewFile)) {
                            include $viewFile;
                        } else {
                            echo "<div class='alert alert-danger'>View not found: " . $content_view . "</div>";
                        }
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Fallback Chart.js implementation for when CDN is blocked
        if (typeof Chart === 'undefined') {
            window.Chart = function(ctx, config) {
                const canvas = ctx.canvas || ctx;
                const container = canvas.parentElement;
                
                // Create fallback chart visualization
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
                `;
                
                if (config.type === 'line') {
                    this.createLineChart(chartDiv, config);
                } else if (config.type === 'doughnut') {
                    this.createDoughnutChart(chartDiv, config);
                }
                
                canvas.style.display = 'none';
                container.appendChild(chartDiv);
                
                return this;
            };
            
            Chart.prototype.createLineChart = function(container, config) {
                const data = config.data;
                const title = document.createElement('h6');
                title.textContent = 'Ingresos vs Gastos (Últimos 12 meses)';
                title.style.cssText = 'color: #495057; margin-bottom: 20px; font-weight: 600;';
                container.appendChild(title);
                
                if (data.labels && data.labels.length > 0) {
                    const chartArea = document.createElement('div');
                    chartArea.style.cssText = 'width: 90%; height: 300px; position: relative; background: white; border-radius: 6px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
                    
                    // Create legend
                    const legend = document.createElement('div');
                    legend.style.cssText = 'display: flex; justify-content: center; gap: 20px; margin-bottom: 15px;';
                    
                    data.datasets.forEach(dataset => {
                        const legendItem = document.createElement('div');
                        legendItem.style.cssText = 'display: flex; align-items: center; gap: 8px; font-size: 14px;';
                        
                        const colorBox = document.createElement('div');
                        colorBox.style.cssText = `width: 16px; height: 16px; background: ${dataset.borderColor}; border-radius: 2px;`;
                        
                        const label = document.createElement('span');
                        label.textContent = dataset.label;
                        label.style.fontWeight = '500';
                        
                        legendItem.appendChild(colorBox);
                        legendItem.appendChild(label);
                        legend.appendChild(legendItem);
                    });
                    
                    chartArea.appendChild(legend);
                    
                    // Create simple bar representation
                    const barsContainer = document.createElement('div');
                    barsContainer.style.cssText = 'display: flex; align-items: end; height: 200px; gap: 5px; padding: 0 10px;';
                    
                    data.labels.forEach((label, index) => {
                        const barGroup = document.createElement('div');
                        barGroup.style.cssText = 'flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px;';
                        
                        const barsWrapper = document.createElement('div');
                        barsWrapper.style.cssText = 'display: flex; gap: 2px; align-items: end; height: 160px;';
                        
                        data.datasets.forEach(dataset => {
                            const value = dataset.data[index] || 0;
                            const maxValue = Math.max(...data.datasets.flatMap(d => d.data));
                            const height = Math.max((value / maxValue) * 150, 5);
                            
                            const bar = document.createElement('div');
                            bar.style.cssText = `
                                width: 20px; 
                                height: ${height}px; 
                                background: ${dataset.borderColor}; 
                                border-radius: 2px 2px 0 0;
                                position: relative;
                                cursor: pointer;
                            `;
                            bar.title = `${dataset.label}: $${value.toLocaleString()}`;
                            
                            barsWrapper.appendChild(bar);
                        });
                        
                        const labelDiv = document.createElement('div');
                        labelDiv.textContent = label;
                        labelDiv.style.cssText = 'font-size: 11px; color: #6c757d; text-align: center; font-weight: 500;';
                        
                        barGroup.appendChild(barsWrapper);
                        barGroup.appendChild(labelDiv);
                        barsContainer.appendChild(barGroup);
                    });
                    
                    chartArea.appendChild(barsContainer);
                    container.appendChild(chartArea);
                } else {
                    const noData = document.createElement('div');
                    noData.textContent = 'No hay datos para mostrar';
                    noData.style.cssText = 'color: #6c757d; font-style: italic;';
                    container.appendChild(noData);
                }
            };
            
            Chart.prototype.createDoughnutChart = function(container, config) {
                const data = config.data;
                const title = document.createElement('h6');
                title.textContent = 'Gastos por Categoría (Mes Actual)';
                title.style.cssText = 'color: #495057; margin-bottom: 20px; font-weight: 600;';
                container.appendChild(title);
                
                if (data.labels && data.labels.length > 0) {
                    const chartArea = document.createElement('div');
                    chartArea.style.cssText = 'width: 90%; display: flex; flex-direction: column; align-items: center; background: white; border-radius: 6px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);';
                    
                    // Create legend
                    const legend = document.createElement('div');
                    legend.style.cssText = 'display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; width: 100%;';
                    
                    data.labels.forEach((label, index) => {
                        const value = data.datasets[0].data[index];
                        const color = data.datasets[0].colors ? data.datasets[0].colors[index] : '#007bff';
                        
                        const legendItem = document.createElement('div');
                        legendItem.style.cssText = 'display: flex; align-items: center; gap: 8px; font-size: 14px; padding: 8px; background: #f8f9fa; border-radius: 4px;';
                        
                        const colorBox = document.createElement('div');
                        colorBox.style.cssText = `width: 16px; height: 16px; background: ${color}; border-radius: 50%;`;
                        
                        const labelText = document.createElement('span');
                        labelText.textContent = label;
                        labelText.style.fontWeight = '500';
                        
                        const valueText = document.createElement('span');
                        valueText.textContent = `$${value.toLocaleString()}`;
                        valueText.style.cssText = 'margin-left: auto; color: #495057; font-weight: 600;';
                        
                        legendItem.appendChild(colorBox);
                        legendItem.appendChild(labelText);
                        legendItem.appendChild(valueText);
                        legend.appendChild(legendItem);
                    });
                    
                    chartArea.appendChild(legend);
                    container.appendChild(chartArea);
                } else {
                    const noData = document.createElement('div');
                    noData.textContent = 'No hay datos para mostrar';
                    noData.style.cssText = 'color: #6c757d; font-style: italic;';
                    container.appendChild(noData);
                }
            };
        }
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
</body>
</html>