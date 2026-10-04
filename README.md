# Sistem Booking Servis AHASS (Astra Honda Authorized Service Station)

Aplikasi web modern berbasis **Laravel 12**, **MySQL**, dan **jQuery AJAX** dengan pendekatan Single Page Application (SPA) sederhana. Aplikasi ini dirancang untuk mencatat pendaftaran servis pelanggan sepeda motor Honda secara efisien, memantau ketersediaan kuota slot jam servis secara real-time, serta memilih paket servis/part.

---

## 🚀 Fitur Utama

1. **Formulir Pendaftaran Booking Servis (SPA Sederhana)**:
   - Input Plat Nomor (kapital otomatis), Nama Pelanggan, dan Tipe Motor Honda.
   - Pilihan Tanggal Servis (minimal hari ini).
   - Pilihan Slot Jam Servis (08:00 – 17:00 WIB, interval 1 jam, total 10 slot).
   - Pilihan Paket Servis & Part resmi AHASS (harga terformat otomatis dalam Rupiah).
   - Pengiriman formulir berbasis AJAX tanpa reload halaman.

2. **Validasi Kuota Slot Backend (Anti Race-Condition)**:
   - Dibatasi **maksimal 3 kendaraan per slot jam per tanggal**.
   - Pengecekan kuota dijamin aman dari race condition menggunakan `DB::transaction()` dan locking.
   - Jika kuota sudah penuh, sistem merespons dengan HTTP status **422 Unprocessable Entity** dan pesan ramah.
   - Slot jam yang penuh otomatis ditandai `(PENUH - 3/3 motor)` dan di-disable di dropdown form.

3. **Daftar Booking Real-Time & Multi-Filter**:
   - Menampilkan daftar pendaftaran servis yang telah masuk secara instan setelah formulir disubmit.
   - **Filter Tanggal**: Filter berdasarkan tanggal servis spesifik (dilengkapi tombol pintas *"Hari Ini"* dan *"Semua Tanggal"*).
   - **Filter Slot Jam**: Filter berdasarkan slot jam operasional.
   - **Pencarian Real-Time (Debounce 300ms)**: Mencari berdasarkan Plat Nomor, Nama Pelanggan, atau Tipe Motor.

4. **Desain Antarmuka Premium Khas AHASS**:
   - Palet warna resmi Honda Red (`#E52421`), Charcoal Dark, dan Clean Slate.
   - Tipografi modern Google Font *Plus Jakarta Sans* & *JetBrains Mono*.
   - Notifikasi status pendaftaran, indikator pulse kuota, dan status ketersediaan interaktif.

---

## 🛠️ Tech Stack & Kebutuhan Sistem

- **PHP**: >= 8.2 (Tested on PHP 8.2.29)
- **Framework**: Laravel 12.x
- **Database**: MySQL 8.x / 9.x (Database: `ahass_booking`)
- **Frontend / Interaction**: Blade, jQuery 3.7.1, Bootstrap 5.3, Bootstrap Icons, Vite 7
- **Testing**: PHPUnit 11 / Laravel Feature Tests

---

## 🗄️ Skema Database

### Tabel `service_packages`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment |
| `name` | VARCHAR(255) | Nama paket servis |
| `price` | DECIMAL(10,2) | Biaya paket servis |
| `created_at` / `updated_at` | TIMESTAMP | Waktu pencatatan |

### Tabel `bookings`
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment |
| `plate_number` | VARCHAR(20) | Nomor plat polisi (uppercase) |
| `customer_name` | VARCHAR(100) | Nama lengkap pelanggan |
| `motorcycle_type` | VARCHAR(100) | Tipe motor (Vario, Beat, PCX, dll) |
| `service_date` | DATE | Tanggal servis |
| `service_time` | VARCHAR(10) | Jam servis (08:00 s/d 17:00) |
| `service_package_id` | BIGINT (FK) | Relasi ke `service_packages.id` (cascade delete) |
| `created_at` / `updated_at` | TIMESTAMP | Waktu pencatatan |

*Index komposit `['service_date', 'service_time']` disertakan untuk optimasi performa query pengecekan slot kuota.*

---

