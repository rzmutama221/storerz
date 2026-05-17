<?php /** Customer Profile View */ ?>

<div class="mb-6">
    <h1 class="text-2xl font-heading font-bold">Profil Saya</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Profile Info -->
    <div class="card-flat">
        <h2 class="font-heading font-semibold text-sm mb-4">Informasi Akun</h2>
        <form action="/dashboard/profile" method="POST" data-protect-submit>
            <?= Helper::csrfField() ?>

            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" class="form-input opacity-60" value="<?= Helper::e($user['username']) ?>" disabled>
                <p class="text-xs text-gray-400 mt-1">Username tidak dapat diubah</p>
            </div>

            <div class="mb-4">
                <label class="form-label">Email</label>
                <input type="email" class="form-input opacity-60" value="<?= Helper::e($user['email']) ?>" disabled>
                <p class="text-xs text-gray-400 mt-1">Email tidak dapat diubah (digunakan untuk verifikasi)</p>
            </div>

            <div class="mb-4">
                <label for="full_name" class="form-label">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" class="form-input" value="<?= Helper::e($user['full_name'] ?? '') ?>" placeholder="Nama lengkap (opsional)">
            </div>

            <div class="mb-4">
                <label for="phone" class="form-label">Nomor HP / WhatsApp</label>
                <input type="text" id="phone" name="phone" class="form-input" value="<?= Helper::e($user['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx">
            </div>

            <div class="mb-4 text-sm text-gray-400 space-y-1">
                <p>Terdaftar: <?= Helper::formatDate($user['created_at'], 'd M Y') ?></p>
                <p>Login terakhir: <?= $user['last_login_at'] ? Helper::formatDate($user['last_login_at'], 'd M Y H:i') : '-' ?></p>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="card-flat">
        <h2 class="font-heading font-semibold text-sm mb-4">Ubah Password</h2>
        <form action="/dashboard/change-password" method="POST" data-protect-submit>
            <?= Helper::csrfField() ?>

            <div class="mb-4">
                <label for="current_password" class="form-label">Password Lama</label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
            </div>

            <div class="mb-4">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Minimal 8 karakter" required>
            </div>

            <div class="mb-6">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Ubah Password</button>
        </form>
    </div>

</div>
