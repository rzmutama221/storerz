<?php /** Customer Active Accounts View */ ?>

<div class="mb-6">
    <h1 class="text-2xl font-heading font-bold">Akun Aktif</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Semua produk yang sedang dalam masa aktif</p>
</div>

<!-- Active Accounts -->
<?php if (empty($activeAccounts)): ?>
    <div class="card-flat text-center py-12 mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 dark:text-gray-600 mx-auto mb-4"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
        <p class="text-gray-400 mb-4">Belum ada produk aktif.</p>
        <a href="/dashboard/products" class="btn btn-primary btn-sm">Belanja Sekarang</a>
    </div>
<?php else: ?>
<div class="space-y-4 mb-8">
    <?php foreach ($activeAccounts as $account): ?>
    <div class="card-flat">
        <div class="flex items-start gap-4">
            <!-- Logo -->
            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-dark flex items-center justify-center overflow-hidden shrink-0">
                <?php if ($account['logo_path']): ?>
                    <img src="/<?= Helper::e($account['logo_path']) ?>" alt="" class="w-10 h-10 object-contain">
                <?php else: ?>
                    <span class="text-sm font-bold text-primary"><?= strtoupper(substr($account['product_name'], 0, 2)) ?></span>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-medium"><?= Helper::e($account['product_name']) ?></h3>
                    <span class="px-2 py-0.5 rounded bg-green-500/10 text-green-400 text-xs font-medium">Aktif</span>
                </div>
                <p class="text-sm text-gray-400 mt-0.5"><?= Helper::e($account['variant_name']) ?> &middot; <?= Helper::e($account['order_number']) ?></p>

                <!-- Expiry Info -->
                <div class="flex items-center gap-4 mt-2 text-sm">
                    <div>
                        <span class="text-gray-400">Expired:</span>
                        <span class="font-medium <?= $account['days_remaining'] <= 3 ? 'text-yellow-500' : 'text-green-500' ?>">
                            <?= Helper::formatDate($account['active_until'], 'd M Y') ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Sisa:</span>
                        <span class="font-medium <?= $account['days_remaining'] <= 3 ? 'text-yellow-500' : '' ?>">
                            <?= $account['days_remaining'] ?> hari
                        </span>
                    </div>
                </div>

                <!-- Credential Data -->
                <?php if ($account['decrypted_data']): ?>
                <div x-data="{ show: false }" class="mt-3">
                    <button @click="show = !show" class="text-xs text-primary hover:text-primary-hover transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span x-text="show ? 'Sembunyikan Data' : 'Lihat Data Akun'"></span>
                    </button>
                    <div x-show="show" x-transition class="mt-2 p-3 bg-gray-50 dark:bg-dark rounded-lg border border-gray-200 dark:border-dark-border">
                        <pre class="font-mono text-sm whitespace-pre-wrap break-all"><?= nl2br(Helper::e($account['decrypted_data'])) ?></pre>
                        <button onclick="RZDKStore.Clipboard.copy(`<?= addslashes(str_replace(["\r\n", "\n"], "\\n", $account['decrypted_data'])) ?>`, this)" class="mt-2 text-xs text-primary hover:text-primary-hover flex items-center gap-1 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            Copy
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($account['fulfillment_notes']): ?>
                <p class="text-xs text-gray-400 mt-2"><strong>Instruksi:</strong> <?= Helper::e($account['fulfillment_notes']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="shrink-0 flex flex-col gap-2">
                <?php if ($account['days_remaining'] <= 7): ?>
                <a href="/dashboard/products/<?= Helper::e($account['product_slug']) ?>" class="btn btn-primary btn-sm">Perpanjang</a>
                <?php endif; ?>
                <a href="/dashboard/orders/<?= (int) $account['id'] ?>" class="btn btn-secondary btn-sm">Detail</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Expired Accounts -->
<?php if (!empty($expiredAccounts)): ?>
<div class="mt-8">
    <h2 class="font-heading font-semibold text-sm mb-3 text-gray-400">Riwayat Expired</h2>
    <div class="card-flat">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Varian</th>
                        <th>Expired</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expiredAccounts as $exp): ?>
                    <tr>
                        <td class="text-sm"><?= Helper::e($exp['product_name']) ?></td>
                        <td class="text-sm text-gray-400"><?= Helper::e($exp['variant_name']) ?></td>
                        <td class="text-sm text-red-400"><?= Helper::formatDate($exp['active_until']) ?></td>
                        <td>
                            <a href="/dashboard/products/<?= Helper::e($exp['product_slug']) ?>" class="text-xs text-primary hover:text-primary-hover transition-colors">Order Lagi</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
