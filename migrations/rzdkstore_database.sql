-- ============================================================
-- RZDK Store - Database Schema
-- Version: 1.0
-- PHP: 8.4 | MySQL: 8.x
-- Generated: 16 Mei 2026
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+07:00";

-- Buat database (opsional, biasanya sudah dibuat via cPanel)
-- CREATE DATABASE IF NOT EXISTS `rzdkstore_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `rzdkstore_db`;

-- ============================================================
-- TABEL: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_token` VARCHAR(100) DEFAULT NULL,
    `verification_token_expires` DATETIME DEFAULT NULL,
    `reset_token` VARCHAR(100) DEFAULT NULL,
    `reset_token_expires` DATETIME DEFAULT NULL,
    `full_name` VARCHAR(100) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('active', 'suspended', 'banned') NOT NULL DEFAULT 'active',
    `notes_admin` TEXT DEFAULT NULL,
    `failed_login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_status` (`status`),
    KEY `idx_verification_token` (`verification_token`),
    KEY `idx_reset_token` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_sort_order` (`sort_order`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: products
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `logo_path` VARCHAR(255) DEFAULT NULL,
    `has_slot_system` TINYINT(1) NOT NULL DEFAULT 0,
    `slot_type` ENUM('none', 'netflix', 'chatgpt') NOT NULL DEFAULT 'none',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_sort_order` (`sort_order`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: product_variants
-- ============================================================
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `duration_days` INT NOT NULL DEFAULT 30,
    `type` ENUM('sharing', 'private', 'semi_private', 'invite', 'redeem', 'service') NOT NULL DEFAULT 'private',
    `fulfillment_mode` ENUM('manual', 'auto_stock') NOT NULL DEFAULT 'manual',
    `platform` ENUM('all', 'android', 'ios', 'web', 'apk', 'mobile', 'tv') NOT NULL DEFAULT 'all',
    `max_users` INT NOT NULL DEFAULT 1,
    `stock_count` INT NOT NULL DEFAULT 0,
    `notes` TEXT DEFAULT NULL,
    `warranty_days` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_fulfillment_mode` (`fulfillment_mode`),
    CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: stock_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `stock_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `variant_id` INT UNSIGNED NOT NULL,
    `data_content` TEXT NOT NULL COMMENT 'Encrypted credential/kode/info',
    `is_sold` TINYINT(1) NOT NULL DEFAULT 0,
    `sold_to_order_id` INT UNSIGNED DEFAULT NULL,
    `sold_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_variant_id` (`variant_id`),
    KEY `idx_is_sold` (`is_sold`),
    CONSTRAINT `fk_stock_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: vouchers
-- ============================================================
CREATE TABLE IF NOT EXISTS `vouchers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'fixed',
    `value` DECIMAL(10,2) NOT NULL,
    `min_order` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `max_discount` DECIMAL(10,2) DEFAULT NULL,
    `applicable_products` TEXT DEFAULT NULL COMMENT 'JSON array of product_ids, NULL = all',
    `usage_limit` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = unlimited',
    `usage_per_user` INT UNSIGNED NOT NULL DEFAULT 1,
    `used_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code` (`code`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: orders
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_number` VARCHAR(30) NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `variant_id` INT UNSIGNED NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `original_price` DECIMAL(10,2) NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `final_price` DECIMAL(10,2) NOT NULL,
    `voucher_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('pending_approval','approved','awaiting_payment','paid','processing','completed','rejected','payment_expired','cancelled','refund') NOT NULL DEFAULT 'pending_approval',
    `rejection_reason` TEXT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `payment_deadline` DATETIME DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `fulfillment_data` TEXT DEFAULT NULL COMMENT 'Encrypted product data sent to customer',
    `fulfillment_notes` TEXT DEFAULT NULL,
    `active_until` DATETIME DEFAULT NULL,
    `payment_method` ENUM('qris_manual', 'payment_gateway') NOT NULL DEFAULT 'qris_manual',
    `payment_proof_path` VARCHAR(255) DEFAULT NULL,
    `admin_notes` TEXT DEFAULT NULL,
    `customer_notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_order_number` (`order_number`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_variant_id` (`variant_id`),
    KEY `idx_status` (`status`),
    KEY `idx_payment_deadline` (`payment_deadline`),
    KEY `idx_active_until` (`active_until`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_orders_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_orders_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambahkan FK stock_items -> orders (setelah orders dibuat)
ALTER TABLE `stock_items` ADD CONSTRAINT `fk_stock_order` FOREIGN KEY (`sold_to_order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

-- ============================================================
-- TABEL: transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `type` ENUM('income', 'refund') NOT NULL DEFAULT 'income',
    `amount` DECIMAL(10,2) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_type` (`type`),
    KEY `idx_recorded_at` (`recorded_at`),
    CONSTRAINT `fk_transactions_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: voucher_usage
-- ============================================================
CREATE TABLE IF NOT EXISTS `voucher_usage` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `voucher_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `used_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_voucher_id` (`voucher_id`),
    KEY `idx_user_id` (`user_id`),
    UNIQUE KEY `uk_voucher_user_order` (`voucher_id`, `user_id`, `order_id`),
    CONSTRAINT `fk_vusage_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_vusage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_vusage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: announcements
-- ============================================================
CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `priority` ENUM('info', 'warning', 'urgent') NOT NULL DEFAULT 'info',
    `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_published_at` (`published_at`),
    CONSTRAINT `fk_announcements_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: announcement_reads
-- ============================================================
CREATE TABLE IF NOT EXISTS `announcement_reads` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `announcement_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `read_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_announcement_user` (`announcement_id`, `user_id`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_areads_announcement` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_areads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: warranties
-- ============================================================
CREATE TABLE IF NOT EXISTS `warranties` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `issue_type` ENUM('login_error', 'service_down', 'account_banned', 'not_working', 'other') NOT NULL,
    `description` TEXT NOT NULL,
    `screenshot_path` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('submitted', 'reviewing', 'resolved', 'rejected') NOT NULL DEFAULT 'submitted',
    `admin_response` TEXT DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_warranties_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_warranties_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: netflix_accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS `netflix_accounts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL COMMENT 'Encrypted (not hashed)',
    `plan` ENUM('basic', 'standard', 'premium') NOT NULL DEFAULT 'premium',
    `max_profiles` INT UNSIGNED NOT NULL DEFAULT 5,
    `notes` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: netflix_slots
-- ============================================================
CREATE TABLE IF NOT EXISTS `netflix_slots` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `account_id` INT UNSIGNED NOT NULL,
    `profile_name` VARCHAR(50) NOT NULL,
    `profile_pin` VARCHAR(10) DEFAULT NULL,
    `customer_id` INT UNSIGNED DEFAULT NULL,
    `customer_name` VARCHAR(100) DEFAULT NULL,
    `customer_contact` VARCHAR(100) DEFAULT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `device_type` ENUM('hp', 'laptop', 'tv', 'tablet', 'other') DEFAULT NULL,
    `order_date` DATE DEFAULT NULL,
    `expired_date` DATE DEFAULT NULL,
    `status` ENUM('available', 'occupied', 'expired', 'maintenance') NOT NULL DEFAULT 'available',
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_account_id` (`account_id`),
    KEY `idx_customer_id` (`customer_id`),
    KEY `idx_status` (`status`),
    KEY `idx_expired_date` (`expired_date`),
    CONSTRAINT `fk_nslots_account` FOREIGN KEY (`account_id`) REFERENCES `netflix_accounts` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_nslots_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_nslots_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: chatgpt_accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS `chatgpt_accounts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `head_email` VARCHAR(100) NOT NULL,
    `head_password` VARCHAR(255) NOT NULL COMMENT 'Encrypted (not hashed)',
    `workspace_name` VARCHAR(100) NOT NULL,
    `max_members` INT UNSIGNED NOT NULL DEFAULT 4,
    `notes` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: chatgpt_members
-- ============================================================
CREATE TABLE IF NOT EXISTS `chatgpt_members` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `account_id` INT UNSIGNED NOT NULL,
    `slot_number` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED DEFAULT NULL,
    `customer_email` VARCHAR(100) DEFAULT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `invite_date` DATE DEFAULT NULL,
    `expired_date` DATE DEFAULT NULL,
    `status` ENUM('available', 'occupied', 'expired', 'pending_invite') NOT NULL DEFAULT 'available',
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_account_id` (`account_id`),
    KEY `idx_customer_id` (`customer_id`),
    KEY `idx_status` (`status`),
    KEY `idx_expired_date` (`expired_date`),
    CONSTRAINT `fk_cmembers_account` FOREIGN KEY (`account_id`) REFERENCES `chatgpt_accounts` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_cmembers_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_cmembers_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: activity_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Admin Account
-- Password: admin123 (bcrypt hash)
-- PENTING: Ganti password setelah deploy!
-- ============================================================
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `is_verified`, `full_name`, `status`, `created_at`) VALUES
('admin', 'admin@rzdkstore.my.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 'RZDK Store Admin', 'active', NOW());

-- ============================================================
-- SEED DATA: Default Settings
-- ============================================================
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'RZDK Store'),
('site_description', 'Premium Digital Store — Akses Layanan Premium Harga Terjangkau'),
('site_url', 'https://rzdkstore.my.id'),
('whatsapp_number', '085111642004'),
('payment_mode', 'qris_manual'),
('payment_timeout_hours', '2'),
('qris_image_path', 'assets/img/qrisrzdkstore.png'),
('smtp_host', 'mail.rzdkstore.my.id'),
('smtp_port', '465'),
('smtp_username', 'noreply@rzdkstore.my.id'),
('smtp_password', ''),
('smtp_encryption', 'ssl'),
('smtp_from_name', 'RZDK Store'),
('maintenance_mode', '0'),
('gateway_provider', ''),
('gateway_api_key', ''),
('gateway_status', 'inactive'),
('reminder_days_before', '3');

-- ============================================================
-- SEED DATA: Kategori Default
-- ============================================================
INSERT INTO `categories` (`name`, `slug`, `icon`, `sort_order`, `is_active`) VALUES
('Streaming Video & Hiburan', 'streaming-video', 'film', 1, 1),
('Streaming Musik', 'streaming-musik', 'music', 2, 1),
('Aplikasi Kreatif', 'aplikasi-kreatif', 'palette', 3, 1),
('AI, Edukasi, & Penulisan', 'ai-edukasi', 'brain', 4, 1),
('Produktivitas & Utilitas', 'produktivitas', 'briefcase', 5, 1);

COMMIT;
