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
        // 1. Tambah field kelengkapan rombongan & pajak ke dining_reservations
        Schema::table('dining_reservations', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('customer_id');
            $table->string('pic_name')->nullable()->after('group_name');
            $table->string('pic_phone')->nullable()->after('pic_name');
            $table->string('activity_link')->nullable()->after('pic_phone');
            $table->boolean('is_wikrama_teachers')->default(false)->after('activity_link');
            $table->string('custom_menu_name')->nullable()->after('dining_package_id');
            $table->boolean('apply_tax')->default(true)->after('discount_amount');
        });

        // 2. Tambah image_path ke dining_packages untuk upload foto paket menu
        Schema::table('dining_packages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('menu_details');
        });

        // 3. Master Layanan Tambahan Fine Dining
        Schema::create('dining_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('service');
            $table->unsignedBigInteger('default_price')->default(0);
            $table->string('unit')->default('porsi');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // 4. Item Layanan Tambahan per Reservasi Dining
        Schema::create('dining_service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_reservation_id')->constrained('dining_reservations')->cascadeOnDelete();
            $table->foreignId('dining_service_id')->nullable()->constrained('dining_services')->nullOnDelete();
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
        Schema::dropIfExists('dining_service_items');
        Schema::dropIfExists('dining_services');

        Schema::table('dining_packages', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('dining_reservations', function (Blueprint $table) {
            $table->dropColumn([
                'group_name',
                'pic_name',
                'pic_phone',
                'activity_link',
                'is_wikrama_teachers',
                'custom_menu_name',
                'apply_tax',
            ]);
        });
    }
};
