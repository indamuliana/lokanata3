# prepare_cpanel_release.ps1
# Script Otomasi Penyiapan Paket Rilis cPanel Shared Hosting (Tanpa Symbolic Link)
# SikolaNata3 - InSitu Hotel Syariah Garut

param (
    [string]$OutputDir = "release_cpanel"
)

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " SIKOLANATA3 - CPANEL SHARED HOSTING DEPLOYMENT PREPARATION" -ForegroundColor Cyan
Write-Host " Target Arsitektur: TANPA SYMBOLIC LINK (NO STORAGE:LINK)" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Bersihkan direktori rilis sebelumnya
if (Test-Path $OutputDir) {
    Write-Host "[1/6] Membersihkan direktori rilis lama: $OutputDir..." -ForegroundColor Yellow
    Remove-Item -Path $OutputDir -Recurse -Force
}

$coreDir = Join-Path $OutputDir "laravel_core"
$publicDir = Join-Path $OutputDir "public_html"

New-Item -ItemType Directory -Path $coreDir -Force | Out-Null
New-Item -ItemType Directory -Path $publicDir -Force | Out-Null

# 2. Salin file inti ke laravel_core
Write-Host "[2/6] Menyalin file inti aplikasi ke $coreDir..." -ForegroundColor Green
$coreItems = @(
    "app", "bootstrap", "config", "database", "resources", "routes",
    "artisan", "composer.json", "composer.lock", "package.json"
)

foreach ($item in $coreItems) {
    if (Test-Path $item) {
        Copy-Item -Path $item -Destination (Join-Path $coreDir $item) -Recurse -Force
    }
}

# Buat struktur storage di laravel_core
$storageDirs = @(
    "storage/app/private",
    "storage/framework/cache/data",
    "storage/framework/sessions",
    "storage/framework/views",
    "storage/logs"
)
foreach ($sd in $storageDirs) {
    New-Item -ItemType Directory -Path (Join-Path $coreDir $sd) -Force | Out-Null
}

# 3. Salin file public ke public_html
Write-Host "[3/6] Menyalin aset web publik ke $publicDir..." -ForegroundColor Green
Copy-Item -Path "public\*" -Destination $publicDir -Recurse -Force

# Pastikan folder public_html/uploads tersedia tanpa symlink
$publicUploads = Join-Path $publicDir "uploads"
if (-not (Test-Path $publicUploads)) {
    New-Item -ItemType Directory -Path $publicUploads -Force | Out-Null
}

# 4. Modifikasi index.php untuk cPanel path traversal
Write-Host "[4/6] Menyesuaikan path di public_html/index.php..." -ForegroundColor Green
$indexPath = Join-Path $publicDir "index.php"
if (Test-Path $indexPath) {
    $indexContent = Get-Content -Path $indexPath -Raw

    # Ganti path autoload dan bootstrap app ke ../laravel_core/
    $indexContent = $indexContent -replace "require __DIR__\s*\.\s*'/../vendor/autoload.php';", "require __DIR__ . '/../laravel_core/vendor/autoload.php';"
    $indexContent = $indexContent -replace "require_once __DIR__\s*\.\s*'/../bootstrap/app.php';", "require_once __DIR__ . '/../laravel_core/bootstrap/app.php';"

    Set-Content -Path $indexPath -Value $indexContent
    Write-Host "   -> Path index.php berhasil diarahkan ke ../laravel_core/" -ForegroundColor DarkGreen
}

# 5. Buat file .htaccess aman di public_html jika belum ada
$htaccessPath = Join-Path $publicDir ".htaccess"
$htaccessContent = @"
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
"@
Set-Content -Path $htaccessPath -Value $htaccessContent

# 6. Buat file panduan deployment cPanel
Write-Host "[5/6] Membuat panduan deployment cPanel..." -ForegroundColor Green
$readmePath = Join-Path $OutputDir "PETUNJUK_DEPLOY_CPANEL.md"
$readmeContent = @"
# PANDUAN DEPLOYMENT CPANEL SIKOLANATA3 (IN SITU HOTEL SYARIAH GARUT)

Arsitektur aplikasi ini dirancang khusus untuk Shared Hosting cPanel **TANPA SYMBOLIC LINK** (tidak membutuhkan command 'php artisan storage:link' yang sering dibatasi hosting).

## STRUKTUR FOLDER DI SERVER:
```text
/home/username/
├── laravel_core/         <-- Letakkan folder 'laravel_core' di luar webroot
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── storage/
│   ├── vendor/          <-- Jalankan 'composer install --no-dev' di sini
│   ├── .env             <-- File konfigurasi database & hotel
│   └── artisan
└── public_html/          <-- Letakkan seluruh isi 'public_html' di sini
    ├── build/           <-- Asset terkompilasi Vite (CSS & JS)
    ├── uploads/         <-- Folder upload publik langsung (Bukan Symlink)
    ├── .htaccess        <-- Rewrite engine
    └── index.php        <-- Telah disesuaikan ke ../laravel_core/
```

## LANGKAH-LANGKAH DEPLOYMENT:

1. **Upload Berkas**:
   - Kompres folder `laravel_core/` menjadi zip dan upload ke direktori home `/home/username/`, lalu ekstrak.
   - Kompres seluruh isi folder `public_html/` dan ekstrak langsung di `/home/username/public_html/`.

2. **Konfigurasi Database MySQL**:
   - Buat database baru di cPanel: misal `username_lokanata`.
   - Buat user database dan berikan Hak Penuh (ALL PRIVILEGES).
   - Salin `.env.example` menjadi `.env` di dalam folder `laravel_core/`.
   - Sesuaikan konfigurasi database di `.env`:
     ```env
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://namadomainanda.com

     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=username_lokanata
     DB_USERNAME=username_userdb
     DB_PASSWORD=password_db_anda
     ```

3. **Migrasi & Seeding Awal**:
   - Buka menu **Terminal** di cPanel (atau SSH):
     ```bash
     cd ~/laravel_core
     composer install --no-dev --optimize-autoloader
     php artisan key:generate
     php artisan migrate --force
     php artisan db:seed --force
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     ```

4. **Konfigurasi Cron Job Otomatisasi (cPanel)**:
   - Buka menu **Cron Jobs** di cPanel.
   - Tambahkan cron job baru setiap menit untuk menjalankan scheduler Laravel (melepas kamar kedaluwarsa & night audit):
     ```bash
     * * * * * cd /home/username/laravel_core && php artisan schedule:run >> /dev/null 2>&1
     ```

5. **Izin Folder (Permissions)**:
   - Pastikan folder berikut memiliki izin tulis (`chmod 775` atau `chmod 755`):
     - `/home/username/laravel_core/storage/`
     - `/home/username/laravel_core/bootstrap/cache/`
     - `/home/username/public_html/uploads/`

Selesai! Aplikasi siap melayani pemesanan hotel syariah secara online dan offline.
"@
Set-Content -Path $readmePath -Value $readmeContent

Write-Host "[6/6] Selesai! Paket rilis cPanel siap di folder '$OutputDir'." -ForegroundColor Cyan
Write-Host "      - $coreDir (Inti aplikasi)" -ForegroundColor White
Write-Host "      - $publicDir (Web publik, uploads langsung)" -ForegroundColor White
Write-Host "      - $readmePath (Panduan lengkap)" -ForegroundColor White
Write-Host "==========================================================" -ForegroundColor Cyan
