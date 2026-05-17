<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || !localStorage.getItem('theme'), sidebarOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::e($pageTitle ?? 'Admin Panel') ?> | RZDK Store</title>

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

        <!-- Sidebar -->
        <?php require BASE_PATH . '/views/components/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Bar -->
            <header class="h-16 bg-white dark:bg-dark-card border-b border-gray-200 dark:border-dark-border flex items-center justify-between px-4 lg:px-6 shrink-0">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-border">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>

                <div class="flex-1"></div>

                <!-- Right side: Theme toggle + User info -->
                <div class="flex items-center gap-3">
                    <!-- Theme Toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-border transition-colors" title="Toggle tema">
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    </button>

                    <!-- User Menu -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-medium">
                            <?= strtoupper(substr(Auth::user()['username'] ?? 'A', 0, 1)) ?>
                        </div>
                        <span class="hidden sm:block text-sm font-medium"><?= Helper::e(Auth::user()['username'] ?? 'Admin') ?></span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Flash Messages -->
                <?php require BASE_PATH . '/views/components/alert.php'; ?>

                <!-- Page Content -->
                <?= $content ?>
            </main>

        </div>
    </div>

    <!-- Custom JS -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
