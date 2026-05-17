<?php
/**
 * XenditGateway - Xendit Payment Gateway Implementation
 * 
 * Docs: https://developers.xendit.co
 */

class XenditGateway extends PaymentGateway
{
    private string $baseUrl = 'https://api.xendit.co';

    public function __construct(string $apiKey, string $secretKey, string $environment = 'sandbox')
    {
        $this->apiKey = $apiKey; // Secret API Key
        $this->secretKey = $secretKey; // Callback verification token
        $this->environment = $environment;
    }

    public function getProviderName(): string
    {
        return 'Xendit';
    }

    public function createTransaction(string $orderNumber, float $amount, string $description, array $customerData = []): array
    {
        $payload = [
            'external_id' => $orderNumber,
            'amount' => (int) $amount,
            'description' => $description,
            'invoice_duration' => 7200, // 2 hours
            'customer' => [
                'given_names' => $customerData['name'] ?? 'Customer',
                'email' => $customerData['email'] ?? '',
            ],
            'success_redirect_url' => Helper::baseUrl() . '/dashboard/orders',
            'failure_redirect_url' => Helper::baseUrl() . '/dashboard/orders',
            'currency' => 'IDR',
        ];

        $authKey = base64_encode($this->apiKey . ':');

        $result = $this->httpRequest($this->baseUrl . '/v2/invoices', 'POST', $payload, [
            'Authorization: Basic ' . $authKey,
        ]);

        if (empty($result['id'])) {
            $this->log('create_failed', "Order: {$orderNumber}, Error: " . json_encode($result));
            return ['success' => false, 'payment_url' => '', 'reference' => '', 'error' => $result['message'] ?? 'Xendit error'];
        }

        $this->log('transaction_created', "Order: {$orderNumber}, Invoice: {$result['id']}");

        return [
            'success' => true,
            'payment_url' => $result['invoice_url'] ?? '',
            'reference' => $result['id'],
            'error' => '',
        ];
    }

    public function verifyCallback(array $payload, string $signature): bool
    {
        // Xendit uses x-callback-token header
        return hash_equals($this->secretKey, $signature);
    }

    public function checkStatus(string $reference): array
    {
        $authKey = base64_encode($this->apiKey . ':');
        $result = $this->httpRequest($this->baseUrl . '/v2/invoices/' . $reference, 'GET', [], [
            'Authorization: Basic ' . $authKey,
        ]);

        $statusMap = ['PAID' => 'paid', 'SETTLED' => 'paid', 'PENDING' => 'pending', 'EXPIRED' => 'expired'];
        $status = $result['status'] ?? 'unknown';

        return ['status' => $statusMap[$status] ?? 'unknown', 'raw' => $result];
    }

    public function getPaymentChannels(): array
    {
        return [
            ['code' => 'QRIS', 'name' => 'QRIS', 'group' => 'E-Wallet'],
            ['code' => 'OVO', 'name' => 'OVO', 'group' => 'E-Wallet'],
            ['code' => 'DANA', 'name' => 'DANA', 'group' => 'E-Wallet'],
            ['code' => 'LINKAJA', 'name' => 'LinkAja', 'group' => 'E-Wallet'],
            ['code' => 'BCA', 'name' => 'BCA Virtual Account', 'group' => 'Bank Transfer'],
            ['code' => 'BRI', 'name' => 'BRI Virtual Account', 'group' => 'Bank Transfer'],
            ['code' => 'MANDIRI', 'name' => 'Mandiri Virtual Account', 'group' => 'Bank Transfer'],
        ];
    }
}
