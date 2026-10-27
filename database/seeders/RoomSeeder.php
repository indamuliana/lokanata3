<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $standard = RoomType::create([
            'name' => 'Standard Room',
            'base_price_normal' => 250000,
            'base_price_high' => 350000,
            'capacity' => 2
        ]);
        
        $deluxe = RoomType::create([
            'name' => 'Deluxe Room',
            'base_price_normal' => 450000,
            'base_price_high' => 600000,
            'capacity' => 2
        ]);

        for ($i = 1; $i <= 10; $i++) {
            Room::create([
                'room_type_id' => $standard->id,
                'room_number' => '10' . $i,
                'condition_status' => 'Normal',
                'readiness_status' => 'Ready',
                'current_occupancy' => 'Available',
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Room::create([
                'room_type_id' => $deluxe->id,
                'room_number' => '20' . $i,
                'condition_status' => 'Normal',
                'readiness_status' => 'Ready',
                'current_occupancy' => 'Available',
            ]);
        }
    }
}
