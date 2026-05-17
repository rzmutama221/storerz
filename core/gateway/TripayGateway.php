<?php
/**
 * TripayGateway - Tripay Payment Gateway Implementation
 * 
 * Tripay is an Indonesian payment aggregator supporting:
 * QRIS, Virtual Account, E-Wallet, Convenience Store
 * 
 * Docs: https://tripay.co.id/developer
 */

class TripayGateway extends PaymentGateway
{
    private string $merchantCode = '';
    private string $baseUrl = '';

    public function __construct(string $apiKey, string $secretKey, string $merchantCode = '', string $environment = 'sandbox')
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->merchantCode = $merchantCode;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production'
            ? 'https://tripay.co.id/api'
            : 'https://tripay.co.id/api-sandbox';
    }

    public function getProviderName(): string
    {
        return 'Tripay';
    }

    /**
     * Create closed payment transaction
     */
    public function createTransaction(string $orderNumber, float $amount, string $description, array $customerData = []): array
    {
        $method = $customerData['method'] ?? 'QRIS';
        $customerName = $customerData['name'] ?? 'Customer';
        $customerEmail = $customerData['email'] ?? '';
        $customerPhone = $customerData['phone'] ?? '';

        // Generate signature
        $signature = hash_hmac('sha256', $this->merchantCode . $orderNumber . (int) $amount, $this->secretKey);

        $payload = [
            'method' => $method,
            'merchant_ref' => $orderNumber,
            'amount' => (int) $amount,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => [
                [
                    'name' => $description,
                    'price' => (int) $amount,
                    'quantity' => 1,
                ],
            ],
            'callback_url' => $this->callbackUrl ?: (Helper::baseUrl() . '/api/payment/callback'),
            'return_url' => Helper::baseUrl() . '/dashboard/orders',
            'expired_time' => (int) (time() + (2 * 3600)), // 2 hours
            'signature' => $signature,
        ];

        $result = $this->httpRequest($this->baseUrl . '/transaction/create', 'POST', $payload, [
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        if (!$result['success'] || empty($result['data'])) {
            $this->log('create_transaction_failed', "Order: {$orderNumber}, Error: " . json_encode($result));
            return [
                'success' => false,
                'payment_url' => '',
                'reference' => '',
                'error' => $result['message'] ?? 'Gateway error',
            ];
        }

        $data = $result['data'];

        $this->log('transaction_created', "Order: {$orderNumber}, Ref: {$data['reference']}, Method: {$method}");

        return [
            'success' => true,
            'payment_url' => $data['checkout_url'] ?? '',
            'reference' => $data['reference'] ?? '',
            'qr_url' => $data['qr_url'] ?? '',
            'pay_code' => $data['pay_code'] ?? '',
            'expired_time' => $data['expired_time'] ?? '',
            'error' => '',
        ];
    }

    /**
     * Verify callback signature from Tripay
     */
    public function verifyCallback(array $payload, string $signature): bool
    {
        $calculatedSignature = hash_hmac('sha256', json_encode($payload), $this->secretKey);
        $isValid = hash_equals($calculatedSignature, $signature);

        if (!$isValid) {
            $this->log('callback_invalid', "Signature mismatch");
        }

        return $isValid;
    }

    /**
     * Check transaction status
     */
    public function checkStatus(string $reference): array
    {
        $result = $this->httpRequest($this->baseUrl . '/transaction/detail', 'GET', [
            'reference' => $reference,
        ], [
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        if (!$result['success'] || empty($result['data'])) {
            return ['status' => 'unknown', 'raw' => $result];
        }

        $data = $result['data'];
        $statusMap = [
            'PAID' => 'paid',
            'UNPAID' => 'pending',
            'EXPIRED' => 'expired',
            'FAILED' => 'failed',
            'REFUND' => 'refunded',
        ];

        return [
            'status' => $statusMap[$data['status']] ?? 'unknown',
            'raw' => $data,
        ];
    }

    /**
     * Get available payment channels from Tripay
     */
    public function getPaymentChannels(): array
    {
        $result = $this->httpRequest($this->baseUrl . '/merchant/payment-channel', 'GET', [], [
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        if (!$result['success'] || empty($result['data'])) {
            return [];
        }

        $channels = [];
        foreach ($result['data'] as $channel) {
            if ($channel['active']) {
                $channels[] = [
                    'code' => $channel['code'],
                    'name' => $channel['name'],
                    'group' => $channel['group'],
                    'fee_flat' => $channel['total_fee']['flat'] ?? 0,
                    'fee_percent' => $channel['total_fee']['percent'] ?? 0,
                    'icon_url' => $channel['icon_url'] ?? '',
                ];
            }
        }

        return $channels;
    }
}
