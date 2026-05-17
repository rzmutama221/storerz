<?php /** Admin Order Detail View */ ?>

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="/admin/orders" class="hover:text-primary transition-colors">Order</a>
    <span>/</span>
    <span><?= Helper::e($order['order_number']) ?></span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Main Info (Left 2/3) -->
    <div class="lg:col-span-2 space-y-4">
        <!-- Order Header -->
        <div class="card-flat">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-xl font-heading font-bold"><?= Helper::e($order['order_number']) ?></h1>
                <?= Helper::statusBadge($order['status']) ?>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div><span class="text-gray-400 block">Produk</span><span class="font-medium"><?= Helper::e($order['product_name']) ?></span></div>
                <div><span class="text-gray-400 block">Varian</span><span class="font-medium"><?= Helper::e($order['variant_name']) ?></span></div>
                <div><span class="text-gray-400 block">Durasi</span><span class="font-medium"><?= (int) $order['duration_days'] ?> hari</span></div>
                <div><span class="text-gray-400 block">Harga Asli</span><span class="font-medium"><?= Helper::formatPrice($order['original_price']) ?></span></div>
                <div><span class="text-gray-400 block">Diskon</span><span class="font-medium text-green-500"><?= $order['discount_amount'] > 0 ? '-' . Helper::formatPrice($order['discount_amount']) : '-' ?></span></div>
                <div><span class="text-gray-400 block">Total Bayar</span><span class="font-bold text-primary text-lg"><?= Helper::formatPrice($order['final_price']) ?></span></div>
                <div><span class="text-gray-400 block">Payment</span><span class="font-medium capitalize"><?= str_replace('_', ' ', $order['payment_method']) ?></span></div>
                <div><span class="text-gray-400 block">Dibuat</span><span class="font-medium"><?= Helper::formatDate($order['created_at'], 'd M Y H:i') ?></span></div>
                <?php if ($order['payment_deadline']): ?>
                <div><span class="text-gray-400 block">Deadline</span><span class="font-medium"><?= Helper::formatDate($order['payment_deadline'], 'd M Y H:i') ?></span></div>
                <?php endif; ?>
            </div>
            <?php if ($order['customer_notes']): ?>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-dark-border">
                <span class="text-xs text-gray-400">Catatan Customer:</span>
                <p class="text-sm mt-1"><?= nl2br(Helper::e($order['customer_notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Proof -->
        <?php if ($order['payment_proof_path']): ?>
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Bukti Pembayaran</h3>
            <img src="/<?= Helper::e($order['payment_proof_path']) ?>" alt="Bukti Bayar" class="max-w-sm rounded-lg border border-gray-200 dark:border-dark-border">
        </div>
        <?php endif; ?>

        <!-- Fulfillment Data (if exists) -->
        <?php if ($fulfillmentData): ?>
        <div class="card-flat border-green-500/30">
            <h3 class="font-heading font-semibold text-sm mb-3 text-green-500">Data Fulfillment</h3>
            <div class="p-3 bg-gray-50 dark:bg-dark rounded-lg font-mono text-sm whitespace-pre-wrap"><?= nl2br(Helper::e($fulfillmentData)) ?></div>
            <?php if ($order['fulfillment_notes']): ?>
            <p class="text-sm text-gray-400 mt-2">Catatan: <?= Helper::e($order['fulfillment_notes']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Action Forms based on Status -->
        <?php if ($order['status'] === 'pending_approval'): ?>
        <div class="card-flat space-y-3">
            <h3 class="font-heading font-semibold text-sm">Tindakan</h3>
            <div class="flex gap-3">
                <form action="/admin/orders/<?= (int) $order['id'] ?>/approve" method="POST">
                    <?= Helper::csrfField() ?>
                    <button type="submit" class="btn btn-primary btn-sm">Approve Order</button>
                </form>
                <form action="/admin/orders/<?= (int) $order['id'] ?>/reject" method="POST" class="flex-1">
                    <?= Helper::csrfField() ?>
                    <div class="flex gap-2">
                        <input type="text" name="rejection_reason" class="form-input flex-1" placeholder="Alasan reject (opsional)">
                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if (in_array($order['status'], ['approved', 'awaiting_payment']) && $order['payment_proof_path']): ?>
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Verifikasi Pembayaran</h3>
            <div class="flex gap-3">
                <form action="/admin/orders/<?= (int) $order['id'] ?>/verify-payment" method="POST">
                    <?= Helper::csrfField() ?>
                    <input type="hidden" name="action" value="confirm">
                    <button type="submit" class="btn btn-primary btn-sm">Konfirmasi Pembayaran</button>
                </form>
                <form action="/admin/orders/<?= (int) $order['id'] ?>/verify-payment" method="POST">
                    <?= Helper::csrfField() ?>
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger btn-sm">Tolak Bukti Bayar</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($order['status'] === 'paid'): ?>
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Fulfill Order</h3>
            <form action="/admin/orders/<?= (int) $order['id'] ?>/fulfill" method="POST" data-protect-submit>
                <?= Helper::csrfField() ?>
                <?php if ($order['fulfillment_mode'] === 'auto_stock'): ?>
                    <p class="text-sm text-gray-400 mb-3">Mode: <strong>Auto Stock</strong> — Sistem akan mengambil item dari stok secara otomatis.</p>
                    <button type="submit" class="btn btn-primary btn-sm">Assign dari Stok</button>
                <?php else: ?>
                    <p class="text-sm text-gray-400 mb-3">Mode: <strong>Manual</strong> — Masukkan data produk/credential yang akan dikirim ke customer.</p>
                    <div class="mb-3">
                        <textarea name="fulfillment_data" class="form-input font-mono text-sm" rows="4" placeholder="Email: xxx@gmail.com&#10;Password: xxx&#10;Profile: Nama Profile&#10;..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="fulfillment_notes" class="form-input" placeholder="Instruksi tambahan untuk customer (opsional)">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan & Fulfill</button>
                <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($order['status'] === 'processing'): ?>
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Selesaikan Order</h3>
            <p class="text-sm text-gray-400 mb-3">Data sudah diinput. Klik untuk menyelesaikan order, mengirim email notifikasi ke customer, dan mencatat transaksi.</p>
            <form action="/admin/orders/<?= (int) $order['id'] ?>/complete" method="POST">
                <?= Helper::csrfField() ?>
                <button type="submit" class="btn btn-primary btn-sm">Selesaikan Order</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar (Right 1/3) -->
    <div class="space-y-4">
        <!-- Customer Info -->
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Customer</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-400">Username:</span> <span class="font-medium"><?= Helper::e($order['username']) ?></span></div>
                <div><span class="text-gray-400">Email:</span> <span class="font-medium"><?= Helper::e($order['customer_email']) ?></span></div>
                <?php if ($order['customer_phone']): ?>
                <div><span class="text-gray-400">Phone:</span> <span class="font-medium"><?= Helper::e($order['customer_phone']) ?></span></div>
                <?php endif; ?>
                <?php if ($order['customer_name']): ?>
                <div><span class="text-gray-400">Nama:</span> <span class="font-medium"><?= Helper::e($order['customer_name']) ?></span></div>
                <?php endif; ?>
            </div>
            <a href="/admin/customers/<?= (int) $order['user_id'] ?>" class="text-xs text-primary hover:text-primary-hover mt-3 inline-block transition-colors">Lihat Profil &rarr;</a>
        </div>

        <!-- Timeline -->
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Timeline</h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></span><div><p>Order dibuat</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['created_at'], 'd M Y H:i') ?></p></div></div>
                <?php if ($order['approved_at']): ?><div class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></span><div><p>Approved</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['approved_at'], 'd M Y H:i') ?></p></div></div><?php endif; ?>
                <?php if ($order['paid_at']): ?><div class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></span><div><p>Payment verified</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['paid_at'], 'd M Y H:i') ?></p></div></div><?php endif; ?>
                <?php if ($order['completed_at']): ?><div class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></span><div><p>Completed</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['completed_at'], 'd M Y H:i') ?></p></div></div><?php endif; ?>
                <?php if ($order['active_until']): ?><div class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 shrink-0"></span><div><p>Aktif hingga</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['active_until'], 'd M Y') ?></p></div></div><?php endif; ?>
                <?php if ($order['status'] === 'rejected'): ?><div class="flex items-start gap-2"><span class="w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0"></span><div><p class="text-red-500">Rejected</p><p class="text-xs text-gray-400"><?= Helper::e($order['rejection_reason']) ?></p></div></div><?php endif; ?>
            </div>
        </div>

        <!-- Admin Notes -->
        <div class="card-flat">
            <h3 class="font-heading font-semibold text-sm mb-3">Catatan Admin</h3>
            <p class="text-sm text-gray-400"><?= $order['admin_notes'] ? nl2br(Helper::e($order['admin_notes'])) : 'Tidak ada catatan.' ?></p>
        </div>
    </div>
</div>
