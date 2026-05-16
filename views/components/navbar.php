<?php
/**
 * Navbar Component - Landing Page
 * Used in: views/layouts/landing.php
 */
?>
<nav x-data="{ mobileOpen: false }" class="sticky top-0 z-50 bg-white/80 dark:bg-dark/80 backdrop-blur-lg border-b border-gray-200 dark:border-dark-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 shrink-0">
                <span class="text-2xl font-heading font-bold text-primary">RZDK</span>
                <span class="text-2xl font-heading font-bold">Store</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-6">
                <a href="/" class="text-sm font-medium hover:text-primary transition-colors <?= Helper::activeClass('/', 'text-primary') ?>">Beranda</a>
                <a href="/products" class="text-sm font-medium hover:text-primary transition-colors <?= Helper::activeClass('/products', 'text-primary') ?>">Produk</a>
                <a href="/#fitur" class="text-sm font-medium hover:text-primary transition-colors">Fitur</a>
                <a href="/#faq" class="text-sm font-medium hover:text-primary transition-colors">FAQ</a>
                <a href="https://wa.me/6285111642004" target="_blank" class="text-sm font-medium hover:text-primary transition-colors">Kontak</a>
            </div>

            <!-- Desktop Right Actions -->
            <div class="hidden md:flex items-center gap-3">
                <!-- Theme Toggle -->
                <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card transition-colors" title="Toggle tema">
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </button>

                <?php if (Auth::check()): ?>
                    <a href="<?= Auth::isAdmin() ? '/admin/dashboard' : '/dashboard' ?>" class="text-sm font-medium px-4 py-2 rounded-lg bg-primary hover:bg-primary-hover text-white transition-colors">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="/login" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 dark:border-dark-border hover:border-primary dark:hover:border-primary transition-colors">
                        Login
                    </a>
                    <a href="/register" class="text-sm font-medium px-4 py-2 rounded-lg bg-primary hover:bg-primary-hover text-white transition-colors">
                        Daftar
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card transition-colors">
                <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                <svg x-show="mobileOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="md:hidden border-t border-gray-200 dark:border-dark-border py-4">
            <div class="flex flex-col gap-2">
                <a href="/" class="px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card transition-colors">Beranda</a>
                <a href="/products" class="px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card transition-colors">Produk</a>
                <a href="/#fitur" class="px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card transition-colors">Fitur</a>
                <a href="/#faq" class="px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card transition-colors">FAQ</a>
                <a href="https://wa.me/6285111642004" target="_blank" class="px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card transition-colors">Kontak</a>

                <div class="border-t border-gray-200 dark:border-dark-border my-2"></div>

                <?php if (Auth::check()): ?>
                    <a href="<?= Auth::isAdmin() ? '/admin/dashboard' : '/dashboard' ?>" class="px-3 py-2 text-sm font-medium rounded-lg bg-primary text-white text-center">Dashboard</a>
                <?php else: ?>
                    <a href="/login" class="px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-dark-border text-center">Login</a>
                    <a href="/register" class="px-3 py-2 text-sm font-medium rounded-lg bg-primary text-white text-center">Daftar</a>
                <?php endif; ?>

                <!-- Mobile Theme Toggle -->
                <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" class="px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-dark-card flex items-center gap-2 transition-colors">
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/></svg>
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    <span x-text="darkMode ? 'Mode Terang' : 'Mode Gelap'"></span>
                </button>
            </div>
        </div>
    </div>
</nav>
