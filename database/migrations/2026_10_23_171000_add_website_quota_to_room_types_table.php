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
            if (!Schema::hasColumn('room_types', 'website_quota')) {
                $table->unsignedInteger('website_quota')->nullable()->after('is_active');
            }
        });

        // Backfill existing room types: default quota = current active physical rooms
        $roomTypes = DB::table('room_types')->get();
        foreach ($roomTypes as $type) {
            $activeCount = DB::table('rooms')
                ->where('room_type_id', $type->id)
                ->where('operational_status', 'active')
                ->count();

            DB::table('room_types')
                ->where('id', $type->id)
                ->update(['website_quota' => $activeCount]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            if (Schema::hasColumn('room_types', 'website_quota')) {
                $table->dropColumn('website_quota');
            }
        });
    }
};
