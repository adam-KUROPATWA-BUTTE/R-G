<?php
namespace Controllers\Api;

use Controllers\Controller;

/**
 * Stripe API Controller
 * Handles Stripe payment integration
 */
class StripeController extends Controller
{
    /**
     * Create Stripe checkout session
     * This method handles the logic from api/create_stripe_session.php
     */
    public function createSession(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // The actual implementation would be migrated from api/create_stripe_session.php
            // For now, we delegate to the existing file for compatibility
            require_once __DIR__ . '/../../../api/create_stripe_session.php';
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Handle Stripe webhook
     * This method handles the logic from api/webhook_stripe.php
     */
    public function webhook(): void
    {
        try {
            // The actual implementation would be migrated from api/webhook_stripe.php
            // For now, we delegate to the existing file for compatibility
            require_once __DIR__ . '/../../../api/webhook_stripe.php';
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            error_log('Stripe webhook error: ' . $e->getMessage());
            exit;
        }
    }
}
