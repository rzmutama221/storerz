<?php
/**
 * Mail Configuration - RZDK Store
 * 
 * Konfigurasi SMTP untuk pengiriman email.
 * 
 * Cara setup di cPanel:
 * 1. Login cPanel → Email Accounts
 * 2. Buat email: noreply@rzdkstore.my.id
 * 3. Masukkan credential di bawah ini
 * 4. SMTP host biasanya: mail.rzdkstore.my.id
 * 5. Port: 465 (SSL) atau 587 (TLS)
 */

return [
    // Gunakan SMTP? (true = SMTP, false = PHP mail())
    'use_smtp' => true,

    // SMTP Host (biasanya mail.domain.com)
    'smtp_host' => 'mail.rzdkstore.my.id',

    // SMTP Port (465 = SSL, 587 = TLS)
    'smtp_port' => 465,

    // SMTP Username (alamat email lengkap)
    'smtp_username' => 'noreply@rzdkstore.my.id',

    // SMTP Password
    'smtp_password' => '',

    // Encryption: 'ssl' atau 'tls'
    'smtp_encryption' => 'ssl',

    // From email (biasanya sama dengan smtp_username)
    'from_email' => 'noreply@rzdkstore.my.id',

    // From name (nama pengirim yang ditampilkan)
    'from_name' => 'RZDK Store',
];
