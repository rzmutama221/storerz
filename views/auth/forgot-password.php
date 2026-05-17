<?php
/**
 * Forgot Password Page View
 */
?>

<h2 class="text-2xl font-heading font-bold text-center mb-2">Lupa Password</h2>
<p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Masukkan email yang terdaftar untuk menerima link reset password</p>

<form action="/forgot-password" method="POST" data-protect-submit>
    <?= Helper::csrfField() ?>

    <!-- Email -->
    <div class="mb-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-input" placeholder="alamat@email.com" autocomplete="email" required autofocus>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-full">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
        Kirim Link Reset
    </button>
</form>

<!-- Back to Login -->
<p class="text-sm text-center mt-6 text-gray-500 dark:text-gray-400">
    <a href="/login" class="text-primary hover:text-primary-hover font-medium transition-colors inline-flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke Login
    </a>
</p>
