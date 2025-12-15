<?php
namespace Controllers;

use Models\Product;

/**
 * Product Controller
 * Handles product display and management
 */
class ProductController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
    }

    /**
     * Display all products
     */
    public function index(): void
    {
        $products = $this->productModel->getAll();
        $this->view('products.index', ['products' => $products]);
    }

    /**
     * Display single product
     */
    public function show(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(404);
            exit('Product not found');
        }

        $product = $this->productModel->getById($id);
        
        if (!$product) {
            http_response_code(404);
            exit('Product not found');
        }

        // Parse images
        $productImages = [];
        if (!empty($product['images'])) {
            $decoded = json_decode($product['images'], true);
            if (is_array($decoded)) {
                $productImages = $decoded;
            }
        }
        if (empty($productImages) && !empty($product['image'])) {
            $productImages = [$product['image']];
        }

        // Parse sizes
        $sizes = $this->productModel->parseSizes($product['sizes'] ?? '');

        $inStock = (int)($product['stock_quantity'] ?? 0) > 0;

        $this->view('products.show', [
            'product' => $product,
            'productImages' => $productImages,
            'sizes' => $sizes,
            'inStock' => $inStock
        ]);
    }

    /**
     * Display bijoux category
     */
    public function bijoux(): void
    {
        $products = $this->productModel->getAll('bijoux');
        $this->view('products.category', [
            'products' => $products,
            'category' => 'bijoux',
            'title' => 'Bijoux',
            'icon' => 'fa-gem',
            'description' => 'Pièces précieuses et uniques pour sublimer votre style'
        ]);
    }

    /**
     * Display women's clothing
     */
    public function vetementsFemme(): void
    {
        $products = $this->productModel->getAll('femme');
        $this->view('products.category', [
            'products' => $products,
            'category' => 'femme',
            'title' => 'Vêtements Femme',
            'icon' => 'fa-female',
            'description' => 'Élégance et sophistication pour la femme moderne'
        ]);
    }

    /**
     * Display men's clothing
     */
    public function vetementsHomme(): void
    {
        $products = $this->productModel->getAll('homme');
        $this->view('products.category', [
            'products' => $products,
            'category' => 'homme',
            'title' => 'Vêtements Homme',
            'icon' => 'fa-male',
            'description' => 'Style raffiné et sophistiqué'
        ]);
    }
}
