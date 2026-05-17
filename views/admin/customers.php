<?php /** Admin Customers List View */ ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-heading font-bold">Customer</h1>
    <span class="text-sm text-gray-400"><?= count($customers) ?> customer</span>
</div>

<!-- Search & Filter -->
<div class="card-flat mb-4">
    <form method="GET" action="/admin/customers" class="flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="<?= Helper::e($search) ?>" class="form-input flex-1 min-w-[200px]" placeholder="Cari username, email, atau nama...">
        <select name="status" class="form-input w-auto" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            <option value="banned" <?= $statusFilter === 'banned' ? 'selected' : '' ?>>Banned</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
    </form>
</div>

<!-- Customers Table -->
<div class="card-flat">
    <?php if (empty($customers)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Tidak ada customer ditemukan.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Nama</th>
                    <th>Orders</th>
                    <th>Total Belanja</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td class="font-medium text-sm"><?= Helper::e($c['username']) ?></td>
                    <td class="text-sm text-gray-400"><?= Helper::e($c['email']) ?></td>
                    <td class="text-sm"><?= Helper::e($c['full_name'] ?? '-') ?></td>
                    <td class="text-sm"><?= (int) $c['total_orders'] ?></td>
                    <td class="text-sm font-medium"><?= Helper::formatPrice($c['total_spend']) ?></td>
                    <td><?= Helper::statusBadge($c['status']) ?></td>
                    <td class="text-xs text-gray-400"><?= Helper::formatDate($c['created_at']) ?></td>
                    <td>
                        <a href="/admin/customers/<?= (int) $c['id'] ?>" class="text-xs text-primary hover:text-primary-hover transition-colors">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
