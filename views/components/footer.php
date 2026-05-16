<?php
/**
 * Footer Component - Landing Page
 * Used in: views/layouts/landing.php
 */
?>
<footer class="bg-gray-50 dark:bg-dark-card border-t border-gray-200 dark:border-dark-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

            <!-- Brand -->
            <div class="md:col-span-2">
                <a href="/" class="flex items-center gap-2 mb-4">
                    <span class="text-2xl font-heading font-bold text-primary">RZDK</span>
                    <span class="text-2xl font-heading font-bold">Store</span>
                </a>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 max-w-sm">
                    Premium Digital Store — Akses layanan premium dengan harga terjangkau. Garansi full, proses cepat, dan dashboard tracking untuk semua transaksi Anda.
                </p>
                <!-- WhatsApp -->
                <a href="https://wa.me/6285111642004" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary hover:text-primary-hover transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    085111642004
                </a>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-heading font-semibold text-sm mb-4">Menu</h4>
                <ul class="space-y-2">
                    <li><a href="/products" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Produk</a></li>
                    <li><a href="/#fitur" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Fitur</a></li>
                    <li><a href="/#faq" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">FAQ</a></li>
                    <li><a href="/login" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Login</a></li>
                    <li><a href="/register" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Daftar</a></li>
                </ul>
            </div>

            <!-- Kategori -->
            <div>
                <h4 class="font-heading font-semibold text-sm mb-4">Kategori</h4>
                <ul class="space-y-2">
                    <li><a href="/products?category=streaming-video" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Streaming Video</a></li>
                    <li><a href="/products?category=streaming-musik" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Streaming Musik</a></li>
                    <li><a href="/products?category=aplikasi-kreatif" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Aplikasi Kreatif</a></li>
                    <li><a href="/products?category=ai-edukasi" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">AI & Edukasi</a></li>
                    <li><a href="/products?category=produktivitas" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">Produktivitas</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="border-t border-gray-200 dark:border-dark-border mt-8 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                &copy; <?= date('Y') ?> RZDK Store. All rights reserved.
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">
                v<?= htmlspecialchars($appConfig['version'] ?? '1.0.0') ?>
            </p>
        </div>
    </div>
</footer>