## 📦 Paket Servis Bawaan (Seeder)

Data paket servis standar AHASS yang disiapkan melalui `ServicePackageSeeder`:
1. **Servis Rutin (Oli & Filter)** — Rp 150.000
2. **Ganti Oli Mesin** — Rp 85.000
3. **Tune Up** — Rp 120.000
4. **Servis Ringan** — Rp 100.000
5. **Servis Besar** — Rp 250.000

---

## 📋 Prasyarat Sistem (Prerequisites)

Sebelum memulai instalasi, pastikan perangkat Anda telah terpasang:
- **PHP** versi 8.2 ke atas (`php -v`)
- **Composer** versi 2.x ke atas (`composer -V`)
- **Node.js** (LTS disarankan, versi 18+) & **NPM** (`node -v && npm -v`)
- **MySQL Server** versi 8.0 / 9.x / MariaDB yang sedang aktif (`mysql --version`)
- Ekstensi PHP yang aktif: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`.

---

## ⚙️ Tutorial Lengkap Cara Install & Menjalankan Aplikasi

Ikuti panduan langkah demi langkah berikut untuk memasang aplikasi dari awal sampai berjalan sempurna:

### Langkah 1: Masuk ke Direktori Repository
Buka aplikasi terminal / command prompt, lalu arahkan ke folder project:
```bash
cd submission-ahass
```

---

### Langkah 2: Pasang Dependensi Backend (Composer)
Jalankan perintah berikut untuk mengunduh semua paket library Laravel:
```bash
composer install
```

---

### Langkah 3: Pasang Dependensi Frontend (NPM)
Pasang seluruh dependensi aset antarmuka dan Vite:
```bash
npm install
```

---

### Langkah 4: Setup Berkas Lingkungan (`.env`)
Salin berkas konfigurasi template `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Kemudian generate kunci enkripsi aplikasi Laravel:
```bash
php artisan key:generate
```

