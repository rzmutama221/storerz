<?php /** Admin Orders List View */ ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-heading font-bold">Manajemen Order</h1>
    <span class="text-sm text-gray-400"><?= $total ?> order</span>
</div>

<!-- Status Tabs -->
<div class="flex flex-wrap gap-2 mb-4">
    <a href="/admin/orders" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= !$statusFilter ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">Semua</a>
    <a href="/admin/orders?status=pending_approval" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'pending_approval' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">
        Pending <?php if (!empty($counts['pending_approval'])): ?><span class="ml-1 px-1.5 py-0.5 rounded-full bg-yellow-500 text-white text-[10px]"><?= $counts['pending_approval'] ?></span><?php endif; ?>
    </a>
    <a href="/admin/orders?status=awaiting_payment" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'awaiting_payment' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">Awaiting Payment</a>
    <a href="/admin/orders?status=paid" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'paid' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">
        Paid <?php if (!empty($counts['paid'])): ?><span class="ml-1 px-1.5 py-0.5 rounded-full bg-green-500 text-white text-[10px]"><?= $counts['paid'] ?></span><?php endif; ?>
    </a>
    <a href="/admin/orders?status=processing" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'processing' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">Processing</a>
    <a href="/admin/orders?status=completed" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?= $statusFilter === 'completed' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card' ?>">Completed</a>
</div>

<!-- Search -->
<div class="card-flat mb-4">
    <form method="GET" action="/admin/orders" class="flex items-center gap-3">
        <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= Helper::e($statusFilter) ?>"><?php endif; ?>
        <input type="text" name="q" value="<?= Helper::e($search) ?>" class="form-input flex-1" placeholder="Cari order number atau username...">
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
    </form>
</div>

<!-- Orders Table -->
<div class="card-flat">
    <?php if (empty($orders)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Tidak ada order<?= $statusFilter ? ' dengan status ini' : '' ?>.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Customer</th>
                    <th>Produk</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="/admin/orders/<?= (int) $o['id'] ?>" class="text-primary hover:text-primary-hover font-medium text-sm"><?= Helper::e($o['order_number']) ?></a></td>
                    <td class="text-sm"><?= Helper::e($o['username']) ?></td>
                    <td>
                        <div class="text-sm"><?= Helper::e($o['product_name']) ?></div>
                        <div class="text-xs text-gray-400"><?= Helper::e($o['variant_name']) ?></div>
                    </td>
                    <td class="text-sm font-medium"><?= Helper::formatPrice($o['final_price']) ?></td>
                    <td><?= Helper::statusBadge($o['status']) ?></td>
                    <td class="text-sm text-gray-400"><?= Helper::timeAgo($o['created_at']) ?></td>
                    <td>
                        <a href="/admin/orders/<?= (int) $o['id'] ?>" class="text-xs text-primary hover:text-primary-hover transition-colors">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-dark-border">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="/admin/orders?page=<?= $i ?><?= $statusFilter ? '&status=' . $statusFilter : '' ?><?= $search ? '&q=' . urlencode($search) : '' ?>" class="px-3 py-1 rounded text-sm <?= $i === $page ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
