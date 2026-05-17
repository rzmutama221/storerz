<?php
/**
 * DuitkuGateway - Duitku Payment Gateway Implementation
 * 
 * Docs: https://docs.duitku.com
 */

class DuitkuGateway extends PaymentGateway
{
    private string $merchantCode = '';
    private string $baseUrl = '';

    public function __construct(string $apiKey, string $secretKey, string $environment = 'sandbox')
    {
        $this->apiKey = $apiKey; // Merchant Key
        $this->secretKey = $secretKey; // Merchant Code
        $this->merchantCode = $secretKey;
        $this->environment = $environment;
        $this->baseUrl = $environment === 'production'
            ? 'https://passport.duitku.com/webapi/api/merchant'
            : 'https://sandbox.duitku.com/webapi/api/merchant';
    }

    public function getProviderName(): string
    {
        return 'Duitku';
    }

    public function createTransaction(string $orderNumber, float $amount, string $description, array $customerData = []): array
    {
        $paymentMethod = $customerData['method'] ?? 'SP'; // SP = ShopeePay, QR = QRIS
        $signature = md5($this->merchantCode . $orderNumber . (int) $amount . $this->apiKey);

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => (int) $amount,
            'merchantOrderId' => $orderNumber,
            'productDetails' => $description,
            'customerVaName' => $customerData['name'] ?? 'Customer',
            'email' => $customerData['email'] ?? '',
            'phoneNumber' => $customerData['phone'] ?? '',
            'paymentMethod' => $paymentMethod,
            'returnUrl' => Helper::baseUrl() . '/dashboard/orders',
            'callbackUrl' => $this->callbackUrl ?: (Helper::baseUrl() . '/api/payment/callback'),
            'signature' => $signature,
            'expiryPeriod' => 120, // 2 hours in minutes
        ];

        $result = $this->httpRequest($this->baseUrl . '/v2/inquiry', 'POST', $payload);

        if (empty($result['paymentUrl'])) {
            $this->log('create_failed', "Order: {$orderNumber}, Error: " . json_encode($result));
            return ['success' => false, 'payment_url' => '', 'reference' => '', 'error' => $result['Message'] ?? 'Duitku error'];
        }

        $this->log('transaction_created', "Order: {$orderNumber}, Ref: {$result['reference']}");

        return [
            'success' => true,
            'payment_url' => $result['paymentUrl'],
            'reference' => $result['reference'] ?? $orderNumber,
            'error' => '',
        ];
    }

    public function verifyCallback(array $payload, string $signature): bool
    {
        $merchantCode = $payload['merchantCode'] ?? '';
        $amount = $payload['amount'] ?? '';
        $merchantOrderId = $payload['merchantOrderId'] ?? '';
        $expected = md5($merchantCode . $amount . $merchantOrderId . $this->apiKey);
        return hash_equals($expected, $signature);
    }

    public function checkStatus(string $reference): array
    {
        $signature = md5($this->merchantCode . $reference . $this->apiKey);
        $result = $this->httpRequest($this->baseUrl . '/transactionStatus', 'POST', [
            'merchantCode' => $this->merchantCode,
            'merchantOrderId' => $reference,
            'signature' => $signature,
        ]);

        $statusMap = ['00' => 'paid', '01' => 'pending', '02' => 'expired'];
        $code = $result['statusCode'] ?? '';

        return ['status' => $statusMap[$code] ?? 'unknown', 'raw' => $result];
    }

    public function getPaymentChannels(): array
    {
        return [
            ['code' => 'QR', 'name' => 'QRIS', 'group' => 'E-Wallet'],
            ['code' => 'SP', 'name' => 'ShopeePay', 'group' => 'E-Wallet'],
            ['code' => 'OV', 'name' => 'OVO', 'group' => 'E-Wallet'],
            ['code' => 'DA', 'name' => 'DANA', 'group' => 'E-Wallet'],
            ['code' => 'BC', 'name' => 'BCA Virtual Account', 'group' => 'Bank Transfer'],
            ['code' => 'M2', 'name' => 'Mandiri Virtual Account', 'group' => 'Bank Transfer'],
            ['code' => 'VA', 'name' => 'Maybank Virtual Account', 'group' => 'Bank Transfer'],
        ];
    }
}
