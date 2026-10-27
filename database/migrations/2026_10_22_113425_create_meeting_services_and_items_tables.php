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
        // 1. Tambah facility_notes ke meeting_reservations
        Schema::table('meeting_reservations', function (Blueprint $table) {
            $table->text('facility_notes')->nullable()->after('notes');
        });

        // 2. Master Layanan Tambahan Meeting
        Schema::create('meeting_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('default_price')->default(0);
            $table->string('unit')->default('paket');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // 3. Item Layanan Tambahan per Reservasi Meeting
        Schema::create('meeting_reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_reservation_id')->constrained('meeting_reservations')->cascadeOnDelete();
            $table->foreignId('meeting_service_id')->nullable()->constrained('meeting_services')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_reservation_items');
        Schema::dropIfExists('meeting_services');
        Schema::table('meeting_reservations', function (Blueprint $table) {
            $table->dropColumn('facility_notes');
        });
    }
};
