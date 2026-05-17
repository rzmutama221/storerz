<?php /** Admin Vouchers Management View */ ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-heading font-bold">Voucher</h1>
    <button onclick="document.getElementById('modal-add-voucher').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Buat Voucher
    </button>
</div>

<!-- Vouchers Table -->
<div class="card-flat">
    <?php if (empty($vouchers)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Belum ada voucher.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tipe</th>
                    <th>Nilai</th>
                    <th>Penggunaan</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vouchers as $v): ?>
                <tr>
                    <td><code class="font-mono font-bold text-sm text-primary"><?= Helper::e($v['code']) ?></code></td>
                    <td>
                        <?php if ($v['type'] === 'percentage'): ?>
                            <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-xs">Persentase</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded bg-green-500/10 text-green-400 text-xs">Nominal</span>
                        <?php endif; ?>
                    </td>
                    <td class="font-medium text-sm">
                        <?= $v['type'] === 'percentage' ? (int)$v['value'] . '%' : Helper::formatPrice($v['value']) ?>
                        <?php if ($v['max_discount']): ?>
                            <span class="text-xs text-gray-400">(max <?= Helper::formatPrice($v['max_discount']) ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-sm">
                        <?= (int)$v['total_used'] ?> / <?= $v['usage_limit'] ? (int)$v['usage_limit'] : '∞' ?>
                    </td>
                    <td class="text-xs text-gray-400">
                        <?= Helper::formatDate($v['start_date']) ?> — <?= Helper::formatDate($v['end_date']) ?>
                    </td>
                    <td>
                        <?php
                        $now = date('Y-m-d');
                        if (!$v['is_active']): ?>
                            <span class="status-dot offline"></span>
                        <?php elseif ($v['end_date'] < $now): ?>
                            <span class="text-xs text-gray-400">Expired</span>
                        <?php elseif ($v['start_date'] > $now): ?>
                            <span class="text-xs text-blue-400">Scheduled</span>
                        <?php else: ?>
                            <span class="status-dot online"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <button onclick="openEditVoucher(<?= htmlspecialchars(json_encode($v)) ?>)" class="text-xs text-primary hover:text-primary-hover">Edit</button>
                            <form action="/admin/vouchers/<?= (int)$v['id'] ?>/delete" method="POST" class="inline" data-confirm="Hapus voucher '<?= Helper::e($v['code']) ?>'?">
                                <?= Helper::csrfField() ?>
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400">Hapus</button>
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

<!-- Modal: Add Voucher -->
<div id="modal-add-voucher" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-add-voucher').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Buat Voucher Baru</h3>
        <form action="/admin/vouchers" method="POST">
            <?= Helper::csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Kode Voucher <span class="text-red-500">*</span></label>
                    <input type="text" name="code" class="form-input font-mono uppercase" placeholder="DISKON10" required>
                </div>
                <div>
                    <label class="form-label">Tipe Diskon</label>
                    <select name="type" class="form-input">
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Nilai <span class="text-red-500">*</span></label>
                    <input type="number" name="value" class="form-input" placeholder="10" min="0" step="0.01" required>
                </div>
                <div>
                    <label class="form-label">Min. Order (Rp)</label>
                    <input type="number" name="min_order" class="form-input" value="0" min="0">
                </div>
                <div>
                    <label class="form-label">Max Diskon (Rp)</label>
                    <input type="number" name="max_discount" class="form-input" placeholder="Kosongkan jika tidak ada" min="0">
                    <p class="text-xs text-gray-400 mt-1">Untuk tipe persentase</p>
                </div>
                <div>
                    <label class="form-label">Limit Penggunaan</label>
                    <input type="number" name="usage_limit" class="form-input" placeholder="Kosongkan = unlimited" min="1">
                </div>
                <div>
                    <label class="form-label">Limit per User</label>
                    <input type="number" name="usage_per_user" class="form-input" value="1" min="1">
                </div>
                <div>
                    <label class="form-label">Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" class="form-input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div>
                    <label class="form-label">Berakhir <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" class="form-input" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Berlaku untuk Produk</label>
                    <select name="applicable_products[]" class="form-input" multiple size="4">
                        <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= Helper::e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Tahan Ctrl untuk pilih lebih dari satu. Kosongkan = berlaku semua produk.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-add-voucher').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Buat Voucher</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Voucher -->
<div id="modal-edit-voucher" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-edit-voucher').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Edit Voucher</h3>
        <form id="form-edit-voucher" action="" method="POST">
            <?= Helper::csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Tipe</label>
                    <select name="type" id="evch-type" class="form-input">
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Nilai</label>
                    <input type="number" name="value" id="evch-value" class="form-input" min="0" step="0.01" required>
                </div>
                <div>
                    <label class="form-label">Min. Order</label>
                    <input type="number" name="min_order" id="evch-minorder" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Max Diskon</label>
                    <input type="number" name="max_discount" id="evch-maxdiscount" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Limit</label>
                    <input type="number" name="usage_limit" id="evch-limit" class="form-input" min="1">
                </div>
                <div>
                    <label class="form-label">Per User</label>
                    <input type="number" name="usage_per_user" id="evch-peruser" class="form-input" min="1">
                </div>
                <div>
                    <label class="form-label">Mulai</label>
                    <input type="date" name="start_date" id="evch-start" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Berakhir</label>
                    <input type="date" name="end_date" id="evch-end" class="form-input" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="evch-active" value="1" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm">Aktif</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-edit-voucher').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditVoucher(v) {
    document.getElementById('form-edit-voucher').action = '/admin/vouchers/' + v.id + '/update';
    document.getElementById('evch-type').value = v.type;
    document.getElementById('evch-value').value = v.value;
    document.getElementById('evch-minorder').value = v.min_order;
    document.getElementById('evch-maxdiscount').value = v.max_discount || '';
    document.getElementById('evch-limit').value = v.usage_limit || '';
    document.getElementById('evch-peruser').value = v.usage_per_user;
    document.getElementById('evch-start').value = v.start_date;
    document.getElementById('evch-end').value = v.end_date;
    document.getElementById('evch-active').checked = v.is_active == 1;
    document.getElementById('modal-edit-voucher').classList.remove('hidden');
}
</script>
