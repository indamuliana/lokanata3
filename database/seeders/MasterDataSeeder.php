<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\RateRule;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 4 Initial Room Types (PRD Section 3.1)
        $types = [
            [
                'name' => 'Standard Room',
                'code' => 'STD',
                'description' => 'Kamar standar nyaman dengan tempat tidur twin/double, AC, dan kamar mandi dalam.',
                'facilities' => ['AC', 'WiFi', 'TV Kabel', 'Water Heater', 'Kamar Mandi Dalam', 'Peralatan Sholat'],
                'standard_capacity' => 2,
                'max_capacity' => 3,
                'base_price' => 350000,
                'extra_person_fee' => 75000,
                'is_active' => true,
                'website_quota' => 5,
            ],
            [
                'name' => 'Superior Room',
                'code' => 'SUP',
                'description' => 'Kamar superior luas dengan pemandangan kota/taman, minibar, dan amenitas lengkap.',
                'facilities' => ['AC', 'WiFi Cepat', 'Smart TV 43"', 'Water Heater', 'Coffee/Tea Maker', 'Peralatan Sholat'],
                'standard_capacity' => 2,
                'max_capacity' => 3,
                'base_price' => 450000,
                'extra_person_fee' => 75000,
                'is_active' => true,
                'website_quota' => 7,
            ],
            [
                'name' => 'Deluxe Room',
                'code' => 'DLX',
                'description' => 'Kamar deluxe mewah dengan balkon pribadi, kasur king-size, dan sofa santai.',
                'facilities' => ['AC', 'WiFi Cepat', 'Smart TV 50"', 'Bathtub/Water Heater', 'Kulkas Mini', 'Balkon', 'Peralatan Sholat'],
                'standard_capacity' => 2,
                'max_capacity' => 3,
                'base_price' => 550000,
                'extra_person_fee' => 100000,
                'is_active' => true,
                'website_quota' => 6,
            ],
            [
                'name' => 'Suite Family Room',
                'code' => 'SUI',
                'description' => 'Kamar keluarga luas dengan ruang tamu terpisah, 2 tempat tidur queen, dan meja makan.',
                'facilities' => ['AC di Setiap Ruangan', 'Living Room', 'Smart TV 55"', 'Dapur Kering & Microwave', 'Bathtub', 'Peralatan Sholat'],
                'standard_capacity' => 4,
                'max_capacity' => 6,
                'base_price' => 850000,
                'extra_person_fee' => 100000,
                'is_active' => true,
                'website_quota' => 2,
            ],
        ];

        $createdTypes = [];
        foreach ($types as $typeData) {
            $createdTypes[$typeData['code']] = RoomType::updateOrCreate(['code' => $typeData['code']], $typeData);
        }

        // 20 Physical Rooms (PRD Section 3.1)
        // Lantai 1: 10 Kamar (101-105 STD, 106-108 SUP, 109-110 DLX)
        $floor1 = [
            ['101', 'STD', 'Lantai 1'],
            ['102', 'STD', 'Lantai 1'],
            ['103', 'STD', 'Lantai 1'],
            ['104', 'STD', 'Lantai 1'],
            ['105', 'STD', 'Lantai 1'],
            ['106', 'SUP', 'Lantai 1'],
            ['107', 'SUP', 'Lantai 1'],
            ['108', 'SUP', 'Lantai 1'],
            ['109', 'DLX', 'Lantai 1'],
            ['110', 'DLX', 'Lantai 1'],
        ];

        // Lantai 2: 10 Kamar (201-204 SUP, 205-208 DLX, 209-210 SUI)
        $floor2 = [
            ['201', 'SUP', 'Lantai 2'],
            ['202', 'SUP', 'Lantai 2'],
            ['203', 'SUP', 'Lantai 2'],
            ['204', 'SUP', 'Lantai 2'],
            ['205', 'DLX', 'Lantai 2'],
            ['206', 'DLX', 'Lantai 2'],
            ['207', 'DLX', 'Lantai 2'],
            ['208', 'DLX', 'Lantai 2'],
            ['209', 'SUI', 'Lantai 2'],
            ['210', 'SUI', 'Lantai 2'],
        ];

        $allRooms = array_merge($floor1, $floor2);
        foreach ($allRooms as [$number, $typeCode, $floor]) {
            Room::updateOrCreate(
                ['room_number' => $number],
                [
                    'room_type_id' => $createdTypes[$typeCode]->id,
                    'floor' => $floor,
                    'operational_status' => 'active',
                    'cleaning_status' => 'clean',
                ]
            );
        }

        // Weekend Rate Rule (+15% pada hari Jumat & Sabtu)
        RateRule::updateOrCreate(
            ['name' => 'Tarif Weekend Jumat & Sabtu'],
            [
                'room_type_id' => null, // Berlaku untuk semua tipe
                'type' => 'weekend',
                'days_of_week' => [5, 6],
                'adjustment_type' => 'percentage',
                'adjustment_value' => 15,
                'priority' => 1,
                'is_active' => true,
            ]
        );

        // Promo Syariah (-10%)
        Promotion::updateOrCreate(
            ['code' => 'SYARIAH10'],
            [
                'name' => 'Diskon Berkah Syariah 10%',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount_amount' => 100000,
                'is_active' => true,
            ]
        );
    }
}
