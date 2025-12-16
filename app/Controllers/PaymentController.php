<?php
namespace Controllers;

use Models\Order;

/**
 * Payment Controller
 * Handles payment success, failure, and related pages
 */
class PaymentController extends Controller
{
    private Order $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
    }

    /**
     * Payment success page
     */
    public function success(): void
    {
        $sessionId = $this->get('session_id');
        $orderId = $this->get('order_id');
        
        if (!$sessionId && !$orderId) {
            $this->redirect('/');
            return;
        }

        // Try to get order by session or ID
        $order = null;
        if ($sessionId) {
            $order = $this->orderModel->getByStripeSession($sessionId);
        } elseif ($orderId) {
            $order = $this->orderModel->getById((int)$orderId);
        }

        // Clear cart on success
        if (function_exists('cart_clear')) {
            cart_clear();
        }

        $this->view('payment.success', [
            'order' => $order,
            'sessionId' => $sessionId,
            'orderId' => $orderId
        ]);
    }

    /**
     * Payment failure page
     */
    public function failure(): void
    {
        $orderId = $this->get('order_id');
        $error = $this->get('error', 'Le paiement a échoué. Veuillez réessayer.');

        $order = null;
        if ($orderId) {
            $order = $this->orderModel->getById((int)$orderId);
        }

        $this->view('payment.failure', [
            'order' => $order,
            'orderId' => $orderId,
            'error' => $error
        ]);
    }

    /**
     * Payment cancel page (alias for checkout cancel)
     */
    public function cancel(): void
    {
        $this->redirect('/checkout/cancel');
    }
}
