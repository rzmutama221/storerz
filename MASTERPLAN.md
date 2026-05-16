# MASTERPLAN - RZDK Store SaaS Panel
## Upgrade Major: Dari Katalog Statis ke Panel SaaS Digital Store

**Versi Dokumen:** 1.0  
**Tanggal:** 16 Mei 2026  
**Domain:** https://rzdkstore.my.id  
**Author:** RZDK Store Development Team

---

## Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Analisis Data Produk Existing](#2-analisis-data-produk-existing)
3. [Arsitektur & Tech Stack](#3-arsitektur--tech-stack)
4. [Struktur Sistem & Role](#4-struktur-sistem--role)
5. [Alur Bisnis & Transaksi](#5-alur-bisnis--transaksi)
6. [Skema Fulfillment Produk](#6-skema-fulfillment-produk)
7. [Sistem Slot Management (Netflix & ChatGPT)](#7-sistem-slot-management-netflix--chatgpt)
8. [Sistem Payment](#8-sistem-payment)
9. [Fitur Panel Admin](#9-fitur-panel-admin)
10. [Fitur Panel Customer](#10-fitur-panel-customer)
11. [Landing Page](#11-landing-page)
12. [Database Schema](#12-database-schema)
13. [Design System & UI Guidelines](#13-design-system--ui-guidelines)
14. [Keamanan & Autentikasi](#14-keamanan--autentikasi)
15. [Deployment & Infrastruktur](#15-deployment--infrastruktur)
16. [Fase Implementasi](#16-fase-implementasi)
17. [Catatan & Keputusan Arsitektur](#17-catatan--keputusan-arsitektur)

---

## 1. Ringkasan Eksekutif

### Kondisi Saat Ini
- Website statis berupa katalog produk sederhana
- 43 produk digital di 5 kategori (streaming, musik, kreatif, AI, utilitas)
- Semua transaksi diproses manual via WhatsApp (+62 851-1164-2004)
- Tidak ada sistem tracking order, customer management, atau reporting

### Tujuan Upgrade
Mengubah website statis menjadi **panel SaaS full-featured** dengan:
- Landing page profesional untuk akuisisi customer
- Dashboard terpisah untuk Admin dan Customer
- Sistem order, payment, dan fulfillment terintegrasi
- Management slot khusus Netflix & ChatGPT
- Reporting keuangan dan transaksi
- Sistem garansi dan announcement per produk
- Opsi payment gateway dengan fallback manual

### Prinsip Pengembangan
1. **Bertahap (Incremental)** - Setiap fase dieksekusi detail dan tuntas
2. **Kualitas > Kecepatan** - Tidak terburu-buru, fokus pada soliditas
3. **Versatile** - Sistem fleksibel untuk perubahan bisnis
4. **Self-contained** - Berjalan di shared hosting cPanel tanpa dependency eksotis

---

## 2. Analisis Data Produk Existing

### Struktur Kategori

| # | Kategori | Jumlah Produk | Range Harga |
|---|----------|---------------|-------------|
| 1 | Streaming Video & Hiburan | 14 | Rp 2.000 - Rp 130.000 |
| 2 | Streaming Musik | 2 | Rp 6.000 - Rp 42.900 |
| 3 | Aplikasi Kreatif | 11 | Rp 2.000 - Rp 195.000 |
| 4 | AI, Edukasi, & Penulisan | 9 | Rp 2.000 - Rp 75.000 |
| 5 | Produktivitas & Utilitas | 7 | Rp 6.000 - Rp 15.000 |

### Pola Data Produk yang Teridentifikasi

**Tipe Akun:**
- **Sharing** - Digunakan bersama beberapa user (ada slot limit)
- **Private** - Eksklusif satu user
- **Semi Private** - Hybrid (contoh: Netflix 1 Profile 2 Device)
- **Member/Invite** - Bergabung ke organisasi/family (ChatGPT Business, Canva, Spotify Family)
- **Kode Redeem** - Langsung redeem di platform terkait

**Variasi Durasi:**
- Harian (1, 2, 3 hari)
- Mingguan (7 hari)
- Bulanan (1, 2, 3, 6 bulan)
- Tahunan (1 tahun / 12 bulan)

**Batasan Platform:**
- Android Only (Ibis Paint, Wink, Apple Music Individual)
- iOS restriction (Spotify Student)
- Web Only (Remini 1 Bulan)
- APK Only (Remini 1 Tahun)
- All Device / Mobile Only / TV Only (Vidio)

**Produk Khusus dengan Slot System:**
- **Netflix** - Slot per profile/device, multiple sharing tiers
- **ChatGPT Business** - Slot per member dalam organisasi (invite email)

**Produk Jasa:**
- Scribd unlock file per dokumen (Rp 2.000) - bukan subscription

### Kompleksitas Produk Netflix
Netflix memiliki varian paling kompleks:
- Sharing 1 Profile 1 User (5 durasi: 1/2/3/7/30 hari)
- Sharing 1 Profile 2 User (5 durasi: 1/2/3/7/30 hari)
- Semi Private 1 Profile 2 Device (bulanan)
- Semi Private Request Nama & PIN (bulanan)

Ini memerlukan **slot tracking per akun Netflix** yang dimiliki admin.

### Kompleksitas ChatGPT Business
- Sistem invite member ke workspace Business
- Setiap "head account" punya kapasitas 4 member
- Perlu tracking: akun head mana, member siapa, kapan expired

---


## 3. Arsitektur & Tech Stack

### Tech Stack (Disesuaikan dengan cPanel Shared Hosting)

| Layer | Teknologi | Keterangan |
|-------|-----------|------------|
| **Backend** | PHP 8.4 | Native PHP dengan arsitektur MVC custom |
| **Database** | MySQL 8.x | Managed via phpMyAdmin |
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla/Alpine.js) | Ringan, no build step |
| **CSS Framework** | Tailwind CSS (CDN/pre-built) | Utility-first, sesuai design system |
| **Icons** | Lucide Icons / Heroicons (SVG) | Konsisten, ringan |
| **Email** | PHP mail() / SMTP cPanel | noreply@rzdkstore.my.id |
| **Session** | PHP Native Session | Secure, HttpOnly cookies |
| **File Upload** | PHP native | Untuk QRIS image, logo produk |
| **Templating** | PHP native (include/require) | Simple, no dependency |

### Kenapa Native PHP (Bukan Framework)?

1. **Kompatibilitas Hosting** - Shared hosting cPanel 100% support PHP native
2. **Tidak butuh Composer** - Menghindari dependency SSH yang mungkin tidak tersedia
3. **Full Control** - Tidak ada overhead framework
4. **Mudah Deploy** - Upload file langsung via File Manager / FTP
5. **Performance** - Tidak ada framework bootstrapping overhead

> **Catatan:** Jika di kemudian hari hosting di-upgrade ke VPS, kode bisa di-refactor ke Laravel/CodeIgniter. Arsitektur MVC yang kita bangun akan memudahkan migrasi.

### Arsitektur Folder Project

```
rzdkstore.my.id/
├── index.php                    # Landing page entry point
├── .htaccess                    # URL rewriting & security
├── config/
│   ├── database.php             # Koneksi MySQL
│   ├── app.php                  # Konfigurasi umum (site name, URL, dll)
│   ├── mail.php                 # SMTP settings
│   └── payment.php              # Payment gateway config & toggle
├── assets/
│   ├── css/
│   │   ├── tailwind.min.css     # Tailwind pre-built
│   │   └── custom.css           # Custom styles & overrides
│   ├── js/
│   │   ├── alpine.min.js        # Alpine.js for interactivity
│   │   ├── app.js               # Global JS utilities
│   │   └── admin.js             # Admin-specific JS
│   ├── img/
│   │   ├── products/            # Logo produk
│   │   ├── qrisrzdkstore.png    # QRIS statis
│   │   └── brand/               # Logo, favicon, OG image
│   └── fonts/                   # Poppins & Inter (self-hosted)
├── core/
│   ├── Router.php               # Simple router
│   ├── Controller.php           # Base controller
│   ├── Model.php                # Base model (query builder)
│   ├── Auth.php                 # Authentication handler
│   ├── Session.php              # Session management
│   ├── Validator.php            # Input validation
│   ├── Mailer.php               # Email sender
│   ├── Helper.php               # Utility functions
│   └── Middleware.php           # Auth & role middleware
├── controllers/
│   ├── AuthController.php       # Login, register, verify, reset
│   ├── LandingController.php    # Landing page
│   ├── admin/
│   │   ├── DashboardController.php
│   │   ├── ProductController.php
│   │   ├── OrderController.php
│   │   ├── CustomerController.php
│   │   ├── FinanceController.php
│   │   ├── VoucherController.php
│   │   ├── AnnouncementController.php
│   │   ├── NetflixController.php
│   │   ├── ChatGPTController.php
│   │   └── SettingController.php
│   └── customer/
│       ├── DashboardController.php
│       ├── ProductController.php
│       ├── OrderController.php
│       ├── AccountController.php
│       └── WarrantyController.php
├── models/
│   ├── User.php
│   ├── Product.php
│   ├── ProductVariant.php
│   ├── Order.php
│   ├── Transaction.php
│   ├── Voucher.php
│   ├── Announcement.php
│   ├── Warranty.php
│   ├── NetflixAccount.php
│   ├── NetflixSlot.php
│   ├── ChatGPTAccount.php
│   ├── ChatGPTMember.php
│   └── StockItem.php
├── views/
│   ├── layouts/
│   │   ├── landing.php          # Layout landing page
│   │   ├── admin.php            # Layout admin panel
│   │   ├── customer.php         # Layout customer panel
│   │   └── auth.php             # Layout halaman auth
│   ├── components/
│   │   ├── navbar.php
│   │   ├── sidebar.php
│   │   ├── footer.php
│   │   ├── alert.php
│   │   └── modal.php
│   ├── landing/                 # Halaman landing page
│   ├── auth/                    # Login, register, verify, reset
│   ├── admin/                   # Semua view admin
│   └── customer/                # Semua view customer
├── migrations/
│   └── rzdkstore_database.sql   # File SQL siap import
├── storage/
│   ├── logs/                    # Error & activity logs
│   └── uploads/                 # User uploads
└── docs/
    ├── MASTERPLAN.md            # Dokumen ini
    └── PRODUK_DATABASE.md       # Data produk reference
```

---

## 4. Struktur Sistem & Role

### Role & Permissions

| Role | Akses | Deskripsi |
|------|-------|-----------|
| **Admin** | Full access panel admin | Owner store (Anda) |
| **Customer** | Dashboard customer | User yang terdaftar & terverifikasi |

### Alur Akses

```
rzdkstore.my.id/                    → Landing Page (public)
rzdkstore.my.id/login               → Login page
rzdkstore.my.id/register            → Register page
rzdkstore.my.id/admin/dashboard     → Admin Dashboard (role: admin)
rzdkstore.my.id/dashboard           → Customer Dashboard (role: customer)
```

### Registrasi & Autentikasi

1. **Register:** Username + Email + Password → Email verifikasi dikirim
2. **Verifikasi:** Klik link verifikasi di email → Akun aktif
3. **Login:** Username + Password → Redirect sesuai role
4. **Lupa Password:** Input email → Link reset dikirim → Set password baru

---

## 5. Alur Bisnis & Transaksi

### Flow Order Customer (Perspektif Customer)

```
[1] Customer browse produk di dashboard
        ↓
[2] Pilih produk & varian → Klik "Order"
        ↓
[3] Form order (konfirmasi detail, apply voucher jika ada)
        ↓
[4] Order masuk ke sistem dengan status: "PENDING_APPROVAL"
        ↓
[5] ⏳ Menunggu Admin approve
        ↓
[6] Admin approve → Status: "APPROVED" → Customer dapat notifikasi
        ↓
[7] Customer melakukan payment (QRIS / Payment Gateway)
        ↓
[8] Upload bukti bayar (jika QRIS manual) / Auto-confirm (jika gateway)
        ↓
[9] Admin verifikasi payment → Status: "PAID"
        ↓
[10] Admin fulfill order:
     - Manual: Admin input data produk/akun → kirim via sistem
     - Auto: Sistem ambil dari stock → tampilkan ke customer
        ↓
[11] Status: "COMPLETED" → Customer bisa lihat detail produk di panel
        ↓
[12] Garansi aktif (sesuai durasi produk)
```

### Kenapa Ada Approval Sebelum Payment?

Ini adalah **keputusan bisnis krusial** yang Anda sebutkan:

> "Sebelum customer payment, harus saya acc terlebih dahulu untuk menghindari customer sudah bayar tetapi produknya tidak ada stoknya."

**Solusi yang kami rancang:**
- Order dibuat → Status `PENDING_APPROVAL`
- Admin menerima notifikasi di panel
- Admin cek ketersediaan real → Approve/Reject
- **Jika di-reject:** Customer mendapat info "stok habis" + alasan
- **Jika di-approve:** Customer baru bisa melanjutkan ke payment
- Ada **batas waktu payment** (misal 2 jam) setelah approved, setelah itu auto-cancel

### Status Order (State Machine)

```
PENDING_APPROVAL → APPROVED → AWAITING_PAYMENT → PAID → PROCESSING → COMPLETED
                 → REJECTED (stok habis/alasan lain)
                                               → PAYMENT_EXPIRED (timeout)
                                                        → REFUND (jika perlu)
                                                                 → CANCELLED
```

---

## 6. Skema Fulfillment Produk

### Dua Mode Fulfillment

| Mode | Deskripsi | Contoh Produk |
|------|-----------|---------------|
| **MANUAL** | Admin proses & kirim data via sistem web | Netflix sharing, ChatGPT invite, Apple Music invite |
| **AUTO_STOCK** | Sistem otomatis tampilkan data dari stok | VPN accounts, Kode redeem, Akun yang sudah ready |

### Detail Mode MANUAL
1. Customer order → Admin approve → Customer bayar → Admin verifikasi
2. Admin **proses manual** (buat akun, invite member, setting profile, dll)
3. Admin input **data fulfillment** ke sistem (credential, link invite, instruksi)
4. Customer menerima data di panel dashboard-nya
5. Status → COMPLETED

### Detail Mode AUTO_STOCK
1. Admin **pre-stock** data produk di sistem (bulk input credential/kode)
2. Customer order → Admin approve → Customer bayar → Admin verifikasi
3. Sistem **otomatis assign** 1 item dari stok ke customer
4. Customer langsung melihat data produk di panel
5. Status → COMPLETED, stok berkurang 1

### Hybrid Approach (Rekomendasi)
- Admin bisa **switch mode per varian produk** kapan saja
- Jika stok AUTO habis, otomatis fallback ke MANUAL
- Dashboard admin menampilkan **alert stok menipis**

### Data Fulfillment yang Dikirim ke Customer
Bergantung tipe produk, bisa berupa:
- Email + Password akun
- Link invite/join
- Kode redeem
- Instruksi step-by-step
- Kombinasi di atas

Format fulfillment bisa di-customize per produk (admin setting template).

---


## 7. Sistem Slot Management (Netflix & ChatGPT)

### 7.1 Netflix Slot Management

**Konsep:** Admin memiliki beberapa akun Netflix. Setiap akun punya profile (max 5) dan setiap profile bisa di-assign ke customer.

**Struktur Data:**
```
Netflix Account (Head)
├── Email: xxx@gmail.com
├── Password: ****
├── Plan: Premium
├── Max Profiles: 5
├── Profiles:
│   ├── Profile 1 → Customer A (expire: 15 Jun 2026)
│   ├── Profile 2 → Customer B (expire: 20 Jun 2026)
│   ├── Profile 3 → [AVAILABLE]
│   ├── Profile 4 → Customer C (expire: 10 Jun 2026)
│   └── Profile 5 → Customer D (expire: 30 Jun 2026)
```

**Fitur Panel Admin Netflix:**
- CRUD akun Netflix (head account)
- Assign/unassign customer ke profile/slot
- View status semua slot (occupied/available)
- Tracking tanggal order & expired per slot
- Info device login per customer
- Alert otomatis saat mendekati expired
- History customer per slot

**Data yang Disimpan per Slot:**
- Nama customer
- Kontak customer (WA/email)
- Tanggal order
- Tanggal expired
- Tipe device login (HP/Laptop/TV/Tablet)
- Nama profile Netflix
- PIN profile (jika ada)
- Status (active/expired/cancelled)

### 7.2 ChatGPT Business Slot Management

**Konsep:** Admin punya beberapa akun head ChatGPT Business. Tiap head bisa invite hingga 4 member.

**Struktur Data:**
```
ChatGPT Head Account
├── Email Head: admin1@domain.com
├── Workspace Name: RZDK Team 1
├── Max Members: 4
├── Members:
│   ├── Slot 1 → customer_email_1@gmail.com (expire: 20 Jun 2026)
│   ├── Slot 2 → customer_email_2@gmail.com (expire: 15 Jun 2026)
│   ├── Slot 3 → [AVAILABLE]
│   └── Slot 4 → customer_email_3@gmail.com (expire: 25 Jun 2026)
```

**Fitur Panel Admin ChatGPT:**
- CRUD akun head ChatGPT Business
- Assign/remove member ke slot
- Monitoring kapasitas per head
- Auto-input data saat ada order ChatGPT masuk (link ke order system)
- Tracking expired per member
- Alert renewal/expired

**Otomasi saat Order ChatGPT:**
1. Customer order ChatGPT Business → input email mereka di form order
2. Admin approve & verifikasi payment
3. Admin pilih head account mana yang akan digunakan → invite email customer
4. Sistem otomatis record: head mana, slot mana, email customer, tanggal expire
5. Mendekati expired → admin dapat alert → bisa contact customer untuk renewal

---

## 8. Sistem Payment

### 8.1 Payment Utama: QRIS Statis (Manual)

**Flow:**
1. Customer order di-approve → Muncul halaman payment
2. Ditampilkan QR Code QRIS (`qrisrzdkstore.png`) + nominal yang harus dibayar
3. Customer scan & bayar via e-wallet/m-banking
4. Customer upload bukti transfer di panel
5. Admin verifikasi manual di panel admin → Confirm payment
6. Order dilanjutkan ke proses fulfillment

**File QRIS:** `assets/img/qrisrzdkstore.png` (upload manual oleh admin)

### 8.2 Payment Gateway (Opsional, Future)

**Konsep:** Payment gateway terintegrasi yang bisa di-toggle ON/OFF oleh admin.

**Kandidat Gateway (Indonesia):**
- Midtrans
- Xendit
- Tripay (aggregator, lebih murah)
- Duitku

**Setting di Admin Panel:**
```
Payment Mode:
○ QRIS Manual Only (default)
○ Payment Gateway (auto-confirm)
○ Both (customer pilih)

Gateway Status:
○ Active
○ Maintenance/Error → Auto fallback ke manual

Gateway Config:
- Provider: [dropdown]
- API Key: [encrypted]
- Callback URL: auto-generated
```

**Flow jika Gateway Error/Maintenance:**
1. Admin switch ke mode manual
2. Customer yang sudah order → info payment berubah ke QRIS manual
3. Customer TIDAK perlu chat WA → Order tetap di panel
4. Admin verifikasi manual → confirm → proses selesai
5. Customer terima update status di dashboard

### 8.3 Batas Waktu Payment

- Setelah order di-approve: **2 jam** untuk melakukan payment
- Jika lewat → Status `PAYMENT_EXPIRED` → Order auto-cancel
- Customer bisa re-order (membuat order baru)
- Admin bisa setting durasi timeout ini

---

## 9. Fitur Panel Admin

### 9.1 Dashboard Admin
- **Statistik hari ini:** Total order, revenue hari ini, pending orders
- **Statistik bulan ini:** Total revenue, total transaksi, customer baru
- **Quick actions:** Approve pending orders, verify payments
- **Alert/Notifikasi:** Stok menipis, order baru, payment masuk, slot expired

### 9.2 Manajemen Produk
- CRUD Kategori produk
- CRUD Produk (nama, deskripsi, logo, status aktif/nonaktif)
- CRUD Varian produk per produk:
  - Nama varian
  - Harga
  - Durasi (hari)
  - Tipe (sharing/private/semi-private/invite/redeem)
  - Mode fulfillment (manual/auto_stock)
  - Platform restriction (all/android/ios/web)
  - Stok (jika auto_stock)
  - Catatan/info tambahan
  - Status (aktif/nonaktif)
- Bulk stock management (input banyak credential sekaligus)
- Setting template fulfillment per produk

### 9.3 Manajemen Order
- List semua order (filter: status, tanggal, customer, produk)
- Approve/Reject pending orders
- Verify payment (lihat bukti bayar, confirm/reject)
- Fulfill order (input data manual / assign dari stok)
- History lengkap per order (audit trail)

### 9.4 Manajemen Customer
- List semua customer (search, filter aktif/nonaktif)
- Detail customer: profil, history order, total spend
- Suspend/ban customer
- Reset password customer (trigger email)
- Catatan internal per customer

### 9.5 Keuangan & Reporting
- **Revenue harian:** Grafik + tabel transaksi hari ini
- **Revenue bulanan:** Grafik trend, breakdown per kategori/produk
- **Export:** Download laporan CSV/Excel per periode
- **Summary:** Total income, jumlah transaksi, average order value
- **Filter:** Per bulan, per produk, per kategori

### 9.6 Sistem Voucher
- CRUD kode voucher
- Tipe diskon: persentase (%) atau nominal (Rp)
- Setting: berlaku untuk produk tertentu / semua produk
- Limit penggunaan (total & per customer)
- Tanggal mulai & expired
- Status aktif/nonaktif
- History penggunaan voucher

### 9.7 Announcement per Produk
- Buat pengumuman yang ditargetkan per produk
- **Hanya customer yang pernah membeli produk tersebut** yang melihat announcement
- Contoh use case:
  - "Netflix maintenance malam ini jam 00:00-02:00"
  - "ChatGPT Business ada update fitur baru"
  - "Spotify Family plan ada perubahan kebijakan"
- Support: judul, konten (rich text), tanggal publish, expired
- Priority level: info / warning / urgent

### 9.8 Netflix Management
- (Detail di Section 7.1)

### 9.9 ChatGPT Management
- (Detail di Section 7.2)

### 9.10 Settings
- Profil admin (ubah password)
- Setting website (nama toko, deskripsi, kontak WA)
- Payment mode toggle
- Upload/ganti QRIS image
- Setting durasi payment timeout
- SMTP configuration
- Maintenance mode toggle

---

## 10. Fitur Panel Customer

### 10.1 Dashboard Customer
- **Selamat datang** + nama customer
- **Order aktif:** Produk yang masih dalam masa aktif
- **Order terakhir:** 5 transaksi terbaru
- **Announcement:** Pengumuman untuk produk yang dimiliki
- **Quick stats:** Total order, total spend

### 10.2 Katalog Produk
- Browse semua produk (by kategori)
- Detail produk + pilih varian
- Info stok (tersedia/habis)
- Tombol Order → Masuk ke flow order

### 10.3 Daftar Transaksi (Order History)
- Semua order yang pernah dibuat
- Filter by status
- Detail per order:
  - Produk & varian yang dipesan
  - Status terkini
  - Tanggal order
  - Tanggal payment
  - Tanggal completed
  - **Data produk/akun** (muncul setelah COMPLETED)
  - Info garansi (masa aktif, tanggal expired)

### 10.4 Detail Akun & Masa Aktif
- List semua produk yang sedang aktif
- Informasi masa aktif (tanggal mulai - tanggal expired)
- Countdown menjelang expired
- Info renewal (bisa langsung re-order)
- Data credential/akun yang diberikan (bisa di-copy)

### 10.5 Claim Garansi
- Form claim garansi:
  - Pilih order mana yang bermasalah
  - Jenis masalah (akun error, tidak bisa login, service down, dll)
  - Deskripsi masalah
  - Screenshot (opsional)
- Status tracking claim:
  - SUBMITTED → REVIEWING → RESOLVED / REJECTED
- Admin bisa respond + resolve (ganti akun, extend masa aktif, dll)
- History claim garansi

### 10.6 Profil & Pengaturan
- Edit profil (username, email)
- Ubah password
- Info akun (tanggal daftar, total order)

---

## 11. Landing Page

### Struktur Landing Page

```
[NAVBAR] - Logo | Produk | Fitur | Kontak | Login/Register

[HERO SECTION]
- Headline: "Premium Digital Store — Akses Layanan Premium Harga Terjangkau"
- Subheadline: Deskripsi singkat
- CTA: "Lihat Produk" + "Daftar Sekarang"
- Background: Gradient gelap dengan accent hijau

[PRODUK HIGHLIGHT]
- Grid 6-8 produk terlaris/terpopuler
- Card: Logo + Nama + Harga mulai dari
- CTA: "Lihat Semua Produk →"

[KATEGORI]
- 5 kategori dengan icon & jumlah produk
- Klik → section produk per kategori

[KEUNGGULAN/FITUR]
- Harga Terjangkau
- Garansi Full
- Proses Cepat
- Support 24/7
- Dashboard Tracking

[TESTIMONI] (opsional, future)

[FAQ]
- Cara order
- Metode pembayaran
- Garansi
- Perbedaan sharing vs private

[CTA SECTION]
- "Bergabung Sekarang — Gratis!"
- Tombol Register

[FOOTER]
- Logo + deskripsi
- Link: Produk, Tentang, Kontak, Syarat & Ketentuan
- WhatsApp: 085111642004
- © 2026 RZDK Store
```

---


## 12. Database Schema

### Diagram Relasi (Overview)

```
users ─────────────┐
                   ├── orders ──── order_items ──── stock_items
                   ├── warranties
                   └── (via orders) announcements_read

products ──── product_variants ──── stock_items
         └── announcements

vouchers ──── voucher_usage

netflix_accounts ──── netflix_slots ──── users (customer)
chatgpt_accounts ──── chatgpt_members ──── users (customer)

transactions ──── orders
settings (key-value)
```

### Tabel-Tabel Utama

#### `users`
```sql
- id (PK, INT, AUTO_INCREMENT)
- username (VARCHAR 50, UNIQUE)
- email (VARCHAR 100, UNIQUE)
- password_hash (VARCHAR 255)
- role ENUM('admin', 'customer')
- is_verified (TINYINT, default 0)
- verification_token (VARCHAR 100, NULLABLE)
- reset_token (VARCHAR 100, NULLABLE)
- reset_token_expires (DATETIME, NULLABLE)
- full_name (VARCHAR 100, NULLABLE)
- phone (VARCHAR 20, NULLABLE)
- status ENUM('active', 'suspended', 'banned')
- notes_admin (TEXT, NULLABLE)
- created_at (DATETIME)
- updated_at (DATETIME)
- last_login_at (DATETIME, NULLABLE)
```

#### `categories`
```sql
- id (PK, INT, AUTO_INCREMENT)
- name (VARCHAR 100)
- slug (VARCHAR 100, UNIQUE)
- icon (VARCHAR 50)
- sort_order (INT, default 0)
- is_active (TINYINT, default 1)
- created_at (DATETIME)
```

#### `products`
```sql
- id (PK, INT, AUTO_INCREMENT)
- category_id (FK → categories.id)
- name (VARCHAR 100)
- slug (VARCHAR 100, UNIQUE)
- description (TEXT)
- logo_path (VARCHAR 255)
- has_slot_system (TINYINT, default 0) -- Netflix/ChatGPT
- slot_type ENUM('none', 'netflix', 'chatgpt') DEFAULT 'none'
- is_active (TINYINT, default 1)
- sort_order (INT, default 0)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `product_variants`
```sql
- id (PK, INT, AUTO_INCREMENT)
- product_id (FK → products.id)
- name (VARCHAR 150) -- e.g. "Sharing 1 Bulan", "Private 3 Bulan"
- price (DECIMAL 10,2)
- duration_days (INT) -- durasi dalam hari
- type ENUM('sharing', 'private', 'semi_private', 'invite', 'redeem', 'service')
- fulfillment_mode ENUM('manual', 'auto_stock')
- platform ENUM('all', 'android', 'ios', 'web', 'apk', 'mobile', 'tv')
- max_users (INT, default 1) -- untuk sharing, berapa user per akun
- stock_count (INT, default 0) -- jika auto_stock
- notes (TEXT, NULLABLE) -- catatan khusus
- warranty_days (INT, default 0) -- durasi garansi
- is_active (TINYINT, default 1)
- sort_order (INT, default 0)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `stock_items`
```sql
- id (PK, INT, AUTO_INCREMENT)
- variant_id (FK → product_variants.id)
- data_content (TEXT) -- credential/kode/info (encrypted)
- is_sold (TINYINT, default 0)
- sold_to_order_id (FK → orders.id, NULLABLE)
- sold_at (DATETIME, NULLABLE)
- created_at (DATETIME)
```

#### `orders`
```sql
- id (PK, INT, AUTO_INCREMENT)
- order_number (VARCHAR 20, UNIQUE) -- e.g. "ORD-20260516-0001"
- user_id (FK → users.id)
- variant_id (FK → product_variants.id)
- quantity (INT, default 1)
- original_price (DECIMAL 10,2)
- discount_amount (DECIMAL 10,2, default 0)
- final_price (DECIMAL 10,2)
- voucher_id (FK → vouchers.id, NULLABLE)
- status ENUM('pending_approval','approved','awaiting_payment','paid','processing','completed','rejected','payment_expired','cancelled','refund')
- rejection_reason (TEXT, NULLABLE)
- approved_at (DATETIME, NULLABLE)
- payment_deadline (DATETIME, NULLABLE)
- paid_at (DATETIME, NULLABLE)
- completed_at (DATETIME, NULLABLE)
- fulfillment_data (TEXT, NULLABLE) -- data produk yang dikirim (encrypted)
- fulfillment_notes (TEXT, NULLABLE) -- instruksi tambahan
- active_until (DATETIME, NULLABLE) -- masa aktif produk
- payment_method ENUM('qris_manual', 'payment_gateway')
- payment_proof_path (VARCHAR 255, NULLABLE)
- admin_notes (TEXT, NULLABLE)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `transactions` (log keuangan)
```sql
- id (PK, INT, AUTO_INCREMENT)
- order_id (FK → orders.id)
- type ENUM('income', 'refund')
- amount (DECIMAL 10,2)
- description (VARCHAR 255)
- recorded_at (DATETIME)
```

#### `vouchers`
```sql
- id (PK, INT, AUTO_INCREMENT)
- code (VARCHAR 50, UNIQUE)
- type ENUM('percentage', 'fixed')
- value (DECIMAL 10,2) -- 10 untuk 10%, atau 5000 untuk Rp5000
- min_order (DECIMAL 10,2, default 0)
- max_discount (DECIMAL 10,2, NULLABLE) -- max potongan jika percentage
- applicable_products (TEXT, NULLABLE) -- JSON array product_ids, NULL = semua
- usage_limit (INT, NULLABLE) -- NULL = unlimited
- usage_per_user (INT, default 1)
- used_count (INT, default 0)
- start_date (DATE)
- end_date (DATE)
- is_active (TINYINT, default 1)
- created_at (DATETIME)
```

#### `voucher_usage`
```sql
- id (PK, INT, AUTO_INCREMENT)
- voucher_id (FK → vouchers.id)
- user_id (FK → users.id)
- order_id (FK → orders.id)
- used_at (DATETIME)
```

#### `announcements`
```sql
- id (PK, INT, AUTO_INCREMENT)
- product_id (FK → products.id) -- target product
- title (VARCHAR 200)
- content (TEXT)
- priority ENUM('info', 'warning', 'urgent')
- published_at (DATETIME)
- expires_at (DATETIME, NULLABLE)
- is_active (TINYINT, default 1)
- created_at (DATETIME)
```

#### `announcement_reads`
```sql
- id (PK, INT, AUTO_INCREMENT)
- announcement_id (FK → announcements.id)
- user_id (FK → users.id)
- read_at (DATETIME)
```

#### `warranties`
```sql
- id (PK, INT, AUTO_INCREMENT)
- order_id (FK → orders.id)
- user_id (FK → users.id)
- issue_type ENUM('login_error', 'service_down', 'account_banned', 'not_working', 'other')
- description (TEXT)
- screenshot_path (VARCHAR 255, NULLABLE)
- status ENUM('submitted', 'reviewing', 'resolved', 'rejected')
- admin_response (TEXT, NULLABLE)
- resolved_at (DATETIME, NULLABLE)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `netflix_accounts`
```sql
- id (PK, INT, AUTO_INCREMENT)
- email (VARCHAR 100)
- password (VARCHAR 255) -- encrypted
- plan ENUM('basic', 'standard', 'premium')
- max_profiles (INT, default 5)
- notes (TEXT, NULLABLE)
- is_active (TINYINT, default 1)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `netflix_slots`
```sql
- id (PK, INT, AUTO_INCREMENT)
- account_id (FK → netflix_accounts.id)
- profile_name (VARCHAR 50)
- profile_pin (VARCHAR 10, NULLABLE)
- customer_id (FK → users.id, NULLABLE)
- customer_name (VARCHAR 100, NULLABLE)
- customer_contact (VARCHAR 100, NULLABLE)
- order_id (FK → orders.id, NULLABLE)
- device_type ENUM('hp', 'laptop', 'tv', 'tablet', 'other', NULLABLE)
- order_date (DATE, NULLABLE)
- expired_date (DATE, NULLABLE)
- status ENUM('available', 'occupied', 'expired', 'maintenance')
- notes (TEXT, NULLABLE)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `chatgpt_accounts`
```sql
- id (PK, INT, AUTO_INCREMENT)
- head_email (VARCHAR 100)
- head_password (VARCHAR 255) -- encrypted
- workspace_name (VARCHAR 100)
- max_members (INT, default 4)
- notes (TEXT, NULLABLE)
- is_active (TINYINT, default 1)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `chatgpt_members`
```sql
- id (PK, INT, AUTO_INCREMENT)
- account_id (FK → chatgpt_accounts.id)
- slot_number (INT) -- 1-4
- customer_id (FK → users.id, NULLABLE)
- customer_email (VARCHAR 100, NULLABLE) -- email yang di-invite
- order_id (FK → orders.id, NULLABLE)
- invite_date (DATE, NULLABLE)
- expired_date (DATE, NULLABLE)
- status ENUM('available', 'occupied', 'expired', 'pending_invite')
- notes (TEXT, NULLABLE)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `settings`
```sql
- id (PK, INT, AUTO_INCREMENT)
- setting_key (VARCHAR 100, UNIQUE)
- setting_value (TEXT)
- updated_at (DATETIME)
```

#### `activity_logs`
```sql
- id (PK, INT, AUTO_INCREMENT)
- user_id (FK → users.id, NULLABLE)
- action (VARCHAR 100)
- description (TEXT)
- ip_address (VARCHAR 45)
- created_at (DATETIME)
```

---

## 13. Design System & UI Guidelines

### Palet Warna

| Nama | Hex | Penggunaan |
|------|-----|------------|
| **Background Primary** | `#171717` | Background utama (dark mode) |
| **Background Secondary** | `#2c2c2c` | Card, sidebar, elevated surfaces |
| **Green Primary** | `#01a35a` | Tombol utama, link, accent |
| **Green Accent/Hover** | `#0edf7d` | Hover state, highlight, badges |
| **Text Primary** | `#FFFFFF` | Teks utama di dark background |
| **Text Secondary** | `#A0A0A0` | Teks secondary, placeholder |
| **Border** | `#3a3a3a` | Border card, divider |
| **Danger** | `#EF4444` | Error, delete, urgent |
| **Warning** | `#F59E0B` | Warning, pending |
| **Success** | `#10B981` | Success state |
| **Info** | `#3B82F6` | Info, link secondary |

### Typography

| Elemen | Font | Weight | Size |
|--------|------|--------|------|
| H1 | Poppins | 700 (Bold) | 2rem / 32px |
| H2 | Poppins | 600 (SemiBold) | 1.5rem / 24px |
| H3 | Poppins | 600 (SemiBold) | 1.25rem / 20px |
| Body | Inter | 400 (Regular) | 1rem / 16px |
| Body Small | Inter | 400 (Regular) | 0.875rem / 14px |
| Caption | Inter | 400 (Regular) | 0.75rem / 12px |
| Button | Inter | 500 (Medium) | 0.875rem / 14px |
| Nav Link | Inter | 500 (Medium) | 0.875rem / 14px |

### Komponen UI

**Buttons:**
- Primary: bg `#01a35a`, hover `#0edf7d`, text white, rounded-lg
- Secondary: bg `#2c2c2c`, border `#3a3a3a`, hover border `#01a35a`
- Danger: bg `#EF4444`, hover darken

**Cards:**
- Background: `#2c2c2c`
- Border: 1px solid `#3a3a3a`
- Border-radius: 12px
- Padding: 1.5rem
- Hover: border-color `#01a35a` (subtle)

**Input Fields:**
- Background: `#171717`
- Border: 1px solid `#3a3a3a`
- Focus: border-color `#01a35a`, ring `#01a35a/20%`
- Text: white
- Placeholder: `#A0A0A0`

**Sidebar (Panel):**
- Background: `#171717`
- Width: 260px (desktop), full overlay (mobile)
- Active item: bg `#01a35a/10%`, border-left `#01a35a`

**Tables:**
- Header: bg `#2c2c2c`
- Rows: alternate `#171717` / `#1f1f1f`
- Border: `#3a3a3a`
- Hover row: bg `#2c2c2c`

### Responsive Breakpoints
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

---

## 14. Keamanan & Autentikasi

### Password Security
- Hashing: `password_hash()` dengan `PASSWORD_BCRYPT`
- Minimum 8 karakter
- Session regenerate ID setelah login
- Brute force protection: max 5 failed attempts → lockout 15 menit

### Session Security
- `session.cookie_httponly = true`
- `session.cookie_secure = true` (HTTPS)
- `session.use_strict_mode = true`
- Session timeout: 2 jam inactivity
- CSRF token di setiap form

### Input Validation & Sanitization
- Prepared statements (PDO) untuk semua query → prevent SQL injection
- `htmlspecialchars()` untuk output → prevent XSS
- Input validation di server-side (selalu)
- File upload validation: tipe, ukuran, rename

### Email Verification Flow
1. Register → Generate token (random 64 chars)
2. Kirim email ke user: `https://rzdkstore.my.id/verify?token=xxx`
3. User klik → Token dicek → `is_verified = 1`
4. Token single-use (dihapus setelah pakai)
5. Token expire: 24 jam

### Password Reset Flow
1. Input email → Cek ada di database
2. Generate reset token + set expiry (1 jam)
3. Kirim email: `https://rzdkstore.my.id/reset-password?token=xxx`
4. User klik → Form set password baru
5. Token single-use + time-limited

### Data Encryption
- Credential produk (di `stock_items.data_content`) di-encrypt dengan `openssl_encrypt()`
- Password Netflix/ChatGPT di-encrypt (bukan hash, karena perlu di-decrypt untuk ditampilkan)
- Encryption key disimpan di `config/` (di luar web root jika memungkinkan)

---

## 15. Deployment & Infrastruktur

### Environment
- **Hosting:** Shared hosting cPanel
- **Domain:** https://rzdkstore.my.id (root domain)
- **PHP:** 8.4
- **MySQL:** Via phpMyAdmin
- **SSL:** Let's Encrypt (auto dari cPanel)
- **Email:** SMTP cPanel → noreply@rzdkstore.my.id

### Deployment Steps
1. Setup database via phpMyAdmin (import `rzdkstore_database.sql`)
2. Upload semua file project ke `public_html/`
3. Set permission: `storage/` → 755, `storage/uploads/` → 755
4. Edit `config/database.php` dengan credential MySQL hosting
5. Edit `config/mail.php` dengan SMTP settings cPanel
6. Upload `qrisrzdkstore.png` ke `assets/img/`
7. Buat akun admin pertama via migration/seed atau manual insert
8. Test semua flow

### .htaccess (Root)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"

# Deny access to sensitive files
<FilesMatch "\.(sql|md|log)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Deny access to config directory
<IfModule mod_rewrite.c>
    RewriteRule ^config/ - [F,L]
    RewriteRule ^core/ - [F,L]
    RewriteRule ^storage/logs/ - [F,L]
</IfModule>
```

---


## 16. Fase Implementasi

### Prinsip: Bertahap, Detail, Berkualitas

Setiap fase harus **selesai sempurna dan teruji** sebelum melanjutkan ke fase berikutnya.

---

### FASE 1: Foundation & Core Architecture
**Estimasi: Sprint 1-2**

**Deliverables:**
- [x] Masterplan dokumen (dokumen ini)
- [ ] File SQL database lengkap (`rzdkstore_database.sql`)
- [ ] Core framework (Router, Controller, Model, Auth, Session, Middleware)
- [ ] Config files (database, app, mail, payment)
- [ ] .htaccess & security setup
- [ ] Base layouts (landing, admin, customer, auth)
- [ ] Design system implementation (CSS/Tailwind setup, fonts)

**Exit Criteria:**
- Routing berjalan dengan benar
- Koneksi database berhasil
- Layout dasar render dengan design system yang benar
- Security headers aktif

---

### FASE 2: Authentication System
**Estimasi: Sprint 3**

**Deliverables:**
- [ ] Halaman Register (form + validasi)
- [ ] Email verification (kirim email + verify token)
- [ ] Halaman Login (form + validasi + brute force protection)
- [ ] Session management (login state, role-based redirect)
- [ ] Password reset (request + email + reset form)
- [ ] Middleware auth & role guard
- [ ] Akun admin seeder/setup

**Exit Criteria:**
- User bisa register, verify email, login, logout
- Admin bisa login ke panel admin
- Customer hanya bisa akses panel customer
- Password reset berfungsi end-to-end

---

### FASE 3: Admin Panel - Produk & Kategori
**Estimasi: Sprint 4**

**Deliverables:**
- [ ] Admin dashboard (layout + sidebar + basic stats placeholder)
- [ ] CRUD Kategori
- [ ] CRUD Produk
- [ ] CRUD Varian Produk
- [ ] Stock management (input/bulk input stock items)
- [ ] Product image upload
- [ ] Fulfillment template per produk

**Exit Criteria:**
- Admin bisa manage semua produk dan varian
- Data produk dari PRODUK_DATABASE.md berhasil diinput
- Stok bisa diinput dan tertrack

---

### FASE 4: Customer Panel - Browse & Order
**Estimasi: Sprint 5**

**Deliverables:**
- [ ] Customer dashboard (layout + sidebar)
- [ ] Katalog produk (browse by category, search)
- [ ] Detail produk + pilih varian
- [ ] Form order (konfirmasi + catatan)
- [ ] Order submission → status PENDING_APPROVAL
- [ ] Order history list + detail view

**Exit Criteria:**
- Customer bisa browse, pilih produk, buat order
- Order masuk ke database dengan status yang benar

---

### FASE 5: Order Processing & Payment (QRIS Manual)
**Estimasi: Sprint 6-7**

**Deliverables:**
- [ ] Admin: List pending orders + approve/reject
- [ ] Notifikasi ke customer saat di-approve
- [ ] Halaman payment (tampilkan QRIS + nominal + deadline)
- [ ] Upload bukti bayar (customer)
- [ ] Admin: Verify payment (view bukti + confirm/reject)
- [ ] Payment timeout auto-expire (via cron atau check on access)
- [ ] Order status update flow lengkap

**Exit Criteria:**
- Full flow order dari pending → approved → paid → processing berjalan
- QRIS ditampilkan dengan benar
- Bukti bayar bisa diupload dan dilihat admin
- Timeout payment berfungsi

---

### FASE 6: Fulfillment System
**Estimasi: Sprint 8**

**Deliverables:**
- [ ] Admin fulfill manual (input data ke order)
- [ ] Admin fulfill auto (assign dari stock)
- [ ] Customer view fulfilled data di panel
- [ ] Data encryption untuk credential
- [ ] Masa aktif tracking (active_until)
- [ ] Status COMPLETED flow

**Exit Criteria:**
- Admin bisa fulfill order (manual & auto)
- Customer bisa lihat data produk setelah completed
- Credential tersimpan terenkripsi

---

### FASE 7: Garansi & Announcement
**Estimasi: Sprint 9**

**Deliverables:**
- [ ] Customer: Form claim garansi
- [ ] Admin: Review & resolve garansi
- [ ] Admin: CRUD announcement per produk
- [ ] Customer: Lihat announcement (hanya untuk produk yang dibeli)
- [ ] Read tracking (mark as read)

**Exit Criteria:**
- Sistem garansi end-to-end berfungsi
- Announcement tepat sasaran (hanya buyer produk terkait)

---

### FASE 8: Voucher & Keuangan
**Estimasi: Sprint 10**

**Deliverables:**
- [ ] Admin: CRUD voucher
- [ ] Customer: Apply voucher saat order
- [ ] Validasi voucher (limit, expired, applicable products)
- [ ] Admin: Dashboard keuangan (revenue harian/bulanan)
- [ ] Grafik & statistik (chart.js atau sejenisnya)
- [ ] Export laporan (CSV)

**Exit Criteria:**
- Voucher berfungsi dengan semua rule (limit, expired, per-product)
- Laporan keuangan akurat dan bisa di-export

---

### FASE 9: Netflix & ChatGPT Slot Management
**Estimasi: Sprint 11-12**

**Deliverables:**
- [ ] Admin: CRUD Netflix accounts & slots
- [ ] Admin: Assign/unassign customer ke Netflix slot
- [ ] Admin: CRUD ChatGPT accounts & members
- [ ] Admin: Assign/remove ChatGPT members
- [ ] Auto-link saat order Netflix/ChatGPT di-fulfill
- [ ] Expired alerts & monitoring dashboard
- [ ] Slot availability overview

**Exit Criteria:**
- Admin bisa manage semua akun Netflix & ChatGPT
- Slot tracking akurat (siapa, kapan order, kapan expired)
- Alert expired berfungsi

---

### FASE 10: Landing Page & Polish
**Estimasi: Sprint 13**

**Deliverables:**
- [ ] Landing page full implementation
- [ ] SEO meta tags
- [ ] Responsive design testing & fix
- [ ] Performance optimization
- [ ] Error handling & user-friendly messages
- [ ] Customer management (admin: list, detail, suspend)
- [ ] Activity logs

**Exit Criteria:**
- Landing page professional dan responsive
- Semua halaman responsive di mobile/tablet/desktop
- Error handling comprehensive

---

### FASE 11: Payment Gateway Integration (Opsional)
**Estimasi: Sprint 14**

**Deliverables:**
- [ ] Admin setting: payment mode toggle
- [ ] Gateway integration (Midtrans/Xendit/Tripay)
- [ ] Auto-confirm payment via callback
- [ ] Fallback ke manual jika gateway error
- [ ] Testing gateway di sandbox mode

**Exit Criteria:**
- Payment gateway berfungsi di mode aktif
- Switch manual/gateway seamless
- Fallback otomatis jika error

---

### FASE 12: Final Testing & Deployment
**Estimasi: Sprint 15**

**Deliverables:**
- [ ] Full integration testing
- [ ] Security audit (SQL injection, XSS, CSRF, file upload)
- [ ] Performance testing
- [ ] Deploy ke cPanel hosting
- [ ] Setup SMTP email
- [ ] Seed data admin + produk awal
- [ ] Documentation user guide

**Exit Criteria:**
- Website live dan berfungsi di https://rzdkstore.my.id
- Semua fitur teruji
- Admin bisa operasikan daily

---

## 17. Catatan & Keputusan Arsitektur

### Keputusan Penting

| # | Keputusan | Alasan |
|---|-----------|--------|
| 1 | Native PHP (tanpa framework) | Kompatibilitas shared hosting, no SSH needed |
| 2 | Approval sebelum payment | Mencegah customer bayar tapi stok kosong |
| 3 | Dual fulfillment mode | Fleksibel antara manual dan auto per produk |
| 4 | QRIS sebagai default | Simple, satu pintu, low maintenance |
| 5 | Gateway sebagai opsi toggle | Bisa dimatikan kapan saja tanpa breaking |
| 6 | Dark theme default | Sesuai branding, modern look untuk digital store |
| 7 | Slot system terpisah | Netflix & ChatGPT punya kompleksitas sendiri |
| 8 | Encrypted credentials | Keamanan data sensitif customer |
| 9 | Email verification wajib | Mencegah spam account, enable password reset |
| 10 | Incremental development | Kualitas terjaga, tidak overwhelming |

### Hal yang Perlu Dibahas Lebih Lanjut

1. **Notifikasi real-time:** Apakah cukup dengan refresh halaman, atau perlu polling/long-polling untuk notifikasi baru?
2. **Email notification:** Seberapa banyak email yang ingin dikirim ke customer? (order approved, payment confirmed, order completed, mendekati expired?)
3. **Multi-language:** Apakah perlu support bahasa lain selain Indonesia?
4. **Dark/Light mode toggle:** Atau fixed dark mode saja?
5. **Mobile app (future):** Apakah ada rencana mobile app? Jika ya, kita bisa siapkan API sederhana dari awal.
6. **Refund policy:** Bagaimana mekanisme refund jika ada? Manual transfer balik?
7. **Auto-renewal reminder:** Apakah perlu email reminder H-3 sebelum expired?
8. **Pricing tier:** Apakah akan ada perubahan harga? Perlu history harga?

### Constraints & Limitations

- **No Node.js/NPM:** Hosting tidak support → semua frontend tanpa build step
- **No WebSocket:** Shared hosting → gunakan polling jika perlu real-time
- **No Cron Job (mungkin):** Cek apakah cPanel support cron → jika tidak, gunakan "lazy check" (cek saat akses)
- **Storage limit:** Perhatikan ukuran file upload (bukti bayar, screenshot garansi)
- **Email limit:** Shared hosting biasanya limit email/jam → batch jika perlu kirim banyak

---

## Penutup

Dokumen ini adalah **living document** yang akan terus di-update seiring perkembangan project. Setiap fase akan menghasilkan update dan refinement terhadap masterplan ini.

**Prioritas utama:** Membangun fondasi yang solid (Fase 1-2) sebelum membangun fitur di atasnya.

---

*Dokumen ini dibuat sebagai panduan pengembangan RZDK Store SaaS Panel. Segala perubahan keputusan arsitektur harus didokumentasikan di sini.*

**Last Updated:** 16 Mei 2026  
**Status:** Draft v1.0 — Menunggu review & feedback owner
