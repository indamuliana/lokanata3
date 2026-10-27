<?php

namespace Database\Seeders;

use App\Models\HotelSetting;
use App\Models\NumberingSequence;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General & Property
            ['key' => 'hotel_name', 'value' => 'InSitu Hotel Syariah Garut', 'type' => 'string', 'group' => 'general', 'description' => 'Nama properti hotel'],
            ['key' => 'hotel_tagline', 'value' => 'Hotel Syariah Nyaman dan Berkah di Garut', 'type' => 'string', 'group' => 'general', 'description' => 'Slogan atau tagline hotel'],
            ['key' => 'hotel_address', 'value' => 'Jl. Situ Indah No. 1, Garut, Jawa Barat', 'type' => 'string', 'group' => 'general', 'description' => 'Alamat lengkap hotel'],
            ['key' => 'hotel_phone', 'value' => '0262-123456', 'type' => 'string', 'group' => 'general', 'description' => 'Nomor telepon kantor'],
            ['key' => 'hotel_whatsapp', 'value' => '628123456789', 'type' => 'string', 'group' => 'general', 'description' => 'Nomor WhatsApp resmi format 62'],
            ['key' => 'hotel_email', 'value' => 'info@insituhotel.id', 'type' => 'string', 'group' => 'general', 'description' => 'Email resmi hotel'],

            // Branding & Images
            ['key' => 'hotel_logo_path', 'value' => '', 'type' => 'string', 'group' => 'branding', 'description' => 'Path file logo hotel'],
            ['key' => 'letterhead_portrait_path', 'value' => '', 'type' => 'string', 'group' => 'branding', 'description' => 'Path kop surat Portrait A4'],
            ['key' => 'letterhead_landscape_path', 'value' => '', 'type' => 'string', 'group' => 'branding', 'description' => 'Path kop surat Landscape A4'],
            ['key' => 'letterhead_thermal_path', 'value' => '', 'type' => 'string', 'group' => 'branding', 'description' => 'Path kop surat Struk Thermal 80mm'],

            // Bank Accounts (Multi-Rekening)
            ['key' => 'bank_accounts', 'value' => json_encode([
                ['bank_name' => 'Bank Syariah Indonesia (BSI)', 'account_number' => '7112233445', 'account_holder' => 'InSitu Hotel Syariah', 'is_active' => true],
                ['bank_name' => 'BCA', 'account_number' => '1480998877', 'account_holder' => 'InSitu Hotel Syariah', 'is_active' => true],
            ]), 'type' => 'json', 'group' => 'finance', 'description' => 'Daftar rekening transfer resmi hotel'],

            // Operations & Check-in
            ['key' => 'checkin_time', 'value' => '13:00', 'type' => 'string', 'group' => 'booking', 'description' => 'Jam check-in standar'],
            ['key' => 'checkout_time', 'value' => '12:00', 'type' => 'string', 'group' => 'booking', 'description' => 'Jam checkout standar'],
            ['key' => 'website_booking_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'booking', 'description' => 'Sakelar aktivasi pemesanan kamar via website'],
            ['key' => 'website_hold_hours', 'value' => '2', 'type' => 'integer', 'group' => 'booking', 'description' => 'Masa tahan booking website belum konfirmasi (jam)'],
            ['key' => 'dashboard_polling_seconds', 'value' => '30', 'type' => 'integer', 'group' => 'general', 'description' => 'Interval polling dashboard kendali kamar (detik)'],

            // Finance & Policy
            ['key' => 'tax_percentage', 'value' => '10', 'type' => 'integer', 'group' => 'tax', 'description' => 'Tarif pajak daerah awal (%)'],
            ['key' => 'service_charge_percentage', 'value' => '0', 'type' => 'integer', 'group' => 'tax', 'description' => 'Tarif service charge awal (%)'],
            ['key' => 'max_receptionist_discount', 'value' => '10', 'type' => 'integer', 'group' => 'pricing', 'description' => 'Batas maksimum diskon mandiri resepsionis (%)'],
            ['key' => 'expense_approval_limit', 'value' => '500000', 'type' => 'integer', 'group' => 'accounting', 'description' => 'Batas pengeluaran tanpa persetujuan Manager (Rp)'],
        ];

        foreach ($settings as $setting) {
            HotelSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $sequences = [
            ['code' => 'RESERVATION', 'prefix' => 'RES', 'date_format' => 'Ymd', 'current_number' => 0, 'padding' => 4],
            ['code' => 'INVOICE', 'prefix' => 'INV', 'date_format' => 'Ymd', 'current_number' => 0, 'padding' => 4],
            ['code' => 'RECEIPT', 'prefix' => 'KW', 'date_format' => 'Ymd', 'current_number' => 0, 'padding' => 4],
            ['code' => 'JOURNAL', 'prefix' => 'JRN', 'date_format' => 'Ymd', 'current_number' => 0, 'padding' => 4],
            ['code' => 'EXPENSE', 'prefix' => 'EXP', 'date_format' => 'Ymd', 'current_number' => 0, 'padding' => 4],
            ['code' => 'PURCHASE', 'prefix' => 'PO', 'date_format' => 'Ymd', 'current_number' => 0, 'padding' => 4],
        ];

        foreach ($sequences as $seq) {
            NumberingSequence::firstOrCreate(
                ['code' => $seq['code']],
                $seq
            );
        }
    }
}
