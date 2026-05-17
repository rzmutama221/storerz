<?php
/**
 * PaymentGateway - Abstract Payment Gateway Interface for RZDK Store
 * 
 * Provides a provider-agnostic interface for payment gateway integration.
 * Concrete implementations: TripayGateway, MidtransGateway, XenditGateway, DuitkuGateway
 * 
 * Usage:
 *   $gateway = PaymentGateway::create(); // Auto-selects based on settings
 *   $result = $gateway->createTransaction($orderId, $amount, $description);
 *   $isValid = $gateway->verifyCallback($payload, $signature);
 */

abstract class PaymentGateway
{
    protected string $apiKey = '';
    protected string $secretKey = '';
    protected string $environment = 'sandbox'; // sandbox or production
    protected string $callbackUrl = '';

    /**
     * Factory: Create gateway instance based on settings
     */
    public static function create(): ?self
    {
        $db = Model::getConnection();
        $settings = [];
        $rows = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('gateway_provider', 'gateway_api_key', 'gateway_secret_key', 'gateway_status')")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // Check if gateway is active
        if (($settings['gateway_status'] ?? 'inactive') !== 'active') {
            return null;
        }

        $provider = $settings['gateway_provider'] ?? '';
        $apiKey = $settings['gateway_api_key'] ?? '';
        $secretKey = $settings['gateway_secret_key'] ?? '';

        switch ($provider) {
            case 'tripay':
                require_once __DIR__ . '/TripayGateway.php';
                return new TripayGateway($apiKey, $secretKey);
            case 'midtrans':
                require_once __DIR__ . '/MidtransGateway.php';
                return new MidtransGateway($apiKey, $secretKey);
            case 'xendit':
                require_once __DIR__ . '/XenditGateway.php';
                return new XenditGateway($apiKey, $secretKey);
            case 'duitku':
                require_once __DIR__ . '/DuitkuGateway.php';
                return new DuitkuGateway($apiKey, $secretKey);
            default:
                return null;
        }
    }

    /**
     * Check if payment gateway is available and active
     */
    public static function isActive(): bool
    {
        try {
            $db = Model::getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'gateway_status' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['setting_value'] === 'active';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get current payment mode from settings
     */
    public static function getPaymentMode(): string
    {
        try {
            $db = Model::getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'payment_mode' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['setting_value'] : 'qris_manual';
        } catch (\Exception $e) {
            return 'qris_manual';
        }
    }

    /**
     * Create a payment transaction
     * Returns: ['success' => bool, 'payment_url' => string, 'reference' => string, 'error' => string]
     */
    abstract public function createTransaction(string $orderNumber, float $amount, string $description, array $customerData = []): array;

    /**
     * Verify callback/webhook signature
     * Returns true if signature is valid
     */
    abstract public function verifyCallback(array $payload, string $signature): bool;

    /**
     * Get payment status from gateway
     * Returns: ['status' => 'paid'|'pending'|'expired'|'failed', 'raw' => array]
     */
    abstract public function checkStatus(string $reference): array;

    /**
     * Get provider name
     */
    abstract public function getProviderName(): string;

    /**
     * Get available payment methods/channels
     */
    abstract public function getPaymentChannels(): array;

    /**
     * Set callback URL
     */
    public function setCallbackUrl(string $url): void
    {
        $this->callbackUrl = $url;
    }

    /**
     * Make HTTP request to gateway API
     */
    protected function httpRequest(string $url, string $method = 'GET', array $data = [], array $headers = []): array
    {
        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error, 'http_code' => 0];
        }

        $decoded = json_decode($response, true) ?? [];
        $decoded['http_code'] = $httpCode;
        $decoded['success'] = ($httpCode >= 200 && $httpCode < 300);

        return $decoded;
    }

    /**
     * Log gateway activity
     */
    protected function log(string $action, string $message): void
    {
        $logEntry = date('Y-m-d H:i:s') . " [{$this->getProviderName()}] {$action}: {$message}\n";
        file_put_contents(BASE_PATH . '/storage/logs/gateway.log', $logEntry, FILE_APPEND);
    }
}
