<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('high_season_dates')) {
            Schema::create('high_season_dates', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('room_types', function (Blueprint $table) {
            if (!Schema::hasColumn('room_types', 'high_season_price')) {
                $table->unsignedInteger('high_season_price')->nullable()->after('base_price');
            }
        });

        Schema::table('meeting_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('meeting_rooms', 'high_hourly_rate')) {
                $table->unsignedInteger('high_hourly_rate')->nullable()->after('hourly_rate');
                $table->unsignedInteger('high_halfday_rate')->nullable()->after('halfday_rate');
                $table->unsignedInteger('high_fullday_rate')->nullable()->after('fullday_rate');
            }
        });

        Schema::table('meeting_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('meeting_packages', 'high_price_per_pax')) {
                $table->unsignedInteger('high_price_per_pax')->nullable()->after('price_per_pax');
            }
        });

        Schema::table('dining_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('dining_packages', 'high_price_per_pax')) {
                $table->unsignedInteger('high_price_per_pax')->nullable()->after('price_per_pax');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dining_packages', function (Blueprint $table) {
            $table->dropColumn(['high_price_per_pax']);
        });

        Schema::table('meeting_packages', function (Blueprint $table) {
            $table->dropColumn(['high_price_per_pax']);
        });

        Schema::table('meeting_rooms', function (Blueprint $table) {
            $table->dropColumn(['high_hourly_rate', 'high_halfday_rate', 'high_fullday_rate']);
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['high_season_price']);
        });

        Schema::dropIfExists('high_season_dates');
    }
};
