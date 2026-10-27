<?php

namespace Tests\Feature;

use App\Livewire\CashierShiftManager;
use App\Livewire\ExpenseManager;
use App\Livewire\NightAuditWizard;
use App\Models\CashierShift;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\NightAudit;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\BillingService;
use App\Services\CashierShiftService;
use App\Services\ExpenseService;
use App\Services\FrontOfficeService;
use App\Services\NightAuditService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class AccountingAndCashierTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $receptionist;
    protected RoomType $roomType;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\DefaultSettingsSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $this->seed(\Database\Seeders\AdditionalServiceSeeder::class);
        $this->seed(\Database\Seeders\ChartOfAccountSeeder::class);

        $this->manager = User::where('username', 'manager')->first() ?? User::factory()->create(['username' => 'mgr']);
        $this->receptionist = User::where('username', 'resepsionis')->first() ?? User::factory()->create(['username' => 'rcp']);

        $this->actingAs($this->receptionist);

        $this->roomType = RoomType::first();
        $this->customer = Customer::create([
            'name' => 'Bpk. Ridwan Kamil',
            'phone' => '628122334455',
        ]);
    }

    public function test_double_entry_balancing_constraint(): void
    {
        // 1. Unbalanced journal: Debit 100.000 != Credit 80.000 -> throws InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Jurnal tidak seimbang');

        AccountingService::createBalancedEntry(
            'Jurnal Tidak Seimbang',
            now()->toDateString(),
            [
                ['account_code' => '1110', 'debit' => 100000, 'credit' => 0],
                ['account_code' => '4110', 'debit' => 0, 'credit' => 80000],
            ]
        );
    }

    public function test_balanced_journal_posts_successfully(): void
    {
        $entry = AccountingService::createBalancedEntry(
            'Setoran Modal Awal',
            now()->toDateString(),
            [
                ['account_code' => '1120', 'debit' => 10000000, 'credit' => 0, 'memo' => 'Bank'],
                ['account_code' => '3100', 'debit' => 0, 'credit' => 10000000, 'memo' => 'Modal'],
            ]
        );

        $this->assertNotNull($entry);
        $this->assertTrue($entry->isBalanced());
        $this->assertEquals(10000000, $entry->total_debit);
        $this->assertEquals(10000000, $entry->total_credit);
        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);
    }

    public function test_payment_creates_automatic_double_entry_journal(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
        ]);

        $payment = BillingService::recordPayment($res, 200000, 'cash');

        // Pastikan jurnal otomatis tercipta
        $journal = JournalEntry::where('reference_type', 'payment')
            ->where('reference_id', $payment->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertTrue($journal->isBalanced());
        $this->assertEquals(200000, $journal->total_debit);

        // Debit: 1110 (Kas), Credit: 2110 (Uang Muka Tamu)
        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'debit' => 200000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'debit' => 0,
            'credit' => 200000,
        ]);
    }

    public function test_operational_expense_auto_approval_threshold(): void
    {
        // 1. Biaya <= Rp 500.000: Auto-Approved langsung
        $smallExp = ExpenseService::createExpense([
            'category' => 'supplies',
            'amount' => 150000,
            'payment_method' => 'cash',
            'description' => 'Beli sabun dan tisu resepsionis',
        ], $this->receptionist);

        $this->assertEquals('approved', $smallExp->status);
        $this->assertNotNull($smallExp->approved_at);

        // Jurnal otomatis terbentuk
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'expense',
            'reference_id' => $smallExp->id,
        ]);

        // 2. Biaya > Rp 500.000: Pending Approval Manager
        $largeExp = ExpenseService::createExpense([
            'category' => 'maintenance',
            'amount' => 1200000,
            'payment_method' => 'bank_transfer',
            'description' => 'Servis AC dan genset hotel',
        ], $this->receptionist);

        $this->assertEquals('pending_approval', $largeExp->status);
        $this->assertNull($largeExp->approved_at);

        // Belum ada jurnal sebelum disetujui
        $this->assertDatabaseMissing('journal_entries', [
            'reference_type' => 'expense',
            'reference_id' => $largeExp->id,
        ]);

        // Manager menyetujui biaya besar
        ExpenseService::approveExpense($largeExp, $this->manager);
        $largeExp->refresh();
        $this->assertEquals('approved', $largeExp->status);
        $this->assertEquals($this->manager->id, $largeExp->approved_by);

        // Sekarang jurnalnya terbentuk
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'expense',
            'reference_id' => $largeExp->id,
        ]);
    }

    public function test_manager_can_reject_expense(): void
    {
        $exp = ExpenseService::createExpense([
            'category' => 'other',
            'amount' => 800000,
            'payment_method' => 'cash',
            'description' => 'Biaya tidak jelas',
        ], $this->receptionist);

        $this->assertEquals('pending_approval', $exp->status);

        ExpenseService::rejectExpense($exp, $this->manager, 'Tidak ada kuitansi pendukung');

        $exp->refresh();
        $this->assertEquals('rejected', $exp->status);
        $this->assertEquals('Tidak ada kuitansi pendukung', $exp->rejection_reason);
    }

    public function test_cashier_shift_lifecycle_and_variance(): void
    {
        // 1. Buka shift pagi dengan modal awal Rp 300.000
        $shift = CashierShiftService::openShift(
            $this->receptionist,
            300000,
            'Shift 1 (Pagi)'
        );

        $this->assertEquals('open', $shift->status);
        $this->assertEquals(300000, $shift->starting_cash);

        // Tidak boleh buka shift kedua saat shift pertama masih terbuka
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('masih memiliki shift kasir aktif');
        CashierShiftService::openShift($this->receptionist, 100000, 'Shift Dobel');
    }

    public function test_cashier_shift_close_calculates_cash_variance(): void
    {
        $shift = CashierShiftService::openShift(
            $this->receptionist,
            500000,
            'Shift Pagi'
        );

        // Buat pembayaran kamar tunai Rp 400.000
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Tunai',
            'customer_phone' => '081234567899',
        ]);
        BillingService::recordPayment($res, 400000, 'cash');

        // Buat pengeluaran tunai disetujui Rp 100.000
        ExpenseService::createExpense([
            'category' => 'supplies',
            'amount' => 100000,
            'payment_method' => 'cash',
            'description' => 'Beli materai & amplop',
        ], $this->receptionist);

        // Ekspektasi: 500.000 (modal) + 400.000 (masuk) - 100.000 (keluar) = 800.000
        $summary = CashierShiftService::calculateShiftSummary($shift);
        $this->assertEquals(800000, $summary['expected_cash']);

        // Tutup shift dengan hitungan fisik Rp 795.000 (selisih kurang Rp 5.000)
        CashierShiftService::closeShift($shift, 795000, 'Kembalian uang receh kurang Rp 5.000');

        $shift->refresh();
        $this->assertEquals('closed', $shift->status);
        $this->assertEquals(800000, $shift->expected_cash);
        $this->assertEquals(795000, $shift->actual_cash);
        $this->assertEquals(-5000, $shift->difference);
        $this->assertEquals('Kembalian uang receh kurang Rp 5.000', $shift->difference_reason);
    }

    public function test_night_audit_flow_and_revenue_recognition(): void
    {
        $today = now()->toDateString();

        // 1. Buat reservasi kamar dan check-in kan
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => $today,
            'check_out_date' => now()->addDays(2)->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Menginap Audit',
            'customer_phone' => '081333444555',
        ]);

        $room = $this->roomType->rooms->first();
        FrontOfficeService::checkIn($res, $room);

        // Pre-audit checklist
        $checklist = NightAuditService::getAuditChecklist($today);
        $this->assertEquals(1, $checklist['occupied_rooms_count']);
        $this->assertFalse($checklist['already_audited']);

        // Jalankan Night Audit
        $audit = NightAuditService::performNightAudit($this->manager, $today, 'Audit tengah malam sukses');

        $this->assertNotNull($audit);
        $this->assertEquals($today, $audit->audit_date->toDateString());
        $this->assertEquals(1, $audit->rooms_occupied_count);
        $this->assertGreaterThan(0, $audit->total_room_revenue);

        // Jurnal pengakuan pendapatan kamar tercipta
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'night_audit',
            'reference_id' => $res->id,
        ]);

        // Menolak eksekusi audit dobel di tanggal yang sama
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sudah pernah dilakukan sebelumnya');
        NightAuditService::performNightAudit($this->manager, $today);
    }

    public function test_livewire_cashier_expense_and_night_audit_components_render(): void
    {
        Livewire::actingAs($this->manager)
            ->test(CashierShiftManager::class)
            ->assertStatus(200)
            ->assertSee('Kasir Shift Front Desk');

        Livewire::actingAs($this->manager)
            ->test(ExpenseManager::class)
            ->assertStatus(200)
            ->assertSee('Pengeluaran Operasional Hotel');

        Livewire::actingAs($this->manager)
            ->test(NightAuditWizard::class)
            ->assertStatus(200)
            ->assertSee('Penutupan Harian (Night Audit)');
    }
}
