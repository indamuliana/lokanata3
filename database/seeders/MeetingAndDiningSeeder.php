<?php

namespace Database\Seeders;

use App\Models\DiningPackage;
use App\Models\DiningService;
use App\Models\MeetingPackage;
use App\Models\MeetingRoom;
use App\Models\MeetingService;
use Illuminate\Database\Seeder;

class MeetingAndDiningSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Meeting Rooms
        MeetingRoom::updateOrCreate(
            ['name' => 'Ruang Rapat Cimanuk'],
            [
                'capacity' => 30,
                'hourly_rate' => 250000,
                'halfday_rate' => 900000,
                'fullday_rate' => 1600000,
                'facilities' => ['Projector HD', 'Sound System & Mic', 'Whiteboard', 'High-Speed Wi-Fi', 'AC', 'Air Mineral'],
                'is_active' => true,
            ]
        );

        MeetingRoom::updateOrCreate(
            ['name' => 'Ballroom Papandayan'],
            [
                'capacity' => 150,
                'hourly_rate' => 750000,
                'halfday_rate' => 2750000,
                'fullday_rate' => 4500000,
                'facilities' => ['Panggung Utama', 'Dual Projector & Screen', 'Premium Sound System', 'Ruang Transit VIP', 'Podium', 'Air Mineral'],
                'is_active' => true,
            ]
        );

        // 2. Meeting Packages
        MeetingPackage::updateOrCreate(
            ['name' => 'Paket Halfday Meeting + Coffee Break'],
            [
                'price_per_pax' => 65000,
                'min_pax' => 10,
                'description' => 'Durasi 4 Jam, 1x Coffee Break dengan 3 pilihan kudapan tradisional Garut & kopi/teh.',
                'is_active' => true,
            ]
        );

        MeetingPackage::updateOrCreate(
            ['name' => 'Paket Fullday Meeting + Lunch & 2x Coffee Break'],
            [
                'price_per_pax' => 120000,
                'min_pax' => 10,
                'description' => 'Durasi 8 Jam, 1x Makan Siang Prasmanan Syariah + 2x Coffee Break.',
                'is_active' => true,
            ]
        );

        // 3. Dining Packages
        DiningPackage::updateOrCreate(
            ['name' => 'Paket Nasi Liwet Garut Komplit'],
            [
                'price_per_pax' => 55000,
                'min_pax' => 2,
                'menu_details' => 'Nasi Liwet wangi, Ayam Goreng Kampung, Tahu & Tempe Goreng, Sambal Cibiuk khas Garut, Lalapan segar, Ikan Asin Jambal Roti, Sayur Asem, Teh Tawar Hangat.',
                'is_active' => true,
            ]
        );

        DiningPackage::updateOrCreate(
            ['name' => 'Paket Gurame Bakar Cobek InSitu'],
            [
                'price_per_pax' => 75000,
                'min_pax' => 2,
                'menu_details' => 'Gurame Bakar Sambal Cobek Khas Sunda, Nasi Putih Wangi, Tumis Kangkung Terasi, Tempe Mendoan, Es Jeruk Kelapa Segar.',
                'is_active' => true,
            ]
        );

        DiningPackage::updateOrCreate(
            ['name' => 'Buffet Prasmanan Khazanah Syariah'],
            [
                'price_per_pax' => 95000,
                'min_pax' => 20,
                'menu_details' => 'Prasmanan lengkap: Daging Sapi Lada Hitam, Ayam Madu Panggang, Ikan Fillet Krispi Asam Manis, Sop Buntut Garut, Puding Buah Segar, Aneka Kerupuk, Es Campur Barakah.',
                'is_active' => true,
            ]
        );

        // 4. Meeting Additional Services (Master Layanan Tambahan)
        MeetingService::updateOrCreate(
            ['name' => 'Cetak Banner / Spanduk Acara'],
            [
                'default_price' => 150000,
                'unit' => 'lembar',
                'is_active' => true,
            ]
        );

        MeetingService::updateOrCreate(
            ['name' => 'Extra Coffee Break & Snack'],
            [
                'default_price' => 30000,
                'unit' => 'porsi',
                'is_active' => true,
            ]
        );

        MeetingService::updateOrCreate(
            ['name' => 'Sound System & Mic Wireless Tambahan'],
            [
                'default_price' => 250000,
                'unit' => 'set',
                'is_active' => true,
            ]
        );

        MeetingService::updateOrCreate(
            ['name' => 'Terminal Colokan Listrik per Meja'],
            [
                'default_price' => 0,
                'unit' => 'titik',
                'is_active' => true,
            ]
        );

        // 5. Dining Additional Services (Master Layanan Tambahan Fine Dining)
        DiningService::updateOrCreate(
            ['name' => 'Liwet Kastrol Tambahan (Porsi Jumbo)'],
            [
                'category' => 'food',
                'default_price' => 85000,
                'unit' => 'kastrol',
                'is_active' => true,
            ]
        );

        DiningService::updateOrCreate(
            ['name' => 'Sate Maranggi Sapi (10 Tusuk)'],
            [
                'category' => 'food',
                'default_price' => 45000,
                'unit' => 'porsi',
                'is_active' => true,
            ]
        );

        DiningService::updateOrCreate(
            ['name' => 'Welcome Drink Mocktail Syariah'],
            [
                'category' => 'beverage',
                'default_price' => 15000,
                'unit' => 'gelas',
                'is_active' => true,
            ]
        );

        DiningService::updateOrCreate(
            ['name' => 'Tumpeng Mini Barakah'],
            [
                'category' => 'food',
                'default_price' => 35000,
                'unit' => 'porsi',
                'is_active' => true,
            ]
        );
    }
}
