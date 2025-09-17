-- Sample Data for ContaBot Sistema Básico Contable
USE ejercito_contabot;

-- Sample users (password is 'password123' hashed)
INSERT INTO users (email, password, name, rfc, user_type) VALUES
('admin@contabot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Sistema', 'XAXX010101000', 'business'),
('usuario@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez García', 'PEGJ850315AB2', 'personal'),
('negocio@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González López', 'GOLM750820CD3', 'business');

-- Sample categories for user 1 (admin)
INSERT INTO categories (user_id, name, description, color) VALUES
(1, 'Alimentación', 'Gastos en comida y bebidas', '#28a745'),
(1, 'Transporte', 'Gastos de transporte y combustible', '#007bff'),
(1, 'Servicios', 'Servicios públicos y suscripciones', '#ffc107'),
(1, 'Salud', 'Gastos médicos y medicamentos', '#dc3545'),
(1, 'Educación', 'Gastos educativos y capacitación', '#6f42c1'),
(1, 'Entretenimiento', 'Gastos de ocio y entretenimiento', '#fd7e14'),
(1, 'Oficina', 'Material de oficina y suministros', '#20c997'),
(1, 'Ventas', 'Ingresos por ventas', '#17a2b8');

-- Sample categories for user 2 (personal)
INSERT INTO categories (user_id, name, description, color) VALUES
(2, 'Alimentación', 'Gastos en comida y bebidas', '#28a745'),
(2, 'Transporte', 'Gastos de transporte', '#007bff'),
(2, 'Vivienda', 'Renta y gastos del hogar', '#6c757d'),
(2, 'Salario', 'Ingresos por trabajo', '#17a2b8');

-- Sample movements for demonstration
INSERT INTO movements (user_id, category_id, type, amount, concept, description, movement_date, classification, payment_method, is_billed) VALUES
-- Income examples
(1, 8, 'income', 15000.00, 'Venta de productos', 'Venta mensual de productos', '2024-01-15', 'business', 'transfer', TRUE),
(2, 10, 'income', 12000.00, 'Salario enero', 'Salario del mes de enero', '2024-01-31', 'personal', 'transfer', FALSE),

-- Expense examples
(1, 1, 'expense', 500.00, 'Comida oficina', 'Gastos de alimentación para el equipo', '2024-01-10', 'business', 'cash', TRUE),
(1, 2, 'expense', 800.00, 'Gasolina', 'Combustible para vehículo de empresa', '2024-01-12', 'fiscal', 'card', TRUE),
(1, 3, 'expense', 1200.00, 'Internet y teléfono', 'Servicios de comunicación mensual', '2024-01-05', 'fiscal', 'transfer', TRUE),
(1, 4, 'expense', 2500.00, 'Consulta médica', 'Revisión médica general', '2024-01-20', 'personal', 'cash', FALSE),
(1, 7, 'expense', 350.00, 'Papel y tinta', 'Material de oficina', '2024-01-18', 'business', 'card', TRUE),

(2, 9, 'expense', 300.00, 'Supermercado', 'Compras de la semana', '2024-01-08', 'personal', 'card', FALSE),
(2, 10, 'expense', 150.00, 'Transporte público', 'Transporte del mes', '2024-01-25', 'personal', 'cash', FALSE),
(2, 11, 'expense', 4500.00, 'Renta', 'Renta mensual del departamento', '2024-01-01', 'personal', 'transfer', FALSE);

-- Additional movements for current month (to show in reports)
INSERT INTO movements (user_id, category_id, type, amount, concept, description, movement_date, classification, payment_method, is_billed) VALUES
(1, 8, 'income', 18000.00, 'Venta febrero', 'Venta mensual de febrero', CURDATE(), 'business', 'transfer', TRUE),
(1, 1, 'expense', 650.00, 'Comida equipo', 'Gastos alimentación equipo febrero', CURDATE(), 'business', 'card', FALSE),
(2, 9, 'expense', 280.00, 'Supermercado', 'Compras de la semana', CURDATE(), 'personal', 'card', FALSE);