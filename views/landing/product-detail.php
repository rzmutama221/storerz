<?php /** Landing - Public Product Detail View (no login required) */ ?>

<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
            <a href="/" class="hover:text-primary transition-colors">Beranda</a>
            <span>/</span>
            <a href="/products" class="hover:text-primary transition-colors">Produk</a>
            <span>/</span>
            <a href="/products?category=<?= Helper::e($product['category_slug']) ?>" class="hover:text-primary transition-colors"><?= Helper::e($product['category_name']) ?></a>
            <span>/</span>
            <span><?= Helper::e($product['name']) ?></span>
        </div>

        <!-- Product Header -->
        <div class="card mb-6">
            <div class="flex items-start gap-4">
                <div class="w-20 h-20 rounded-xl bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden shrink-0 border border-gray-200 dark:border-dark-border">
                    <?php if ($product['logo_path']): ?>
                        <img src="/<?= Helper::e($product['logo_path']) ?>" alt="<?= Helper::e($product['name']) ?>" class="w-14 h-14 object-contain">
                    <?php else: ?>
                        <span class="text-2xl font-bold text-primary"><?= strtoupper(substr($product['name'], 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="text-2xl font-heading font-bold"><?= Helper::e($product['name']) ?></h1>
                    <p class="text-sm text-gray-400 mt-1"><?= Helper::e($product['category_name']) ?></p>
                    <?php if ($product['description']): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3"><?= nl2br(Helper::e($product['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Variants / Pricing Table -->
        <h2 class="font-heading font-semibold text-lg mb-4">Daftar Harga & Varian</h2>

        <?php if (empty($variants)): ?>
            <div class="card text-center py-8">
                <p class="text-gray-400">Tidak ada varian tersedia untuk produk ini saat ini.</p>
            </div>
        <?php else: ?>
        <div class="space-y-3 mb-8">
            <?php foreach ($variants as $v): ?>
            <div class="card hover:border-primary transition-colors">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium"><?= Helper::e($v['name']) ?></span>
                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-dark text-xs capitalize"><?= str_replace('_', ' ', $v['type']) ?></span>
                            <?php if ($v['platform'] !== 'all'): ?>
                                <span class="px-2 py-0.5 rounded bg-yellow-500/10 text-yellow-500 text-xs capitalize"><?= $v['platform'] ?> only</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                            <span><?= (int) $v['duration_days'] ?> hari</span>
                            <?php if ((int) $v['warranty_days'] > 0): ?>
                                <span>Garansi <?= (int) $v['warranty_days'] ?> hari</span>
                            <?php endif; ?>
                            <?php if ($v['max_users'] > 1): ?>
                                <span>Max <?= (int) $v['max_users'] ?> user</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($v['notes']): ?>
                            <p class="text-xs text-gray-400 mt-1"><?= Helper::e($v['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xl font-bold text-primary"><?= Helper::formatPrice($v['price']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <div class="card text-center border-primary/30">
            <p class="text-sm mb-4">Tertarik? Daftar gratis untuk memesan produk ini.</p>
            <div class="flex items-center justify-center gap-3">
                <a href="/register" class="btn btn-primary">Daftar & Order</a>
                <a href="/login" class="btn btn-secondary">Sudah Punya Akun</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
