<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || !localStorage.getItem('theme') }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= Helper::e($pageDescription ?? 'Premium Digital Store — Akses Layanan Premium Harga Terjangkau') ?>">
    <title><?= Helper::e($pageTitle ?? 'RZDK Store') ?></title>

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
<body class="font-body bg-white dark:bg-dark text-gray-900 dark:text-white transition-colors duration-200">

    <!-- Navbar -->
    <?php require BASE_PATH . '/views/components/navbar.php'; ?>

    <!-- Main Content -->
    <main>
        <?= $content ?>
    </main>

    <!-- Footer -->
    <?php require BASE_PATH . '/views/components/footer.php'; ?>

    <!-- Custom JS -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
