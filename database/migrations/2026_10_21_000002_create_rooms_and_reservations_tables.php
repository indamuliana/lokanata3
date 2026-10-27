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
        // 1. Tipe Kamar
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Standard, Deluxe, Suite, Family
            $table->string('code')->unique(); // STD, DLX, SUI, FAM
            $table->text('description')->nullable();
            $table->json('facilities')->nullable(); // AC, Smart TV, Water Heater, dll.
            $table->unsignedInteger('standard_capacity')->default(2);
            $table->unsignedInteger('max_capacity')->default(3);
            $table->unsignedBigInteger('base_price'); // IDR integer
            $table->unsignedBigInteger('extra_person_fee')->default(0); // IDR integer
            $table->string('photo_path')->nullable(); // Path di public/uploads/rooms/
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Kamar Fisik
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique(); // e.g. 101, 102
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->string('floor')->nullable(); // Lantai 1, 2, dll.
            $table->text('notes')->nullable();
            
            // 4 Dimensi Status (PRD 7.2)
            $table->enum('operational_status', ['active', 'maintenance', 'disabled'])->default('active');
            $table->enum('cleaning_status', ['dirty', 'cleaning', 'clean', 'inspected'])->default('clean');
            
            $table->timestamps();
        });

        // 3. Aturan Tarif (Weekday, Weekend, High Season, Tanggal Khusus)
        Schema::create('rate_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->string('name'); // Weekend rate, Libur Lebaran, dll.
            $table->enum('type', ['weekend', 'high_season', 'specific_date', 'custom'])->default('weekend');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('days_of_week')->nullable(); // [5, 6] untuk Jumat, Sabtu
            $table->enum('adjustment_type', ['fixed', 'percentage'])->default('fixed');
            $table->bigInteger('adjustment_value'); // Nominal rupiah flat atau persentase kenaikan (e.g. 20 untuk +20%)
            $table->integer('priority')->default(1); // Angka lebih besar = prioritas lebih tinggi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Promo & Diskon
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->unsignedBigInteger('discount_value'); // e.g. 10 (%) atau 50000 (Rp)
            $table->unsignedBigInteger('max_discount_amount')->nullable(); // Maksimal diskon dalam Rp
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Data Pelanggan / Tamu Terpadu
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->index(); // Format normalisasi 62...
            $table->enum('identity_type', ['KTP', 'SIM', 'Paspor'])->default('KTP');
            $table->string('identity_number')->nullable()->index();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Reservasi Kamar
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique(); // RES-20261021-0001
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('room_type_id')->constrained('room_types');
            
            $table->enum('source', ['walk_in', 'ota_manual', 'website', 'whatsapp_manual'])->default('walk_in');
            
            // Detail khusus OTA Manual (PRD 6.3)
            $table->string('ota_name')->nullable(); // Booking.com, Traveloka, Agoda, dll.
            $table->string('ota_reference_number')->nullable();
            $table->unsignedBigInteger('ota_gross_amount')->nullable();
            $table->unsignedBigInteger('ota_commission')->nullable();
            $table->unsignedBigInteger('ota_net_expected')->nullable();
            $table->enum('ota_settlement_status', ['pending', 'settled'])->nullable();
            
            // Tanggal & Tamu
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('infants')->default(0);
            
            // Status Reservasi (PRD 7.2)
            $table->enum('status', [
                'pending',          // Menunggu konfirmasi / pembayaran (Website)
                'confirmed',        // Terkonfirmasi
                'checked_in',       // Sedang menginap
                'checked_out',      // Selesai menginap
                'cancelled',        // Dibatalkan
                'expired',          // Kedaluwarsa (lewat batas tahan 2 jam)
                'no_show'           // Tamu tidak hadir
            ])->default('confirmed');
            
            // Snapshot Finansial Terkunci (PRD 7.4, 7.5, 13)
            $table->unsignedBigInteger('subtotal'); // Total tarif kamar
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('service_charge_amount')->default(0);
            $table->unsignedBigInteger('total_amount'); // Total tagihan final
            $table->unsignedBigInteger('paid_amount')->default(0); // Total pembayaran terverifikasi
            
            // Status Finansial
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid');
            
            $table->timestamp('expires_at')->nullable(); // Batas waktu masa tahan booking website
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['check_in_date', 'check_out_date']);
            $table->index('status');
        });

        // 7. Alokasi Kamar Fisik (Bisa alokasi awal atau saat check-in)
        Schema::create('reservation_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('assigned_from');
            $table->date('assigned_to');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['reservation_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_rooms');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('rate_rules');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
    }
};
