<?php
/**
 * Admin Edit Product View
 */
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
        <a href="/admin/products" class="hover:text-primary transition-colors">Produk</a>
        <span>/</span>
        <span>Edit</span>
    </div>
    <h1 class="text-2xl font-heading font-bold">Edit: <?= Helper::e($product['name']) ?></h1>
</div>

<!-- Form -->
<div class="card-flat max-w-2xl">
    <form action="/admin/products/<?= (int) $product['id'] ?>/edit" method="POST" enctype="multipart/form-data" data-protect-submit>
        <?= Helper::csrfField() ?>

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="form-label">Nama Produk <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" class="form-input" value="<?= Helper::e($product['name']) ?>" required>
        </div>

        <!-- Category -->
        <div class="mb-4">
            <label for="category_id" class="form-label">Kategori <span class="text-red-500">*</span></label>
            <select id="category_id" name="category_id" class="form-input" required>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= Helper::e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea id="description" name="description" class="form-input" rows="3"><?= Helper::e($product['description']) ?></textarea>
        </div>

        <!-- Current Logo -->
        <?php if ($product['logo_path']): ?>
        <div class="mb-2">
            <label class="form-label">Logo Saat Ini</label>
            <div class="w-16 h-16 rounded-lg bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden border border-gray-200 dark:border-dark-border">
                <img src="/<?= Helper::e($product['logo_path']) ?>" alt="" class="w-12 h-12 object-contain">
            </div>
        </div>
        <?php endif; ?>

        <!-- Logo Upload -->
        <div class="mb-4">
            <label class="form-label"><?= $product['logo_path'] ? 'Ganti Logo' : 'Logo Produk' ?></label>
            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input">
            <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah. Maks 2MB.</p>
        </div>

        <!-- Slot Type -->
        <div class="mb-4">
            <label for="slot_type" class="form-label">Tipe Slot</label>
            <select id="slot_type" name="slot_type" class="form-input">
                <option value="none" <?= $product['slot_type'] === 'none' ? 'selected' : '' ?>>Normal (Tanpa sistem slot)</option>
                <option value="netflix" <?= $product['slot_type'] === 'netflix' ? 'selected' : '' ?>>Netflix (Slot per profile)</option>
                <option value="chatgpt" <?= $product['slot_type'] === 'chatgpt' ? 'selected' : '' ?>>ChatGPT (Slot per member)</option>
            </select>
        </div>

        <!-- Sort Order -->
        <div class="mb-4">
            <label for="sort_order" class="form-label">Urutan Tampil</label>
            <input type="number" id="sort_order" name="sort_order" class="form-input w-32" value="<?= (int) $product['sort_order'] ?>" min="0">
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?> class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                <span class="text-sm">Produk Aktif (tampil di katalog)</span>
            </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Simpan Perubahan
            </button>
            <a href="/admin/products/<?= (int) $product['id'] ?>/variants" class="btn btn-secondary">Kelola Varian</a>
            <a href="/admin/products" class="text-sm text-gray-400 hover:text-primary transition-colors">Batal</a>
        </div>
    </form>
</div>
