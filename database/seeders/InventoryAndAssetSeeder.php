<?php

namespace Database\Seeders;

use App\Models\FixedAsset;
use App\Models\InventoryItem;
use App\Models\Room;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class InventoryAndAssetSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Vendors
        $v1 = Vendor::firstOrCreate(['name' => 'CV Berkah Amenities Garut'], [
            'phone' => '08122334455',
            'email' => 'berkah@amenities.id',
            'address' => 'Jl. Otista No. 45, Tarogong Kaler, Garut',
            'contact_person' => 'H. Asep Saepudin',
            'is_active' => true,
        ]);

        $v2 = Vendor::firstOrCreate(['name' => 'PT Sentosa Linen Nusantara'], [
            'phone' => '08211223344',
            'email' => 'order@sentosalinen.co.id',
            'address' => 'Kawasan Industri Rancaekek, Bandung',
            'contact_person' => 'Ibu Ratna',
            'is_active' => true,
        ]);

        $v3 = Vendor::firstOrCreate(['name' => 'Koperasi Petani Organik Garut'], [
            'phone' => '08579988776',
            'email' => 'petanigarut@kop.id',
            'address' => 'Jl. Raya Samarang No. 12, Garut',
            'contact_person' => 'Kang Dadan',
            'is_active' => true,
        ]);

        // 2. Inventory Items
        $items = [
            ['code' => 'INV-AMN-001', 'name' => 'Sabun Hotel Herbal Garut 30ml', 'category' => 'amenities', 'unit' => 'pcs', 'current_stock' => 150, 'minimum_stock' => 20, 'average_cost' => 2500],
            ['code' => 'INV-AMN-002', 'name' => 'Sikat Gigi & Odol Eco Kit', 'category' => 'amenities', 'unit' => 'pcs', 'current_stock' => 120, 'minimum_stock' => 20, 'average_cost' => 3500],
            ['code' => 'INV-AMN-003', 'name' => 'Sandal Hotel Bordir Syariah', 'category' => 'amenities', 'unit' => 'pasang', 'current_stock' => 80, 'minimum_stock' => 25, 'average_cost' => 8000],
            ['code' => 'INV-LIN-001', 'name' => 'Handuk Mandi Putih 70x140cm', 'category' => 'linen', 'unit' => 'pcs', 'current_stock' => 50, 'minimum_stock' => 15, 'average_cost' => 65000],
            ['code' => 'INV-LIN-002', 'name' => 'Sprei King Size 180x200cm', 'category' => 'linen', 'unit' => 'pcs', 'current_stock' => 40, 'minimum_stock' => 10, 'average_cost' => 120000],
            ['code' => 'INV-FNB-001', 'name' => 'Beras Liwet Garut Organik', 'category' => 'fnb', 'unit' => 'kg', 'current_stock' => 60, 'minimum_stock' => 15, 'average_cost' => 18000],
            ['code' => 'INV-FNB-002', 'name' => 'Kopi Arabika Papandayan (Beans)', 'category' => 'fnb', 'unit' => 'kg', 'current_stock' => 15, 'minimum_stock' => 5, 'average_cost' => 110000],
        ];

        foreach ($items as $itm) {
            InventoryItem::firstOrCreate(['code' => $itm['code']], $itm);
        }

        // 3. Fixed Assets
        $firstRoom = Room::first();

        $assets = [
            [
                'code' => 'AST-AC-101',
                'name' => 'AC Daikin 1 PK Inverter 101',
                'category' => 'electronic',
                'room_id' => $firstRoom?->id,
                'purchase_date' => '2025-01-10',
                'purchase_cost' => 4800000,
                'useful_life_years' => 5,
                'salvage_value' => 500000,
                'accumulated_depreciation' => 0,
                'status' => 'active',
            ],
            [
                'code' => 'AST-TV-101',
                'name' => 'Smart TV 43 Inch Syariah 101',
                'category' => 'electronic',
                'room_id' => $firstRoom?->id,
                'purchase_date' => '2025-01-10',
                'purchase_cost' => 3500000,
                'useful_life_years' => 4,
                'salvage_value' => 300000,
                'accumulated_depreciation' => 0,
                'status' => 'active',
            ],
            [
                'code' => 'AST-GEN-001',
                'name' => 'Genset Silent Perkins 50 KVA',
                'category' => 'building',
                'room_id' => null,
                'purchase_date' => '2024-06-01',
                'purchase_cost' => 75000000,
                'useful_life_years' => 10,
                'salvage_value' => 10000000,
                'accumulated_depreciation' => 0,
                'status' => 'active',
            ],
        ];

        foreach ($assets as $ast) {
            FixedAsset::firstOrCreate(['code' => $ast['code']], $ast);
        }
    }
}
