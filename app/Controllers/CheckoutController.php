<?php
namespace Controllers;

use Models\Cart;
use Models\Order;

/**
 * Checkout Controller
 * Handles the checkout process
 */
class CheckoutController extends Controller
{
    private Cart $cartModel;
    private Order $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
    }

    /**
     * Display checkout page
     */
    public function index(): void
    {
        $items = $this->cartModel->getItems();
        $total = $this->cartModel->getTotal();

        if (empty($items) || $total <= 0) {
            $this->redirect('/cart');
            return;
        }

        $cancelMessage = isset($_GET['cancelled']) ? 
            "Paiement annulé. Vous pouvez réessayer ou modifier votre commande." : '';

        $this->view('checkout.index', [
            'items' => $items,
            'total' => $total,
            'cancelMessage' => $cancelMessage
        ]);
    }

    /**
     * Process checkout
     */
    public function process(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checkout');
            return;
        }

        $this->validateCsrf();

        $items = $this->cartModel->getItems();
        $total = $this->cartModel->getTotal();

        if (empty($items) || $total <= 0) {
            $this->redirect('/cart');
            return;
        }

        // This would integrate with payment gateway
        // For now, we'll redirect to the existing create_checkout.php
        // In a full refactor, this logic would be moved here
        
        $this->redirect('/create_checkout.php');
    }

    /**
     * Checkout success page
     */
    public function success(): void
    {
        $sessionId = $this->get('session_id');
        
        if (!$sessionId) {
            $this->redirect('/');
            return;
        }

        // Get order by session ID
        $order = $this->orderModel->getByStripeSession($sessionId);
        
        $this->view('checkout.success', [
            'order' => $order,
            'sessionId' => $sessionId
        ]);
    }

    /**
     * Checkout cancel page
     */
    public function cancel(): void
    {
        $this->view('checkout.cancel');
    }
}
