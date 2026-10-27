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
        // 1. Shift Kasir Front Desk
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('shift_name')->default('Shift Pagi'); // Shift Pagi, Siang, Malam
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->unsignedBigInteger('starting_cash'); // Modal awal kas fisik
            $table->unsignedBigInteger('expected_cash')->default(0);
            $table->unsignedBigInteger('actual_cash')->nullable(); // Hitungan fisik tutup shift
            $table->bigInteger('difference')->default(0); // actual - expected
            $table->text('difference_reason')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Pengeluaran Operasional
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique(); // EXP-20261022-0001
            $table->foreignId('cashier_shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->enum('category', ['supplies', 'utilities', 'maintenance', 'fnb', 'transport', 'other'])->default('other');
            $table->unsignedBigInteger('amount'); // Rupiah integer
            $table->enum('payment_method', ['cash', 'bank_transfer'])->default('cash');
            $table->text('description');
            $table->string('proof_file_path')->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // 3. Bagan Akun (Chart of Accounts)
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // 1110, 1120, 2110, dll.
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Jurnal Umum (Journal Entries Header)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique(); // JRN-20261022-0001
            $table->date('entry_date');
            $table->string('reference_type')->nullable(); // payment, expense, checkout, night_audit, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->boolean('is_posted')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        // 5. Rincian Baris Jurnal (Journal Lines)
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->string('memo')->nullable();
            $table->timestamps();
        });

        // 6. Catatan Rekam Jejak Night Audit
        Schema::create('night_audits', function (Blueprint $table) {
            $table->id();
            $table->date('audit_date')->unique();
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('performed_at');
            $table->unsignedInteger('rooms_occupied_count')->default(0);
            $table->unsignedBigInteger('total_room_revenue')->default(0);
            $table->unsignedBigInteger('total_meeting_revenue')->default(0);
            $table->unsignedBigInteger('total_dining_revenue')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('night_audits');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('cashier_shifts');
    }
};
