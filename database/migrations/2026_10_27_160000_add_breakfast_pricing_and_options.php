<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            if (!Schema::hasColumn('room_types', 'price_without_breakfast')) {
                $table->unsignedBigInteger('price_without_breakfast')->nullable()->after('base_price');
            }
            if (!Schema::hasColumn('room_types', 'high_season_price_without_breakfast')) {
                $table->unsignedBigInteger('high_season_price_without_breakfast')->nullable()->after('high_season_price');
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'include_breakfast')) {
                $table->boolean('include_breakfast')->default(true)->after('infants');
            }
        });

        // Set default value for existing room types (selisih default Rp 40.000)
        DB::table('room_types')->get()->each(function ($rt) {
            $baseWithout = max(0, (int) $rt->base_price - 40000);
            $highBase = $rt->high_season_price ?: $rt->base_price;
            $highWithout = max(0, (int) $highBase - 40000);

            DB::table('room_types')->where('id', $rt->id)->update([
                'price_without_breakfast' => $baseWithout,
                'high_season_price_without_breakfast' => $highWithout,
            ]);
        });

        // Synchronize Extra Bed Additional Services
        $now = now();
        // Update existing 'Extra Bed' to 'Extra Bed (Dengan Sarapan)' if present
        $existingExtraBed = DB::table('additional_services')->where('name', 'Extra Bed')->first();
        if ($existingExtraBed) {
            DB::table('additional_services')->where('id', $existingExtraBed->id)->update([
                'name' => 'Extra Bed (Dengan Sarapan)',
                'default_price' => 150000,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('additional_services')->updateOrInsert(
                ['name' => 'Extra Bed (Dengan Sarapan)'],
                [
                    'category' => 'amenities',
                    'default_price' => 150000,
                    'unit' => 'pcs',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('additional_services')->updateOrInsert(
            ['name' => 'Extra Bed (Tanpa Sarapan)'],
            [
                'category' => 'amenities',
                'default_price' => 135000,
                'unit' => 'pcs',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            if (Schema::hasColumn('room_types', 'price_without_breakfast')) {
                $table->dropColumn('price_without_breakfast');
            }
            if (Schema::hasColumn('room_types', 'high_season_price_without_breakfast')) {
                $table->dropColumn('high_season_price_without_breakfast');
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'include_breakfast')) {
                $table->dropColumn('include_breakfast');
            }
        });
    }
};
