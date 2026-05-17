<?php /** Customer Product Detail + Order Form */ ?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="/dashboard/products" class="hover:text-primary transition-colors">Produk</a>
    <span>/</span>
    <span><?= Helper::e($product['name']) ?></span>
</div>

<!-- Product Header -->
<div class="card-flat mb-6">
    <div class="flex items-start gap-4">
        <div class="w-16 h-16 rounded-xl bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden shrink-0">
            <?php if ($product['logo_path']): ?>
                <img src="/<?= Helper::e($product['logo_path']) ?>" alt="" class="w-12 h-12 object-contain">
            <?php else: ?>
                <span class="text-xl font-bold text-primary"><?= strtoupper(substr($product['name'], 0, 2)) ?></span>
            <?php endif; ?>
        </div>
        <div>
            <h1 class="text-xl font-heading font-bold"><?= Helper::e($product['name']) ?></h1>
            <p class="text-sm text-gray-400 mt-1"><?= Helper::e($product['category_name']) ?></p>
            <?php if ($product['description']): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-3"><?= nl2br(Helper::e($product['description'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Variants -->
<h2 class="font-heading font-semibold mb-3">Pilih Varian</h2>

<?php if (empty($variants)): ?>
    <div class="card-flat text-center py-8">
        <p class="text-gray-400">Tidak ada varian tersedia untuk produk ini.</p>
    </div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($variants as $v): ?>
    <div class="card-flat hover:border-primary transition-colors" x-data="{ showOrder: false }">
        <div class="flex items-center justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-sm"><?= Helper::e($v['name']) ?></span>
                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-dark text-xs capitalize"><?= str_replace('_', ' ', $v['type']) ?></span>
                    <?php if ($v['platform'] !== 'all'): ?>
                        <span class="px-2 py-0.5 rounded bg-yellow-500/10 text-yellow-500 text-xs capitalize"><?= $v['platform'] ?> only</span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                    <span><?= (int) $v['duration_days'] ?> hari</span>
                    <?php if ($v['warranty_days'] > 0): ?>
                        <span>Garansi <?= (int) $v['warranty_days'] ?> hari</span>
                    <?php endif; ?>
                    <?php if ($v['available_stock'] <= 0): ?>
                        <span class="text-red-400 font-medium">Stok Habis</span>
                    <?php elseif ($v['fulfillment_mode'] === 'auto_stock' && $v['available_stock'] <= 5): ?>
                        <span class="text-yellow-400">Sisa <?= (int) $v['available_stock'] ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($v['notes']): ?>
                    <p class="text-xs text-gray-400 mt-1"><?= Helper::e($v['notes']) ?></p>
                <?php endif; ?>
            </div>
            <div class="text-right shrink-0">
                <p class="text-lg font-bold text-primary"><?= Helper::formatPrice($v['price']) ?></p>
                <?php if ($v['available_stock'] > 0): ?>
                <button @click="showOrder = !showOrder" class="btn btn-primary btn-sm mt-2">
                    <span x-text="showOrder ? 'Batal' : 'Order'"></span>
                </button>
                <?php else: ?>
                <button disabled class="btn btn-sm opacity-50 cursor-not-allowed bg-gray-500 text-white mt-2">Habis</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Form (Hidden until clicked) -->
        <div x-show="showOrder" x-transition class="mt-4 pt-4 border-t border-gray-200 dark:border-dark-border">
            <form action="/dashboard/order" method="POST" data-protect-submit>
                <?= Helper::csrfField() ?>
                <input type="hidden" name="variant_id" value="<?= (int) $v['id'] ?>">

                <!-- Voucher -->
                <div class="mb-3">
                    <label class="form-label text-xs">Kode Voucher (opsional)</label>
                    <input type="text" name="voucher_code" class="form-input" placeholder="Masukkan kode voucher">
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label text-xs">Catatan (opsional)</label>
                    <textarea name="customer_notes" class="form-input" rows="2" placeholder="Contoh: Email untuk invite ChatGPT, preferensi nama profile, dll"></textarea>
                </div>

                <!-- Summary -->
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-dark mb-3">
                    <span class="text-sm">Total Pembayaran</span>
                    <span class="text-lg font-bold text-primary"><?= Helper::formatPrice($v['price']) ?></span>
                </div>

                <p class="text-xs text-gray-400 mb-3">Order akan menunggu approval admin sebelum Anda bisa melakukan pembayaran.</p>

                <button type="submit" class="btn btn-primary w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/><path d="m12 12 4 10 1.7-4.3L22 16Z"/></svg>
                    Buat Order
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
