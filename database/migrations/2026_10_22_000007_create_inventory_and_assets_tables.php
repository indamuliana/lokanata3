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
        // 1. Vendors / Pemasok
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Inventory Items / Master Barang
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('category', ['amenities', 'linen', 'fnb', 'maintenance'])->default('amenities');
            $table->string('unit')->default('pcs'); // pcs, pack, kg, botol, roll
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(10);
            $table->unsignedBigInteger('average_cost')->default(0); // Weighted average in integer Rupiah
            $table->timestamps();
        });

        // 3. Inventory Movements / Mutasi Stok
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->enum('type', ['purchase', 'usage', 'adjustment', 'waste']);
            $table->integer('quantity'); // Positive integer count moved
            $table->unsignedBigInteger('unit_cost')->default(0);
            $table->unsignedBigInteger('total_cost')->default(0);
            $table->string('reference_type')->nullable(); // 'stock_opname', 'room_cleaning', 'direct_purchase'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Stock Opnames / Rekonsiliasi Fisik
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 5. Stock Opname Items
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->integer('system_stock');
            $table->integer('physical_stock');
            $table->integer('difference'); // physical_stock - system_stock
            $table->unsignedBigInteger('unit_cost')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 6. Fixed Assets / Aset Tetap
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('category', ['electronic', 'furniture', 'building', 'vehicle', 'other'])->default('furniture');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->date('purchase_date');
            $table->unsignedBigInteger('purchase_cost'); // Integer Rupiah
            $table->integer('useful_life_years')->default(5);
            $table->unsignedBigInteger('salvage_value')->default(0);
            $table->unsignedBigInteger('accumulated_depreciation')->default(0);
            $table->enum('status', ['active', 'maintenance', 'disposed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Asset Depreciations / Riwayat Penyusutan Bulanan
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->cascadeOnDelete();
            $table->date('period_date'); // E.g., '2026-10-01'
            $table->unsignedBigInteger('depreciation_amount');
            $table->unsignedBigInteger('accumulated_after');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('vendors');
    }
};
