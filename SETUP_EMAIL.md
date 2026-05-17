# 📧 Panduan Setup Email (SMTP) di cPanel
## RZDK Store — rzdkstore.my.id

> **Untuk siapa panduan ini?**  
> Panduan ini ditulis untuk pengguna yang baru pertama kali menggunakan cPanel hosting. Setiap langkah dijelaskan dengan detail dan disertai penjelasan "kenapa" agar lebih mudah dipahami.

---

## Daftar Isi
1. [Apa Itu Email SMTP dan Kenapa Diperlukan?](#1-apa-itu-email-smtp-dan-kenapa-diperlukan)
2. [Langkah 1 — Buat Akun Email di cPanel](#2-langkah-1--buat-akun-email-di-cpanel)
3. [Langkah 2 — Catat Informasi SMTP](#3-langkah-2--catat-informasi-smtp)
4. [Langkah 3 — Konfigurasi di Website (config/mail.php)](#4-langkah-3--konfigurasi-di-website-configmailphp)
5. [Langkah 4 — Test Kirim Email](#5-langkah-4--test-kirim-email)
6. [Checklist Verifikasi](#6-checklist-verifikasi)
7. [Troubleshooting — Jika Email Tidak Terkirim](#7-troubleshooting--jika-email-tidak-terkirim)

---

## 1. Apa Itu Email SMTP dan Kenapa Diperlukan?

**SMTP** (Simple Mail Transfer Protocol) adalah "jalan tol" yang digunakan website untuk mengirim email secara otomatis.

Website RZDK Store menggunakan email otomatis untuk:

| Kejadian | Email yang Dikirim |
|----------|-------------------|
| Customer baru daftar | Link verifikasi akun |
| Customer lupa password | Link reset password |
| Order selesai diproses | Notifikasi "order completed" |
| Produk mau expired (H-3) | Reminder perpanjang |

**Tanpa setup SMTP:** Semua email di atas tidak akan terkirim → customer tidak bisa verifikasi akun → tidak bisa login.

---

## 2. Langkah 1 — Buat Akun Email di cPanel

### 2.1 Login ke cPanel
1. Buka browser, ketik: `https://rzdkstore.my.id:2083` (atau tanyakan ke provider hosting Anda URL cPanel-nya)
2. Masukkan **username** dan **password** cPanel Anda
3. Klik **Log in**

> 💡 **Tips:** Beberapa hosting menggunakan port berbeda: `:2083`, `:2082`, atau melalui link khusus seperti `https://hosting.nama.com/cpanel`

---

### 2.2 Masuk ke Menu Email Accounts
1. Di halaman utama cPanel, cari bagian **"EMAIL"**
2. Klik **"Email Accounts"**

![Lokasi menu Email Accounts di cPanel]

---

### 2.3 Buat Akun Email Baru
1. Klik tombol **"+ Create"** atau **"Create Email Account"**

2. Isi form berikut:

   | Field | Isi dengan |
   |-------|-----------|
   | **Domain** | Pilih `rzdkstore.my.id` dari dropdown |
   | **Username** | Ketik: `noreply` |
   | **Password** | Buat password yang kuat (min. 12 karakter) |
   | **Storage Space** | Biarkan default (atau set 100MB) |

   > ✅ Hasilnya nanti akan jadi: **noreply@rzdkstore.my.id**

3. Klik **"Create"** atau **"Create Account"**

4. Jika berhasil, akan muncul pesan "Account Created" ✅

---

## 3. Langkah 2 — Catat Informasi SMTP

Setelah akun email berhasil dibuat, Anda perlu mengetahui "alamat server email" untuk dikonfigurasi di website.

### 3.1 Cari Informasi SMTP Server
1. Masih di halaman **Email Accounts**
2. Temukan email `noreply@rzdkstore.my.id` yang baru dibuat
3. Klik **"Connect Devices"** atau ikon ⚙️ di sebelah email tersebut

4. Cari bagian **"Incoming Server"** atau **"Mail Server Settings"**

5. Catat informasi berikut:

   | Informasi | Nilai Umum | Catat Punya Anda |
   |-----------|-----------|-----------------|
   | **Mail Server / Host** | `mail.rzdkstore.my.id` | _________________ |
   | **Port SSL** | `465` | _________________ |
   | **Port TLS/STARTTLS** | `587` | _________________ |
   | **Username** | `noreply@rzdkstore.my.id` | _________________ |
   | **Password** | (password yang tadi dibuat) | _________________ |

> 💡 **Penjelasan Port:**
> - **Port 465 + SSL** → Lebih umum dipakai di shared hosting cPanel. Koneksi langsung terenkripsi.
> - **Port 587 + TLS** → Alternatif, enkripsi dimulai setelah koneksi.
> 
> **Rekomendasi: Gunakan Port 465 + SSL**

---

## 4. Langkah 3 — Konfigurasi di Website (config/mail.php)

Sekarang kita masukkan informasi SMTP tadi ke dalam file konfigurasi website.

### 4.1 Buka File Manager cPanel
1. Di cPanel, cari **"File Manager"**
2. Klik **"File Manager"**
3. Navigate ke folder: `public_html/config/`
4. Klik file **`mail.php`** → Klik **"Edit"**

---

### 4.2 Ubah Isi File mail.php

File ini akan terlihat seperti ini:

```php
return [
    'use_smtp' => true,
    'smtp_host' => 'mail.rzdkstore.my.id',
    'smtp_port' => 465,
    'smtp_username' => 'noreply@rzdkstore.my.id',
    'smtp_password' => '',    // ← Kosong, perlu diisi
    'smtp_encryption' => 'ssl',
    'from_email' => 'noreply@rzdkstore.my.id',
    'from_name' => 'RZDK Store',
];
```

**Yang perlu diubah:**

1. **`smtp_host`** → Ganti dengan mail server hosting Anda (dari Langkah 2)
   ```php
   'smtp_host' => 'mail.rzdkstore.my.id',  // Biasanya sudah benar
   ```

2. **`smtp_password`** → Isi dengan password email yang tadi Anda buat
   ```php
   'smtp_password' => 'PASSWORD_EMAIL_ANDA_DISINI',
   ```

3. **`smtp_port`** → Sesuaikan dengan port hosting Anda
   ```php
   'smtp_port' => 465,   // Untuk SSL
   // ATAU
   'smtp_port' => 587,   // Untuk TLS
   ```

4. **`smtp_encryption`** → Sesuaikan dengan port
   ```php
   'smtp_encryption' => 'ssl',   // Jika pakai port 465
   // ATAU
   'smtp_encryption' => 'tls',   // Jika pakai port 587
   ```

5. Klik **"Save Changes"**

---

### 4.3 Alternatif: Update via Admin Panel Website

Jika website sudah berjalan, Anda bisa update pengaturan SMTP langsung dari **Admin Panel → Settings → Email (SMTP)**:

1. Login ke `https://rzdkstore.my.id/login` sebagai admin
2. Buka **Admin Panel → Settings** (menu sidebar kiri)
3. Scroll ke bagian **"Email (SMTP)"**
4. Isi semua field yang diperlukan
5. Klik **"Simpan Semua Pengaturan"**

---

## 5. Langkah 4 — Test Kirim Email

Setelah konfigurasi selesai, lakukan pengujian:

### Test 1: Register Akun Baru
1. Buka `https://rzdkstore.my.id/register`
2. Daftar dengan email **pribadi Anda** (bukan email admin)
3. Cek apakah email verifikasi masuk ke inbox Anda
4. ✅ Jika masuk → SMTP berfungsi!
5. ❌ Jika tidak masuk → Lihat bagian Troubleshooting

### Test 2: Cek Log Error
Jika email tidak terkirim:
1. cPanel → **File Manager**
2. Navigate ke `public_html/storage/logs/`
3. Buka file `error.log`
4. Cari baris yang mengandung kata "SMTP" atau "mail" untuk menemukan pesan error

---

## 6. Checklist Verifikasi

Centang semua item berikut setelah setup selesai:

- [ ] Akun email `noreply@rzdkstore.my.id` berhasil dibuat di cPanel
- [ ] Password email sudah dicatat dengan aman
- [ ] File `config/mail.php` sudah diupdate dengan informasi SMTP benar
- [ ] Field `smtp_password` sudah diisi (tidak kosong)
- [ ] Test register → Email verifikasi berhasil masuk ke inbox
- [ ] Cek folder Spam jika email tidak ada di Inbox

---

## 7. Troubleshooting — Jika Email Tidak Terkirim

### ❌ Problem 1: "Authentication Failed" atau "Invalid Credentials"
**Penyebab:** Username atau password SMTP salah.  
**Solusi:**
- Pastikan `smtp_username` menggunakan **email lengkap**: `noreply@rzdkstore.my.id` (bukan hanya `noreply`)
- Coba reset password email di cPanel → Email Accounts → Ubah password
- Update password baru di `config/mail.php`

---

### ❌ Problem 2: "Connection Refused" atau "Could not connect to SMTP host"
**Penyebab:** Port atau server salah, atau hosting memblokir port tersebut.  
**Solusi:**
1. Coba ganti port 465 → 587 (atau sebaliknya)
2. Ganti encryption: `ssl` → `tls` (atau sebaliknya)
3. Hubungi support hosting tanyakan: *"Port berapa yang aktif untuk SMTP outgoing?"*

---

### ❌ Problem 3: Email Masuk ke Folder Spam
**Penyebab:** Email dari domain baru/hosting shared sering dianggap spam.  
**Solusi:**
- Minta customer untuk **tandai email sebagai "Bukan Spam"**
- Pertimbangkan setup **SPF dan DKIM** di cPanel:
  1. cPanel → **Email Deliverability**
  2. Klik **"Repair"** untuk domain `rzdkstore.my.id`
  3. Ini akan otomatis setup SPF + DKIM

---

### ❌ Problem 4: Email Tidak Terkirim Tapi Tidak Ada Error
**Penyebab:** Kemungkinan limit email hosting terlampaui.  
**Solusi:**
- Shared hosting biasanya batasi **100-500 email per jam**
- Cek di cPanel → **Email** → **Track Delivery** untuk melihat status email
- Jika limit terlampaui, tunggu 1 jam dan coba lagi

---

### ❌ Problem 5: "SSL certificate problem" atau "SSL Error"
**Penyebab:** Sertifikat SSL hosting bermasalah.  
**Solusi:**
- Coba ubah ke port 587 + TLS (biasanya lebih toleran)
- Atau hubungi support hosting

---

## 📞 Jika Masih Bermasalah

Hubungi **support hosting Anda** dan tanyakan:
1. *"Berapa port SMTP yang aktif untuk kirim email dari PHP?"*
2. *"Apakah ada setting khusus untuk PHP mail() atau SMTP di hosting ini?"*
3. *"Apakah ada limit jumlah email per jam?"*

---

*Dokumen ini dibuat khusus untuk RZDK Store — rzdkstore.my.id*  
*Jika ada pertanyaan, hubungi WhatsApp: 085111642004*
