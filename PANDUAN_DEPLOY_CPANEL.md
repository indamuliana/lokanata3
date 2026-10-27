# Panduan Deploy Laravel ke cPanel (Subdomain, Tanpa Symlink)

Struktur deployment ini dirancang agar folder core Laravel aman berada di luar folder publik, dan isi folder `public/` berada di `public_html/silokanata/`.

---

## 1. Skema Struktur Direktori di cPanel

Misalkan direktori home akun cPanel Anda adalah `/home/username/`:

```text
/home/username/
├── laravel_app/                     <-- Core Project Laravel (AMAN, tidak bisa diakses publik)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/                      <-- Sudah terinstall dari lokal
│   ├── .env                         <-- Konfigurasi database & path cPanel
│   └── artisan
│
└── public_html/
    └── silokanata/                  <-- Document Root Subdomain (Berasal dari isi folder public/)
        ├── build/                   <-- Hasil build Vite (npm run build)
        ├── uploads/                 <-- Folder file upload publik (pengganti symlink)
        ├── index.php                <-- Dimodifikasi arah path require-nya
        ├── .htaccess
        └── favicon.ico, robots.txt, dll.
```

---

## 2. Persiapan di Komputer Lokal Sebelum Upload

1. **Jalankan Build Asset Vite (Wajib):**
   ```bash
   npm run build
   ```
   Pastikan folder `public/build` sudah terbuat.

2. **Pastikan Vendor Siap:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Export Database MySQL:**
   - Melalui phpMyAdmin lokal (XAMPP): Buka `http://localhost/phpmyadmin` -> Pilih database aplikasi -> Tab **Export** -> Klik **Export** (`.sql`).

4. **Kemas File Menjadi ZIP:**
   - **File 1 (`laravel_core.zip`)**: Kompres semua folder/file project **KECUALI** folder `public/` dan file `.git`/`node_modules`.
   - **File 2 (`public_files.zip`)**: Kompres seluruh isi di dalam folder `public/`.

---

## 3. Langkah di cPanel

### A. Buat Subdomain
1. Buka cPanel -> Masuk ke menu **Domains** / **Subdomains**.
2. Buat subdomain, misal: `silokanata.domainanda.com`.
3. Set **Document Root** ke: `public_html/silokanata`.

### B. Setup Database & phpMyAdmin
1. Buka menu **MySQL Databases** di cPanel.
2. Buat database baru (misal: `username_silokanata`).
3. Buat user database baru beserta password yang aman.
4. Hubungkan user ke database dengan checklist **ALL PRIVILEGES**.
5. Buka menu **phpMyAdmin** di cPanel -> Pilih database tersebut -> Tab **Import** -> Upload file `.sql` dari lokal.

### C. Upload dan Ekstrak File
1. Buka **File Manager** cPanel.
2. Buat folder baru di luar `public_html`, misal: `/home/username/laravel_app`.
3. Upload `laravel_core.zip` ke dalam `/home/username/laravel_app` lalu **Extract**.
4. Masuk ke folder `/home/username/public_html/silokanata/`.
5. Upload `public_files.zip` lalu **Extract** di dalam folder tersebut.
6. Buat folder baru di dalam `silokanata` bernama `uploads` dengan permission `755` (untuk file upload publik).

---

## 4. Konfigurasi `index.php` di `public_html/silokanata/`

Buka dan edit file `/home/username/public_html/silokanata/index.php`. Sesuaikan path agar mengarah ke folder `laravel_app`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Sesuaikan path ke laravel_app:
if (file_exists($maintenance = __DIR__.'/../../laravel_app/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../../laravel_app/vendor/autoload.php';

$app = require_once __DIR__.'/../../laravel_app/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

*(Catatan: `__DIR__.'/../../laravel_app'` karena posisi `index.php` berada di dalam `public_html/silokanata` yang artinya naik 2 tingkat).*

---

## 5. Konfigurasi `.env` di `laravel_app`

Buka file `/home/username/laravel_app/.env` (pastikan fitur "Show Hidden Files" aktif di File Manager cPanel), sesuaikan variabel berikut:

```env
APP_NAME=SILOKANATA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://silokanata.domainanda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_silokanata
DB_USERNAME=username_dbuser
DB_PASSWORD=PasswordDatabaseAnda

# Konfigurasi Storage Tanpa Symlink
FILESYSTEM_DISK=public
FILESYSTEM_PUBLIC_ROOT=/home/username/public_html/silokanata/uploads
FILESYSTEM_PUBLIC_URL=https://silokanata.domainanda.com/uploads
LARAVEL_PUBLIC_PATH=/home/username/public_html/silokanata
```

---

## 6. Permissions / Hak Akses File
Pastikan permission folder berikut di `laravel_app/`:
- `laravel_app/storage/` -> **775** atau **755**
- `laravel_app/bootstrap/cache/` -> **775** atau **755**
- `public_html/silokanata/uploads/` -> **755**
