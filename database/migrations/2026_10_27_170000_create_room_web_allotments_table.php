<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_web_allotments', function (Blueprint $table) {
            $table->id();
            $table->date('allotment_date')->index();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['allotment_date', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_web_allotments');
    }
};
