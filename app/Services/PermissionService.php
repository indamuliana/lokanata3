<?php

namespace App\Services;

class PermissionService
{
    /**
     * Get all system permissions grouped by operational module
     */
    public static function getPermissionsGrouped(): array
    {
        return [
            'dashboard' => [
                'name' => 'Kendali Kamar (Dashboard)',
                'icon' => '🏨',
                'permissions' => [
                    ['code' => 'dashboard.view', 'name' => 'Lihat Kendali Kamar & Okupansi', 'type' => 'view'],
                    ['code' => 'room_status.edit', 'name' => 'Ubah Status Kebersihan Kamar (Bersih/Kotor/Inspeksi)', 'type' => 'manage'],
                    ['code' => 'room_plot.edit', 'name' => 'Plotting / Alokasi Tamu ke Kamar Fisik', 'type' => 'manage'],
                    ['code' => 'maintenance.manage', 'name' => 'Kelola Laporan Kerusakan Kamar (PMR)', 'type' => 'manage'],
                ],
            ],
            'reservations' => [
                'name' => 'Reservasi Kamar Hotel',
                'icon' => '🛎️',
                'permissions' => [
                    ['code' => 'reservations.view', 'name' => 'Lihat Daftar & Detail Reservasi Kamar', 'type' => 'view'],
                    ['code' => 'reservations.manage', 'name' => 'Buat & Edit Reservasi, Pindah Kamar, Extend, Batalkan', 'type' => 'manage'],
                    ['code' => 'checkin_checkout.manage', 'name' => 'Eksekusi Check-In & Check-Out Tamu', 'type' => 'manage'],
                    ['code' => 'reservation_payment.manage', 'name' => 'Catat Pembayaran & Layanan Tambahan (Add-on)', 'type' => 'manage'],
                ],
            ],
            'meeting' => [
                'name' => 'Ruang Meeting & Acara',
                'icon' => '👥',
                'permissions' => [
                    ['code' => 'meeting.view', 'name' => 'Lihat Reservasi & Jadwal Ruang Meeting', 'type' => 'view'],
                    ['code' => 'meeting.manage', 'name' => 'Buat & Edit Reservasi Meeting, Paket & Pembayaran', 'type' => 'manage'],
                ],
            ],
            'dining' => [
                'name' => 'Restoran Syariah (Fine Dining)',
                'icon' => '🍽️',
                'permissions' => [
                    ['code' => 'dining.view', 'name' => 'Lihat Pesanan Meja & Katering Dining', 'type' => 'view'],
                    ['code' => 'dining.manage', 'name' => 'Buat & Edit Reservasi Dining, Paket Menu & Pembayaran', 'type' => 'manage'],
                ],
            ],
            'calendar' => [
                'name' => 'Kalender Reservasi Terpadu',
                'icon' => '📅',
                'permissions' => [
                    ['code' => 'calendar.view', 'name' => 'Lihat Kalender Okupansi Visual', 'type' => 'view'],
                ],
            ],
            'cashier' => [
                'name' => 'Kasir Shift Front Desk',
                'icon' => '💵',
                'permissions' => [
                    ['code' => 'cashier.view', 'name' => 'Lihat Riwayat & Ringkasan Shift Kasir', 'type' => 'view'],
                    ['code' => 'cashier.manage', 'name' => 'Buka & Tutup Shift Kasir serta Rekonsiliasi Kas Fisik', 'type' => 'manage'],
                ],
            ],
            'expenses' => [
                'name' => 'Pengeluaran Operasional',
                'icon' => '📝',
                'permissions' => [
                    ['code' => 'expenses.view', 'name' => 'Lihat Daftar Pengeluaran Operasional', 'type' => 'view'],
                    ['code' => 'expenses.create', 'name' => 'Catat Pengeluaran Operasional Baru', 'type' => 'manage'],
                    ['code' => 'expenses.approve', 'name' => 'Approval / Persetujuan Pengeluaran Kas', 'type' => 'manage'],
                ],
            ],
            'night_audit' => [
                'name' => 'Penutupan Harian (Night Audit)',
                'icon' => '🌙',
                'permissions' => [
                    ['code' => 'night_audit.view', 'name' => 'Lihat Pre-Audit Checklist & Riwayat Audit', 'type' => 'view'],
                    ['code' => 'night_audit.execute', 'name' => 'Eksekusi Night Audit & Posting Jurnal Akrual', 'type' => 'manage'],
                ],
            ],
            'inventory' => [
                'name' => 'Inventaris & Logistik',
                'icon' => '📦',
                'permissions' => [
                    ['code' => 'inventory.view', 'name' => 'Lihat Daftar Barang & Kartu Stok', 'type' => 'view'],
                    ['code' => 'inventory.manage', 'name' => 'Catat Pembelian, Pemakaian, Waste & Stock Opname', 'type' => 'manage'],
                ],
            ],
            'assets' => [
                'name' => 'Aset Tetap Properti',
                'icon' => '🏢',
                'permissions' => [
                    ['code' => 'assets.view', 'name' => 'Lihat Daftar Aset Tetap & Nilai Buku', 'type' => 'view'],
                    ['code' => 'assets.manage', 'name' => 'Daftarkan Aset Baru & Eksekusi Depresiasi Bulanan', 'type' => 'manage'],
                ],
            ],
            'reports' => [
                'name' => 'Laporan & Analitik Keuangan',
                'icon' => '📊',
                'permissions' => [
                    ['code' => 'reports.view', 'name' => 'Lihat Okupansi, ADR, RevPAR, Laba Rugi, Neraca', 'type' => 'view'],
                    ['code' => 'reports.export', 'name' => 'Unduh Laporan format CSV & Cetak PDF', 'type' => 'manage'],
                ],
            ],
            'rooms_master' => [
                'name' => 'Master Kamar & Tipe',
                'icon' => '🛏️',
                'permissions' => [
                    ['code' => 'rooms_master.view', 'name' => 'Lihat Master Tipe Kamar & Nomor Kamar Fisik', 'type' => 'view'],
                    ['code' => 'rooms_master.manage', 'name' => 'Kelola Tipe Kamar, Harga Dasar, dan Kamar Fisik', 'type' => 'manage'],
                ],
            ],
            'settings' => [
                'name' => 'Pengaturan Hotel & Sistem',
                'icon' => '⚙️',
                'permissions' => [
                    ['code' => 'settings.view', 'name' => 'Lihat Identitas Hotel, Rekening & Konfigurasi', 'type' => 'view'],
                    ['code' => 'settings.manage', 'name' => 'Ubah Pengaturan Hotel, Rekening, Pajak & Limit Biaya', 'type' => 'manage'],
                ],
            ],
            'users_roles' => [
                'name' => 'Pengguna & Hak Akses',
                'icon' => '🛡️',
                'permissions' => [
                    ['code' => 'users.view', 'name' => 'Lihat Daftar Pengguna & Staf', 'type' => 'view'],
                    ['code' => 'users.manage', 'name' => 'Tambah/Ubah Staf, Reset Password & Status Akun', 'type' => 'manage'],
                    ['code' => 'roles.manage', 'name' => 'Tambah Role Baru & Atur Matriks Hak Akses', 'type' => 'manage'],
                ],
            ],
            'web_admin' => [
                'name' => 'Administrasi Website Publik',
                'icon' => '🌐',
                'permissions' => [
                    ['code' => 'web_admin.view', 'name' => 'Lihat Konten Web, Berita & Galeri', 'type' => 'view'],
                    ['code' => 'web_admin.manage', 'name' => 'Kelola Artikel Berita, Galeri, Hero & Kontak Web', 'type' => 'manage'],
                ],
            ],
        ];
    }

    /**
     * Get flat list of all permission codes
     */
    public static function getAllPermissionCodes(): array
    {
        $codes = [];
        foreach (self::getPermissionsGrouped() as $module) {
            foreach ($module['permissions'] as $p) {
                $codes[] = $p['code'];
            }
        }
        return $codes;
    }
}
