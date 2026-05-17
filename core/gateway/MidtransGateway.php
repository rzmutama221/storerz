<?php
/**
 * MidtransGateway - Midtrans Payment Gateway Implementation (Snap)
 * 
 * Docs: https://docs.midtrans.com
 */

class MidtransGateway extends PaymentGateway
{
    private string $baseUrl = '';

    public function __construct(string $apiKey, string $secretKey, string $environment = 'sandbox')
    {
        $this->apiKey = $apiKey; // Server Key
        $this->secretKey = $secretKey; // Client Key (for Snap)
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production'
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    public function getProviderName(): string
    {
        return 'Midtrans';
    }

    public function createTransaction(string $orderNumber, float $amount, string $description, array $customerData = []): array
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $orderNumber,
                'gross_amount' => (int) $amount,
            ],
            'item_details' => [
                [
                    'id' => $orderNumber,
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => substr($description, 0, 50),
                ],
            ],
            'customer_details' => [
                'first_name' => $customerData['name'] ?? 'Customer',
                'email' => $customerData['email'] ?? '',
                'phone' => $customerData['phone'] ?? '',
            ],
            'callbacks' => [
                'finish' => Helper::baseUrl() . '/dashboard/orders',
            ],
        ];

        $authKey = base64_encode($this->apiKey . ':');

        $result = $this->httpRequest($this->baseUrl . '/transactions', 'POST', $payload, [
            'Authorization: Basic ' . $authKey,
        ]);

        if (empty($result['token'])) {
            $this->log('create_failed', "Order: {$orderNumber}, Error: " . json_encode($result));
            return ['success' => false, 'payment_url' => '', 'reference' => '', 'error' => $result['error_messages'][0] ?? 'Midtrans error'];
        }

        $this->log('transaction_created', "Order: {$orderNumber}, Token: {$result['token']}");

        return [
            'success' => true,
            'payment_url' => $result['redirect_url'] ?? '',
            'reference' => $result['token'],
            'error' => '',
        ];
    }

    public function verifyCallback(array $payload, string $signature): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->apiKey);
        return hash_equals($expected, $signature);
    }

    public function checkStatus(string $reference): array
    {
        $url = str_replace('/snap/v1', '/v2', $this->baseUrl) . "/{$reference}/status";
        $authKey = base64_encode($this->apiKey . ':');

        $result = $this->httpRequest($url, 'GET', [], [
            'Authorization: Basic ' . $authKey,
        ]);

        $statusMap = [
            'capture' => 'paid', 'settlement' => 'paid',
            'pending' => 'pending', 'expire' => 'expired',
            'deny' => 'failed', 'cancel' => 'failed',
        ];

        $txStatus = $result['transaction_status'] ?? 'unknown';
        return ['status' => $statusMap[$txStatus] ?? 'unknown', 'raw' => $result];
    }

    public function getPaymentChannels(): array
    {
        return [
            ['code' => 'qris', 'name' => 'QRIS', 'group' => 'E-Wallet'],
            ['code' => 'gopay', 'name' => 'GoPay', 'group' => 'E-Wallet'],
            ['code' => 'shopeepay', 'name' => 'ShopeePay', 'group' => 'E-Wallet'],
            ['code' => 'bca_va', 'name' => 'BCA Virtual Account', 'group' => 'Bank Transfer'],
            ['code' => 'bni_va', 'name' => 'BNI Virtual Account', 'group' => 'Bank Transfer'],
            ['code' => 'bri_va', 'name' => 'BRI Virtual Account', 'group' => 'Bank Transfer'],
        ];
    }
}
