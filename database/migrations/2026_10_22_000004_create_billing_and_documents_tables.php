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
        // 1. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV-20261022-0001
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('service_charge_amount')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled'])->default('unpaid');
            $table->string('qr_token', 64)->unique()->nullable();
            $table->timestamps();
        });

        // 2. Pembayaran (Payments & Receipts)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique(); // KW-20261022-0001
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->unsignedBigInteger('amount'); // Nominal Rupiah integer
            $table->enum('payment_method', ['cash', 'bank_transfer', 'qris_manual', 'card_manual'])->default('cash');
            $table->string('bank_account')->nullable(); // misal: BCA, Mandiri, BSI
            $table->string('reference_number')->nullable(); // No bukti transfer / no slip
            $table->string('proof_file_path')->nullable(); // Lokasi penyimpanan privat di storage/app/private/
            $table->boolean('is_refund')->default(false);
            $table->text('refund_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Master Katalog Layanan Tambahan (Add-on Services)
        Schema::create('additional_services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Extra Bed, Extra Breakfast, Laundry, dll.
            $table->string('category')->default('amenities'); // amenities, fnb, laundry, late_checkout, others
            $table->unsignedBigInteger('default_price'); // IDR integer
            $table->string('unit')->default('pcs'); // pcs, porsi, kg, jam
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Layanan Tambahan yang Dipesan pada Reservasi
        Schema::create('reservation_service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('additional_service_id')->nullable()->constrained('additional_services')->nullOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        // 5. Token Verifikasi QR Publik Tanpa PII (PRD 17.2)
        Schema::create('public_verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('document_type'); // invoice, receipt
            $table->unsignedBigInteger('document_id');
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_verification_tokens');
        Schema::dropIfExists('reservation_service_items');
        Schema::dropIfExists('additional_services');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
