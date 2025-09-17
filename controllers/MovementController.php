<?php
/**
 * Movement Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class MovementController extends BaseController {
    private $movementModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        requireLogin();
        $this->movementModel = new Movement($this->db);
        $this->categoryModel = new Category($this->db);
    }
    
    /**
     * List movements with pagination and filters
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $page = max(1, (int)$this->get('page', 1));
        $limit = 20;
        
        // Get filters
        $filters = [
            'date_from' => $this->get('date_from'),
            'date_to' => $this->get('date_to'),
            'type' => $this->get('type'),
            'category_id' => $this->get('category_id'),
            'classification' => $this->get('classification'),
            'search' => $this->get('search')
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return !empty($value);
        });
        
        // Get movements and total count
        $movements = $this->movementModel->getUserMovements($userId, $filters, $page, $limit);
        $totalCount = $this->movementModel->getUserMovementsCount($userId, $filters);
        $totalPages = ceil($totalCount / $limit);
        
        // Get categories for filter dropdown
        $categories = $this->categoryModel->getUserCategories($userId);
        
        $data = [
            'title' => 'Movimientos - ContaBot',
            'movements' => $movements,
            'categories' => $categories,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_count' => $totalCount,
                'limit' => $limit
            ],
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('movements/index', $data);
    }
    
    /**
     * Show create movement form
     */
    public function create() {
        $userId = $_SESSION['user_id'];
        $categories = $this->categoryModel->getUserCategories($userId);
        
        if (empty($categories)) {
            $this->setFlash('warning', 'Primero debes crear al menos una categoría.');
            $this->redirect('categories/create');
        }
        
        if ($this->isPost()) {
            $data = [
                'user_id' => $userId,
                'category_id' => $this->post('category_id'),
                'type' => $this->post('type'),
                'amount' => (float)$this->post('amount'),
                'concept' => sanitizeInput($this->post('concept')),
                'description' => sanitizeInput($this->post('description')),
                'movement_date' => $this->post('movement_date'),
                'classification' => $this->post('classification'),
                'payment_method' => $this->post('payment_method'),
                'is_billed' => $this->post('is_billed') === 'on'
            ];
            
            // Validation
            $errors = $this->validateMovementData($data, $categories);
            
            if (empty($errors)) {
                $movementId = $this->movementModel->create($data);
                
                if ($movementId) {
                    // Handle file upload if present
                    if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = $this->movementModel->uploadReceiptFile($_FILES['receipt_file'], $movementId);
                        
                        if ($uploadResult['success']) {
                            // Update movement with filename
                            $updateData = $data;
                            $updateData['receipt_file'] = $uploadResult['filename'];
                            $this->movementModel->update($movementId, $userId, $updateData);
                        } else {
                            $this->setFlash('warning', 'Movimiento creado pero no se pudo subir el archivo: ' . $uploadResult['message']);
                        }
                    }
                    
                    $this->setFlash('success', 'Movimiento registrado exitosamente.');
                    $this->redirect('movements');
                } else {
                    $errors[] = 'Error al crear el movimiento. Intenta nuevamente.';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Nuevo Movimiento - ContaBot',
            'categories' => $categories,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('movements/create', $data);
    }
    
    /**
     * Show edit movement form
     */
    public function edit($id = null) {
        $userId = $_SESSION['user_id'];
        
        if (!$id) {
            $this->setFlash('error', 'ID de movimiento requerido.');
            $this->redirect('movements');
        }
        
        $movement = $this->movementModel->getById($id, $userId);
        
        if (!$movement) {
            $this->setFlash('error', 'Movimiento no encontrado.');
            $this->redirect('movements');
        }
        
        $categories = $this->categoryModel->getUserCategories($userId);
        
        if ($this->isPost()) {
            $data = [
                'category_id' => $this->post('category_id'),
                'type' => $this->post('type'),
                'amount' => (float)$this->post('amount'),
                'concept' => sanitizeInput($this->post('concept')),
                'description' => sanitizeInput($this->post('description')),
                'movement_date' => $this->post('movement_date'),
                'classification' => $this->post('classification'),
                'payment_method' => $this->post('payment_method'),
                'is_billed' => $this->post('is_billed') === 'on',
                'receipt_file' => $movement['receipt_file'] // Keep existing file
            ];
            
            // Validation
            $errors = $this->validateMovementData($data, $categories);
            
            if (empty($errors)) {
                // Handle file upload if present
                if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->movementModel->uploadReceiptFile($_FILES['receipt_file'], $id);
                    
                    if ($uploadResult['success']) {
                        // Delete old file if exists
                        if ($movement['receipt_file'] && file_exists(UPLOAD_PATH . $movement['receipt_file'])) {
                            unlink(UPLOAD_PATH . $movement['receipt_file']);
                        }
                        $data['receipt_file'] = $uploadResult['filename'];
                    } else {
                        $this->setFlash('warning', 'Error al subir el archivo: ' . $uploadResult['message']);
                    }
                }
                
                if ($this->movementModel->update($id, $userId, $data)) {
                    $this->setFlash('success', 'Movimiento actualizado exitosamente.');
                    $this->redirect('movements');
                } else {
                    $errors[] = 'Error al actualizar el movimiento. Intenta nuevamente.';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Editar Movimiento - ContaBot',
            'movement' => $movement,
            'categories' => $categories,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('movements/edit', $data);
    }
    
    /**
     * Delete movement
     */
    public function delete($id = null) {
        $userId = $_SESSION['user_id'];
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de movimiento requerido.'], 400);
            return;
        }
        
        $result = $this->movementModel->delete($id, $userId);
        
        if ($this->get('ajax')) {
            $this->json($result);
        } else {
            $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $this->redirect('movements');
        }
    }
    
    /**
     * Toggle billing status
     */
    public function toggleBilling($id = null) {
        $userId = $_SESSION['user_id'];
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de movimiento requerido.'], 400);
            return;
        }
        
        $movement = $this->movementModel->getById($id, $userId);
        
        if (!$movement) {
            $this->json(['success' => false, 'message' => 'Movimiento no encontrado.'], 404);
            return;
        }
        
        $newStatus = !$movement['is_billed'];
        
        if ($this->movementModel->updateBillingStatus($id, $userId, $newStatus)) {
            $message = $newStatus ? 'Marcado como facturado' : 'Marcado como pendiente';
            $this->json(['success' => true, 'message' => $message, 'is_billed' => $newStatus]);
        } else {
            $this->json(['success' => false, 'message' => 'Error al actualizar el estado.'], 500);
        }
    }
    
    /**
     * Download receipt file
     */
    public function download($id = null) {
        $userId = $_SESSION['user_id'];
        
        if (!$id) {
            $this->setFlash('error', 'ID de movimiento requerido.');
            $this->redirect('movements');
        }
        
        $movement = $this->movementModel->getById($id, $userId);
        
        if (!$movement || !$movement['receipt_file']) {
            $this->setFlash('error', 'Archivo no encontrado.');
            $this->redirect('movements');
        }
        
        $filepath = UPLOAD_PATH . $movement['receipt_file'];
        
        if (!file_exists($filepath)) {
            $this->setFlash('error', 'Archivo no encontrado en el servidor.');
            $this->redirect('movements');
        }
        
        // Set headers for file download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($movement['receipt_file']) . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        exit();
    }
    
    /**
     * Validate movement data
     */
    private function validateMovementData($data, $categories) {
        $errors = [];
        
        // Category validation
        if (empty($data['category_id'])) {
            $errors[] = 'La categoría es requerida.';
        } else {
            $categoryExists = false;
            foreach ($categories as $category) {
                if ($category['id'] == $data['category_id']) {
                    $categoryExists = true;
                    break;
                }
            }
            if (!$categoryExists) {
                $errors[] = 'La categoría seleccionada no es válida.';
            }
        }
        
        // Type validation
        if (!in_array($data['type'], ['income', 'expense'])) {
            $errors[] = 'El tipo de movimiento no es válido.';
        }
        
        // Amount validation
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'El monto debe ser mayor a 0.';
        }
        
        // Concept validation
        if (empty($data['concept'])) {
            $errors[] = 'El concepto es requerido.';
        }
        
        // Date validation
        if (empty($data['movement_date'])) {
            $errors[] = 'La fecha es requerida.';
        } elseif (!strtotime($data['movement_date'])) {
            $errors[] = 'La fecha no es válida.';
        }
        
        // Classification validation
        if (!in_array($data['classification'], ['personal', 'business', 'fiscal', 'non_fiscal'])) {
            $errors[] = 'La clasificación no es válida.';
        }
        
        // Payment method validation
        if (!in_array($data['payment_method'], ['cash', 'card', 'transfer', 'check', 'other'])) {
            $errors[] = 'El método de pago no es válido.';
        }
        
        return $errors;
    }
}
?>