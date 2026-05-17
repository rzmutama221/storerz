<?php /** Admin Warranties List View */ ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-heading font-bold">Klaim Garansi</h1>
</div>

<!-- Filter -->
<div class="flex flex-wrap gap-2 mb-4">
    <a href="/admin/warranties" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= !$statusFilter ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">Semua</a>
    <a href="/admin/warranties?status=submitted" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'submitted' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">Baru</a>
    <a href="/admin/warranties?status=reviewing" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'reviewing' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">Reviewing</a>
    <a href="/admin/warranties?status=resolved" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'resolved' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">Resolved</a>
</div>

<!-- Warranties Table -->
<div class="card-flat">
    <?php if (empty($warranties)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Tidak ada klaim garansi<?= $statusFilter ? ' dengan status ini' : '' ?>.</p>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($warranties as $w): ?>
        <div class="p-4 rounded-lg border border-gray-200 dark:border-dark-border">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    <p class="font-medium text-sm"><?= Helper::e($w['product_name']) ?> — <?= Helper::e($w['variant_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= Helper::e($w['username']) ?> &middot; <?= Helper::e($w['order_number']) ?> &middot; <?= Helper::timeAgo($w['created_at']) ?></p>
                </div>
                <?= Helper::statusBadge($w['status']) ?>
            </div>
            <div class="mb-3">
                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-dark text-xs capitalize"><?= str_replace('_', ' ', $w['issue_type']) ?></span>
                <p class="text-sm mt-2"><?= nl2br(Helper::e($w['description'])) ?></p>
                <?php if ($w['screenshot_path']): ?>
                    <a href="/<?= Helper::e($w['screenshot_path']) ?>" target="_blank" class="text-xs text-primary mt-1 inline-block">Lihat Screenshot</a>
                <?php endif; ?>
            </div>
            <?php if ($w['admin_response']): ?>
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-dark-border">
                <p class="text-xs text-gray-400">Respon Admin:</p>
                <p class="text-sm mt-1"><?= nl2br(Helper::e($w['admin_response'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if (in_array($w['status'], ['submitted', 'reviewing'])): ?>
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-dark-border">
                <form action="/admin/warranties/<?= (int) $w['id'] ?>/respond" method="POST" class="space-y-2">
                    <?= Helper::csrfField() ?>
                    <textarea name="admin_response" class="form-input text-sm" rows="2" placeholder="Respon/solusi..." required></textarea>
                    <div class="flex gap-2">
                        <button type="submit" name="action" value="resolve" class="btn btn-primary btn-sm">Resolve</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Tolak</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
