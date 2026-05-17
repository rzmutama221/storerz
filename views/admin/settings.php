<?php /** Admin Settings View */ ?>

<div class="mb-6">
    <h1 class="text-2xl font-heading font-bold">Settings</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Konfigurasi website, payment, dan email</p>
</div>

<form action="/admin/settings" method="POST" data-protect-submit>
    <?= Helper::csrfField() ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- General Settings -->
        <div class="card-flat">
            <h2 class="font-heading font-semibold text-sm mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Umum
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Toko</label>
                    <input type="text" name="site_name" class="form-input" value="<?= Helper::e($settings['site_name'] ?? 'RZDK Store') ?>">
                </div>
                <div>
                    <label class="form-label">Deskripsi</label>
                    <textarea name="site_description" class="form-input" rows="2"><?= Helper::e($settings['site_description'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="whatsapp_number" class="form-input" value="<?= Helper::e($settings['whatsapp_number'] ?? '085111642004') ?>" placeholder="085111642004">
                </div>
                <div>
                    <label class="form-label">Reminder Expired (Hari Sebelum)</label>
                    <input type="number" name="reminder_days_before" class="form-input w-32" value="<?= Helper::e($settings['reminder_days_before'] ?? '3') ?>" min="1" max="14">
                    <p class="text-xs text-gray-400 mt-1">Email reminder dikirim H-X sebelum produk expired</p>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm">Mode Maintenance (website offline untuk customer)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Payment Settings -->
        <div class="card-flat">
            <h2 class="font-heading font-semibold text-sm mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                Payment
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Mode Pembayaran</label>
                    <select name="payment_mode" class="form-input">
                        <option value="qris_manual" <?= ($settings['payment_mode'] ?? '') === 'qris_manual' ? 'selected' : '' ?>>QRIS Manual Only</option>
                        <option value="gateway" <?= ($settings['payment_mode'] ?? '') === 'gateway' ? 'selected' : '' ?>>Payment Gateway Only</option>
                        <option value="both" <?= ($settings['payment_mode'] ?? '') === 'both' ? 'selected' : '' ?>>Both (Customer Pilih)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Timeout Payment (Jam)</label>
                    <input type="number" name="payment_timeout_hours" class="form-input w-32" value="<?= Helper::e($settings['payment_timeout_hours'] ?? '2') ?>" min="1" max="48">
                    <p class="text-xs text-gray-400 mt-1">Batas waktu customer bayar setelah order di-approve</p>
                </div>

                <div class="pt-3 border-t border-gray-200 dark:border-dark-border">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Payment Gateway (Opsional)</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="form-label">Provider</label>
                            <select name="gateway_provider" class="form-input">
                                <option value="" <?= empty($settings['gateway_provider']) ? 'selected' : '' ?>>-- Belum Dipilih --</option>
                                <option value="midtrans" <?= ($settings['gateway_provider'] ?? '') === 'midtrans' ? 'selected' : '' ?>>Midtrans</option>
                                <option value="xendit" <?= ($settings['gateway_provider'] ?? '') === 'xendit' ? 'selected' : '' ?>>Xendit</option>
                                <option value="tripay" <?= ($settings['gateway_provider'] ?? '') === 'tripay' ? 'selected' : '' ?>>Tripay</option>
                                <option value="duitku" <?= ($settings['gateway_provider'] ?? '') === 'duitku' ? 'selected' : '' ?>>Duitku</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">API Key</label>
                            <input type="text" name="gateway_api_key" class="form-input font-mono text-sm" value="<?= Helper::e($settings['gateway_api_key'] ?? '') ?>" placeholder="Masukkan API key">
                        </div>
                        <div>
                            <label class="form-label">Status Gateway</label>
                            <select name="gateway_status" class="form-input">
                                <option value="inactive" <?= ($settings['gateway_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="active" <?= ($settings['gateway_status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="maintenance" <?= ($settings['gateway_status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMTP Settings -->
        <div class="card-flat">
            <h2 class="font-heading font-semibold text-sm mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Email (SMTP)
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-input" value="<?= Helper::e($settings['smtp_host'] ?? 'mail.rzdkstore.my.id') ?>">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Port</label>
                        <input type="number" name="smtp_port" class="form-input" value="<?= Helper::e($settings['smtp_port'] ?? '465') ?>">
                    </div>
                    <div>
                        <label class="form-label">Encryption</label>
                        <select name="smtp_encryption" class="form-input">
                            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                            <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Username (Email)</label>
                    <input type="text" name="smtp_username" class="form-input" value="<?= Helper::e($settings['smtp_username'] ?? 'noreply@rzdkstore.my.id') ?>">
                </div>
                <div>
                    <label class="form-label">Password</label>
                    <input type="password" name="smtp_password" class="form-input" value="<?= Helper::e($settings['smtp_password'] ?? '') ?>" placeholder="••••••••">
                </div>
                <div>
                    <label class="form-label">Nama Pengirim</label>
                    <input type="text" name="smtp_from_name" class="form-input" value="<?= Helper::e($settings['smtp_from_name'] ?? 'RZDK Store') ?>">
                </div>
            </div>
        </div>

        <!-- QRIS Upload -->
        <div class="card-flat">
            <h2 class="font-heading font-semibold text-sm mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h.01"/><path d="M17 7h.01"/><path d="M7 17h.01"/><path d="M13 13h4v4h-4z"/></svg>
                QRIS
            </h2>
            <div class="mb-4">
                <p class="text-sm text-gray-400 mb-3">Gambar QRIS statis yang ditampilkan ke customer saat pembayaran.</p>
                <?php
                $qrisPath = $settings['qris_image_path'] ?? 'assets/img/qrisrzdkstore.png';
                $qrisExists = file_exists(BASE_PATH . '/' . $qrisPath);
                ?>
                <?php if ($qrisExists): ?>
                <div class="bg-white p-3 rounded-lg inline-block mb-3 border border-gray-200 dark:border-dark-border">
                    <img src="/<?= Helper::e($qrisPath) ?>" alt="QRIS" class="w-40 h-40 object-contain">
                </div>
                <p class="text-xs text-green-500 mb-3">QRIS aktif tersedia.</p>
                <?php else: ?>
                <div class="bg-gray-100 dark:bg-dark p-4 rounded-lg mb-3 text-center">
                    <p class="text-sm text-gray-400">Belum ada gambar QRIS. Upload di bawah ini.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="flex items-center gap-3">
        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Simpan Semua Pengaturan
        </button>
    </div>
</form>

<!-- QRIS Upload (separate form with enctype) -->
<div class="mt-6">
    <div class="card-flat max-w-lg">
        <h3 class="font-heading font-semibold text-sm mb-3">Upload / Ganti QRIS</h3>
        <form action="/admin/settings/qris" method="POST" enctype="multipart/form-data" data-protect-submit>
            <?= Helper::csrfField() ?>
            <div class="mb-3">
                <input type="file" name="qris_image" accept="image/jpeg,image/png,image/webp" class="form-input" required>
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WebP. Maks 3MB. File akan disimpan sebagai <code>qrisrzdkstore.png</code></p>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Upload QRIS
            </button>
        </form>
    </div>
</div>
