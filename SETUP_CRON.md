# ⏰ Panduan Setup Cron Jobs di cPanel
## RZDK Store — rzdkstore.my.id

> **Untuk siapa panduan ini?**  
> Panduan ini ditulis untuk pengguna yang belum pernah mengatur cron job sebelumnya. Semua dijelaskan dari dasar, dengan analogi sederhana agar mudah dimengerti.

---

## Daftar Isi
1. [Apa Itu Cron Job dan Kenapa Diperlukan?](#1-apa-itu-cron-job-dan-kenapa-diperlukan)
2. [Cron Job yang Dibutuhkan RZDK Store](#2-cron-job-yang-dibutuhkan-rzdk-store)
3. [Langkah 1 — Cari Tahu Username cPanel Anda](#3-langkah-1--cari-tahu-username-cpanel-anda)
4. [Langkah 2 — Buka Menu Cron Jobs di cPanel](#4-langkah-2--buka-menu-cron-jobs-di-cpanel)
5. [Langkah 3 — Daftarkan Cron Job Pertama (Payment Expired)](#5-langkah-3--daftarkan-cron-job-pertama-payment-expired)
6. [Langkah 4 — Daftarkan Cron Job Kedua (Reminder Expired)](#6-langkah-4--daftarkan-cron-job-kedua-reminder-expired)
7. [Langkah 5 — Daftarkan Cron Job Ketiga (Update Slot)](#7-langkah-5--daftarkan-cron-job-ketiga-update-slot)
8. [Langkah 6 — Verifikasi Semua Cron Job](#8-langkah-6--verifikasi-semua-cron-job)
9. [Cara Membaca Format Jadwal Cron (Bonus)](#9-cara-membaca-format-jadwal-cron-bonus)
10. [Checklist Akhir](#10-checklist-akhir)
11. [Troubleshooting — Jika Cron Tidak Berjalan](#11-troubleshooting--jika-cron-tidak-berjalan)

---

## 1. Apa Itu Cron Job dan Kenapa Diperlukan?

### Analogi Sederhana

Bayangkan Anda punya **asisten toko** yang bertugas:
- Setiap 5 menit → Cek apakah ada customer yang terlambat bayar → Batalkan ordernya otomatis
- Setiap hari pagi jam 08.00 → Cek siapa yang produknya mau habis 3 hari lagi → Kirim email pengingat
- Setiap tengah malam → Bersihkan data slot Netflix/ChatGPT yang sudah expired

Nah, **Cron Job** adalah "asisten otomatis" itu. Dia berjalan di latar belakang, di server hosting, tanpa perlu Anda melakukan apapun.

### Kenapa RZDK Store Membutuhkannya?

| Tanpa Cron Job | Dengan Cron Job |
|----------------|-----------------|
| Order yang sudah melewati batas waktu bayar tidak pernah dibatalkan | Order expired otomatis terbatalkan setiap 5 menit |
| Customer tidak pernah dapat notifikasi produk mau habis | Email reminder terkirim otomatis H-3 expired |
| Slot Netflix/ChatGPT yang sudah expired statusnya tidak berubah | Slot otomatis ter-update statusnya setiap tengah malam |

---

## 2. Cron Job yang Dibutuhkan RZDK Store

RZDK Store membutuhkan **3 cron job**:

| # | Nama | Fungsi | Jadwal |
|---|------|--------|--------|
| 1 | **Check Payment Expired** | Batalkan order yang melewati batas waktu bayar | Setiap 5 menit |
| 2 | **Send Expiry Reminder** | Kirim email reminder H-3 sebelum produk expired | Setiap hari jam 08:00 |
| 3 | **Update Expired Slots** | Update status slot Netflix & ChatGPT yang sudah expired | Setiap hari jam 00:05 |

---

## 3. Langkah 1 — Cari Tahu Username cPanel Anda

Sebelum mulai, Anda perlu tahu **username cPanel** karena digunakan di dalam perintah cron.

### Cara Menemukan Username cPanel:

**Cara 1 — Lihat di URL cPanel:**
1. Login ke cPanel
2. Perhatikan URL di browser: `https://server.hosting.com:2083/cpsess.../frontend/USERNAME/...`
3. Bagian `USERNAME` itulah username Anda

**Cara 2 — Lihat di pojok kanan atas:**
1. Setelah login cPanel, lihat bagian pojok kanan atas
2. Biasanya tertulis "Hello, **namauser**" atau nama domain Anda

**Cara 3 — Tanya provider hosting:**
1. Hubungi support hosting Anda
2. Tanyakan: *"Berapa username cPanel saya?"*

> 📝 **Catat username Anda:** _________________________
>
> Contoh username: `rzdk123`, `rzdk_store`, `rzdkuser` (bukan email, bukan domain)

---

## 4. Langkah 2 — Buka Menu Cron Jobs di cPanel

1. Login ke cPanel Anda
2. Di halaman utama cPanel, cari bagian **"ADVANCED"** atau gunakan fitur Search
3. Ketik **"cron"** di kotak pencarian
4. Klik **"Cron Jobs"**

> 💡 Jika tidak ada di Advanced, coba cari di bagian **"Tools"** atau **"Developer Tools"**

---

### Tampilan Halaman Cron Jobs

Halaman Cron Jobs terdiri dari dua bagian:
- **Common Settings** → Tombol cepat untuk jadwal umum (pilihan ini akan kita gunakan)
- **Add New Cron Job** → Form manual untuk mengisi detail cron

Di bagian **"Add New Cron Job"**, Anda akan melihat 6 kolom:
```
[Minute] [Hour] [Day] [Month] [Weekday] [Command]
```

Jangan khawatir jika belum paham artinya — kita akan isi satu per satu dengan langkah yang jelas.

---

## 5. Langkah 3 — Daftarkan Cron Job Pertama (Payment Expired)

### Fungsi:
Membatalkan otomatis order yang melewati batas waktu bayar (default: 2 jam setelah di-approve).

### Jadwal: Setiap 5 Menit

**Cara Mengisi Form:**

Anda bisa gunakan **Common Settings** sebagai pintasan:
1. Pada bagian **"Common Settings"**, klik dropdown
2. Pilih **"Every 5 Minutes"**
3. Kolom jadwal akan otomatis terisi: `*/5 * * * *`

**Atau isi manual:**

| Kolom | Isi dengan | Penjelasan |
|-------|-----------|------------|
| **Minute** | `*/5` | Setiap 5 menit |
| **Hour** | `*` | Semua jam (tanda `*` = "setiap") |
| **Day** | `*` | Semua hari |
| **Month** | `*` | Semua bulan |
| **Weekday** | `*` | Semua hari dalam seminggu |

**Command (Perintah yang Dijalankan):**

Salin perintah berikut, lalu **ganti USERNAME** dengan username cPanel Anda:

```
/usr/local/bin/php /home/USERNAME/public_html/cron/check_payment_expired.php
```

**Contoh jika username Anda adalah `rzdk123`:**
```
/usr/local/bin/php /home/rzdk123/public_html/cron/check_payment_expired.php
```

**Langkah:**
1. Tempel perintah di atas ke kolom **"Command"**
2. Klik tombol **"Add New Cron Job"**
3. Jika berhasil, akan muncul pesan sukses di bagian atas halaman ✅

---

## 6. Langkah 4 — Daftarkan Cron Job Kedua (Reminder Expired)

### Fungsi:
Mengirim email pengingat ke customer yang produknya akan expired 3 hari lagi.

### Jadwal: Setiap Hari Jam 08:00 Pagi

**Isi Form:**

| Kolom | Isi dengan | Penjelasan |
|-------|-----------|------------|
| **Minute** | `0` | Menit ke-0 (tepat di menit 0) |
| **Hour** | `8` | Jam 8 pagi |
| **Day** | `*` | Setiap hari |
| **Month** | `*` | Setiap bulan |
| **Weekday** | `*` | Setiap hari dalam seminggu |

> 💡 **Catatan waktu:** Waktu di cPanel menggunakan **zona waktu server** (biasanya UTC). Jika server menggunakan UTC, maka jam 8 pagi WIB = jam 1 UTC. Tanyakan ke hosting Anda timezone server-nya, lalu sesuaikan.
>
> **Contoh penyesuaian:**
> - WIB (UTC+7): Jam 08:00 WIB → masukkan `1` di kolom Hour (karena 8 - 7 = 1 UTC)
> - WITA (UTC+8): Jam 08:00 WITA → masukkan `0` di kolom Hour
> - WIB dan server UTC: Jam 08:00 WIB = `Hour: 1, Minute: 0`

**Command (Perintah yang Dijalankan):**

Salin perintah berikut, lalu **ganti USERNAME**:

```
/usr/local/bin/php /home/USERNAME/public_html/cron/send_expiry_reminder.php
```

**Contoh:**
```
/usr/local/bin/php /home/rzdk123/public_html/cron/send_expiry_reminder.php
```

**Langkah:**
1. Isi kolom Minute: `0`
2. Isi kolom Hour: `1` (untuk WIB, atau `8` jika server WIB)
3. Biarkan kolom Day, Month, Weekday tetap `*`
4. Tempel perintah ke kolom **"Command"**
5. Klik **"Add New Cron Job"** ✅

---

## 7. Langkah 5 — Daftarkan Cron Job Ketiga (Update Slot)

### Fungsi:
Memperbarui status slot Netflix dan ChatGPT yang sudah melewati tanggal expired menjadi status "expired".

### Jadwal: Setiap Hari Jam 00:05 (5 menit setelah tengah malam)

**Isi Form:**

| Kolom | Isi dengan | Penjelasan |
|-------|-----------|------------|
| **Minute** | `5` | Menit ke-5 |
| **Hour** | `0` | Jam 0 (tengah malam) |
| **Day** | `*` | Setiap hari |
| **Month** | `*` | Setiap bulan |
| **Weekday** | `*` | Setiap hari dalam seminggu |

> 💡 **Kenapa jam 00:05, bukan 00:00?**  
> Dibuat 5 menit setelah tengah malam agar tidak bentrok jika ada proses lain yang berjalan tepat di jam 00:00. Ini praktik yang baik.
>
> **Penyesuaian timezone (WIB/UTC+7):**
> - Jam 00:05 WIB = jam 17:05 UTC hari sebelumnya
> - Jika server UTC: Minute: `5`, Hour: `17`

**Command (Perintah yang Dijalankan):**

Salin perintah berikut, lalu **ganti USERNAME**:

```
/usr/local/bin/php /home/USERNAME/public_html/cron/update_expired_slots.php
```

**Contoh:**
```
/usr/local/bin/php /home/rzdk123/public_html/cron/update_expired_slots.php
```

**Langkah:**
1. Isi kolom Minute: `5`
2. Isi kolom Hour: `0` (atau `17` jika server UTC dan Anda WIB)
3. Tempel perintah ke kolom **"Command"**
4. Klik **"Add New Cron Job"** ✅

---

## 8. Langkah 6 — Verifikasi Semua Cron Job

Setelah mendaftarkan ketiga cron job, halaman akan menampilkan daftar semua cron yang aktif.

### Tampilan yang Benar

Anda seharusnya melihat 3 baris seperti ini:

| Minute | Hour | Day | Month | Weekday | Command |
|--------|------|-----|-------|---------|---------|
| `*/5` | `*` | `*` | `*` | `*` | `/usr/local/bin/php /home/USERNAME/public_html/cron/check_payment_expired.php` |
| `0` | `1` | `*` | `*` | `*` | `/usr/local/bin/php /home/USERNAME/public_html/cron/send_expiry_reminder.php` |
| `5` | `0` | `*` | `*` | `*` | `/usr/local/bin/php /home/USERNAME/public_html/cron/update_expired_slots.php` |

---

### Cara Verifikasi Cron Berjalan

Setelah cron job berjalan pertama kali, ia akan membuat **file log**. Anda bisa cek apakah cron sudah pernah berjalan:

1. cPanel → **File Manager**
2. Navigate ke: `public_html/storage/logs/`
3. Cari file bernama `cron.log`
4. Jika file ada dan berisi teks → cron sudah berjalan ✅
5. Jika file tidak ada atau kosong → Cron belum pernah berjalan (normal jika baru dipasang, tunggu sesuai jadwal)

**Contoh isi `cron.log` yang normal:**
```
2026-05-17 00:05:03 - Slots expired: Netflix=2, ChatGPT=0
2026-05-17 01:00:01 - Expiry reminder: 3/3 emails sent (target: 2026-05-20)
2026-05-17 08:05:01 - 1 order(s) marked as payment_expired
```

---

## 9. Cara Membaca Format Jadwal Cron (Bonus)

Untuk memahami lebih dalam, berikut penjelasan format jadwal cron:

```
* * * * *
│ │ │ │ └── Hari dalam seminggu (0-7, 0 dan 7 = Minggu)
│ │ │ └──── Bulan (1-12)
│ │ └────── Tanggal dalam bulan (1-31)
│ └──────── Jam (0-23)
└────────── Menit (0-59)
```

### Contoh-Contoh Jadwal Cron

| Format | Artinya |
|--------|---------|
| `*/5 * * * *` | Setiap 5 menit, sepanjang hari, setiap hari |
| `0 8 * * *` | Tepat jam 08:00, setiap hari |
| `5 0 * * *` | Jam 00:05 (5 menit setelah tengah malam), setiap hari |
| `0 * * * *` | Tepat di menit ke-0 setiap jam (setiap jam) |
| `0 8 * * 1` | Jam 08:00 setiap hari Senin saja |
| `0 8,20 * * *` | Jam 08:00 DAN 20:00, setiap hari |
| `*/15 * * * *` | Setiap 15 menit |
| `0 0 1 * *` | Tengah malam setiap tanggal 1 (awal bulan) |

> 💡 **Tanda `*` artinya "setiap"** — `*` di kolom Jam = setiap jam, `*` di kolom Hari = setiap hari, dst.

---

## 10. Checklist Akhir

Centang semua item setelah selesai setup:

**Cron Job 1 — Check Payment Expired:**
- [ ] Sudah terdaftar di cPanel Cron Jobs
- [ ] Jadwal: `*/5 * * * *`
- [ ] Command sudah menggunakan username cPanel yang benar

**Cron Job 2 — Send Expiry Reminder:**
- [ ] Sudah terdaftar di cPanel Cron Jobs
- [ ] Jadwal: `0 1 * * *` (atau disesuaikan timezone)
- [ ] Command sudah menggunakan username cPanel yang benar

**Cron Job 3 — Update Expired Slots:**
- [ ] Sudah terdaftar di cPanel Cron Jobs
- [ ] Jadwal: `5 0 * * *` (atau disesuaikan timezone)
- [ ] Command sudah menggunakan username cPanel yang benar

**Verifikasi:**
- [ ] Total ada 3 cron job di halaman Cron Jobs cPanel
- [ ] Setelah beberapa jam, file `storage/logs/cron.log` sudah ada dan berisi log

---

## 11. Troubleshooting — Jika Cron Tidak Berjalan

### ❌ Problem 1: File cron.log kosong setelah beberapa jam

**Kemungkinan penyebab:**
1. Path file PHP salah
2. Username salah
3. Cron job tidak tersimpan

**Solusi:**

**Langkah 1 — Periksa path PHP:**
Beberapa hosting menggunakan path PHP yang berbeda. Coba verifikasi dengan cara:
1. cPanel → **Terminal** (jika tersedia)
2. Ketik: `which php` → Akan menampilkan path PHP yang benar
3. Contoh output: `/usr/local/bin/php` atau `/usr/bin/php`
4. Gunakan path yang muncul di perintah cron

Jika tidak ada Terminal di cPanel, hubungi support hosting dan tanyakan: *"Berapa path PHP yang benar untuk cron job di hosting ini?"*

**Langkah 2 — Verifikasi username:**
1. cPanel → klik nama Anda di pojok kanan atas → lihat username
2. Pastikan path `/home/USERNAME/` menggunakan username yang tepat

**Langkah 3 — Test manual via Terminal:**
Jika cPanel Anda punya Terminal:
```bash
/usr/local/bin/php /home/USERNAME/public_html/cron/check_payment_expired.php
```
Jika muncul output → PHP bisa jalan, masalah ada di penjadwalan.  
Jika muncul error → Perbaiki error tersebut terlebih dahulu.

---

### ❌ Problem 2: Error "No such file or directory"

**Penyebab:** Path folder salah — file cron tidak ada di lokasi yang dituju.

**Solusi:**
1. cPanel → File Manager
2. Cari folder `public_html/cron/`
3. Pastikan ada 3 file:
   - `check_payment_expired.php`
   - `send_expiry_reminder.php`
   - `update_expired_slots.php`
4. Jika folder `cron/` tidak ada → File project belum terupload dengan benar

---

### ❌ Problem 3: Error "Permission Denied"

**Penyebab:** File PHP tidak punya izin untuk dieksekusi.

**Solusi:**
1. cPanel → File Manager
2. Navigate ke `public_html/cron/`
3. Select semua file `.php` di folder cron
4. Klik **"Permissions"** atau **"Change Permissions"**
5. Set ke `644` (rw-r--r--)
6. Klik **"Change Permissions"**

---

### ❌ Problem 4: Cron Berjalan Tapi Email Reminder Tidak Terkirim

**Penyebab:** Setup SMTP belum benar atau ada error di pengiriman email.

**Solusi:**
1. Cek file `storage/logs/cron.log` → Lihat apakah ada baris "emails sent"
2. Contoh log sukses: `Expiry reminder: 1/1 emails sent`
3. Contoh log gagal: `Expiry reminder: 0/1 emails sent`
4. Jika 0 email terkirim → Ikuti panduan **SETUP_EMAIL.md** untuk perbaiki konfigurasi SMTP

---

### ❌ Problem 5: Cron Berjalan Dua Kali atau Sangat Sering

**Penyebab:** Tidak sengaja mendaftarkan cron job yang sama dua kali.

**Solusi:**
1. cPanel → Cron Jobs
2. Lihat daftar semua cron
3. Jika ada duplikat (perintah sama), hapus salah satunya dengan klik **"Delete"** atau ikon 🗑️

---

### ❌ Problem 6: Cron Tidak Ada di Menu cPanel

**Penyebab:** Beberapa paket hosting murah tidak menyertakan fitur Cron Jobs.

**Solusi:**
1. Hubungi support hosting dan tanyakan: *"Apakah paket hosting saya support Cron Jobs?"*
2. Jika tidak support → Pertimbangkan upgrade paket hosting
3. Alternatif: Gunakan layanan cron eksternal gratis seperti **cron-job.org**:
   - Daftar di https://cron-job.org
   - Buat 3 cron job yang memanggil URL website:
     - `https://rzdkstore.my.id/cron/run?key=SECRET_KEY&job=payment_expired`
   - Catatan: Perlu modifikasi kecil pada file cron agar bisa dipanggil via URL

---

## 📋 Ringkasan Cepat (Quick Reference)

```
CRON JOB 1 — Setiap 5 Menit
Jadwal: */5 * * * *
Command: /usr/local/bin/php /home/USERNAME/public_html/cron/check_payment_expired.php

CRON JOB 2 — Jam 08:00 Setiap Hari
Jadwal: 0 1 * * *  ← (jika server UTC, untuk WIB)
Jadwal: 0 8 * * *  ← (jika server WIB)
Command: /usr/local/bin/php /home/USERNAME/public_html/cron/send_expiry_reminder.php

CRON JOB 3 — Jam 00:05 Setiap Hari
Jadwal: 5 17 * * *  ← (jika server UTC, untuk WIB)
Jadwal: 5 0 * * *   ← (jika server WIB)
Command: /usr/local/bin/php /home/USERNAME/public_html/cron/update_expired_slots.php

Ganti USERNAME dengan username cPanel Anda!
```

---

## 📞 Jika Masih Bermasalah

Hubungi **support hosting Anda** dan sampaikan:

> *"Saya ingin setup cron job PHP di hosting saya. Bisa bantu saya verifikasi:*
> *1. Apa path PHP yang benar? (/usr/local/bin/php atau lainnya)*
> *2. Apa timezone server hosting ini?*
> *3. Apakah cron job sudah berjalan? (cek di server log)"*

---

*Dokumen ini dibuat khusus untuk RZDK Store — rzdkstore.my.id*  
*Jika ada pertanyaan, hubungi WhatsApp: 085111642004*
