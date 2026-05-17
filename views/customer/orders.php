<?php /** Customer Orders List View */ ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-heading font-bold">Transaksi Saya</h1>
</div>

<!-- Filter -->
<div class="flex flex-wrap gap-2 mb-4">
    <a href="/dashboard/orders" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= !$statusFilter ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">Semua</a>
    <a href="/dashboard/orders?status=pending_approval" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'pending_approval' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">Menunggu</a>
    <a href="/dashboard/orders?status=approved" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'approved' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">Bayar</a>
    <a href="/dashboard/orders?status=processing" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'processing' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">Diproses</a>
    <a href="/dashboard/orders?status=completed" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'completed' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">Selesai</a>
</div>

<!-- Orders List -->
<?php if (empty($orders)): ?>
    <div class="card-flat text-center py-12">
        <p class="text-gray-400 mb-4">Belum ada transaksi<?= $statusFilter ? ' dengan status ini' : '' ?>.</p>
        <a href="/dashboard/products" class="btn btn-primary btn-sm">Belanja Sekarang</a>
    </div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($orders as $order): ?>
    <a href="/dashboard/orders/<?= (int) $order['id'] ?>" class="card-flat block hover:border-primary transition-colors">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden shrink-0">
                <?php if ($order['logo_path']): ?>
                    <img src="/<?= Helper::e($order['logo_path']) ?>" alt="" class="w-8 h-8 object-contain">
                <?php else: ?>
                    <span class="text-xs font-bold text-primary"><?= strtoupper(substr($order['product_name'], 0, 2)) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-medium truncate"><?= Helper::e($order['product_name']) ?></p>
                    <?= Helper::statusBadge($order['status']) ?>
                </div>
                <p class="text-xs text-gray-400 mt-0.5"><?= Helper::e($order['order_number']) ?> &middot; <?= Helper::e($order['variant_name']) ?></p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-medium"><?= Helper::formatPrice($order['final_price']) ?></p>
                <p class="text-xs text-gray-400"><?= Helper::formatDate($order['created_at']) ?></p>
            </div>
        </div>
        <?php if (in_array($order['status'], ['approved', 'awaiting_payment'])): ?>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-dark-border flex items-center justify-between">
                <span class="text-xs text-yellow-500">Menunggu pembayaran</span>
                <span class="text-xs font-medium text-primary">Bayar Sekarang &rarr;</span>
            </div>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
