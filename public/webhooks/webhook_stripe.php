<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../config/config_stripe.php';
require_once __DIR__ . '/../../vendor/stripe-php/init.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

error_log("=== STRIPE WEBHOOK RECEIVED ===");
error_log("Payload: " . $payload);

try {
    // ✅ Vérifier la signature du webhook
    if (STRIPE_WEBHOOK_SECRET) {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sig_header,
            STRIPE_WEBHOOK_SECRET
        );
    } else {
        // En mode dev sans webhook secret
        $event = json_decode($payload, false);
    }

    error_log("Event type: " . $event->type);

    // Gérer les événements
    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            
            $orderId = $session->metadata->order_id ?? null;
            $paymentStatus = $session->payment_status;

            error_log("Session completed - Order ID: $orderId - Payment status: $paymentStatus");

            if ($orderId && $paymentStatus === 'paid') {
                $pdo = db();
                $stmt = $pdo->prepare("
                    UPDATE commandes 
                    SET statut = 'paid', date_paiement = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$orderId]);
                
                error_log("✅ Order #$orderId marked as PAID");
            }
            break;

        case 'payment_intent.succeeded':
            error_log("Payment intent succeeded");
            break;

        case 'payment_intent.payment_failed':
            error_log("❌ Payment failed");
            break;

        default:
            error_log("Unhandled event type: " . $event->type);
    }

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (\Stripe\Exception\SignatureVerificationException $e) {
    error_log("❌ Webhook signature verification failed: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
} catch (Exception $e) {
    error_log("❌ Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>