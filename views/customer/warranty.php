<?php /** Customer Warranty Claims View */ ?>

<div class="mb-6">
    <h1 class="text-2xl font-heading font-bold">Garansi</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ajukan klaim garansi jika ada masalah dengan produk Anda</p>
</div>

<!-- Submit Claim Form -->
<?php if (!empty($eligibleOrders)): ?>
<div class="card-flat mb-6">
    <h2 class="font-heading font-semibold text-sm mb-4">Ajukan Klaim Baru</h2>
    <form action="/dashboard/warranty" method="POST" enctype="multipart/form-data" data-protect-submit>
        <?= Helper::csrfField() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <!-- Select Order -->
            <div>
                <label class="form-label">Produk Bermasalah <span class="text-red-500">*</span></label>
                <select name="order_id" class="form-input" required>
                    <option value="">Pilih produk...</option>
                    <?php foreach ($eligibleOrders as $eo): ?>
                    <option value="<?= (int) $eo['id'] ?>"><?= Helper::e($eo['product_name']) ?> — <?= Helper::e($eo['variant_name']) ?> (<?= Helper::e($eo['order_number']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Issue Type -->
            <div>
                <label class="form-label">Jenis Masalah <span class="text-red-500">*</span></label>
                <select name="issue_type" class="form-input" required>
                    <option value="">Pilih jenis masalah...</option>
                    <option value="login_error">Tidak bisa login / akses</option>
                    <option value="service_down">Layanan down / tidak berfungsi</option>
                    <option value="account_banned">Akun di-banned / suspend</option>
                    <option value="not_working">Fitur tidak bekerja</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="form-label">Deskripsi Masalah <span class="text-red-500">*</span></label>
            <textarea name="description" class="form-input" rows="3" placeholder="Jelaskan masalah yang Anda alami secara detail..." required minlength="10"></textarea>
        </div>

        <!-- Screenshot -->
        <div class="mb-4">
            <label class="form-label">Screenshot (opsional)</label>
            <input type="file" name="screenshot" accept="image/jpeg,image/png,image/webp" class="form-input">
            <p class="text-xs text-gray-400 mt-1">Screenshot error/masalah. Format: JPG, PNG, WebP. Maks 3MB.</p>
        </div>

        <button type="submit" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
            Ajukan Klaim
        </button>
    </form>
</div>
<?php else: ?>
<div class="card-flat mb-6 text-center py-6">
    <p class="text-sm text-gray-400">Tidak ada produk yang memenuhi syarat untuk klaim garansi saat ini.</p>
</div>
<?php endif; ?>

<!-- Claims History -->
<h2 class="font-heading font-semibold text-sm mb-3">Riwayat Klaim</h2>

<?php if (empty($claims)): ?>
    <div class="card-flat text-center py-8">
        <p class="text-sm text-gray-400">Belum ada klaim garansi.</p>
    </div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($claims as $claim): ?>
    <div class="card-flat">
        <div class="flex items-start justify-between gap-3 mb-2">
            <div>
                <p class="font-medium text-sm"><?= Helper::e($claim['product_name']) ?> — <?= Helper::e($claim['variant_name']) ?></p>
                <p class="text-xs text-gray-400"><?= Helper::e($claim['order_number']) ?> &middot; <?= Helper::timeAgo($claim['created_at']) ?></p>
            </div>
            <?= Helper::statusBadge($claim['status']) ?>
        </div>

        <div class="mb-2">
            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-dark text-xs capitalize"><?= str_replace('_', ' ', $claim['issue_type']) ?></span>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400"><?= nl2br(Helper::e($claim['description'])) ?></p>

        <?php if ($claim['screenshot_path']): ?>
        <a href="/<?= Helper::e($claim['screenshot_path']) ?>" target="_blank" class="text-xs text-primary mt-2 inline-block">Lihat Screenshot</a>
        <?php endif; ?>

        <?php if ($claim['admin_response']): ?>
        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-dark-border">
            <p class="text-xs text-gray-400 mb-1">Respon Admin:</p>
            <p class="text-sm"><?= nl2br(Helper::e($claim['admin_response'])) ?></p>
            <?php if ($claim['resolved_at']): ?>
            <p class="text-xs text-gray-400 mt-1">Resolved: <?= Helper::formatDate($claim['resolved_at'], 'd M Y H:i') ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
