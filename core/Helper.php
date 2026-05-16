<?php
/**
 * Helper - Utility Functions for RZDK Store
 * 
 * Common helper functions used throughout the application.
 */

class Helper
{
    /**
     * Generate CSRF token and store in session
     */
    public static function csrfToken(): string
    {
        Session::start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Output CSRF hidden input field
     */
    public static function csrfField(): string
    {
        $token = self::csrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrf(?string $token): bool
    {
        Session::start();
        if (!$token || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize output to prevent XSS
     */
    public static function e(?string $value): string
    {
        if ($value === null) return '';
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a URL-friendly slug from a string
     */
    public static function slug(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Format price to Indonesian Rupiah
     */
    public static function formatPrice(float|int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format date to Indonesian locale
     */
    public static function formatDate(?string $date, string $format = 'd M Y'): string
    {
        if (!$date) return '-';

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $timestamp = strtotime($date);
        if (!$timestamp) return '-';

        $formatted = date($format, $timestamp);

        // Replace English month abbreviations with Indonesian
        foreach ($months as $num => $name) {
            $englishMonth = date('M', mktime(0, 0, 0, $num, 1));
            $formatted = str_replace($englishMonth, $name, $formatted);
        }

        return $formatted;
    }

    /**
     * Format datetime to relative time (e.g., "2 jam yang lalu")
     */
    public static function timeAgo(?string $datetime): string
    {
        if (!$datetime) return '-';

        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        if ($diff < 2592000) return floor($diff / 604800) . ' minggu lalu';

        return self::formatDate($datetime);
    }

    /**
     * Generate order number (ORD-YYYYMMDD-XXXX)
     */
    public static function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $db = Model::getConnection();

        // Get today's order count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = (int) ($result['total'] ?? 0) + 1;

        return 'ORD-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get a setting value from database
     */
    public static function setting(string $key, ?string $default = null): ?string
    {
        static $cache = [];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        try {
            $db = Model::getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
            $stmt->execute([':key' => $key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = $result ? $result['setting_value'] : $default;
            $cache[$key] = $value;
            return $value;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Upload a file securely
     * Returns the relative path on success, null on failure
     */
    public static function uploadFile(string $fieldName, string $directory, array $allowedTypes = [], int $maxSizeKb = 2048): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];

        // Validate file type
        if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes, true)) {
            return null;
        }

        // Validate file size
        if (($file['size'] / 1024) > $maxSizeKb) {
            return null;
        }

        // Create directory if not exists
        $uploadDir = BASE_PATH . '/storage/uploads/' . trim($directory, '/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'storage/uploads/' . trim($directory, '/') . '/' . $filename;
        }

        return null;
    }

    /**
     * Encrypt sensitive data (for credentials storage)
     */
    public static function encrypt(string $data): string
    {
        $key = self::getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Decrypt sensitive data
     */
    public static function decrypt(string $data): ?string
    {
        $key = self::getEncryptionKey();
        $decoded = base64_decode($data);

        if (!$decoded || !str_contains($decoded, '::')) {
            return null;
        }

        [$iv, $encrypted] = explode('::', $decoded, 2);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Get encryption key from config
     */
    private static function getEncryptionKey(): string
    {
        $config = require BASE_PATH . '/config/app.php';
        return hash('sha256', $config['encryption_key']);
    }

    /**
     * Truncate text with ellipsis
     */
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Get status badge HTML
     */
    public static function statusBadge(string $status): string
    {
        $badges = [
            'pending_approval' => ['bg-yellow-500/20 text-yellow-400', 'Menunggu Approval'],
            'approved' => ['bg-blue-500/20 text-blue-400', 'Disetujui'],
            'awaiting_payment' => ['bg-orange-500/20 text-orange-400', 'Menunggu Pembayaran'],
            'paid' => ['bg-emerald-500/20 text-emerald-400', 'Sudah Dibayar'],
            'processing' => ['bg-purple-500/20 text-purple-400', 'Diproses'],
            'completed' => ['bg-green-500/20 text-green-400', 'Selesai'],
            'rejected' => ['bg-red-500/20 text-red-400', 'Ditolak'],
            'payment_expired' => ['bg-gray-500/20 text-gray-400', 'Expired'],
            'cancelled' => ['bg-gray-500/20 text-gray-400', 'Dibatalkan'],
            'refund' => ['bg-red-500/20 text-red-400', 'Refund'],
            'active' => ['bg-green-500/20 text-green-400', 'Aktif'],
            'suspended' => ['bg-yellow-500/20 text-yellow-400', 'Suspended'],
            'banned' => ['bg-red-500/20 text-red-400', 'Banned'],
            'available' => ['bg-green-500/20 text-green-400', 'Tersedia'],
            'occupied' => ['bg-blue-500/20 text-blue-400', 'Terisi'],
            'expired' => ['bg-gray-500/20 text-gray-400', 'Expired'],
            'maintenance' => ['bg-yellow-500/20 text-yellow-400', 'Maintenance'],
            'submitted' => ['bg-blue-500/20 text-blue-400', 'Diajukan'],
            'reviewing' => ['bg-yellow-500/20 text-yellow-400', 'Sedang Direview'],
            'resolved' => ['bg-green-500/20 text-green-400', 'Selesai'],
        ];

        $badge = $badges[$status] ?? ['bg-gray-500/20 text-gray-400', ucfirst($status)];

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $badge[0] . '">' . self::e($badge[1]) . '</span>';
    }

    /**
     * Log activity to database
     */
    public static function logActivity(string $action, ?string $description = null, ?int $userId = null): void
    {
        try {
            $db = Model::getConnection();
            $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (:user_id, :action, :description, :ip, :ua, NOW())");
            $stmt->execute([
                ':user_id' => $userId ?? Auth::id(),
                ':action' => $action,
                ':description' => $description,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Exception $e) {
            // Silently fail - logging should never break the app
        }
    }

    /**
     * Get base URL
     */
    public static function baseUrl(): string
    {
        $config = require BASE_PATH . '/config/app.php';
        return rtrim($config['url'], '/');
    }

    /**
     * Generate full URL
     */
    public static function url(string $path = ''): string
    {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }

    /**
     * Get asset URL
     */
    public static function asset(string $path): string
    {
        return self::baseUrl() . '/assets/' . ltrim($path, '/');
    }

    /**
     * Check if current URL matches path (for active nav)
     */
    public static function isActive(string $path): bool
    {
        $currentUrl = '/' . trim($_GET['url'] ?? '', '/');
        return str_starts_with($currentUrl, $path);
    }

    /**
     * Return active class if path matches
     */
    public static function activeClass(string $path, string $class = 'active'): string
    {
        return self::isActive($path) ? $class : '';
    }
}
