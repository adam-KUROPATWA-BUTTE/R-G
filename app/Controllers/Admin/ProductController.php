<?php
namespace Controllers\Admin;

use Controllers\Controller;
use Models\Product;

/**
 * Admin Product Controller
 * Handles product management in admin panel
 */
class ProductController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->productModel = new Product();
    }

    /**
     * Display all products
     */
    public function index(): void
    {
        // Handle delete action
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? '';
            $id = (int)($_GET['id'] ?? 0);
            
            if ($action === 'delete' && $id > 0) {
                $this->validateCsrf();
                try {
                    $this->productModel->delete($id);
                    $this->redirect('/admin/products?msg=supprime');
                    return;
                } catch (\Exception $e) {
                    $error = 'Erreur lors de la suppression';
                }
            }
        }

        $products = $this->productModel->getAll();
        $msg = $_GET['msg'] ?? '';

        $this->view('admin.products.index', [
            'products' => $products,
            'msg' => $msg,
            'error' => $error ?? null
        ]);
    }

    /**
     * Show create product form
     */
    public function create(): void
    {
        $this->view('admin.products.create');
    }

    /**
     * Show edit product form
     */
    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        
        if ($id <= 0) {
            $this->redirect('/admin/products');
            return;
        }

        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $this->redirect('/admin/products');
            return;
        }

        $this->view('admin.products.edit', ['product' => $product]);
    }

    /**
     * Store new product
     */
    public function store(): void
    {
        $this->validateCsrf();
        
        // Handle product creation logic here
        // This would need full implementation based on product fields
        
        $this->redirect('/admin/products?msg=created');
    }

    /**
     * Update existing product
     */
    public function update(array $params): void
    {
        $this->validateCsrf();
        
        $id = (int)($params['id'] ?? 0);
        
        // Handle product update logic here
        // This would need full implementation based on product fields
        
        $this->redirect('/admin/products?msg=updated');
    }
}
