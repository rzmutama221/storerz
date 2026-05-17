<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || !localStorage.getItem('theme') }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::e($pageTitle ?? 'Login') ?> | RZDK Store</title>

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

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">

        <!-- Logo -->
        <a href="/" class="mb-8 flex items-center gap-2">
            <span class="text-3xl font-heading font-bold text-primary">RZDK</span>
            <span class="text-3xl font-heading font-bold">Store</span>
        </a>

        <!-- Auth Card -->
        <div class="w-full max-w-md bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-lg p-6 sm:p-8">
            <!-- Flash Messages -->
            <?php require BASE_PATH . '/views/components/alert.php'; ?>

            <!-- Content -->
            <?= $content ?>
        </div>

        <!-- Theme Toggle (bottom corner) -->
        <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" class="fixed bottom-4 right-4 p-3 rounded-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border shadow-lg hover:shadow-xl transition-all" title="Toggle tema">
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        </button>

        <!-- Footer text -->
        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400">
            &copy; <?= date('Y') ?> RZDK Store. All rights reserved.
        </p>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
