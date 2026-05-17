<?php
/**
 * Admin Categories Management View
 * CRUD inline: list, add (modal), edit (modal), delete
 */
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-heading font-bold">Kategori</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola kategori produk</p>
    </div>
    <button onclick="document.getElementById('modal-add-category').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Tambah Kategori
    </button>
</div>

<!-- Categories Table -->
<div class="card-flat">
    <?php if (empty($categories)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada kategori. Klik "Tambah Kategori" untuk memulai.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Produk</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td>
                        <span class="text-xl">
                            <?php
                            $icons = ['film' => '🎬', 'music' => '🎧', 'palette' => '🎨', 'brain' => '🤖', 'briefcase' => '💼', 'gamepad' => '🎮', 'cloud' => '☁️', 'lock' => '🔒'];
                            echo $icons[$cat['icon']] ?? '📦';
                            ?>
                        </span>
                    </td>
                    <td class="font-medium"><?= Helper::e($cat['name']) ?></td>
                    <td class="text-sm text-gray-400"><?= Helper::e($cat['slug']) ?></td>
                    <td><span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-dark text-xs"><?= (int) $cat['product_count'] ?></span></td>
                    <td><?= (int) $cat['sort_order'] ?></td>
                    <td>
                        <?php if ($cat['is_active']): ?>
                            <span class="status-dot online"></span> <span class="text-xs text-green-400">Aktif</span>
                        <?php else: ?>
                            <span class="status-dot offline"></span> <span class="text-xs text-gray-400">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <button onclick="openEditCategory(<?= htmlspecialchars(json_encode($cat)) ?>)" class="text-xs text-primary hover:text-primary-hover transition-colors">Edit</button>
                            <?php if ((int) $cat['product_count'] === 0): ?>
                            <form action="/admin/categories/<?= (int) $cat['id'] ?>/delete" method="POST" class="inline" data-confirm="Hapus kategori '<?= Helper::e($cat['name']) ?>'?">
                                <?= Helper::csrfField() ?>
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400 transition-colors">Hapus</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal: Add Category -->
<div id="modal-add-category" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" x-data>
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-add-category').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-md p-6 animate-fade-in">
        <h3 class="text-lg font-heading font-semibold mb-4">Tambah Kategori</h3>
        
        <form action="/admin/categories" method="POST">
            <?= Helper::csrfField() ?>

            <div class="mb-4">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="name" class="form-input" placeholder="Contoh: Streaming Video & Hiburan" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Icon</label>
                <select name="icon" class="form-input" required>
                    <option value="">Pilih icon...</option>
                    <option value="film">🎬 Film/Video</option>
                    <option value="music">🎧 Musik</option>
                    <option value="palette">🎨 Kreatif/Desain</option>
                    <option value="brain">🤖 AI/Edukasi</option>
                    <option value="briefcase">💼 Produktivitas</option>
                    <option value="gamepad">🎮 Gaming</option>
                    <option value="cloud">☁️ Cloud/Storage</option>
                    <option value="lock">🔒 Security/VPN</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" class="form-input" value="0" min="0">
                <p class="text-xs text-gray-400 mt-1">Angka kecil tampil lebih dulu</p>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-add-category').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Category -->
<div id="modal-edit-category" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-edit-category').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-md p-6 animate-fade-in">
        <h3 class="text-lg font-heading font-semibold mb-4">Edit Kategori</h3>
        
        <form id="form-edit-category" action="" method="POST">
            <?= Helper::csrfField() ?>

            <div class="mb-4">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="name" id="edit-cat-name" class="form-input" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Icon</label>
                <select name="icon" id="edit-cat-icon" class="form-input" required>
                    <option value="film">🎬 Film/Video</option>
                    <option value="music">🎧 Musik</option>
                    <option value="palette">🎨 Kreatif/Desain</option>
                    <option value="brain">🤖 AI/Edukasi</option>
                    <option value="briefcase">💼 Produktivitas</option>
                    <option value="gamepad">🎮 Gaming</option>
                    <option value="cloud">☁️ Cloud/Storage</option>
                    <option value="lock">🔒 Security/VPN</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" id="edit-cat-sort" class="form-input" min="0">
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit-cat-active" value="1" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="text-sm">Aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-edit-category').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCategory(cat) {
    document.getElementById('form-edit-category').action = '/admin/categories/' + cat.id + '/update';
    document.getElementById('edit-cat-name').value = cat.name;
    document.getElementById('edit-cat-icon').value = cat.icon;
    document.getElementById('edit-cat-sort').value = cat.sort_order;
    document.getElementById('edit-cat-active').checked = cat.is_active == 1;
    document.getElementById('modal-edit-category').classList.remove('hidden');
}
</script>
