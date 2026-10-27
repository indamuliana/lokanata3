<?php

namespace Database\Seeders;

use App\Models\AdditionalService;
use Illuminate\Database\Seeder;

class AdditionalServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Extra Bed',
                'category' => 'amenities',
                'default_price' => 150000,
                'unit' => 'pcs',
                'is_active' => true,
            ],
            [
                'name' => 'Sarapan Pagi Tambahan (Extra Breakfast)',
                'category' => 'fnb',
                'default_price' => 50000,
                'unit' => 'porsi',
                'is_active' => true,
            ],
            [
                'name' => 'Laundry Reguler',
                'category' => 'laundry',
                'default_price' => 20000,
                'unit' => 'kg',
                'is_active' => true,
            ],
            [
                'name' => 'Late Check-out (per Jam)',
                'category' => 'late_checkout',
                'default_price' => 50000,
                'unit' => 'jam',
                'is_active' => true,
            ],
            [
                'name' => 'Airport / Stasiun Shuttle Garut',
                'category' => 'others',
                'default_price' => 100000,
                'unit' => 'trip',
                'is_active' => true,
            ],
        ];

        foreach ($services as $svc) {
            AdditionalService::updateOrCreate(
                ['name' => $svc['name']],
                $svc
            );
        }
    }
}
