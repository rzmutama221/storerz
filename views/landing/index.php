<?php
/**
 * Landing Page - Homepage
 * Placeholder: will be fully designed in Fase 10
 */
?>

<!-- Hero Section -->
<section class="relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-heading font-bold mb-6">
                Premium Digital Store
                <span class="gradient-text block mt-2">Harga Terjangkau</span>
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                Akses layanan streaming, AI, dan aplikasi kreatif premium dengan harga mulai dari Rp 2.000. Garansi full, proses cepat, dan dashboard tracking untuk semua transaksi.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/products" class="btn btn-primary btn-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    Lihat Produk
                </a>
                <a href="/register" class="btn btn-secondary btn-lg">
                    Daftar Gratis
                </a>
            </div>
        </div>
    </div>
    <!-- Background decoration -->
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-primary/5 blur-3xl"></div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-16 border-t border-gray-200 dark:border-dark-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-heading font-bold">Produk Populer</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Layanan premium terlaris dengan harga terjangkau</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
            <?php foreach ($featuredProducts as $product): ?>
            <a href="/products/<?= Helper::e($product['slug']) ?>" class="card hover:border-primary group transition-all">
                <!-- Logo -->
                <div class="w-12 h-12 mb-3 rounded-lg bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden">
                    <?php if ($product['logo_path']): ?>
                        <img src="/<?= Helper::e($product['logo_path']) ?>" alt="<?= Helper::e($product['name']) ?>" class="w-10 h-10 object-contain">
                    <?php else: ?>
                        <span class="text-lg font-bold text-primary"><?= strtoupper(substr($product['name'], 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                <!-- Info -->
                <h3 class="font-medium text-sm mb-1 group-hover:text-primary transition-colors"><?= Helper::e($product['name']) ?></h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2"><?= Helper::e($product['category_name']) ?></p>
                <p class="text-sm font-semibold text-primary">
                    Mulai <?= Helper::formatPrice($product['price_from']) ?>
                </p>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8">
            <a href="/products" class="btn btn-secondary">
                Lihat Semua Produk
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories -->
<?php if (!empty($categories)): ?>
<section class="py-16 bg-gray-50 dark:bg-dark-card border-t border-gray-200 dark:border-dark-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-heading font-bold">Kategori</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Pilih kategori layanan yang Anda butuhkan</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="/products?category=<?= Helper::e($cat['slug']) ?>" class="card text-center hover:border-primary group">
                <div class="text-3xl mb-2">
                    <?php
                    $icons = ['film' => '🎬', 'music' => '🎧', 'palette' => '🎨', 'brain' => '🤖', 'briefcase' => '💼'];
                    echo $icons[$cat['icon']] ?? '📦';
                    ?>
                </div>
                <h3 class="font-medium text-sm group-hover:text-primary transition-colors"><?= Helper::e($cat['name']) ?></h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= (int)$cat['product_count'] ?> produk</p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features -->
<section id="fitur" class="py-16 border-t border-gray-200 dark:border-dark-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-heading font-bold">Kenapa RZDK Store?</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center p-6">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <h3 class="font-heading font-semibold mb-2">Harga Terjangkau</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Mulai dari Rp 2.000 saja untuk akses layanan premium</p>
            </div>
            <div class="text-center p-6">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                </div>
                <h3 class="font-heading font-semibold mb-2">Garansi Full</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Garansi sesuai durasi produk. Klaim mudah via dashboard</p>
            </div>
            <div class="text-center p-6">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <h3 class="font-heading font-semibold mb-2">Proses Cepat</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Produk diproses setelah pembayaran dikonfirmasi</p>
            </div>
            <div class="text-center p-6">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                </div>
                <h3 class="font-heading font-semibold mb-2">Dashboard Tracking</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pantau order, masa aktif, dan data akun di satu tempat</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="py-16 border-t border-gray-200 dark:border-dark-border">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-heading font-bold">FAQ</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Pertanyaan yang sering ditanyakan</p>
        </div>

        <div class="space-y-3" x-data="{ open: null }">
            <!-- FAQ 1 -->
            <div class="border border-gray-200 dark:border-dark-border rounded-lg overflow-hidden">
                <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                    <span class="font-medium text-sm">Bagaimana cara order?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform" :class="open === 1 && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === 1" x-transition class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">
                    Daftar akun gratis, pilih produk dan varian yang diinginkan, lalu buat order. Admin akan approve dan Anda bisa melakukan pembayaran via QRIS. Setelah pembayaran dikonfirmasi, produk akan dikirim melalui dashboard.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="border border-gray-200 dark:border-dark-border rounded-lg overflow-hidden">
                <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                    <span class="font-medium text-sm">Metode pembayaran apa saja yang tersedia?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform" :class="open === 2 && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === 2" x-transition class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">
                    Saat ini kami menerima pembayaran melalui QRIS (scan menggunakan e-wallet atau m-banking). Semua bank dan e-wallet yang support QRIS bisa digunakan (OVO, GoPay, DANA, ShopeePay, BCA, Mandiri, dll).
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="border border-gray-200 dark:border-dark-border rounded-lg overflow-hidden">
                <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                    <span class="font-medium text-sm">Bagaimana sistem garansi?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform" :class="open === 3 && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === 3" x-transition class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">
                    Setiap produk memiliki masa garansi sesuai durasinya. Jika ada masalah (tidak bisa login, akun error, dll), Anda bisa mengajukan klaim garansi melalui menu Garansi di dashboard. Admin akan merespon dan menyelesaikan masalah Anda.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="border border-gray-200 dark:border-dark-border rounded-lg overflow-hidden">
                <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                    <span class="font-medium text-sm">Apa bedanya Sharing dan Private?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform" :class="open === 4 && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === 4" x-transition class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">
                    <strong>Sharing</strong> — Akun digunakan bersama beberapa user, harga lebih murah. <strong>Private</strong> — Akun hanya untuk Anda sendiri, tidak dibagikan ke siapapun. <strong>Semi Private</strong> — Hybrid, contohnya 1 profil Netflix untuk 2 device.
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="border border-gray-200 dark:border-dark-border rounded-lg overflow-hidden">
                <button @click="open = open === 5 ? null : 5" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                    <span class="font-medium text-sm">Berapa lama proses setelah bayar?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform" :class="open === 5 && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === 5" x-transition class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">
                    Setelah pembayaran dikonfirmasi, produk akan diproses dalam waktu 1-30 menit (jam operasional). Beberapa produk dengan stok tersedia akan langsung muncul otomatis di dashboard Anda.
                </div>
            </div>

            <!-- FAQ 6 -->
            <div class="border border-gray-200 dark:border-dark-border rounded-lg overflow-hidden">
                <button @click="open = open === 6 ? null : 6" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-dark-card transition-colors">
                    <span class="font-medium text-sm">Kenapa order perlu approval terlebih dahulu?</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform" :class="open === 6 && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open === 6" x-transition class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">
                    Untuk memastikan ketersediaan stok sebelum Anda melakukan pembayaran. Ini mencegah situasi di mana Anda sudah bayar tapi produk tidak tersedia. Proses approval biasanya hanya beberapa menit.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gray-50 dark:bg-dark-card border-t border-gray-200 dark:border-dark-border">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-heading font-bold mb-4">Bergabung Sekarang</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-8">Daftar gratis dan mulai akses layanan premium dengan harga terjangkau</p>
        <a href="/register" class="btn btn-primary btn-lg animate-pulse-green">
            Daftar Gratis
        </a>
    </div>
</section>
