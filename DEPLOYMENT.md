# RZDK Store — Panduan Deployment ke cPanel

**Versi:** 1.0  
**Target:** Shared Hosting cPanel  
**Domain:** https://rzdkstore.my.id  
**PHP:** 8.4

---

## Prasyarat

- [x] Akses cPanel hosting
- [x] Domain sudah diarahkan ke hosting
- [x] PHP 8.4 aktif (cek di cPanel → Select PHP Version)
- [x] MySQL database tersedia
- [x] SSL/HTTPS aktif (Let's Encrypt via cPanel)

---

## Langkah 1: Setup Database

1. Login cPanel → **MySQL Databases**
2. Buat database baru: `rzdkstore_db` (atau sesuai prefix hosting, misal `rzdkst_db`)
3. Buat user baru: `rzdkstore_user` (atau sesuai prefix hosting)
4. Assign user ke database dengan **ALL PRIVILEGES**
5. Buka **phpMyAdmin** → Pilih database yang baru dibuat
6. Klik tab **Import** → Upload file `migrations/rzdkstore_database.sql`
7. Klik **Go** → Pastikan semua tabel terbuat (18 tabel + seed data)

---

## Langkah 2: Upload File Project

### Opsi A: File Manager (Recommended)
1. Compress seluruh project menjadi `.zip` (kecuali folder `.git`)
2. Login cPanel → **File Manager**
3. Navigate ke `public_html/`
4. **Hapus** semua file default (index.html, cgi-bin, dll)
5. Upload file `.zip` ke `public_html/`
6. Extract di sana
7. Pastikan `index.php` berada langsung di `public_html/index.php`

### Opsi B: FTP
1. Gunakan FileZilla atau FTP client lainnya
2. Connect ke hosting (cek credential di cPanel → FTP Accounts)
3. Upload semua file ke `/public_html/`

---

## Langkah 3: Konfigurasi

### 3.1 Database (`config/database.php`)
```php
'host' => 'localhost',
'port' => '3306',
'database' => 'rzdkst_db',       // Sesuaikan
'username' => 'rzdkst_user',     // Sesuaikan
'password' => 'PASSWORD_ANDA',   // Sesuaikan
```

### 3.2 Application (`config/app.php`)
```php
'url' => 'https://rzdkstore.my.id',
'encryption_key' => 'GANTI_DENGAN_STRING_RANDOM_64_KARAKTER',  // WAJIB GANTI!
'debug' => false,  // WAJIB false di production
```

**Generate encryption key:** Jalankan di browser → `https://rzdkstore.my.id/generate-key` lalu hapus route-nya, ATAU generate manual: buka PHP terminal dan jalankan `echo bin2hex(random_bytes(32));`

### 3.3 Email (`config/mail.php`)
1. cPanel → **Email Accounts** → Buat: `noreply@rzdkstore.my.id`
2. Update config:
```php
'smtp_host' => 'mail.rzdkstore.my.id',
'smtp_port' => 465,
'smtp_username' => 'noreply@rzdkstore.my.id',
'smtp_password' => 'PASSWORD_EMAIL',
'smtp_encryption' => 'ssl',
```

---

## Langkah 4: Set Permissions

Di File Manager atau via FTP, set permission:
```
storage/               → 755
storage/logs/          → 755
storage/uploads/       → 755
assets/img/            → 755
```

---

## Langkah 5: Upload QRIS

1. Siapkan gambar QRIS statis Anda (format: PNG/JPG/WebP)
2. Upload ke: `public_html/assets/img/qrisrzdkstore.png`
3. Atau upload nanti via Admin Panel → Settings → QRIS

---

## Langkah 6: Setup Cron Jobs

Login cPanel → **Cron Jobs**

Tambahkan 3 cron job:

| Schedule | Command |
|----------|---------|
| Every 5 minutes (`*/5 * * * *`) | `/usr/local/bin/php /home/USERNAME/public_html/cron/check_payment_expired.php` |
| Daily at 8:00 AM (`0 8 * * *`) | `/usr/local/bin/php /home/USERNAME/public_html/cron/send_expiry_reminder.php` |
| Daily at 0:05 AM (`5 0 * * *`) | `/usr/local/bin/php /home/USERNAME/public_html/cron/update_expired_slots.php` |

> **Catatan:** Ganti `USERNAME` dengan username cPanel Anda.

---

## Langkah 7: Login Admin Pertama Kali

1. Buka: `https://rzdkstore.my.id/login`
2. Login dengan credential default:
   - **Username:** `admin`
   - **Password:** `admin123`
3. **SEGERA** ubah password di Admin Panel → Settings atau langsung di phpMyAdmin

---

## Langkah 8: Verifikasi

Cek semua halaman:
- [ ] `https://rzdkstore.my.id/` — Landing page tampil
- [ ] `https://rzdkstore.my.id/products` — Katalog produk (public)
- [ ] `https://rzdkstore.my.id/login` — Halaman login
- [ ] `https://rzdkstore.my.id/register` — Halaman register
- [ ] `https://rzdkstore.my.id/admin/dashboard` — Admin dashboard
- [ ] Coba register akun baru → Cek email verifikasi masuk
- [ ] Coba buat order → Flow lengkap
- [ ] Cek QRIS tampil di halaman payment
- [ ] Cek `.htaccess` berjalan (coba akses `/config/` → harus 403 Forbidden)

---

## Langkah 9: Post-Deploy Checklist

- [ ] Ganti password admin default
- [ ] Ganti `encryption_key` di `config/app.php`
- [ ] Set `debug => false` di `config/app.php`
- [ ] Test email (register akun dummy)
- [ ] Upload QRIS image
- [ ] Input produk pertama via Admin Panel
- [ ] Setup cron jobs
- [ ] Backup database secara berkala (cPanel → Backup)

---

## Troubleshooting

### Error 500 (Internal Server Error)
- Cek `storage/logs/error.log`
- Pastikan PHP version 8.4
- Pastikan `mod_rewrite` aktif
- Cek permission folder `storage/`

### Email Tidak Terkirim
- Cek credential SMTP di `config/mail.php`
- Cek email limit hosting (biasanya 500/jam)
- Cek spam folder penerima
- Test kirim manual di phpMyAdmin: `SELECT * FROM activity_logs ORDER BY id DESC LIMIT 5`

### .htaccess Tidak Berfungsi
- Pastikan `mod_rewrite` aktif (cek di cPanel → Apache Handlers)
- Pastikan file `.htaccess` ada di `public_html/`
- Cek apakah hosting support `AllowOverride All`

### Database Error
- Pastikan credential di `config/database.php` benar
- Pastikan user memiliki ALL PRIVILEGES pada database
- Cek apakah semua tabel ter-import dengan benar (18 tabel)

---

## Struktur Final di Hosting

```
public_html/
├── index.php
├── .htaccess
├── config/
├── core/
├── controllers/
├── models/
├── views/
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   │   └── qrisrzdkstore.png  ← Upload manual
│   └── fonts/
├── migrations/
├── storage/
│   ├── logs/
│   └── uploads/
└── cron/
```

---

## Backup Strategy

1. **Database:** cPanel → Backup → Download MySQL backup (mingguan)
2. **Files:** cPanel → Backup → Full backup (bulanan)
3. **Atau** setup auto-backup jika hosting support

---

*Dokumen ini dibuat sebagai panduan deployment RZDK Store ke production.*
