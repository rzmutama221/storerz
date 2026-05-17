<?php
/**
 * Admin Create Product View
 */
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
        <a href="/admin/products" class="hover:text-primary transition-colors">Produk</a>
        <span>/</span>
        <span>Tambah Baru</span>
    </div>
    <h1 class="text-2xl font-heading font-bold">Tambah Produk</h1>
</div>

<!-- Form -->
<div class="card-flat max-w-2xl">
    <form action="/admin/products/create" method="POST" enctype="multipart/form-data" data-protect-submit>
        <?= Helper::csrfField() ?>

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="form-label">Nama Produk <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" class="form-input" placeholder="Contoh: Netflix, Spotify Premium, ChatGPT" required>
        </div>

        <!-- Category -->
        <div class="mb-4">
            <label for="category_id" class="form-label">Kategori <span class="text-red-500">*</span></label>
            <select id="category_id" name="category_id" class="form-input" required>
                <option value="">Pilih kategori...</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea id="description" name="description" class="form-input" rows="3" placeholder="Deskripsi singkat tentang produk ini..."></textarea>
        </div>

        <!-- Logo -->
        <div class="mb-4">
            <label class="form-label">Logo Produk</label>
            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input">
            <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, GIF, WebP. Maks 2MB.</p>
        </div>

        <!-- Slot Type -->
        <div class="mb-4">
            <label for="slot_type" class="form-label">Tipe Slot</label>
            <select id="slot_type" name="slot_type" class="form-input">
                <option value="none">Normal (Tanpa sistem slot)</option>
                <option value="netflix">Netflix (Slot per profile)</option>
                <option value="chatgpt">ChatGPT (Slot per member)</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Pilih "Netflix" atau "ChatGPT" jika produk ini menggunakan sistem slot management.</p>
        </div>

        <!-- Sort Order -->
        <div class="mb-6">
            <label for="sort_order" class="form-label">Urutan Tampil</label>
            <input type="number" id="sort_order" name="sort_order" class="form-input w-32" value="0" min="0">
            <p class="text-xs text-gray-400 mt-1">Angka kecil ditampilkan lebih dulu.</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Simpan & Lanjut ke Varian
            </button>
            <a href="/admin/products" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
