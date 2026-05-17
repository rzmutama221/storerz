<?php
/**
 * Email Verification Notice Page
 * Shown after registration
 */
?>

<div class="text-center">
    <!-- Icon -->
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
    </div>

    <h2 class="text-2xl font-heading font-bold mb-2">Cek Email Anda</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
        Kami telah mengirim link verifikasi ke alamat email Anda. Klik link tersebut untuk mengaktifkan akun.
    </p>

    <!-- Info Box -->
    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg p-4 mb-6 text-left">
        <h4 class="text-sm font-semibold text-blue-700 dark:text-blue-400 mb-2">Tips:</h4>
        <ul class="text-xs text-blue-600 dark:text-blue-300 space-y-1">
            <li>&#8226; Cek folder <strong>Spam</strong> atau <strong>Junk</strong> jika tidak ditemukan di Inbox</li>
            <li>&#8226; Link verifikasi berlaku selama <strong>24 jam</strong></li>
            <li>&#8226; Pastikan email yang digunakan benar dan aktif</li>
        </ul>
    </div>

    <!-- Actions -->
    <div class="space-y-3">
        <a href="/login" class="btn btn-primary w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
            Ke Halaman Login
        </a>

        <p class="text-xs text-gray-400">
            Tidak menerima email? Hubungi kami via 
            <a href="https://wa.me/6285111642004" target="_blank" class="text-primary hover:text-primary-hover">WhatsApp</a>
        </p>
    </div>
</div>
