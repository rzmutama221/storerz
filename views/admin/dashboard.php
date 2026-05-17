<?php
/**
 * Admin Dashboard View
 */
?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-heading font-bold">Dashboard</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Selamat datang, <?= Helper::e(Auth::user()['username'] ?? 'Admin') ?>. Berikut ringkasan hari ini.</p>
</div>

<!-- Stats Grid: Today -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Today Orders -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <span class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/><path d="m12 12 4 10 1.7-4.3L22 16Z"/></svg>
            </span>
        </div>
        <div class="stat-value"><?= (int) $todayOrders ?></div>
        <div class="stat-label">Order Hari Ini</div>
    </div>

    <!-- Today Revenue -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <span class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
        </div>
        <div class="stat-value text-lg"><?= Helper::formatPrice($todayRevenue) ?></div>
        <div class="stat-label">Revenue Hari Ini</div>
    </div>

    <!-- Pending Orders -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <span class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
        </div>
        <div class="stat-value"><?= (int) $pendingOrders ?></div>
        <div class="stat-label">Menunggu Approval</div>
    </div>

    <!-- Awaiting Payment -->
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <span class="w-9 h-9 rounded-lg bg-orange-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            </span>
        </div>
        <div class="stat-value"><?= (int) $awaitingPayment ?></div>
        <div class="stat-label">Menunggu Payment</div>
    </div>
</div>

<!-- Stats Grid: Monthly -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-value text-lg text-primary"><?= Helper::formatPrice($monthRevenue) ?></div>
        <div class="stat-label">Revenue Bulan Ini</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int) $monthOrders ?></div>
        <div class="stat-label">Order Selesai (Bulan Ini)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int) $newCustomers ?></div>
        <div class="stat-label">Customer Baru (Bulan Ini)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int) $totalCustomers ?></div>
        <div class="stat-label">Total Customer</div>
    </div>
</div>

<!-- Alerts Section -->
<?php if (!empty($lowStock) || !empty($expiringSlots) || $pendingWarranties > 0): ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    
    <!-- Low Stock Alert -->
    <?php if (!empty($lowStock)): ?>
    <div class="card-flat border-yellow-500/30">
        <h3 class="font-heading font-semibold text-sm mb-3 flex items-center gap-2 text-yellow-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
            Stok Menipis
        </h3>
        <ul class="space-y-2">
            <?php foreach ($lowStock as $item): ?>
            <li class="flex items-center justify-between text-sm">
                <span class="truncate"><?= Helper::e($item['product_name']) ?> — <?= Helper::e($item['name']) ?></span>
                <span class="shrink-0 ml-2 px-2 py-0.5 rounded bg-yellow-500/10 text-yellow-500 text-xs font-medium"><?= (int) $item['stock_count'] ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Expiring Slots -->
    <?php if (!empty($expiringSlots)): ?>
    <div class="card-flat border-orange-500/30">
        <h3 class="font-heading font-semibold text-sm mb-3 flex items-center gap-2 text-orange-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Slot Segera Expired
        </h3>
        <ul class="space-y-2">
            <?php foreach ($expiringSlots as $slot): ?>
            <li class="text-sm">
                <div class="flex items-center justify-between">
                    <span class="truncate capitalize"><?= Helper::e($slot['type']) ?> — <?= Helper::e($slot['slot_name']) ?></span>
                    <span class="shrink-0 ml-2 text-xs text-orange-400"><?= Helper::formatDate($slot['expired_date']) ?></span>
                </div>
                <div class="text-xs text-gray-400"><?= Helper::e($slot['customer_name']) ?></div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Pending Warranties -->
    <?php if ($pendingWarranties > 0): ?>
    <div class="card-flat border-red-500/30">
        <h3 class="font-heading font-semibold text-sm mb-3 flex items-center gap-2 text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
            Klaim Garansi
        </h3>
        <p class="text-sm text-gray-400">Ada <strong class="text-white"><?= (int) $pendingWarranties ?></strong> klaim garansi yang menunggu respon.</p>
        <a href="/admin/warranties" class="inline-flex items-center gap-1 text-sm text-primary hover:text-primary-hover mt-2 transition-colors">
            Lihat Semua
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="flex flex-wrap gap-3 mb-6">
    <?php if ($pendingOrders > 0): ?>
    <a href="/admin/orders?status=pending_approval" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Approve Order (<?= (int) $pendingOrders ?>)
    </a>
    <?php endif; ?>
    <a href="/admin/products/create" class="btn btn-secondary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Tambah Produk
    </a>
    <a href="/admin/orders" class="btn btn-secondary btn-sm">Semua Order</a>
</div>

<!-- Recent Orders Table -->
<div class="card-flat">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-heading font-semibold">Order Terbaru</h3>
        <a href="/admin/orders" class="text-sm text-primary hover:text-primary-hover transition-colors">Lihat Semua</a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada order.</p>
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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td>
                        <a href="/admin/orders/<?= (int) $order['id'] ?>" class="text-primary hover:text-primary-hover font-medium"><?= Helper::e($order['order_number']) ?></a>
                    </td>
                    <td><?= Helper::e($order['username']) ?></td>
                    <td>
                        <div class="text-sm"><?= Helper::e($order['product_name']) ?></div>
                        <div class="text-xs text-gray-400"><?= Helper::e($order['variant_name']) ?></div>
                    </td>
                    <td class="font-medium"><?= Helper::formatPrice($order['final_price']) ?></td>
                    <td><?= Helper::statusBadge($order['status']) ?></td>
                    <td class="text-sm text-gray-400"><?= Helper::timeAgo($order['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
