<?php
/**
 * Admin Products List View
 */
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-heading font-bold">Produk</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola semua produk digital</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="/admin/categories" class="btn btn-secondary btn-sm">Kategori</a>
        <a href="/admin/products/create" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Tambah Produk
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card-flat mb-4">
    <form method="GET" action="/admin/products" class="flex flex-wrap items-center gap-3">
        <select name="category" class="form-input w-auto" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>><?= Helper::e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($categoryFilter): ?>
        <a href="/admin/products" class="text-xs text-gray-400 hover:text-primary transition-colors">Reset filter</a>
        <?php endif; ?>
        <span class="text-xs text-gray-400 ml-auto"><?= count($products) ?> produk</span>
    </form>
</div>

<!-- Products Table -->
<div class="card-flat">
    <?php if (empty($products)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada produk. Klik "Tambah Produk" untuk memulai.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Varian</th>
                    <th>Harga</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden shrink-0">
                                <?php if ($product['logo_path']): ?>
                                    <img src="/<?= Helper::e($product['logo_path']) ?>" alt="" class="w-7 h-7 object-contain">
                                <?php else: ?>
                                    <span class="text-xs font-bold text-primary"><?= strtoupper(substr($product['name'], 0, 2)) ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="font-medium text-sm"><?= Helper::e($product['name']) ?></div>
                                <div class="text-xs text-gray-400">/<?= Helper::e($product['slug']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm"><?= Helper::e($product['category_name']) ?></td>
                    <td>
                        <a href="/admin/products/<?= (int) $product['id'] ?>/variants" class="text-sm text-primary hover:text-primary-hover transition-colors">
                            <?= (int) $product['variant_count'] ?> varian
                        </a>
                    </td>
                    <td class="text-sm">
                        <?php if ($product['price_from']): ?>
                            <?= Helper::formatPrice($product['price_from']) ?>
                            <?php if ($product['price_to'] && $product['price_to'] != $product['price_from']): ?>
                                <span class="text-gray-400">—</span> <?= Helper::formatPrice($product['price_to']) ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['slot_type'] !== 'none'): ?>
                            <span class="px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 text-xs font-medium capitalize"><?= Helper::e($product['slot_type']) ?></span>
                        <?php else: ?>
                            <span class="text-xs text-gray-400">Normal</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['is_active']): ?>
                            <span class="status-dot online"></span>
                        <?php else: ?>
                            <span class="status-dot offline"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="/admin/products/<?= (int) $product['id'] ?>/variants" class="text-xs text-gray-400 hover:text-primary transition-colors" title="Varian">Varian</a>
                            <a href="/admin/products/<?= (int) $product['id'] ?>/edit" class="text-xs text-primary hover:text-primary-hover transition-colors">Edit</a>
                            <form action="/admin/products/<?= (int) $product['id'] ?>/delete" method="POST" class="inline" data-confirm="Hapus produk '<?= Helper::e($product['name']) ?>'? Semua varian dan stok juga akan dihapus.">
                                <?= Helper::csrfField() ?>
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400 transition-colors">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
