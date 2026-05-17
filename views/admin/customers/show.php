<?php /** Admin Customer Detail View */ ?>

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="/admin/customers" class="hover:text-primary transition-colors">Customer</a>
    <span>/</span>
    <span><?= Helper::e($customer['username']) ?></span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Customer Info (Left) -->
    <div class="space-y-4">
        <div class="card-flat">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary text-lg font-bold">
                    <?= strtoupper(substr($customer['username'], 0, 1)) ?>
                </div>
                <div>
                    <h2 class="font-heading font-semibold"><?= Helper::e($customer['username']) ?></h2>
                    <?= Helper::statusBadge($customer['status']) ?>
                </div>
            </div>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-400">Email:</span> <?= Helper::e($customer['email']) ?></div>
                <div><span class="text-gray-400">Nama:</span> <?= Helper::e($customer['full_name'] ?? '-') ?></div>
                <div><span class="text-gray-400">Phone:</span> <?= Helper::e($customer['phone'] ?? '-') ?></div>
                <div><span class="text-gray-400">Terdaftar:</span> <?= Helper::formatDate($customer['created_at'], 'd M Y H:i') ?></div>
                <div><span class="text-gray-400">Login terakhir:</span> <?= $customer['last_login_at'] ? Helper::formatDate($customer['last_login_at'], 'd M Y H:i') : '-' ?></div>
                <div><span class="text-gray-400">Verified:</span> <?= $customer['is_verified'] ? '<span class="text-green-400">Ya</span>' : '<span class="text-red-400">Belum</span>' ?></div>
            </div>
            <?php if ($customer['notes_admin']): ?>
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-dark-border">
                <p class="text-xs text-gray-400">Catatan Admin:</p>
                <p class="text-sm mt-1"><?= nl2br(Helper::e($customer['notes_admin'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="card-flat">
            <div class="grid grid-cols-3 gap-3 text-center">
                <div><div class="text-lg font-bold"><?= (int) $stats['total_orders'] ?></div><div class="text-xs text-gray-400">Order</div></div>
                <div><div class="text-lg font-bold"><?= (int) $stats['completed_orders'] ?></div><div class="text-xs text-gray-400">Selesai</div></div>
                <div><div class="text-lg font-bold text-primary"><?= Helper::formatPrice($stats['total_spend']) ?></div><div class="text-xs text-gray-400">Belanja</div></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Tindakan</h3>
            <?php if ($customer['status'] === 'active'): ?>
            <form action="/admin/customers/<?= (int) $customer['id'] ?>/suspend" method="POST">
                <?= Helper::csrfField() ?>
                <input type="text" name="reason" class="form-input mb-2" placeholder="Alasan suspend (opsional)">
                <button type="submit" class="btn btn-danger btn-sm w-full" data-confirm="Suspend customer ini?">Suspend</button>
            </form>
            <?php else: ?>
            <form action="/admin/customers/<?= (int) $customer['id'] ?>/activate" method="POST">
                <?= Helper::csrfField() ?>
                <button type="submit" class="btn btn-primary btn-sm w-full">Aktifkan Kembali</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Orders History (Right 2/3) -->
    <div class="lg:col-span-2">
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-4">Riwayat Order</h3>
            <?php if (empty($orders)): ?>
                <p class="text-sm text-gray-400 text-center py-6">Belum ada order.</p>
            <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No. Order</th>
                            <th>Produk</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><a href="/admin/orders/<?= (int) $o['id'] ?>" class="text-primary hover:text-primary-hover text-sm font-medium"><?= Helper::e($o['order_number']) ?></a></td>
                            <td>
                                <div class="text-sm"><?= Helper::e($o['product_name']) ?></div>
                                <div class="text-xs text-gray-400"><?= Helper::e($o['variant_name']) ?></div>
                            </td>
                            <td class="text-sm font-medium"><?= Helper::formatPrice($o['final_price']) ?></td>
                            <td><?= Helper::statusBadge($o['status']) ?></td>
                            <td class="text-xs text-gray-400"><?= Helper::formatDate($o['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
