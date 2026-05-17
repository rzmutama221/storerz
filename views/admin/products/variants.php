<?php
/**
 * Admin Variants Management View
 */
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="/admin/products" class="hover:text-primary transition-colors">Produk</a>
            <span>/</span>
            <a href="/admin/products/<?= (int) $product['id'] ?>/edit" class="hover:text-primary transition-colors"><?= Helper::e($product['name']) ?></a>
            <span>/</span>
            <span>Varian</span>
        </div>
        <h1 class="text-2xl font-heading font-bold">Varian — <?= Helper::e($product['name']) ?></h1>
    </div>
    <button onclick="document.getElementById('modal-add-variant').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Tambah Varian
    </button>
</div>

<!-- Variants Table -->
<div class="card-flat">
    <?php if (empty($variants)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada varian untuk produk ini.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Varian</th>
                    <th>Harga</th>
                    <th>Durasi</th>
                    <th>Tipe</th>
                    <th>Mode</th>
                    <th>Stok</th>
                    <th>Platform</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variants as $v): ?>
                <tr>
                    <td class="font-medium text-sm"><?= Helper::e($v['name']) ?></td>
                    <td class="text-sm font-medium text-primary"><?= Helper::formatPrice($v['price']) ?></td>
                    <td class="text-sm"><?= (int) $v['duration_days'] ?> hari</td>
                    <td><span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-dark text-xs capitalize"><?= str_replace('_', ' ', $v['type']) ?></span></td>
                    <td>
                        <?php if ($v['fulfillment_mode'] === 'auto_stock'): ?>
                            <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-xs">Auto</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded bg-gray-500/10 text-gray-400 text-xs">Manual</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($v['fulfillment_mode'] === 'auto_stock'): ?>
                            <a href="/admin/stock/<?= (int) $v['id'] ?>" class="text-sm text-primary hover:text-primary-hover transition-colors">
                                <?= (int) $v['available_stock'] ?> tersedia
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-gray-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="text-xs capitalize"><?= $v['platform'] === 'all' ? 'Semua' : $v['platform'] ?></span></td>
                    <td>
                        <?php if ($v['is_active']): ?>
                            <span class="status-dot online"></span>
                        <?php else: ?>
                            <span class="status-dot offline"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <button onclick="openEditVariant(<?= htmlspecialchars(json_encode($v)) ?>)" class="text-xs text-primary hover:text-primary-hover transition-colors">Edit</button>
                            <?php if ($v['fulfillment_mode'] === 'auto_stock'): ?>
                            <a href="/admin/stock/<?= (int) $v['id'] ?>" class="text-xs text-gray-400 hover:text-primary transition-colors">Stok</a>
                            <?php endif; ?>
                            <form action="/admin/variants/<?= (int) $v['id'] ?>/delete" method="POST" class="inline" data-confirm="Hapus varian '<?= Helper::e($v['name']) ?>'?">
                                <?= Helper::csrfField() ?>
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400 transition-colors">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal: Add Variant -->
<div id="modal-add-variant" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-add-variant').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Tambah Varian</h3>
        
        <form action="/admin/products/<?= (int) $product['id'] ?>/variants" method="POST">
            <?= Helper::csrfField() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Nama Varian <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Contoh: Sharing 1 Bulan" required>
                </div>
                <div>
                    <label class="form-label">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" class="form-input" placeholder="30000" min="0" step="100" required>
                </div>
                <div>
                    <label class="form-label">Durasi (Hari) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_days" class="form-input" value="30" min="1" required>
                </div>
                <div>
                    <label class="form-label">Tipe Akun</label>
                    <select name="type" class="form-input">
                        <option value="sharing">Sharing</option>
                        <option value="private">Private</option>
                        <option value="semi_private">Semi Private</option>
                        <option value="invite">Invite/Member</option>
                        <option value="redeem">Kode Redeem</option>
                        <option value="service">Jasa</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Mode Fulfillment</label>
                    <select name="fulfillment_mode" class="form-input">
                        <option value="manual">Manual (admin proses)</option>
                        <option value="auto_stock">Auto Stock (dari stok)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Platform</label>
                    <select name="platform" class="form-input">
                        <option value="all">Semua</option>
                        <option value="android">Android Only</option>
                        <option value="ios">iOS Only</option>
                        <option value="web">Web Only</option>
                        <option value="apk">APK Only</option>
                        <option value="mobile">Mobile Only</option>
                        <option value="tv">TV Only</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Max Users (sharing)</label>
                    <input type="number" name="max_users" class="form-input" value="1" min="1">
                </div>
                <div>
                    <label class="form-label">Garansi (Hari)</label>
                    <input type="number" name="warranty_days" class="form-input" value="0" min="0">
                </div>
                <div>
                    <label class="form-label">Urutan</label>
                    <input type="number" name="sort_order" class="form-input" value="0" min="0">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-add-variant').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Varian</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Variant -->
<div id="modal-edit-variant" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-edit-variant').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Edit Varian</h3>
        
        <form id="form-edit-variant" action="" method="POST">
            <?= Helper::csrfField() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Nama Varian</label>
                    <input type="text" name="name" id="ev-name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" id="ev-price" class="form-input" min="0" step="100" required>
                </div>
                <div>
                    <label class="form-label">Durasi (Hari)</label>
                    <input type="number" name="duration_days" id="ev-duration" class="form-input" min="1" required>
                </div>
                <div>
                    <label class="form-label">Tipe</label>
                    <select name="type" id="ev-type" class="form-input">
                        <option value="sharing">Sharing</option>
                        <option value="private">Private</option>
                        <option value="semi_private">Semi Private</option>
                        <option value="invite">Invite/Member</option>
                        <option value="redeem">Kode Redeem</option>
                        <option value="service">Jasa</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Mode Fulfillment</label>
                    <select name="fulfillment_mode" id="ev-mode" class="form-input">
                        <option value="manual">Manual</option>
                        <option value="auto_stock">Auto Stock</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Platform</label>
                    <select name="platform" id="ev-platform" class="form-input">
                        <option value="all">Semua</option>
                        <option value="android">Android</option>
                        <option value="ios">iOS</option>
                        <option value="web">Web</option>
                        <option value="apk">APK</option>
                        <option value="mobile">Mobile</option>
                        <option value="tv">TV</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Max Users</label>
                    <input type="number" name="max_users" id="ev-maxusers" class="form-input" min="1">
                </div>
                <div>
                    <label class="form-label">Garansi (Hari)</label>
                    <input type="number" name="warranty_days" id="ev-warranty" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Urutan</label>
                    <input type="number" name="sort_order" id="ev-sort" class="form-input" min="0">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" id="ev-notes" class="form-input" rows="2"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="ev-active" value="1" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm">Aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-edit-variant').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Update Varian</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditVariant(v) {
    document.getElementById('form-edit-variant').action = '/admin/variants/' + v.id + '/update';
    document.getElementById('ev-name').value = v.name;
    document.getElementById('ev-price').value = v.price;
    document.getElementById('ev-duration').value = v.duration_days;
    document.getElementById('ev-type').value = v.type;
    document.getElementById('ev-mode').value = v.fulfillment_mode;
    document.getElementById('ev-platform').value = v.platform;
    document.getElementById('ev-maxusers').value = v.max_users;
    document.getElementById('ev-warranty').value = v.warranty_days;
    document.getElementById('ev-sort').value = v.sort_order;
    document.getElementById('ev-notes').value = v.notes || '';
    document.getElementById('ev-active').checked = v.is_active == 1;
    document.getElementById('modal-edit-variant').classList.remove('hidden');
}
</script>
