<?php
/**
 * Sidebar Component - Admin Panel
 * Used in: views/layouts/admin.php
 */
?>
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-dark border-r border-gray-200 dark:border-dark-border transform lg:translate-x-0 lg:static lg:inset-auto transition-transform duration-200 ease-in-out flex flex-col">

    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-dark-border shrink-0">
        <a href="/admin/dashboard" class="flex items-center gap-2">
            <span class="text-xl font-heading font-bold text-primary">RZDK</span>
            <span class="text-xl font-heading font-bold">Admin</span>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden ml-auto p-1 rounded hover:bg-gray-100 dark:hover:bg-dark-border">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="/admin/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/dashboard', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    Dashboard
                </a>
            </li>

            <!-- Divider: Store -->
            <li class="pt-4 pb-2">
                <span class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Toko</span>
            </li>

            <!-- Products -->
            <li>
                <a href="/admin/products" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/products', 'bg-primary/10 text-primary') ?> <?= Helper::activeClass('/admin/categories', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    Produk
                </a>
            </li>

            <!-- Orders -->
            <li>
                <a href="/admin/orders" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/orders', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/><path d="m12 12 4 10 1.7-4.3L22 16Z"/></svg>
                    Order
                </a>
            </li>

            <!-- Customers -->
            <li>
                <a href="/admin/customers" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/customers', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Customer
                </a>
            </li>

            <!-- Finance -->
            <li>
                <a href="/admin/finance" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/finance', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Keuangan
                </a>
            </li>

            <!-- Divider: Tools -->
            <li class="pt-4 pb-2">
                <span class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Tools</span>
            </li>

            <!-- Vouchers -->
            <li>
                <a href="/admin/vouchers" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/vouchers', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                    Voucher
                </a>
            </li>

            <!-- Announcements -->
            <li>
                <a href="/admin/announcements" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/announcements', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                    Pengumuman
                </a>
            </li>

            <!-- Warranties -->
            <li>
                <a href="/admin/warranties" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/warranties', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    Garansi
                </a>
            </li>

            <!-- Divider: Slot Management -->
            <li class="pt-4 pb-2">
                <span class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Slot</span>
            </li>

            <!-- Netflix -->
            <li>
                <a href="/admin/netflix" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/netflix', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="15" x="2" y="7" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>
                    Netflix
                </a>
            </li>

            <!-- ChatGPT -->
            <li>
                <a href="/admin/chatgpt" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/chatgpt', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
                    ChatGPT
                </a>
            </li>

            <!-- Divider: System -->
            <li class="pt-4 pb-2">
                <span class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Sistem</span>
            </li>

            <!-- Settings -->
            <li>
                <a href="/admin/settings" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= Helper::activeClass('/admin/settings', 'bg-primary/10 text-primary') ?> hover:bg-gray-100 dark:hover:bg-dark-card">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    Settings
                </a>
            </li>

            <!-- Logout -->
            <li class="pt-2">
                <a href="/logout" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Logout
                </a>
            </li>
        </ul>
    </nav>
</aside>
