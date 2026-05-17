<?php /** Customer Payment Page */ ?>

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="/dashboard/orders" class="hover:text-primary transition-colors">Transaksi</a>
    <span>/</span>
    <a href="/dashboard/orders/<?= (int) $order['id'] ?>" class="hover:text-primary transition-colors"><?= Helper::e($order['order_number']) ?></a>
    <span>/</span>
    <span>Pembayaran</span>
</div>

<div class="max-w-lg mx-auto">
    <!-- Order Summary -->
    <div class="card-flat mb-4">
        <h2 class="font-heading font-semibold mb-3">Ringkasan Order</h2>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-400">Produk</span><span class="font-medium"><?= Helper::e($order['product_name']) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-400">Varian</span><span><?= Helper::e($order['variant_name']) ?></span></div>
            <div class="flex justify-between border-t border-gray-200 dark:border-dark-border pt-2 mt-2">
                <span class="font-semibold">Total Bayar</span>
                <span class="text-xl font-bold text-primary"><?= Helper::formatPrice($order['final_price']) ?></span>
            </div>
        </div>
    </div>

    <!-- Payment Deadline -->
    <?php if ($order['payment_deadline']): ?>
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg px-4 py-3 mb-4 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 shrink-0"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <div>
            <p class="text-sm font-medium text-yellow-500">Batas Waktu Pembayaran</p>
            <p class="text-lg font-bold" data-countdown="<?= $order['payment_deadline'] ?>">--:--:--</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- QRIS Section -->
    <div class="card-flat mb-4 text-center">
        <h3 class="font-heading font-semibold mb-3">Scan QRIS untuk Pembayaran</h3>
        <div class="bg-white p-4 rounded-lg inline-block mb-3">
            <img src="/assets/img/qrisrzdkstore.png" alt="QRIS RZDK Store" class="w-56 h-56 object-contain mx-auto" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22224%22 height=%22224%22 fill=%22%23ccc%22><rect width=%22224%22 height=%22224%22 rx=%228%22/><text x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2214%22 fill=%22%23666%22>QRIS Not Found</text></svg>'">
        </div>
        <p class="text-sm text-gray-400">Scan menggunakan e-wallet atau m-banking</p>
        <p class="text-sm font-medium mt-1">Bayar tepat: <span class="text-primary font-bold"><?= Helper::formatPrice($order['final_price']) ?></span></p>
    </div>

    <!-- Upload Proof -->
    <div class="card-flat">
        <h3 class="font-heading font-semibold mb-3">Upload Bukti Pembayaran</h3>
        <form action="/dashboard/payment/<?= (int) $order['id'] ?>" method="POST" enctype="multipart/form-data" data-protect-submit>
            <?= Helper::csrfField() ?>
            <div class="mb-4">
                <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp" class="form-input" required>
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WebP. Maks 3MB. Screenshot bukti transfer/e-receipt.</p>
            </div>
            <button type="submit" class="btn btn-primary w-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                Upload Bukti Bayar
            </button>
        </form>
    </div>

    <!-- Note -->
    <p class="text-xs text-gray-400 text-center mt-4">
        Setelah upload, admin akan memverifikasi pembayaran Anda. Status order akan di-update otomatis.
    </p>
</div>