Buka file `.env` menggunakan teks editor Anda (VSCode / Nano / lainnya), lalu pastikan bagian konfigurasi database sesuai dengan pengaturan MySQL lokal Anda:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ahass_booking
DB_USERNAME=root
DB_PASSWORD=
```
> **Catatan**: Jika user `root` MySQL Anda menggunakan password, isi pada baris `DB_PASSWORD=password_anda`.

---

### Langkah 5: Persiapan & Pengisian Database
Anda dapat memilih salah satu dari **dua metode** di bawah ini:

#### Pilihan A: Menggunakan Artisan Migration & Seeder (Direkomendasikan)
Metode standar Laravel yang otomatis membuat database, seluruh tabel, index, dan mengisi data awal 5 paket servis:
```bash
# 1. Buat database ahass_booking jika belum ada:
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ahass_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Jalankan migrasi dan seeder paket servis:
php artisan migrate:fresh --seed
```

#### Pilihan B: Menggunakan Berkas `schema.sql`
Jika Anda lebih menyukai import file SQL langsung:
- **Melalui Terminal**:
  ```bash
  mysql -u root -p < schema.sql
  ```
- **Melalui GUI (phpMyAdmin / TablePlus / DBeaver / Navicat)**:
  1. Buka aplikasi GUI database Anda.
  2. Buka menu **Import** / **Run SQL Script**.
  3. Pilih berkas `schema.sql` yang berada di root project atau di `database/schema.sql`.
  4. Eksekusi script SQL. Database `ahass_booking` beserta tabel `service_packages`, `bookings`, dan 5 paket servis bawaan akan otomatis dibuat dan terisi.

---

### Langkah 6: Kompilasi Aset Frontend (Vite)
Kompilasi script JavaScript (`booking.js`) dan styling CSS ke dalam format production:
```bash
npm run build
```
*(Opsional: Jika sedang melakukan perubahan kode frontend secara aktif, Anda juga bisa menjalankan `npm run dev` pada terminal terpisah).*

---

### Langkah 7: Jalankan Server Aplikasi
Jalankan server lokal bawaan Laravel:
```bash
php artisan serve
```

Terminal akan menampilkan informasi seperti berikut:
```text
   INFO  Server running on [http://127.0.0.1:8000].

   Press Ctrl+C to stop the server
```

---

### Langkah 8: Akses Aplikasi di Browser
Buka browser favorit Anda (Google Chrome, Firefox, Safari, atau Edge) lalu akses URL:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

Sekarang aplikasi **Sistem Booking Servis AHASS** sudah siap digunakan! Anda dapat langsung mencoba mendaftarkan servis, melihat kuota slot jam terisi secara real-time, dan menggunakan fitur filter pencarian.

---

## 🧪 Pengujian Otomatis (Automated Tests)

Project ini dilengkapi dengan test suite lengkap (11 tests, 48 assertions) mencakup pengujian fungsionalitas HTTP, validasi form, batasan kuota 3 motor per slot, dan fitur penyaringan data:

```bash
php artisan test
```

Untuk memeriksa kepatuhan standar kode PSR-12:
```bash
./vendor/bin/pint --test
```

---

## 🔗 Endpoint API (Internal AJAX)

| Endpoint | Method | Keterangan |
|---|---|---|
| `/` | `GET` | Menampilkan antarmuka utama SPA |
| `/packages` | `GET` | Mengambil daftar paket servis AHASS (JSON) |
| `/slots?date=YYYY-MM-DD` | `GET` | Memeriksa ketersediaan kuota slot pada tanggal tertentu |
| `/bookings?date=&time=&search=` | `GET` | Mengambil daftar booking dengan filter tanggal, jam, dan pencarian |
| `/bookings` | `POST` | Mendaftarkan booking servis baru (dilengkapi validasi kuota) |

---

## 🎯 Panduan Pengujian Fitur Aplikasi (Walkthrough)

Setelah aplikasi terbuka di browser (`http://127.0.0.1:8000`), Anda dapat mencoba beberapa skenario berikut:

1. **Pendaftaran Booking Servis Normal**:
   - Isi formulir di panel kiri: Plat Nomor (contoh: `B 1234 ABC`), Nama (contoh: `Ahmad`), Tipe Motor (contoh: `Vario 160`).
   - Pilih slot jam servis (misal `09:00 WIB`) dan salah satu paket servis.
   - Klik **Daftarkan Booking Servis**. Data akan tersimpan via AJAX dan langsung muncul di tabel sebelah kanan tanpa reload halaman!

2. **Pengujian Validasi Kuota Maksimal 3 Motor**:
   - Daftarkan motor ke-1, ke-2, dan ke-3 pada jam dan tanggal yang sama (misal `10:00 WIB`).
   - Perhatikan indikator slot jam pada formulir akan menampilkan sisa kuota secara real-time.
   - Coba daftarkan motor ke-4 pada slot `10:00 WIB`. Sistem backend akan menolak pendaftaran dan mengembalikan peringatan bahwa slot tersebut telah penuh.
   - Slot jam yang sudah terisi 3 motor otomatis berstatus `(PENUH - 3/3 motor)` dan dinonaktifkan (*disabled*) pada formulir.

3. **Pengujian Filter & Pencarian Cepat**:
   - Gunakan filter tanggal atau klik tombol pintas **"Hari Ini"** / **"Semua Tanggal"**.
   - Pilih filter slot jam spesifik untuk menampilkan pendaftaran pada jam tertentu.
   - Ketik sebagian plat nomor atau nama pelanggan pada kotak pencarian (pencarian bekerja otomatis dengan debounce 300ms).

---

## ❓ Solusi Kendala Umum (Troubleshooting)

- **Port 8000 sudah digunakan oleh aplikasi lain?**
  Jalankan server pada port alternatif:
  ```bash
  php artisan serve --port=8080
  ```
  Lalu buka `http://127.0.0.1:8080`.

- **Muncul pesan "Vite manifest not found" di browser?**
  Pastikan Anda telah mengompilasi aset frontend dengan menjalankan:
  ```bash
  npm run build
  ```

- **Error "SQLSTATE[HY000] [2002] Connection refused"?**
  Pastikan service MySQL Anda sudah berjalan dan konfigurasi `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` pada berkas `.env` sudah sesuai dengan instalasi MySQL lokal Anda.

