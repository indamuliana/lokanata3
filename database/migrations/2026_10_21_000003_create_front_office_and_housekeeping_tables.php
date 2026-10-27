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
        // 1. Pindah Kamar (Room Move)
        Schema::create('room_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('from_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('to_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->text('reason');
            $table->bigInteger('price_adjustment')->default(0); // Selisih harga jika beda tipe
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. PMR (Please Make Up Room)
        Schema::create('pmr_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->dateTime('requested_at');
            $table->text('notes')->nullable();
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['requested', 'in_progress', 'completed', 'cancelled'])->default('requested');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        // 3. Laporan Kerusakan & Maintenance
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('issue_description');
            $table->enum('urgency', ['low', 'medium', 'high', 'emergency'])->default('medium');
            $table->enum('status', ['reported', 'in_progress', 'repaired', 'cancelled'])->default('reported');
            $table->text('resolution_notes')->nullable();
            $table->dateTime('repaired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
        Schema::dropIfExists('pmr_requests');
        Schema::dropIfExists('room_moves');
    }
};
