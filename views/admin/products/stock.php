<?php
/**
 * Admin Stock Management View
 */
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="/admin/products" class="hover:text-primary transition-colors">Produk</a>
            <span>/</span>
            <a href="/admin/products/<?= (int) $variant['product_id'] ?>/variants" class="hover:text-primary transition-colors"><?= Helper::e($variant['product_name']) ?></a>
            <span>/</span>
            <span>Stok: <?= Helper::e($variant['name']) ?></span>
        </div>
        <h1 class="text-2xl font-heading font-bold">Stok — <?= Helper::e($variant['name']) ?></h1>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-value text-green-400"><?= $availableCount ?></div>
        <div class="stat-label">Tersedia</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-gray-400"><?= $soldCount ?></div>
        <div class="stat-label">Terjual</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $availableCount + $soldCount ?></div>
        <div class="stat-label">Total Item</div>
    </div>
</div>

<!-- Add Stock Form -->
<div class="card-flat mb-6">
    <h3 class="font-heading font-semibold mb-3">Tambah Stok (Bulk Input)</h3>
    <p class="text-sm text-gray-400 mb-4">Masukkan data credential/kode, satu item per baris. Data akan dienkripsi secara otomatis.</p>
    
    <form action="/admin/stock/<?= (int) $variant['id'] ?>" method="POST" data-protect-submit>
        <?= Helper::csrfField() ?>

        <div class="mb-4">
            <textarea name="bulk_data" class="form-input font-mono text-sm" rows="6" placeholder="email1@gmail.com:password123&#10;email2@gmail.com:password456&#10;KODE-REDEEM-ABC123&#10;..." required></textarea>
            <p class="text-xs text-gray-400 mt-1">Format bebas, 1 item per baris. Contoh: email:password, kode redeem, atau data lainnya.</p>
        </div>

        <button type="submit" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Tambah ke Stok
        </button>
    </form>
</div>

<!-- Stock Items List -->
<div class="card-flat">
    <h3 class="font-heading font-semibold mb-4">Daftar Item Stok</h3>

    <?php if (empty($stockItems)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada item stok. Gunakan form di atas untuk menambahkan.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Data (Terenkripsi)</th>
                    <th>Status</th>
                    <th>Terjual ke</th>
                    <th>Tanggal Input</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stockItems as $i => $item): ?>
                <tr>
                    <td class="text-xs text-gray-400"><?= $i + 1 ?></td>
                    <td>
                        <code class="text-xs bg-gray-100 dark:bg-dark px-2 py-1 rounded"><?= Helper::truncate($item['data_content'], 40) ?></code>
                    </td>
                    <td>
                        <?php if ($item['is_sold']): ?>
                            <span class="px-2 py-0.5 rounded bg-gray-500/10 text-gray-400 text-xs">Terjual</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded bg-green-500/10 text-green-400 text-xs">Tersedia</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-xs">
                        <?php if ($item['is_sold'] && $item['order_number']): ?>
                            <a href="/admin/orders/<?= (int) $item['sold_to_order_id'] ?>" class="text-primary hover:text-primary-hover"><?= Helper::e($item['order_number']) ?></a>
                            <?php if ($item['sold_to_username']): ?>
                                <span class="text-gray-400">(<?= Helper::e($item['sold_to_username']) ?>)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-xs text-gray-400"><?= Helper::formatDate($item['created_at'], 'd M Y H:i') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
