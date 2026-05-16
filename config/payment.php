<?php
/**
 * Payment Configuration - RZDK Store
 * 
 * Konfigurasi metode pembayaran.
 * Mode default: QRIS Manual (satu pintu).
 * Payment gateway bisa diaktifkan nanti.
 */

return [
    // Mode pembayaran aktif:
    // 'qris_manual' = Hanya QRIS statis (default)
    // 'gateway' = Hanya payment gateway
    // 'both' = Customer bisa pilih
    'mode' => 'qris_manual',

    // Path ke gambar QRIS (relatif dari root)
    'qris_image' => 'assets/img/qrisrzdkstore.png',

    // Nama penerima QRIS (untuk ditampilkan ke customer)
    'qris_recipient_name' => 'RZDK Store',

    // Batas waktu payment setelah order di-approve (dalam jam)
    'timeout_hours' => 2,

    // ============================================================
    // PAYMENT GATEWAY (Opsional - diaktifkan nanti)
    // ============================================================

    'gateway' => [
        // Status gateway: 'active', 'inactive', 'maintenance'
        'status' => 'inactive',

        // Provider: 'midtrans', 'xendit', 'tripay', 'duitku'
        'provider' => '',

        // API credentials (encrypted di database, plain di sini untuk config)
        'api_key' => '',
        'secret_key' => '',

        // Mode: 'sandbox' atau 'production'
        'environment' => 'sandbox',

        // Callback URL (auto-generated berdasarkan site URL)
        'callback_url' => '/api/payment/callback',

        // Metode pembayaran yang diaktifkan (tergantung provider)
        'enabled_methods' => [
            'qris',
            'bank_transfer',
            'ewallet',
        ],
    ],

    // ============================================================
    // REFUND SETTINGS
    // ============================================================

    'refund' => [
        // Metode refund: 'manual_transfer'
        'method' => 'manual_transfer',

        // Catatan: refund dilakukan manual oleh admin via transfer balik
    ],
];
