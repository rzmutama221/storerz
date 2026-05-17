<?php
/**
 * Reset Password Page View
 * $token is passed from controller
 */
?>

<h2 class="text-2xl font-heading font-bold text-center mb-2">Reset Password</h2>
<p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Masukkan password baru untuk akun Anda</p>

<form action="/reset-password" method="POST" data-protect-submit>
    <?= Helper::csrfField() ?>
    <input type="hidden" name="token" value="<?= Helper::e($token) ?>">

    <!-- New Password -->
    <div class="mb-4">
        <label for="password" class="form-label">Password Baru</label>
        <div class="relative">
            <input type="password" id="password" name="password" class="form-input pr-10" placeholder="Minimal 8 karakter" autocomplete="new-password" required autofocus>
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
        <label for="password_confirm" class="form-label">Konfirmasi Password Baru</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Ulangi password baru" autocomplete="new-password" required>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-full">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Reset Password
    </button>
</form>

<!-- Back to Login -->
<p class="text-sm text-center mt-6 text-gray-500 dark:text-gray-400">
    <a href="/login" class="text-primary hover:text-primary-hover font-medium transition-colors inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke Login
    </a>
</p>
