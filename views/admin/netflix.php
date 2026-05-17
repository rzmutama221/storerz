<?php
/**
 * Admin Netflix Management View
 * 
 * Manages Netflix head accounts and their profile slots.
 */
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-heading font-bold">Netflix Management</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola akun Netflix dan slot profil customer.</p>
    </div>
    <button onclick="document.getElementById('modal-add-account').classList.remove('hidden')" class="btn btn-primary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Tambah Akun
    </button>
</div>

<!-- Stats Row -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-value"><?= (int) ($stats['total_accounts'] ?? 0) ?></div>
        <div class="stat-label">Total Akun</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int) ($stats['total_slots'] ?? 0) ?></div>
        <div class="stat-label">Total Slot</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-green-400"><?= (int) ($stats['available'] ?? 0) ?></div>
        <div class="stat-label">Available</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-blue-400"><?= (int) ($stats['occupied'] ?? 0) ?></div>
        <div class="stat-label">Occupied</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-gray-400"><?= (int) ($stats['expired'] ?? 0) ?></div>
        <div class="stat-label">Expired</div>
    </div>
</div>

<!-- Accounts Section -->
<div class="mb-6">
    <h2 class="text-lg font-heading font-semibold mb-4">Daftar Akun Netflix</h2>

    <?php if (empty($accounts)): ?>
        <div class="card-flat">
            <p class="text-sm text-gray-400 text-center py-8">Belum ada akun Netflix. Klik "Tambah Akun" untuk memulai.</p>
        </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <?php foreach ($accounts as $account): ?>
        <div class="card-flat">
            <!-- Account Header -->
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-sm"><?= Helper::e($account['email']) ?></h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 rounded bg-red-500/10 text-red-400 text-xs font-medium capitalize"><?= Helper::e($account['plan']) ?></span>
                        <?php if (!$account['is_active']): ?>
                            <span class="px-2 py-0.5 rounded bg-gray-500/10 text-gray-400 text-xs">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                </div>
                <button onclick="openEditAccount(<?= htmlspecialchars(json_encode($account)) ?>)" class="text-xs text-primary hover:text-primary-hover">Edit</button>
            </div>

            <!-- Slot Occupancy Bar -->
            <?php
                $totalSlots = (int) $account['total_slots'];
                $occupiedSlots = (int) $account['occupied_slots'];
                $availableSlots = (int) $account['available_slots'];
                $expiredSlots = (int) $account['expired_slots'];
                $occupancyPct = $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100) : 0;
            ?>
            <div class="mb-3">
                <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                    <span>Slot Terisi: <?= $occupiedSlots ?>/<?= $totalSlots ?></span>
                    <span><?= $occupancyPct ?>%</span>
                </div>
                <div class="w-full h-2 bg-gray-200 dark:bg-dark-border rounded-full overflow-hidden">
                    <?php if ($totalSlots > 0): ?>
                    <div class="h-full flex">
                        <?php if ($occupiedSlots > 0): ?>
                        <div class="bg-blue-500 h-full" style="width: <?= round(($occupiedSlots / $totalSlots) * 100) ?>%"></div>
                        <?php endif; ?>
                        <?php if ($expiredSlots > 0): ?>
                        <div class="bg-gray-500 h-full" style="width: <?= round(($expiredSlots / $totalSlots) * 100) ?>%"></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> Available: <?= $availableSlots ?></span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Occupied: <?= $occupiedSlots ?></span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-500"></span> Expired: <?= $expiredSlots ?></span>
                </div>
            </div>

            <!-- Inline Slots -->
            <div class="space-y-2">
                <?php
                // Fetch slots for this account
                $db = Model::getConnection();
                $stmtSlots = $db->prepare("SELECT * FROM netflix_slots WHERE account_id = :account_id ORDER BY profile_name ASC");
                $stmtSlots->execute([':account_id' => $account['id']]);
                $accountSlots = $stmtSlots->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php foreach ($accountSlots as $slot): ?>
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-gray-50 dark:bg-dark-bg/50 border border-gray-100 dark:border-dark-border/50">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium"><?= Helper::e($slot['profile_name']) ?></span>
                        <?php if ($slot['status'] === 'available'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Tersedia</span>
                        <?php elseif ($slot['status'] === 'occupied'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">Terisi</span>
                        <?php elseif ($slot['status'] === 'expired'): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">Expired</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">Maintenance</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($slot['status'] === 'occupied'): ?>
                            <span class="text-xs text-gray-400"><?= Helper::e($slot['customer_name']) ?></span>
                            <form action="/admin/netflix/slots/<?= (int) $slot['id'] ?>/release" method="POST" class="inline" data-confirm="Release slot '<?= Helper::e($slot['profile_name']) ?>'?">
                                <?= Helper::csrfField() ?>
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400">Release</button>
                            </form>
                        <?php elseif ($slot['status'] === 'available' || $slot['status'] === 'expired'): ?>
                            <button onclick="openAssignNetflixSlot(<?= (int) $slot['id'] ?>, '<?= Helper::e($slot['profile_name']) ?>')" class="text-xs text-primary hover:text-primary-hover">Assign</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($account['notes']): ?>
            <p class="text-xs text-gray-400 mt-3 italic"><?= Helper::e($account['notes']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- All Occupied/Expired Slots Table -->
<div class="card-flat">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-heading font-semibold">Slot Occupied & Expired</h3>
        <span class="text-xs text-gray-400"><?= count($allSlots) ?> slot</span>
    </div>

    <?php if (empty($allSlots)): ?>
        <p class="text-sm text-gray-400 text-center py-8">Tidak ada slot yang sedang terisi atau expired.</p>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Akun</th>
                    <th>Profil</th>
                    <th>Customer</th>
                    <th>Device</th>
                    <th>Order Date</th>
                    <th>Expired</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allSlots as $slot): ?>
                <tr>
                    <td class="text-sm"><?= Helper::e($slot['account_email']) ?></td>
                    <td class="text-sm font-medium"><?= Helper::e($slot['profile_name']) ?></td>
                    <td>
                        <div class="text-sm"><?= Helper::e($slot['customer_name']) ?></div>
                        <?php if (!empty($slot['customer_username'])): ?>
                        <div class="text-xs text-gray-400">@<?= Helper::e($slot['customer_username']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($slot['device_type']): ?>
                        <span class="text-xs capitalize"><?= Helper::e($slot['device_type']) ?></span>
                        <?php else: ?>
                        <span class="text-xs text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-sm text-gray-400"><?= Helper::formatDate($slot['order_date']) ?></td>
                    <td class="text-sm">
                        <?php
                        $expDate = $slot['expired_date'] ?? null;
                        $isExpiringSoon = $expDate && (strtotime($expDate) - time()) < (3 * 86400) && strtotime($expDate) > time();
                        $isExpired = $expDate && strtotime($expDate) < time();
                        ?>
                        <?php if ($isExpired): ?>
                            <span class="text-red-400"><?= Helper::formatDate($expDate) ?></span>
                        <?php elseif ($isExpiringSoon): ?>
                            <span class="text-orange-400"><?= Helper::formatDate($expDate) ?></span>
                        <?php else: ?>
                            <?= Helper::formatDate($expDate) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= Helper::statusBadge($slot['status']) ?></td>
                    <td>
                        <?php if ($slot['status'] === 'occupied'): ?>
                        <form action="/admin/netflix/slots/<?= (int) $slot['id'] ?>/release" method="POST" class="inline" data-confirm="Release slot ini?">
                            <?= Helper::csrfField() ?>
                            <button type="submit" class="text-xs text-red-500 hover:text-red-400">Release</button>
                        </form>
                        <?php elseif ($slot['status'] === 'expired'): ?>
                        <button onclick="openAssignNetflixSlot(<?= (int) $slot['id'] ?>, '<?= Helper::e($slot['profile_name']) ?>')" class="text-xs text-primary hover:text-primary-hover">Re-assign</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal: Add Account -->
<div id="modal-add-account" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-add-account').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Tambah Akun Netflix</h3>
        <form action="/admin/netflix/accounts" method="POST">
            <?= Helper::csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" class="form-input" placeholder="netflix@email.com" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                    <input type="text" name="password" class="form-input" placeholder="Password akun Netflix" required>
                </div>
                <div>
                    <label class="form-label">Plan <span class="text-red-500">*</span></label>
                    <select name="plan" class="form-input" required>
                        <option value="basic">Basic</option>
                        <option value="standard">Standard</option>
                        <option value="premium" selected>Premium</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Max Profil <span class="text-red-500">*</span></label>
                    <input type="number" name="max_profiles" class="form-input" value="5" min="1" max="10" required>
                    <p class="text-xs text-gray-400 mt-1">Jumlah slot yang akan dibuat</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Catatan internal (opsional)"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-add-account').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Tambah Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Assign Slot -->
<div id="modal-assign-slot" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-assign-slot').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-1">Assign Slot Netflix</h3>
        <p class="text-sm text-gray-400 mb-4">Profil: <span id="assign-profile-name" class="font-medium text-white"></span></p>
        <form id="form-assign-slot" action="" method="POST">
            <?= Helper::csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Nama Customer <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" class="form-input" placeholder="Nama lengkap" required>
                </div>
                <div>
                    <label class="form-label">Kontak Customer</label>
                    <input type="text" name="customer_contact" class="form-input" placeholder="WA / Email">
                </div>
                <div>
                    <label class="form-label">Nama Profil</label>
                    <input type="text" name="profile_name" id="assign-profile-input" class="form-input" placeholder="Nama profil Netflix">
                </div>
                <div>
                    <label class="form-label">PIN Profil</label>
                    <input type="text" name="profile_pin" class="form-input" placeholder="4 digit (opsional)" maxlength="10">
                </div>
                <div>
                    <label class="form-label">Tipe Device</label>
                    <select name="device_type" class="form-input">
                        <option value="">-- Pilih --</option>
                        <option value="hp">HP</option>
                        <option value="laptop">Laptop</option>
                        <option value="tv">TV</option>
                        <option value="tablet">Tablet</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Expired Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expired_date" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Customer ID</label>
                    <input type="number" name="customer_id" class="form-input" placeholder="ID user (opsional)">
                </div>
                <div>
                    <label class="form-label">Order ID</label>
                    <input type="number" name="order_id" class="form-input" placeholder="ID order (opsional)">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Catatan internal (opsional)"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-assign-slot').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Assign Slot</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Account -->
<div id="modal-edit-account" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('modal-edit-account').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border rounded-xl shadow-xl w-full max-w-lg p-6 animate-fade-in my-8">
        <h3 class="text-lg font-heading font-semibold mb-4">Edit Akun Netflix</h3>
        <form id="form-edit-account" action="" method="POST">
            <?= Helper::csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="edit-email" class="form-input" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Password Baru</label>
                    <input type="text" name="password" class="form-input" placeholder="Kosongkan jika tidak berubah">
                </div>
                <div>
                    <label class="form-label">Plan</label>
                    <select name="plan" id="edit-plan" class="form-input">
                        <option value="basic">Basic</option>
                        <option value="standard">Standard</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer mt-6">
                        <input type="checkbox" name="is_active" id="edit-active" value="1" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <span class="text-sm">Aktif</span>
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" id="edit-notes" class="form-input" rows="2"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-dark-border">
                <button type="button" onclick="document.getElementById('modal-edit-account').classList.add('hidden')" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignNetflixSlot(slotId, profileName) {
    document.getElementById('form-assign-slot').action = '/admin/netflix/slots/' + slotId + '/assign';
    document.getElementById('assign-profile-name').textContent = profileName;
    document.getElementById('assign-profile-input').value = profileName;
    document.getElementById('modal-assign-slot').classList.remove('hidden');
}

function openEditAccount(account) {
    document.getElementById('form-edit-account').action = '/admin/netflix/accounts/' + account.id + '/update';
    document.getElementById('edit-email').value = account.email;
    document.getElementById('edit-plan').value = account.plan;
    document.getElementById('edit-notes').value = account.notes || '';
    document.getElementById('edit-active').checked = account.is_active == 1;
    document.getElementById('modal-edit-account').classList.remove('hidden');
}
</script>
