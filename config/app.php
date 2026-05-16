<?php
/**
 * Application Configuration - RZDK Store
 * 
 * Konfigurasi umum aplikasi.
 */

return [
    // Nama website
    'name' => 'RZDK Store',

    // Deskripsi website
    'description' => 'Premium Digital Store — Akses Layanan Premium Harga Terjangkau',

    // URL website (tanpa trailing slash)
    'url' => 'https://rzdkstore.my.id',

    // Timezone
    'timezone' => 'Asia/Jakarta',

    // Encryption key untuk encrypt/decrypt data sensitif
    // PENTING: Ganti dengan string random yang panjang dan unik!
    // Generate: buka browser → https://rzdkstore.my.id/generate-key (hapus route ini setelah generate)
    'encryption_key' => 'GANTI_DENGAN_RANDOM_STRING_YANG_PANJANG_DAN_UNIK_32_KARAKTER',

    // Debug mode (set false di production!)
    'debug' => false,

    // Nomor WhatsApp store (format: 08xxx)
    'whatsapp' => '085111642004',

    // WhatsApp link
    'whatsapp_link' => 'https://wa.me/6285111642004',

    // Session timeout dalam detik (default: 2 jam)
    'session_timeout' => 7200,

    // Maksimum ukuran upload file (dalam KB)
    'max_upload_size' => 2048, // 2MB

    // Allowed image types untuk upload
    'allowed_image_types' => [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
    ],

    // Payment timeout setelah approval (dalam jam)
    'payment_timeout_hours' => 2,

    // Reminder expiry (dalam hari sebelum expired)
    'reminder_days_before' => 3,

    // Versi aplikasi
    'version' => '1.0.0',
];
