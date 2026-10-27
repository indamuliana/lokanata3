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
        Schema::table('room_types', function (Blueprint $table) {
            if (!Schema::hasColumn('room_types', 'color')) {
                $table->string('color', 30)->default('#059669')->after('description');
            }
            if (!Schema::hasColumn('room_types', 'photos')) {
                $table->json('photos')->nullable()->after('photo_path');
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'room_type_snapshot')) {
                $table->json('room_type_snapshot')->nullable()->after('room_type_id');
            }
        });

        // Ensure room_type_id is nullable with nullOnDelete
        try {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropForeign(['room_type_id']);
            });
        } catch (\Throwable $e) {}

        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('room_type_id')->nullable()->change();
            $table->foreign('room_type_id')->references('id')->on('room_types')->nullOnDelete();
        });

        Schema::table('reservation_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('reservation_rooms', 'room_snapshot')) {
                $table->json('room_snapshot')->nullable()->after('room_id');
            }
        });

        try {
            Schema::table('reservation_rooms', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
            });
        } catch (\Throwable $e) {}

        Schema::table('reservation_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->nullable()->change();
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_rooms', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_snapshot');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropColumn('room_type_snapshot');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['color', 'photos']);
        });
    }
};
