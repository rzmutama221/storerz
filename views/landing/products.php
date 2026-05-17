<?php /** Landing - Public Products Catalog */ ?>

<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-heading font-bold"><?= $currentCategory ? Helper::e($currentCategory['name']) : 'Semua Produk' ?></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Layanan premium dengan harga terjangkau</p>
        </div>

        <!-- Category Filter -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="/products" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= !$categorySlug ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">Semua</a>
            <?php foreach ($categories as $cat): ?>
            <a href="/products?category=<?= Helper::e($cat['slug']) ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $categorySlug === $cat['slug'] ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-dark-card hover:bg-gray-200 dark:hover:bg-dark-border' ?>">
                <?= Helper::e($cat['name']) ?> (<?= (int) $cat['product_count'] ?>)
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="text-center py-16">
                <p class="text-gray-400">Tidak ada produk dalam kategori ini.</p>
            </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach ($products as $product): ?>
            <a href="/products/<?= Helper::e($product['slug']) ?>" class="card group hover:border-primary transition-all">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden shrink-0">
                        <?php if ($product['logo_path']): ?>
                            <img src="/<?= Helper::e($product['logo_path']) ?>" alt="" class="w-10 h-10 object-contain">
                        <?php else: ?>
                            <span class="text-sm font-bold text-primary"><?= strtoupper(substr($product['name'], 0, 2)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-medium group-hover:text-primary transition-colors"><?= Helper::e($product['name']) ?></h3>
                        <p class="text-xs text-gray-400"><?= Helper::e($product['category_name']) ?></p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-primary">Mulai <?= Helper::formatPrice($product['price_from']) ?></span>
                    <span class="text-xs text-gray-400"><?= (int) $product['variant_count'] ?> varian</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- CTA for non-logged users -->
        <?php if (!Auth::check()): ?>
        <div class="text-center mt-12 p-8 rounded-xl bg-gray-50 dark:bg-dark-card border border-gray-200 dark:border-dark-border">
            <h3 class="font-heading font-semibold text-lg mb-2">Siap untuk Order?</h3>
            <p class="text-sm text-gray-400 mb-4">Daftar gratis dan mulai menikmati layanan premium</p>
            <a href="/register" class="btn btn-primary">Daftar Sekarang</a>
        </div>
        <?php endif; ?>
    </div>
</section>
