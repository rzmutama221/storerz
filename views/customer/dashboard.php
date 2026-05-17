<?php /** Customer Dashboard View */ ?>

<div class="mb-6">
    <h1 class="text-2xl font-heading font-bold">Selamat Datang, <?= Helper::e(Auth::user()['full_name'] ?? Auth::user()['username']) ?>!</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Berikut ringkasan akun Anda.</p>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-value"><?= $totalOrders ?></div>
        <div class="stat-label">Total Order</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-lg"><?= Helper::formatPrice($totalSpend) ?></div>
        <div class="stat-label">Total Belanja</div>
    </div>
</div>

<!-- Announcements -->
<?php if (!empty($announcements)): ?>
<div class="mb-6 space-y-3">
    <h2 class="font-heading font-semibold text-sm">Pengumuman</h2>
    <?php foreach ($announcements as $ann): ?>
    <div class="px-4 py-3 rounded-lg border <?= $ann['priority'] === 'urgent' ? 'border-red-500/30 bg-red-500/5' : ($ann['priority'] === 'warning' ? 'border-yellow-500/30 bg-yellow-500/5' : 'border-blue-500/30 bg-blue-500/5') ?>">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-sm font-medium"><?= Helper::e($ann['title']) ?></p>
                <p class="text-xs text-gray-400 mt-0.5"><?= Helper::e($ann['product_name']) ?> &middot; <?= Helper::timeAgo($ann['published_at']) ?></p>
            </div>
            <span class="shrink-0 px-2 py-0.5 rounded text-xs font-medium capitalize <?= $ann['priority'] === 'urgent' ? 'bg-red-500/10 text-red-400' : ($ann['priority'] === 'warning' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-blue-500/10 text-blue-400') ?>"><?= $ann['priority'] ?></span>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2"><?= nl2br(Helper::e(Helper::truncate($ann['content'], 200))) ?></p>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Active Products -->
<div class="card-flat mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-heading font-semibold">Produk Aktif</h2>
        <a href="/dashboard/accounts" class="text-sm text-primary hover:text-primary-hover transition-colors">Lihat Semua</a>
    </div>
    <?php if (empty($activeProducts)): ?>
        <p class="text-sm text-gray-400 text-center py-6">Belum ada produk aktif. <a href="/dashboard/products" class="text-primary hover:text-primary-hover">Belanja sekarang</a></p>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($activeProducts as $ap): ?>
        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-dark">
            <div class="w-10 h-10 rounded-lg bg-white dark:bg-dark-card flex items-center justify-center overflow-hidden shrink-0 border border-gray-200 dark:border-dark-border">
                <?php if ($ap['logo_path']): ?>
                    <img src="/<?= Helper::e($ap['logo_path']) ?>" alt="" class="w-8 h-8 object-contain">
                <?php else: ?>
                    <span class="text-xs font-bold text-primary"><?= strtoupper(substr($ap['product_name'], 0, 2)) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?= Helper::e($ap['product_name']) ?></p>
                <p class="text-xs text-gray-400"><?= Helper::e($ap['variant_name']) ?></p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs text-gray-400">Expired</p>
                <p class="text-sm font-medium <?= strtotime($ap['active_until']) < strtotime('+3 days') ? 'text-yellow-500' : 'text-green-500' ?>"><?= Helper::formatDate($ap['active_until']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Orders -->
<div class="card-flat">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-heading font-semibold">Order Terakhir</h2>
        <a href="/dashboard/orders" class="text-sm text-primary hover:text-primary-hover transition-colors">Semua Transaksi</a>
    </div>
    <?php if (empty($recentOrders)): ?>
        <p class="text-sm text-gray-400 text-center py-6">Belum ada transaksi.</p>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($recentOrders as $ro): ?>
        <a href="/dashboard/orders/<?= (int) $ro['id'] ?>" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-dark transition-colors">
            <div>
                <p class="text-sm font-medium"><?= Helper::e($ro['product_name']) ?></p>
                <p class="text-xs text-gray-400"><?= Helper::e($ro['order_number']) ?> &middot; <?= Helper::timeAgo($ro['created_at']) ?></p>
            </div>
            <div class="text-right">
                <?= Helper::statusBadge($ro['status']) ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
