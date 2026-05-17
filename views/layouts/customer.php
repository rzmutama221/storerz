<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || !localStorage.getItem('theme'), sidebarOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::e($pageTitle ?? 'Dashboard') ?> | RZDK Store</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#01a35a', hover: '#0edf7d' },
                        dark: { DEFAULT: '#171717', card: '#2c2c2c', border: '#3a3a3a' },
                    },
                    fontFamily: {
                        heading: ['Poppins', 'sans-serif'],
                        body: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="font-body bg-gray-50 dark:bg-dark text-gray-900 dark:text-white transition-colors duration-200">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar Overlay (Mobile) -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:leave="transition-opacity ease-in duration-200" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <!-- Customer Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-dark border-r border-gray-200 dark:border-dark-border transform lg:translate-x-0 lg:static lg:inset-auto transition-transform duration-200 ease-in-out flex flex-col">

            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-dark-border shrink-0">
                <a href="/dashboard" class="flex items-center gap-2">
                    <span class="text-xl font-heading font-bold text-primary">RZDK</span>
                    <span class="text-xl font-heading font-bold">Store</span>
                </a>
                <!-- Close button (mobile) -->
                <button @click="sidebarOpen = false" class="lg:hidden ml-auto p-1 rounded hover:bg-gray-100 dark:hover:bg-dark-border">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <ul class="space-y-1">
                    <li>
                        <a href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/dashboard', 'bg-primary/10 text-primary border-l-2 border-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/products" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/dashboard/products', 'bg-primary/10 text-primary border-l-2 border-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            Produk
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/orders" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/dashboard/orders', 'bg-primary/10 text-primary border-l-2 border-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Transaksi
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/accounts" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/dashboard/accounts', 'bg-primary/10 text-primary border-l-2 border-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                            Akun Aktif
                        </a>
                    </li>
                    <li>
                        <a href="/dashboard/warranty" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/dashboard/warranty', 'bg-primary/10 text-primary border-l-2 border-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                            Garansi
                        </a>
                    </li>

                    <li class="pt-4 mt-4 border-t border-gray-200 dark:border-dark-border">
                        <a href="/dashboard/profile" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/dashboard/profile', 'bg-primary/10 text-primary border-l-2 border-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Profil
                        </a>
                    </li>
                    <li>
                        <a href="/logout" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Bar -->
            <header class="h-16 bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border flex items-center justify-between px-4 lg:px-6 shrink-0">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-border">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>

                <div class="flex-1"></div>

                <div class="flex items-center gap-3">
                    <!-- Theme Toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-border transition-colors" title="Toggle tema">
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    </button>

                    <!-- User -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-medium">
                            <?= strtoupper(substr(Auth::user()['username'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="hidden sm:block text-sm font-medium"><?= Helper::e(Auth::user()['full_name'] ?? Auth::user()['username'] ?? 'User') ?></span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <?php require BASE_PATH . '/views/components/alert.php'; ?>
                <?= $content ?>
            </main>

        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
