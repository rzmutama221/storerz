<?php
/**
 * Register Page View
 */
$oldInput = Session::get('old_input', []);
Session::remove('old_input');
?>

<h2 class="text-2xl font-heading font-bold text-center mb-2">Buat Akun Baru</h2>
<p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Daftar gratis untuk mulai berbelanja</p>

<form action="/register" method="POST" data-protect-submit>
    <?= Helper::csrfField() ?>

    <!-- Username -->
    <div class="mb-4">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" class="form-input" placeholder="Pilih username (huruf, angka, dash, underscore)" value="<?= Helper::e($oldInput['username'] ?? '') ?>" autocomplete="username" required autofocus>
        <p class="text-xs text-gray-400 mt-1">Minimal 3 karakter. Hanya huruf, angka, dash (-), underscore (_)</p>
    </div>

    <!-- Email -->
    <div class="mb-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-input" placeholder="alamat@email.com" value="<?= Helper::e($oldInput['email'] ?? '') ?>" autocomplete="email" required>
        <p class="text-xs text-gray-400 mt-1">Akan digunakan untuk verifikasi dan reset password</p>
    </div>

    <!-- Password -->
    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="relative">
            <input type="password" id="password" name="password" class="form-input pr-10" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
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

    <!-- Confirm Password -->
    <div class="mb-6">
        <label for="password_confirm" class="form-label">Konfirmasi Password</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Ulangi password" autocomplete="new-password" required>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-full">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
        Daftar
    </button>
</form>

<!-- Login Link -->
<p class="text-sm text-center mt-6 text-gray-500 dark:text-gray-400">
    Sudah punya akun? <a href="/login" class="text-primary hover:text-primary-hover font-medium transition-colors">Login di sini</a>
</p>
