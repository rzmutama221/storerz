<?php
/**
 * CRON: Update Expired Slots (Netflix & ChatGPT)
 * 
 * Runs once daily via cPanel cron job.
 * Updates slot status from 'occupied' to 'expired' when expired_date has passed.
 * 
 * cPanel Cron Command:
 *   /usr/local/bin/php /home/USERNAME/public_html/cron/update_expired_slots.php
 * 
 * Schedule: Daily at 00:05 AM
 *   5 0 * * *
 */

// Bootstrap
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Model.php';

date_default_timezone_set('Asia/Jakarta');

try {
    $db = Model::getConnection();
    $today = date('Y-m-d');

    // Update Netflix slots
    $stmt = $db->prepare("
        UPDATE netflix_slots 
        SET status = 'expired', updated_at = NOW() 
        WHERE status = 'occupied' 
          AND expired_date IS NOT NULL 
          AND expired_date < :today
    ");
    $stmt->execute([':today' => $today]);
    $netflixExpired = $stmt->rowCount();

    // Update ChatGPT members
    $stmt = $db->prepare("
        UPDATE chatgpt_members 
        SET status = 'expired', updated_at = NOW() 
        WHERE status = 'occupied' 
          AND expired_date IS NOT NULL 
          AND expired_date < :today
    ");
    $stmt->execute([':today' => $today]);
    $chatgptExpired = $stmt->rowCount();

    $logMessage = date('Y-m-d H:i:s') . " - Slots expired: Netflix={$netflixExpired}, ChatGPT={$chatgptExpired}\n";
    file_put_contents(BASE_PATH . '/storage/logs/cron.log', $logMessage, FILE_APPEND);

} catch (Exception $e) {
    $errorMessage = date('Y-m-d H:i:s') . " - CRON ERROR (expired_slots): " . $e->getMessage() . "\n";
    file_put_contents(BASE_PATH . '/storage/logs/cron.log', $errorMessage, FILE_APPEND);
}
