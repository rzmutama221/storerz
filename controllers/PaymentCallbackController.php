<?php
/**
 * PaymentCallbackController - Handles payment gateway callbacks/webhooks
 * 
 * This controller receives callbacks from payment gateway providers
 * and auto-confirms payment for orders.
 * 
 * Route: POST /api/payment/callback
 */

class PaymentCallbackController extends Controller
{
    /**
     * Handle incoming payment callback
     */
    public function handle(): void
    {
        // Load gateway
        require_once BASE_PATH . '/core/gateway/PaymentGateway.php';
        $gateway = PaymentGateway::create();

        if (!$gateway) {
            $this->json(['status' => 'error', 'message' => 'Gateway not configured'], 500);
            return;
        }

        // Get raw payload
        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody, true) ?? [];

        // Get signature from header (different per provider)
        $signature = $this->getCallbackSignature($gateway->getProviderName());

        // Verify signature
        if (!$gateway->verifyCallback($payload, $signature)) {
            $this->logCallback('invalid_signature', $gateway->getProviderName(), $rawBody);
            $this->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            return;
        }

        // Extract order number based on provider
        $orderNumber = $this->extractOrderNumber($gateway->getProviderName(), $payload);

        if (!$orderNumber) {
            $this->logCallback('missing_order', $gateway->getProviderName(), $rawBody);
            $this->json(['status' => 'error', 'message' => 'Order not found in payload'], 400);
            return;
        }

        // Check payment status from gateway
        $reference = $this->extractReference($gateway->getProviderName(), $payload);
        $statusResult = $gateway->checkStatus($reference ?: $orderNumber);
        $paymentStatus = $statusResult['status'] ?? 'unknown';

        // Only process if status is 'paid'
        if ($paymentStatus !== 'paid') {
            $this->logCallback('status_not_paid', $gateway->getProviderName(), "Order: {$orderNumber}, Status: {$paymentStatus}");
            $this->json(['status' => 'ok', 'message' => "Status: {$paymentStatus}"], 200);
            return;
        }

        // Find and update order
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = :order_number AND status IN ('approved', 'awaiting_payment') LIMIT 1");
        $stmt->execute([':order_number' => $orderNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $this->logCallback('order_not_found', $gateway->getProviderName(), "Order: {$orderNumber}");
            $this->json(['status' => 'ok', 'message' => 'Order not in valid state'], 200);
            return;
        }

        // Auto-confirm payment
        $stmt = $db->prepare("UPDATE orders SET status = 'paid', payment_method = 'payment_gateway', paid_at = NOW(), updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $order['id']]);

        $this->logCallback('payment_confirmed', $gateway->getProviderName(), "Order: {$orderNumber}, Amount: {$order['final_price']}");

        // Log activity
        try {
            Helper::logActivity('gateway_payment_confirmed', "Payment auto-confirmed for {$orderNumber} via {$gateway->getProviderName()}");
        } catch (\Exception $e) {
            // Silently fail
        }

        $this->json(['status' => 'ok', 'message' => 'Payment confirmed'], 200);
    }

    /**
     * Get callback signature from request headers based on provider
     */
    private function getCallbackSignature(string $provider): string
    {
        return match ($provider) {
            'Tripay' => $_SERVER['HTTP_X_CALLBACK_SIGNATURE'] ?? '',
            'Midtrans' => $_POST['signature_key'] ?? ($_SERVER['HTTP_X_SIGNATURE'] ?? ''),
            'Xendit' => $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '',
            'Duitku' => $_POST['signature'] ?? '',
            default => '',
        };
    }

    /**
     * Extract order number from payload based on provider
     */
    private function extractOrderNumber(string $provider, array $payload): ?string
    {
        return match ($provider) {
            'Tripay' => $payload['merchant_ref'] ?? null,
            'Midtrans' => $payload['order_id'] ?? null,
            'Xendit' => $payload['external_id'] ?? null,
            'Duitku' => $payload['merchantOrderId'] ?? null,
            default => null,
        };
    }

    /**
     * Extract payment reference from payload
     */
    private function extractReference(string $provider, array $payload): ?string
    {
        return match ($provider) {
            'Tripay' => $payload['reference'] ?? null,
            'Midtrans' => $payload['transaction_id'] ?? ($payload['order_id'] ?? null),
            'Xendit' => $payload['id'] ?? null,
            'Duitku' => $payload['reference'] ?? null,
            default => null,
        };
    }

    /**
     * Log callback for debugging
     */
    private function logCallback(string $type, string $provider, string $data): void
    {
        $logEntry = date('Y-m-d H:i:s') . " [{$provider}] {$type}: {$data}\n";
        file_put_contents(BASE_PATH . '/storage/logs/gateway.log', $logEntry, FILE_APPEND);
    }
}
