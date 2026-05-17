<?php
/**
 * Login Page View
 */
$oldInput = Session::get('old_input', []);
Session::remove('old_input');
?>

<h2 class="text-2xl font-heading font-bold text-center mb-2">Selamat Datang</h2>
<p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Masuk ke akun RZDK Store Anda</p>

<form action="/login" method="POST" data-protect-submit>
    <?= Helper::csrfField() ?>

    <!-- Username -->
    <div class="mb-4">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username" value="<?= Helper::e($oldInput['username'] ?? '') ?>" autocomplete="username" required autofocus>
    </div>

    <!-- Password -->
    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="relative">
            <input type="password" id="password" name="password" class="form-input pr-10" placeholder="Masukkan password" autocomplete="current-password" required>
            <button type="button" data-toggle-password="password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="icon-show">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
                <span class="icon-hide" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                </span>
            </button>
        </div>
    </div>

    <!-- Forgot Password Link -->
    <div class="flex items-center justify-end mb-6">
        <a href="/forgot-password" class="text-sm text-primary hover:text-primary-hover transition-colors">Lupa password?</a>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-full">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
        Login
    </button>
</form>

<!-- Register Link -->
<p class="text-sm text-center mt-6 text-gray-500 dark:text-gray-400">
    Belum punya akun? <a href="/register" class="text-primary hover:text-primary-hover font-medium transition-colors">Daftar sekarang</a>
</p>
