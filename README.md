# Lokanata 3 - PMS InSitu Hotel Syariah Garut

Sistem Manajemen Operasional Hotel Terintegrasi (PMS - Property Management System) untuk **InSitu Hotel Syariah Garut**.

## Fitur Utama

- **Kendali Kamar (Room Rack)**: Monitoring status fisik, okupansi, kebersihan, dan alokasi kamar fisik langsung dari dashboard.
- **Reservasi Multi-Kanal**: Reservasi front office, website publik, walk-in, dan Online Travel Agent (OTA).
- **Public Booking Engine**: Reservasi kamar mandiri untuk tamu publik dengan alokasi cepat di dashboard resepsionis.
- **Housekeeping & PMR**: Manajemen kebersihan kamar, status dirty/clean/inspected, serta permintaan Make Up Room (PMR).
- **Ruang Pertemuan (Meeting Room)**: Booking dan manajemen acara ruang rapat / aula.
- **Fine Dining & Resto**: Pemesanan dan pengelolaan paket santap / resto.
- **Billing & Transaksi**: Pencatatan pembayaran, invoice PDF, struk kasir termal, dan pelunasan bertahap.
- **Kalender Musim**: Pengaturan tarif Normal Season dan High Season.

## Kebutuhan Sistem

- PHP >= 8.2
- MySQL / MariaDB
- Composer
- Node.js & NPM

## Panduan Instalasi Lokal

1. Clone repositori:
   ```bash
   git clone https://github.com/indamuliana/lokanata3.git
   cd lokanata3
   ```
2. Salin environment dan sesuaikan konfigurasi database:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Install dependensi:
   ```bash
   composer install
   npm install
   npm run build
   ```
4. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```
5. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
