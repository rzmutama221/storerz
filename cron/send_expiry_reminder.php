<?php
/**
 * CRON: Send Expiry Reminder (H-3)
 * 
 * Runs once daily at 08:00 AM via cPanel cron job.
 * Sends email reminders to customers whose products expire in 3 days.
 * 
 * cPanel Cron Command:
 *   /usr/local/bin/php /home/USERNAME/public_html/cron/send_expiry_reminder.php
 * 
 * Schedule: Daily at 8 AM
 *   0 8 * * *
 */

// Bootstrap
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Helper.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Mailer.php';

date_default_timezone_set('Asia/Jakarta');

try {
    $db = Model::getConnection();

    // Get reminder_days setting (default 3)
    $reminderDays = 3;
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'reminder_days_before' LIMIT 1");
    $stmt->execute();
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($setting) {
        $reminderDays = (int) $setting['setting_value'];
    }

    // Calculate target date (orders expiring in exactly $reminderDays days)
    $targetDate = date('Y-m-d', strtotime("+{$reminderDays} days"));

    // Find orders expiring on target date that haven't been reminded yet
    $stmt = $db->prepare("
        SELECT o.id, o.order_number, o.active_until, o.user_id,
               u.username, u.email,
               p.name as product_name, pv.name as variant_name
        FROM orders o
        JOIN users u ON u.id = o.user_id
        JOIN product_variants pv ON pv.id = o.variant_id
        JOIN products p ON p.id = pv.product_id
        WHERE o.status = 'completed'
          AND DATE(o.active_until) = :target_date
          AND u.is_verified = 1
          AND u.status = 'active'
    ");
    $stmt->execute([':target_date' => $targetDate]);
    $expiringOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sentCount = 0;

    foreach ($expiringOrders as $order) {
        $sent = Mailer::quickSend(
            $order['email'],
            "Reminder: {$order['product_name']} akan expired dalam {$reminderDays} hari",
            'expiry-reminder',
            [
                'username' => $order['username'],
                'product_name' => $order['product_name'],
                'variant_name' => $order['variant_name'],
                'expired_date' => date('d M Y', strtotime($order['active_until'])),
                'dashboard_url' => 'https://rzdkstore.my.id/dashboard/products',
            ]
        );

        if ($sent) $sentCount++;

        // Small delay to avoid email rate limiting
        usleep(500000); // 0.5 second
    }

    $logMessage = date('Y-m-d H:i:s') . " - Expiry reminder: {$sentCount}/" . count($expiringOrders) . " emails sent (target: {$targetDate})\n";
    file_put_contents(BASE_PATH . '/storage/logs/cron.log', $logMessage, FILE_APPEND);

} catch (Exception $e) {
    $errorMessage = date('Y-m-d H:i:s') . " - CRON ERROR (expiry_reminder): " . $e->getMessage() . "\n";
    file_put_contents(BASE_PATH . '/storage/logs/cron.log', $errorMessage, FILE_APPEND);
}
