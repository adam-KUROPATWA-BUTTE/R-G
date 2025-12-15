<?php
namespace Controllers;

use Models\Cart;

/**
 * Cart Controller
 * Handles shopping cart operations
 */
class CartController extends Controller
{
    private Cart $cartModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
    }

    /**
     * Display cart
     */
    public function index(): void
    {
        $items = $this->cartModel->getItems();
        $total = $this->cartModel->getTotal();
        $count = $this->cartModel->getCount();

        $this->view('cart.index', [
            'items' => $items,
            'total' => $total,
            'count' => $count
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $this->validateCsrf();

        $id = (int)($this->post('id') ?? 0);
        $qty = (int)($this->post('qty') ?? 1);
        $size = trim($this->post('size') ?? '');

        try {
            $this->cartModel->add($id, $qty, $size);
            $_SESSION['success'] = 'Produit ajouté au panier';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        // Redirect back or to cart
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer && strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
            header("Location: $referer");
        } else {
            $this->redirect('/cart');
        }
        exit;
    }

    /**
     * Update cart item quantity
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $this->validateCsrf();

        $id = (int)($this->post('id') ?? 0);
        $qty = (int)($this->post('qty') ?? 0);
        $itemIndex = $this->post('item_index');

        try {
            if ($itemIndex !== null) {
                $this->cartModel->updateByIndex((int)$itemIndex, $qty);
            } else {
                $size = trim($this->post('size') ?? '');
                $this->cartModel->update($id, $qty, $size);
            }
            $_SESSION['success'] = 'Panier mis à jour';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/cart');
    }

    /**
     * Remove item from cart
     */
    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $this->validateCsrf();

        $id = (int)($this->post('id') ?? 0);
        $index = $this->post('index');

        try {
            if ($index !== null) {
                $this->cartModel->removeByIndex((int)$index);
            } else {
                $size = trim($this->post('size') ?? '');
                $this->cartModel->remove($id, $size);
            }
            $_SESSION['success'] = 'Article retiré du panier';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/cart');
    }

    /**
     * Clear cart
     */
    public function clear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $this->validateCsrf();

        $this->cartModel->clear();
        $_SESSION['success'] = 'Panier vidé';

        $this->redirect('/cart');
    }
}
