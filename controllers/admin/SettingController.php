<?php
/**
 * Admin SettingController - RZDK Store
 * 
 * Manages site settings: general config, payment mode, QRIS upload, SMTP.
 */

class SettingController extends Controller
{
    /**
     * Show settings page
     */
    public function index(): void
    {
        $db = Model::getConnection();

        // Get all settings as key-value
        $rows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('admin/settings', [
            'pageTitle' => 'Settings',
            'settings' => $settings,
        ], 'admin');
    }

    /**
     * Update settings
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $allowedKeys = [
            'site_name', 'site_description', 'whatsapp_number',
            'payment_mode', 'payment_timeout_hours',
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'smtp_from_name',
            'maintenance_mode', 'gateway_provider', 'gateway_api_key', 'gateway_status',
            'reminder_days_before',
        ];

        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (:key, :value, NOW()) ON DUPLICATE KEY UPDATE setting_value = :value2, updated_at = NOW()");

        foreach ($allowedKeys as $key) {
            $value = $this->input($key);
            if ($value !== null) {
                $stmt->execute([':key' => $key, ':value' => $value, ':value2' => $value]);
            }
        }

        Helper::logActivity('settings_updated', 'Site settings updated', Auth::id());
        Session::flash('success', 'Pengaturan berhasil disimpan.');
        $this->redirect('/admin/settings');
    }

    /**
     * Upload QRIS image
     */
    public function uploadQris(): void
    {
        if (!$this->validateCsrf()) return;

        if (!isset($_FILES['qris_image']) || $_FILES['qris_image']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Pilih file gambar QRIS untuk diupload.');
            $this->redirect('/admin/settings');
            return;
        }

        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['qris_image']['type'], $allowed)) {
            Session::flash('error', 'Format file tidak valid. Gunakan JPG, PNG, atau WebP.');
            $this->redirect('/admin/settings');
            return;
        }

        if ($_FILES['qris_image']['size'] > 3 * 1024 * 1024) {
            Session::flash('error', 'Ukuran file maks 3MB.');
            $this->redirect('/admin/settings');
            return;
        }

        // Move to assets/img/qrisrzdkstore.png
        $destination = BASE_PATH . '/assets/img/qrisrzdkstore.png';
        if (move_uploaded_file($_FILES['qris_image']['tmp_name'], $destination)) {
            // Update setting
            $db = Model::getConnection();
            $db->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES ('qris_image_path', 'assets/img/qrisrzdkstore.png', NOW()) ON DUPLICATE KEY UPDATE setting_value = 'assets/img/qrisrzdkstore.png', updated_at = NOW()")->execute();

            Helper::logActivity('qris_uploaded', 'QRIS image updated', Auth::id());
            Session::flash('success', 'Gambar QRIS berhasil diupload.');
        } else {
            Session::flash('error', 'Gagal mengupload file. Periksa permission folder.');
        }

        $this->redirect('/admin/settings');
    }
}
