<?php /** Admin Announcements Management View */ ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-heading font-bold">Pengumuman</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kirim pengumuman ke customer berdasarkan produk yang mereka beli</p>
    </div>
    <button onclick="document.getElementById('modal-add-announcement').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Buat Pengumuman
    </button>
</div>

<!-- Announcements List -->
<?php if (empty($announcements)): ?>
    <div class="card-flat text-center py-8">
        <p class="text-sm text-gray-400">Belum ada pengumuman.</p>
    </div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($announcements as $ann): ?>
    <div class="card-flat">
        <div class="flex items-start justify-between gap-4 mb-3">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-medium"><?= Helper::e($ann['title']) ?></h3>
                    <span class="px-2 py-0.5 rounded text-xs font-medium capitalize <?= $ann['priority'] === 'urgent' ? 'bg-red-500/10 text-red-400' : ($ann['priority'] === 'warning' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-blue-500/10 text-blue-400') ?>"><?= $ann['priority'] ?></span>
                    <?php if (!$ann['is_active']): ?>
                        <span class="px-2 py-0.5 rounded bg-gray-500/10 text-gray-400 text-xs">Nonaktif</span>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    Target: <strong><?= Helper::e($ann['product_name']) ?></strong> &middot; 
                    Published: <?= Helper::formatDate($ann['published_at']) ?> &middot;
                    Dibaca: <?= (int)$ann['read_count'] ?> customer
                    <?php if ($ann['expires_at']): ?>
                        &middot; Expired: <?= Helper::formatDate($ann['expires_at']) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="openEditAnnouncement(<?= htmlspecialchars(json_encode($ann)) ?>)" class="text-xs text-primary hover:text-primary-hover">Edit</button>
                <form action="/admin/announcements/<?= (int)$ann['id'] ?>/delete" method="POST" class="inline" data-confirm="Hapus pengumuman ini?">
                    <?= Helper::csrfField() ?>
                    <button type="submit" class="text-xs text-red-500 hover:text-red-400">Hapus</button>
                </form>
            </div>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?= nl2br(Helper::e(Helper::truncate($ann['content'], 300))) ?></p>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal: Add Announcement -->
<div id="modal-add-announcement" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-add-announcement').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Buat Pengumuman</h3>
        <form action="/admin/announcements" method="POST">
            <?= Helper::csrfField() ?>
            <div class="space-y-4 mb-4">
                <div>
                    <label class="form-label">Produk Target <span class="text-red-500">*</span></label>
                    <select name="product_id" class="form-input" required>
                        <option value="">Pilih produk...</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= Helper::e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hanya customer yang pernah membeli produk ini yang akan melihat pengumuman.</p>
                </div>
                <div>
                    <label class="form-label">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-input" placeholder="Judul pengumuman" required>
                </div>
                <div>
                    <label class="form-label">Konten <span class="text-red-500">*</span></label>
                    <textarea name="content" class="form-input" rows="4" placeholder="Isi pengumuman..." required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Prioritas</label>
                        <select name="priority" class="form-input">
                            <option value="info">Info (biru)</option>
                            <option value="warning">Warning (kuning)</option>
                            <option value="urgent">Urgent (merah)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Expired (opsional)</label>
                        <input type="datetime-local" name="expires_at" class="form-input">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-add-announcement').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Publikasikan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Announcement -->
<div id="modal-edit-announcement" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-edit-announcement').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Edit Pengumuman</h3>
        <form id="form-edit-announcement" action="" method="POST">
            <?= Helper::csrfField() ?>
            <div class="space-y-4 mb-4">
                <div>
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" id="eann-title" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Konten</label>
                    <textarea name="content" id="eann-content" class="form-input" rows="4" required></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Prioritas</label>
                        <select name="priority" id="eann-priority" class="form-input">
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Expired</label>
                        <input type="datetime-local" name="expires_at" id="eann-expires" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="eann-active" value="1" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm">Aktif</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-edit-announcement').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAnnouncement(a) {
    document.getElementById('form-edit-announcement').action = '/admin/announcements/' + a.id + '/update';
    document.getElementById('eann-title').value = a.title;
    document.getElementById('eann-content').value = a.content;
    document.getElementById('eann-priority').value = a.priority;
    document.getElementById('eann-expires').value = a.expires_at ? a.expires_at.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('eann-active').checked = a.is_active == 1;
    document.getElementById('modal-edit-announcement').classList.remove('hidden');
}
</script>
