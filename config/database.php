<?php
/**
 * Database Configuration - RZDK Store
 * 
 * PENTING: Ubah nilai-nilai di bawah ini sesuai dengan
 * credential database MySQL di cPanel hosting Anda.
 * 
 * Cara mendapatkan info ini di cPanel:
 * 1. Login cPanel → MySQL Databases
 * 2. Buat database baru (misal: rzdkstore_db)
 * 3. Buat user baru dan assign ke database dengan ALL PRIVILEGES
 * 4. Masukkan info tersebut di bawah ini
 */

return [
    // Database host (biasanya 'localhost' untuk shared hosting)
    'host' => 'localhost',

    // Port MySQL (default: 3306)
    'port' => '3306',

    // Nama database yang sudah dibuat di cPanel
    'database' => 'rzdkstore_db',

    // Username database (bukan username cPanel)
    'username' => 'rzdkstore_user',

    // Password database
    'password' => '',

    // Charset (jangan diubah)
    'charset' => 'utf8mb4',

    // Collation (jangan diubah)
    'collation' => 'utf8mb4_unicode_ci',
];
