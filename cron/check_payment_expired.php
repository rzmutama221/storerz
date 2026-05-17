<?php
/**
 * CRON: Check Payment Expired
 * 
 * Runs every 5 minutes via cPanel cron job.
 * Marks orders as 'payment_expired' if payment_deadline has passed.
 * 
 * cPanel Cron Command:
 *   /usr/local/bin/php /home/USERNAME/public_html/cron/check_payment_expired.php
 * 
 * Schedule: Every 5 minutes
 *   */5 * * * *
 */

// Bootstrap
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Model.php';

date_default_timezone_set('Asia/Jakarta');

try {
    $db = Model::getConnection();

    // Find orders that are approved/awaiting_payment and past deadline
    $stmt = $db->prepare("
        UPDATE orders 
        SET status = 'payment_expired', updated_at = NOW() 
        WHERE status IN ('approved', 'awaiting_payment') 
          AND payment_deadline IS NOT NULL 
          AND payment_deadline < NOW()
    ");
    $stmt->execute();
    $affected = $stmt->rowCount();

    if ($affected > 0) {
        // Log the expired orders
        $logMessage = date('Y-m-d H:i:s') . " - {$affected} order(s) marked as payment_expired\n";
        file_put_contents(BASE_PATH . '/storage/logs/cron.log', $logMessage, FILE_APPEND);
    }

} catch (Exception $e) {
    $errorMessage = date('Y-m-d H:i:s') . " - CRON ERROR (payment_expired): " . $e->getMessage() . "\n";
    file_put_contents(BASE_PATH . '/storage/logs/cron.log', $errorMessage, FILE_APPEND);
}
