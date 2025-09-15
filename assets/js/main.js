/**
 * Main JavaScript
 * ContaBot - Sistema Básico Contable
 */

// Global variables
const BASE_URL = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');

// Document ready
$(document).ready(function() {
    initializeApp();
});

/**
 * Initialize application
 */
function initializeApp() {
    initializeTooltips();
    initializePopovers();
    initializeFileUploads();
    initializeFormValidation();
    initializeDataTables();
    initializeDatePickers();
    initializeCharts();
    initializeAjaxSetup();
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Bootstrap popovers
 */
function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Initialize file upload functionality
 */
function initializeFileUploads() {
    $('.file-upload-zone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $('.file-upload-zone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $('.file-upload-zone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const input = $(this).find('input[type="file"]')[0];
            input.files = files;
            updateFileDisplay(input);
        }
    });
    
    $('input[type="file"]').on('change', function() {
        updateFileDisplay(this);
    });
}

/**
 * Update file display
 */
function updateFileDisplay(input) {
    const files = input.files;
    const display = $(input).closest('.file-upload-zone').find('.file-display');
    
    if (files.length > 0) {
        let html = '<div class="mt-2"><small class="text-muted">Archivos seleccionados:</small><ul class="list-unstyled mt-1">';
        for (let i = 0; i < files.length; i++) {
            html += `<li><i class="fas fa-file"></i> ${files[i].name} (${formatFileSize(files[i].size)})</li>`;
        }
        html += '</ul></div>';
        display.html(html);
    } else {
        display.empty();
    }
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Custom validation for forms with .needs-validation class
    $('.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Real-time validation
    $('.needs-validation input, .needs-validation select, .needs-validation textarea').on('blur change', function() {
        if ($(this).closest('form').hasClass('was-validated')) {
            this.checkValidity();
        }
    });
}

/**
 * Initialize DataTables
 */
function initializeDataTables() {
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-sm-12"t>><"row"<"col-sm-5"i><"col-sm-7"p>>',
            order: [[0, 'desc']] // Sort by first column descending by default
        });
    }
}

/**
 * Initialize date pickers
 */
function initializeDatePickers() {
    // Set default date format for date inputs
    $('input[type="date"]').each(function() {
        if (!$(this).val()) {
            $(this).val(new Date().toISOString().split('T')[0]);
        }
    });
}

/**
 * Initialize charts
 */
function initializeCharts() {
    // Chart.js default configuration
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.color = '#495057';
        Chart.defaults.plugins.legend.position = 'bottom';
    }
}

/**
 * Initialize AJAX setup
 */
function initializeAjaxSetup() {
    // CSRF token for AJAX requests
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                const token = $('meta[name=csrf-token]').attr('content') || 
                             $('input[name=csrf_token]').val();
                if (token) {
                    xhr.setRequestHeader("X-CSRF-TOKEN", token);
                }
            }
        }
    });
    
    // Global AJAX error handler
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        if (xhr.status === 401) {
            showAlert('Sesión expirada. Por favor, inicia sesión nuevamente.', 'warning');
            setTimeout(() => {
                window.location.href = BASE_URL + '/login';
            }, 2000);
        } else if (xhr.status === 403) {
            showAlert('No tienes permisos para realizar esta acción.', 'danger');
        } else if (xhr.status >= 500) {
            showAlert('Error del servidor. Por favor, intenta nuevamente.', 'danger');
        }
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Add to page
    if ($('.alert-container').length) {
        $('.alert-container').append(alertHtml);
    } else {
        $('main').prepend('<div class="alert-container">' + alertHtml + '</div>');
    }
    
    // Auto-dismiss
    if (duration > 0) {
        setTimeout(() => {
            $(`#${alertId}`).alert('close');
        }, duration);
    }
}

/**
 * Format money
 */
function formatMoney(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('es-MX');
}

/**
 * Confirm dialog
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Show loading spinner
 */
function showLoading(element) {
    const spinner = '<span class="spinner me-2"></span>';
    $(element).html(spinner + $(element).text()).prop('disabled', true);
}

/**
 * Hide loading spinner
 */
function hideLoading(element, originalText) {
    $(element).html(originalText).prop('disabled', false);
}

/**
 * Validate form before submit
 */
function validateForm(formElement) {
    const form = $(formElement);
    let isValid = true;
    
    // Clear previous errors
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();
    
    // Check required fields
    form.find('[required]').each(function() {
        if (!$(this).val().trim()) {
            $(this).addClass('is-invalid');
            $(this).after('<div class="invalid-feedback">Este campo es requerido.</div>');
            isValid = false;
        }
    });
    
    // Check email fields
    form.find('input[type="email"]').each(function() {
        const email = $(this).val().trim();
        if (email && !isValidEmail(email)) {
            $(this).addClass('is-invalid');
            $(this).after('<div class="invalid-feedback">Email inválido.</div>');
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate email
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Auto-resize textareas
 */
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// Add auto-resize to all textareas
$(document).on('input', 'textarea.auto-resize', function() {
    autoResizeTextarea(this);
});

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Copiado al portapapeles', 'success', 2000);
    }).catch(() => {
        showAlert('Error al copiar', 'danger', 2000);
    });
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableSelector, filename = 'export.csv') {
    const table = $(tableSelector);
    const rows = [];
    
    // Headers
    const headers = [];
    table.find('thead th').each(function() {
        headers.push($(this).text().trim());
    });
    rows.push(headers.join(','));
    
    // Data
    table.find('tbody tr').each(function() {
        const row = [];
        $(this).find('td').each(function() {
            row.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
        });
        rows.push(row.join(','));
    });
    
    // Download
    const csvContent = rows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

/**
 * Print page
 */
function printPage() {
    window.print();
}

/**
 * Generate random color
 */
function generateRandomColor() {
    const colors = [
        '#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8',
        '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6c757d'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}

/**
 * Debounce function
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Export functions for use in other files
window.ContaBot = {
    showAlert,
    formatMoney,
    formatDate,
    confirmAction,
    showLoading,
    hideLoading,
    validateForm,
    copyToClipboard,
    exportTableToCSV,
    printPage,
    generateRandomColor,
    debounce
};