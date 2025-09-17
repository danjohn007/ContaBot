/**
 * ContaBot Application JavaScript
 * Sistema Básico Contable
 */

// Global app object
const ContaBot = {
    
    // Initialize the application
    init: function() {
        this.setupEventListeners();
        this.initTooltips();
        this.initPopovers();
        this.initFileUploads();
        this.initFormValidation();
    },
    
    // Setup global event listeners
    setupEventListeners: function() {
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', this.toggleSidebar);
        }
        
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert-auto-hide').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
        
        // Confirm delete actions
        document.querySelectorAll('[data-confirm-delete]').forEach(element => {
            element.addEventListener('click', this.confirmDelete);
        });
        
        // Format currency inputs
        document.querySelectorAll('.currency-input').forEach(input => {
            input.addEventListener('input', this.formatCurrency);
        });
        
        // Handle form submissions with loading states
        document.querySelectorAll('form[data-loading]').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmission);
        });
    },
    
    // Initialize Bootstrap tooltips
    initTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },
    
    // Initialize Bootstrap popovers
    initPopovers: function() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    },
    
    // Initialize file upload functionality
    initFileUploads: function() {
        document.querySelectorAll('.file-upload-area').forEach(area => {
            const input = area.querySelector('input[type="file"]');
            
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });
            
            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });
            
            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                
                if (input) {
                    input.files = e.dataTransfer.files;
                    this.handleFileSelection(input);
                }
            });
            
            if (input) {
                input.addEventListener('change', () => {
                    this.handleFileSelection(input);
                });
            }
        });
    },
    
    // Initialize form validation
    initFormValidation: function() {
        // Bootstrap validation
        document.querySelectorAll('.needs-validation').forEach(form => {
            form.addEventListener('submit', (event) => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
        
        // Custom validation rules
        document.querySelectorAll('[data-validate]').forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });
    },
    
    // Toggle sidebar visibility
    toggleSidebar: function() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    },
    
    // Confirm delete actions
    confirmDelete: function(event) {
        const message = event.target.getAttribute('data-confirm-delete') || 
                       '¿Está seguro de que desea eliminar este elemento?';
        
        if (!confirm(message)) {
            event.preventDefault();
            return false;
        }
        return true;
    },
    
    // Format currency inputs
    formatCurrency: function(event) {
        let value = event.target.value;
        
        // Remove non-numeric characters except decimal point
        value = value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Limit to 2 decimal places
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        event.target.value = value;
    },
    
    // Handle form submissions with loading states
    handleFormSubmission: function(event) {
        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner"></span> Procesando...';
            submitBtn.disabled = true;
            
            // Re-enable after 10 seconds to prevent permanent lock
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        }
    },
    
    // Handle file selection
    handleFileSelection: function(input) {
        const files = input.files;
        const preview = input.parentElement.querySelector('.file-preview');
        
        if (preview) {
            preview.innerHTML = '';
            
            Array.from(files).forEach(file => {
                const fileInfo = document.createElement('div');
                fileInfo.className = 'alert alert-info';
                fileInfo.innerHTML = `
                    <i class="fas fa-file me-2"></i>
                    <strong>${file.name}</strong>
                    <small class="text-muted">(${this.formatFileSize(file.size)})</small>
                `;
                preview.appendChild(fileInfo);
            });
        }
    },
    
    // Validate individual form fields
    validateField: function(field) {
        const validationType = field.getAttribute('data-validate');
        let isValid = true;
        let message = '';
        
        switch (validationType) {
            case 'rfc':
                const rfcPattern = /^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
                isValid = !field.value || rfcPattern.test(field.value);
                message = 'RFC no válido';
                break;
                
            case 'amount':
                isValid = field.value > 0;
                message = 'El monto debe ser mayor a 0';
                break;
                
            case 'email':
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                isValid = emailPattern.test(field.value);
                message = 'Email no válido';
                break;
        }
        
        this.showFieldValidation(field, isValid, message);
    },
    
    // Show field validation feedback
    showFieldValidation: function(field, isValid, message) {
        const feedback = field.parentElement.querySelector('.invalid-feedback');
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            
            if (feedback) {
                feedback.textContent = message;
            }
        }
    },
    
    // Format file size
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    // Show notification
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    },
    
    // AJAX helper
    ajax: function(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const config = Object.assign(defaults, options);
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                this.showNotification('Error de conexión', 'danger');
                throw error;
            });
    }
};

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    ContaBot.init();
});

// Make ContaBot globally available
window.ContaBot = ContaBot;