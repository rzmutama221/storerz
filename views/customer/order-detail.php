<?php /** Customer Order Detail View */ ?>

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="/dashboard/orders" class="hover:text-primary transition-colors">Transaksi</a>
    <span>/</span>
    <span><?= Helper::e($order['order_number']) ?></span>
</div>

<div class="max-w-2xl">
    <!-- Order Header -->
    <div class="card-flat mb-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-heading font-bold"><?= Helper::e($order['order_number']) ?></h1>
            <?= Helper::statusBadge($order['status']) ?>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-400">Produk</span>
                <p class="font-medium"><?= Helper::e($order['product_name']) ?></p>
            </div>
            <div>
                <span class="text-gray-400">Varian</span>
                <p class="font-medium"><?= Helper::e($order['variant_name']) ?></p>
            </div>
            <div>
                <span class="text-gray-400">Harga</span>
                <p class="font-medium"><?= Helper::formatPrice($order['original_price']) ?></p>
            </div>
            <div>
                <span class="text-gray-400">Diskon</span>
                <p class="font-medium <?= $order['discount_amount'] > 0 ? 'text-green-500' : '' ?>">
                    <?= $order['discount_amount'] > 0 ? '-' . Helper::formatPrice($order['discount_amount']) : '-' ?>
                    <?php if ($order['voucher_code']): ?><span class="text-xs text-gray-400">(<?= Helper::e($order['voucher_code']) ?>)</span><?php endif; ?>
                </p>
            </div>
            <div>
                <span class="text-gray-400">Total Bayar</span>
                <p class="font-bold text-primary text-lg"><?= Helper::formatPrice($order['final_price']) ?></p>
            </div>
            <div>
                <span class="text-gray-400">Tanggal Order</span>
                <p class="font-medium"><?= Helper::formatDate($order['created_at'], 'd M Y H:i') ?></p>
            </div>
        </div>

        <?php if ($order['customer_notes']): ?>
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-dark-border">
            <span class="text-xs text-gray-400">Catatan Anda:</span>
            <p class="text-sm mt-1"><?= nl2br(Helper::e($order['customer_notes'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Status Timeline -->
    <div class="card-flat mb-4">
        <h3 class="font-heading font-semibold text-sm mb-3">Status Order</h3>
        <div class="space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                <div><p class="text-sm">Order dibuat</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['created_at'], 'd M Y H:i') ?></p></div>
            </div>
            <?php if ($order['approved_at']): ?>
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                <div><p class="text-sm">Disetujui admin</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['approved_at'], 'd M Y H:i') ?></p></div>
            </div>
            <?php endif; ?>
            <?php if ($order['paid_at']): ?>
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                <div><p class="text-sm">Pembayaran dikonfirmasi</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['paid_at'], 'd M Y H:i') ?></p></div>
            </div>
            <?php endif; ?>
            <?php if ($order['completed_at']): ?>
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 shrink-0"></div>
                <div><p class="text-sm">Order selesai</p><p class="text-xs text-gray-400"><?= Helper::formatDate($order['completed_at'], 'd M Y H:i') ?></p></div>
            </div>
            <?php endif; ?>
            <?php if ($order['status'] === 'rejected'): ?>
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0"></div>
                <div><p class="text-sm text-red-500">Ditolak</p><p class="text-xs text-gray-400"><?= Helper::e($order['rejection_reason'] ?? 'Tidak ada alasan') ?></p></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Action -->
    <?php if (in_array($order['status'], ['approved', 'awaiting_payment']) && (!$order['payment_deadline'] || strtotime($order['payment_deadline']) > time())): ?>
    <div class="card-flat border-primary/30 mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium">Silakan lakukan pembayaran</p>
                <?php if ($order['payment_deadline']): ?>
                <p class="text-xs text-gray-400">Batas waktu: <span class="text-yellow-500 font-medium" data-countdown="<?= $order['payment_deadline'] ?>"></span></p>
                <?php endif; ?>
            </div>
            <a href="/dashboard/payment/<?= (int) $order['id'] ?>" class="btn btn-primary btn-sm">Bayar</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Fulfillment Data (shown when completed) -->
    <?php if ($order['status'] === 'completed' && $fulfillmentData): ?>
    <div class="card-flat border-green-500/30 mb-4">
        <h3 class="font-heading font-semibold text-sm mb-3 text-green-500 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Data Produk Anda
        </h3>
        <div class="p-4 bg-gray-50 dark:bg-dark rounded-lg font-mono text-sm whitespace-pre-wrap break-all"><?= nl2br(Helper::e($fulfillmentData)) ?></div>
        <button onclick="RZDKStore.Clipboard.copy('<?= addslashes($fulfillmentData) ?>', this)" class="btn btn-secondary btn-sm mt-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
            Copy
        </button>

        <?php if ($order['fulfillment_notes']): ?>
        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-dark-border">
            <span class="text-xs text-gray-400">Instruksi:</span>
            <p class="text-sm mt-1"><?= nl2br(Helper::e($order['fulfillment_notes'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($order['active_until']): ?>
        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-dark-border flex items-center justify-between">
            <span class="text-xs text-gray-400">Masa aktif hingga:</span>
            <span class="text-sm font-medium <?= strtotime($order['active_until']) < strtotime('+3 days') ? 'text-yellow-500' : 'text-green-500' ?>"><?= Helper::formatDate($order['active_until'], 'd M Y') ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="flex flex-wrap gap-3">
        <a href="/dashboard/orders" class="btn btn-secondary btn-sm">&larr; Kembali</a>
        <?php if ($order['status'] === 'completed' && $order['active_until'] && strtotime($order['active_until']) > time()): ?>
        <a href="/dashboard/warranty" class="btn btn-secondary btn-sm">Klaim Garansi</a>
        <?php endif; ?>
    </div>
</div>
