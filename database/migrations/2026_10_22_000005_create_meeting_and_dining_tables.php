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
        // 1. Master Ruang Meeting
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('capacity');
            $table->unsignedBigInteger('hourly_rate')->default(0);
            $table->unsignedBigInteger('halfday_rate')->default(0);
            $table->unsignedBigInteger('fullday_rate')->default(0);
            $table->json('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Master Paket Meeting
        Schema::create('meeting_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('price_per_pax');
            $table->unsignedInteger('min_pax')->default(10);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Reservasi Ruang Meeting
        Schema::create('meeting_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('meeting_room_id')->constrained('meeting_rooms')->cascadeOnDelete();
            $table->foreignId('meeting_package_id')->nullable()->constrained('meeting_packages')->nullOnDelete();
            $table->string('event_name');
            $table->date('event_date');
            $table->string('start_time', 10); // e.g. "09:00"
            $table->string('end_time', 10);   // e.g. "13:00"
            $table->unsignedInteger('pax');
            $table->enum('pricing_type', ['all_in', 'per_pax', 'hourly'])->default('all_in');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('additional_charges')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['meeting_room_id', 'event_date']);
        });

        // 4. Master Paket Fine Dining / Restoran Syariah
        Schema::create('dining_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('price_per_pax');
            $table->unsignedInteger('min_pax')->default(2);
            $table->text('menu_details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Reservasi Fine Dining
        Schema::create('dining_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('dining_package_id')->nullable()->constrained('dining_packages')->nullOnDelete();
            $table->string('table_number_or_area')->nullable();
            $table->date('reservation_date');
            $table->string('time_slot', 10); // e.g. "12:30", "19:00"
            $table->unsignedInteger('pax');
            $table->unsignedBigInteger('price_per_pax')->default(0);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('additional_charges')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->text('special_requests')->nullable();
            $table->enum('status', ['booked', 'confirmed', 'seated', 'completed', 'cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reservation_date', 'time_slot']);
        });

        // 6. Hubungkan tabel Payments ke Meeting & Dining
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->change();
            $table->foreignId('meeting_reservation_id')->nullable()->after('reservation_id')->constrained('meeting_reservations')->nullOnDelete();
            $table->foreignId('dining_reservation_id')->nullable()->after('meeting_reservation_id')->constrained('dining_reservations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['dining_reservation_id']);
            $table->dropForeign(['meeting_reservation_id']);
            $table->dropColumn(['meeting_reservation_id', 'dining_reservation_id']);
        });

        Schema::dropIfExists('dining_reservations');
        Schema::dropIfExists('dining_packages');
        Schema::dropIfExists('meeting_reservations');
        Schema::dropIfExists('meeting_packages');
        Schema::dropIfExists('meeting_rooms');
    }
};
