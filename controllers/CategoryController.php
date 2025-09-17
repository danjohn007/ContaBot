<?php
/**
 * Category Controller
 * Sistema Básico Contable - ContaBot
 */

require_once 'BaseController.php';

class CategoryController extends BaseController {
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        requireLogin();
        $this->categoryModel = new Category($this->db);
    }
    
    /**
     * List categories
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $categories = $this->categoryModel->getCategoriesWithStats($userId);
        
        $data = [
            'title' => 'Categorías - ContaBot',
            'categories' => $categories,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('categories/index', $data);
    }
    
    /**
     * Show create category form
     */
    public function create() {
        $userId = $_SESSION['user_id'];
        
        if ($this->isPost()) {
            $name = sanitizeInput($this->post('name'));
            $description = sanitizeInput($this->post('description'));
            $color = $this->post('color', '#007bff');
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'El nombre de la categoría es requerido.';
            } elseif (strlen($name) > 255) {
                $errors[] = 'El nombre de la categoría es demasiado largo.';
            } elseif ($this->categoryModel->nameExists($userId, $name)) {
                $errors[] = 'Ya existe una categoría con ese nombre.';
            }
            
            // Color validation
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                $color = '#007bff'; // Default color
            }
            
            if (empty($errors)) {
                $categoryId = $this->categoryModel->create($userId, $name, $description, $color);
                
                if ($categoryId) {
                    $this->setFlash('success', 'Categoría creada exitosamente.');
                    
                    // If this is a popup request (from movement form), close and refresh parent
                    if ($this->get('popup')) {
                        echo "<script>
                            if (window.opener) {
                                window.opener.location.reload();
                                window.close();
                            } else {
                                window.location.href = '" . BASE_URL . "categories';
                            }
                        </script>";
                        exit();
                    }
                    
                    $this->redirect('categories');
                } else {
                    $errors[] = 'Error al crear la categoría. Intenta nuevamente.';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Nueva Categoría - ContaBot',
            'popup' => $this->get('popup', false),
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('categories/create', $data);
    }
    
    /**
     * Show edit category form
     */
    public function edit($id = null) {
        $userId = $_SESSION['user_id'];
        
        if (!$id) {
            $this->setFlash('error', 'ID de categoría requerido.');
            $this->redirect('categories');
        }
        
        $category = $this->categoryModel->getById($id, $userId);
        
        if (!$category) {
            $this->setFlash('error', 'Categoría no encontrada.');
            $this->redirect('categories');
        }
        
        if ($this->isPost()) {
            $name = sanitizeInput($this->post('name'));
            $description = sanitizeInput($this->post('description'));
            $color = $this->post('color', '#007bff');
            
            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'El nombre de la categoría es requerido.';
            } elseif (strlen($name) > 255) {
                $errors[] = 'El nombre de la categoría es demasiado largo.';
            } elseif ($this->categoryModel->nameExists($userId, $name, $id)) {
                $errors[] = 'Ya existe una categoría con ese nombre.';
            }
            
            // Color validation
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                $color = '#007bff'; // Default color
            }
            
            if (empty($errors)) {
                if ($this->categoryModel->update($id, $userId, $name, $description, $color)) {
                    $this->setFlash('success', 'Categoría actualizada exitosamente.');
                    $this->redirect('categories');
                } else {
                    $errors[] = 'Error al actualizar la categoría. Intenta nuevamente.';
                }
            }
            
            if (!empty($errors)) {
                $this->setFlash('error', implode('<br>', $errors));
            }
        }
        
        $data = [
            'title' => 'Editar Categoría - ContaBot',
            'category' => $category,
            'flash' => $this->getFlash()
        ];
        
        $this->viewWithLayout('categories/edit', $data);
    }
    
    /**
     * Delete category
     */
    public function delete($id = null) {
        $userId = $_SESSION['user_id'];
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de categoría requerido.'], 400);
            return;
        }
        
        $result = $this->categoryModel->delete($id, $userId);
        
        if ($this->get('ajax')) {
            $this->json($result);
        } else {
            $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
            $this->redirect('categories');
        }
    }
    
    /**
     * Create default categories for new users
     */
    public function createDefaults() {
        $userId = $_SESSION['user_id'];
        
        // Check if user already has categories
        $existingCategories = $this->categoryModel->getUserCategories($userId);
        
        if (empty($existingCategories)) {
            $this->categoryModel->createDefaultCategories($userId);
            $this->setFlash('success', 'Categorías por defecto creadas exitosamente.');
        } else {
            $this->setFlash('info', 'Ya tienes categorías creadas.');
        }
        
        $this->redirect('categories');
    }
    
    /**
     * Get categories as JSON (for AJAX requests)
     */
    public function api() {
        $userId = $_SESSION['user_id'];
        $categories = $this->categoryModel->getUserCategories($userId);
        
        $this->json([
            'success' => true,
            'categories' => $categories
        ]);
    }
    
    /**
     * Preview category color
     */
    public function preview() {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        $color = $this->post('color', '#007bff');
        
        // Validate color
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $color = '#007bff';
        }
        
        $this->json([
            'success' => true,
            'color' => $color,
            'preview' => [
                'background' => $color,
                'text' => $this->getContrastColor($color)
            ]
        ]);
    }
    
    /**
     * Get contrast color (black or white) for better readability
     */
    private function getContrastColor($hexColor) {
        // Remove # if present
        $hexColor = ltrim($hexColor, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Calculate brightness
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        // Return white for dark colors, black for light colors
        return $brightness > 128 ? '#000000' : '#ffffff';
    }
}
?>