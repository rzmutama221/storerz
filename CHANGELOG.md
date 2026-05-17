# Changelog - RZDK Store

Semua perubahan signifikan pada project ini didokumentasikan di sini.

---

## [1.0.0] - 2026-05-17

### Phase 1: Foundation & Core Architecture
- Arsitektur MVC custom (Router, Controller, Model, Auth, Session, Middleware, Validator, Mailer, Helper)
- Database schema 18 tabel dengan foreign keys dan indexes
- .htaccess (URL rewrite, security headers, directory blocking, compression, caching)
- Design system: Dark/Light mode, Tailwind CSS, Poppins + Inter
- 4 layout templates (landing, admin, customer, auth)
- Responsive components (navbar, sidebar, footer, alert)

### Phase 2: Authentication System
- Register dengan email verification (token 24 jam)
- Login dengan brute force protection (5x gagal → lock 15 menit)
- Password reset via email (token 1 jam)
- Role-based middleware (admin/customer)
- CSRF protection di semua form

### Phase 3: Admin Panel - Produk & Kategori
- CRUD Kategori (modal inline)
- CRUD Produk (logo upload, slot type)
- CRUD Varian (harga, durasi, tipe, platform, fulfillment mode)
- Stock management (bulk input, encrypted storage)
- Admin dashboard (stats, alerts, recent orders)

### Phase 4: Customer Panel - Browse & Order
- Katalog produk (search, filter kategori)
- Detail produk + inline order form
- Sistem voucher (validasi code, hitung diskon)
- Order history + detail dengan status timeline
- Payment page (QRIS display, countdown, upload proof)

### Phase 5: Order Processing & Payment
- Admin order management (approve, reject, verify, fulfill, complete)
- Dual fulfillment (manual input + auto-stock assignment)
- Payment deadline dengan auto-expire (cron)
- Email notification saat order completed
- 3 cron jobs (payment expire, expiry reminder, slot update)
- Warranty claim management

### Phase 6: Fulfillment System
- Customer active accounts (decrypted credentials, show/hide, copy)
- Days remaining counter + expiry warning
- Warranty submission (eligible check, duplicate prevention)
- Customer profile + password change
- Public product catalog (landing page, no login required)

### Phase 7: Announcement & Voucher
- Announcement per produk (hanya buyer produk yang melihat)
- Priority levels (info, warning, urgent) + expiry
- Voucher CRUD (percentage/fixed, limits, scope, date range)
- Read tracking untuk announcements

### Phase 8: Finance & Customer Management
- Finance dashboard (revenue, bar chart, category breakdown, top products)
- CSV export (Excel-compatible UTF-8 BOM)
- Customer list (search, filter status)
- Customer detail (profile, stats, order history)
- Suspend/activate customer

### Phase 9: Netflix & ChatGPT Slot Management
- Netflix account CRUD + profile slot management
- ChatGPT Business account CRUD + member slot management
- Visual occupancy bars
- Assign/release slots with customer tracking
- Expiry date monitoring + auto-expire via cron

### Phase 10: Landing Page & Settings
- Admin settings (general, payment mode, SMTP, QRIS upload)
- FAQ section (6 item accordion)
- Settings stored in database (key-value)
- QRIS upload with validation

### Phase 11: Payment Gateway Integration
- Abstract gateway framework (factory pattern)
- 4 provider implementations: Tripay, Midtrans, Xendit, Duitku
- Universal callback controller (auto-confirm payment)
- Provider switchable via admin settings
- Fallback to QRIS manual if gateway inactive/error

### Phase 12: Final Deployment
- Comprehensive deployment guide (DEPLOYMENT.md)
- Changelog documentation
- Production-ready configuration

---

## Tech Stack

- **Backend:** PHP 8.4 (Native MVC)
- **Database:** MySQL 8.x
- **Frontend:** HTML5, Tailwind CSS (CDN), Alpine.js
- **Email:** SMTP cPanel (noreply@rzdkstore.my.id)
- **Hosting:** Shared hosting cPanel
- **Domain:** https://rzdkstore.my.id
