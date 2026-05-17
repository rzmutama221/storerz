<?php /** Admin Finance Dashboard View */ ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-heading font-bold">Keuangan</h1>
    <div class="flex items-center gap-3">
        <!-- Month Selector -->
        <form method="GET" action="/admin/finance" class="flex items-center gap-2">
            <input type="month" name="month" value="<?= Helper::e($month) ?>" class="form-input w-auto" onchange="this.form.submit()">
        </form>
        <a href="/admin/finance/export?month=<?= Helper::e($month) ?>" class="btn btn-secondary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Export CSV
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-value text-lg text-primary"><?= Helper::formatPrice($monthRevenue) ?></div>
        <div class="stat-label">Revenue Bulan Ini</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-lg text-red-400"><?= Helper::formatPrice($monthRefunds) ?></div>
        <div class="stat-label">Refund</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $monthOrderCount ?></div>
        <div class="stat-label">Transaksi Selesai</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-lg"><?= Helper::formatPrice($avgOrderValue) ?></div>
        <div class="stat-label">Rata-rata Order</div>
    </div>
</div>

<!-- Daily Revenue Chart -->
<div class="card-flat mb-6">
    <h2 class="font-heading font-semibold text-sm mb-4">Revenue Harian</h2>
    <?php if (empty($dailyData)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada transaksi bulan ini.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <div class="flex items-end gap-1 h-40 min-w-[400px]">
            <?php
            $maxRevenue = max(array_column($dailyData, 'daily_revenue'));
            foreach ($dailyData as $day):
                $height = $maxRevenue > 0 ? ($day['daily_revenue'] / $maxRevenue) * 100 : 0;
                $dayNum = date('d', strtotime($day['date']));
            ?>
            <div class="flex-1 flex flex-col items-center gap-1 group" title="<?= $dayNum ?> - <?= Helper::formatPrice($day['daily_revenue']) ?>">
                <div class="w-full bg-primary/80 hover:bg-primary rounded-t transition-colors" style="height: <?= max(4, $height) ?>%"></div>
                <span class="text-[9px] text-gray-400 group-hover:text-primary"><?= $dayNum ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Revenue by Category -->
    <div class="card-flat">
        <h2 class="font-heading font-semibold text-sm mb-4">Revenue per Kategori</h2>
        <?php if (empty($revenueByCategory)): ?>
            <p class="text-sm text-gray-400 text-center py-6">Belum ada data.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($revenueByCategory as $cat): ?>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium"><?= Helper::e($cat['category_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= (int) $cat['order_count'] ?> transaksi</p>
                </div>
                <span class="font-medium text-sm text-primary"><?= Helper::formatPrice($cat['total_revenue']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Products -->
    <div class="card-flat">
        <h2 class="font-heading font-semibold text-sm mb-4">Produk Terlaris</h2>
        <?php if (empty($topProducts)): ?>
            <p class="text-sm text-gray-400 text-center py-6">Belum ada data.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($topProducts as $i => $tp): ?>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary shrink-0"><?= $i + 1 ?></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate"><?= Helper::e($tp['product_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= Helper::e($tp['variant_name']) ?> &middot; <?= (int) $tp['order_count'] ?>x</p>
                </div>
                <span class="text-sm font-medium text-primary shrink-0"><?= Helper::formatPrice($tp['total_revenue']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Transactions Log -->
<div class="card-flat">
    <h2 class="font-heading font-semibold text-sm mb-4">Log Transaksi</h2>
    <?php if (empty($transactions)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada transaksi bulan ini.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $t): ?>
                <tr>
                    <td class="text-sm text-gray-400"><?= Helper::formatDate($t['recorded_at'], 'd M H:i') ?></td>
                    <td>
                        <?php if ($t['type'] === 'income'): ?>
                            <span class="px-2 py-0.5 rounded bg-green-500/10 text-green-400 text-xs">Income</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded bg-red-500/10 text-red-400 text-xs">Refund</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="/admin/orders/<?= (int) $t['order_id'] ?>" class="text-xs text-primary hover:text-primary-hover"><?= Helper::e($t['order_number']) ?></a></td>
                    <td class="text-sm"><?= Helper::e($t['username']) ?></td>
                    <td class="text-sm"><?= Helper::e($t['product_name']) ?></td>
                    <td class="text-sm font-medium <?= $t['type'] === 'income' ? 'text-green-500' : 'text-red-500' ?>">
                        <?= $t['type'] === 'income' ? '+' : '-' ?><?= Helper::formatPrice($t['amount']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
