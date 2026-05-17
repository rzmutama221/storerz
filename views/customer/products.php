<?php /** Customer Products Catalog View */ ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-heading font-bold">Produk</h1>
</div>

<!-- Search & Filter -->
<div class="card-flat mb-4">
    <form method="GET" action="/dashboard/products" class="flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="<?= Helper::e($search) ?>" class="form-input w-auto flex-1 min-w-[200px]" placeholder="Cari produk...">
        <select name="category" class="form-input w-auto" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= Helper::e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>><?= Helper::e($cat['name']) ?> (<?= (int) $cat['product_count'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <?php if ($categorySlug || $search): ?>
        <a href="/dashboard/products" class="text-xs text-gray-400 hover:text-primary transition-colors">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Products Grid -->
<?php if (empty($products)): ?>
    <div class="card-flat text-center py-12">
        <p class="text-gray-400">Tidak ada produk yang ditemukan.</p>
    </div>
<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($products as $product): ?>
    <a href="/dashboard/products/<?= Helper::e($product['slug']) ?>" class="card group hover:border-primary transition-all">
        <div class="flex items-start gap-3">
            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden shrink-0">
                <?php if ($product['logo_path']): ?>
                    <img src="/<?= Helper::e($product['logo_path']) ?>" alt="" class="w-10 h-10 object-contain">
                <?php else: ?>
                    <span class="text-sm font-bold text-primary"><?= strtoupper(substr($product['name'], 0, 2)) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-sm group-hover:text-primary transition-colors"><?= Helper::e($product['name']) ?></h3>
                <p class="text-xs text-gray-400 mt-0.5"><?= Helper::e($product['category_name']) ?></p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-sm font-semibold text-primary">Mulai <?= Helper::formatPrice($product['price_from']) ?></span>
                    <span class="text-xs text-gray-400">&middot; <?= (int) $product['variant_count'] ?> varian</span>
                </div>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
