<?php

namespace Tests\Feature;

use App\Livewire\PublicBookingEngine;
use App\Livewire\ReportDashboard;
use App\Models\AdditionalService;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DiningPackage;
use App\Models\DiningReservation;
use App\Models\FixedAsset;
use App\Models\InventoryItem;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BillingService;
use App\Services\CashierShiftService;
use App\Services\ExpenseService;
use App\Services\FineDiningService;
use App\Services\FixedAssetService;
use App\Services\FrontOfficeService;
use App\Services\InventoryService;
use App\Services\NightAuditService;
use App\Services\ReportService;
use App\Services\ReservationService;
use Database\Seeders\AdditionalServiceSeeder;
use Database\Seeders\ChartOfAccountSeeder;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\MeetingAndDiningSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FullSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(DefaultSettingsSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->seed(AdditionalServiceSeeder::class);
        $this->seed(MeetingAndDiningSeeder::class);
        $this->seed(ChartOfAccountSeeder::class);

        $this->manager = User::factory()->create(['name' => 'Manager Hotel']);
        $this->manager->assignRole('Manager');

        $this->receptionist = User::factory()->create(['name' => 'Resepsionis Hotel']);
        $this->receptionist->assignRole('Resepsionis');
    }

    public function test_end_to_end_hotel_management_lifecycle(): void
    {
        $roomType = RoomType::where('name', 'like', '%Deluxe%')->firstOrFail();
        $checkIn = now()->toDateString();
        $checkOut = now()->addDays(2)->toDateString();

        // 1. PUBLIC WEBSITE BOOKING FLOW
        $initialAvailable = ReservationService::getAvailableRoomCount($roomType, $checkIn, $checkOut);
        $this->assertGreaterThan(0, $initialAvailable);

        $livewireBooking = Livewire::test(PublicBookingEngine::class)
            ->set('check_in_date', $checkIn)
            ->set('check_out_date', $checkOut)
            ->set('room_type_id', $roomType->id)
            ->set('guest_name', 'H. Ridwan Kamil')
            ->set('guest_phone', '081122334455')
            ->set('guest_nik', '3205012345670001')
            ->call('submitBooking')
            ->assertHasNoErrors();

        $reservation = Reservation::where('source', 'website')->latest()->firstOrFail();
        $this->assertEquals('pending', $reservation->status);
        $this->assertNotNull($reservation->expires_at);
        $this->assertTrue($reservation->expires_at->isFuture());

        // Inventory held (available decreased by 1)
        $newAvailable = ReservationService::getAvailableRoomCount($roomType, $checkIn, $checkOut);
        $this->assertEquals($initialAvailable - 1, $newAvailable);

        // 2. FRONT DESK OPENS CASHIER SHIFT & RECORDS DP PAYMENT
        $this->actingAs($this->receptionist);
        $shift = CashierShiftService::openShift($this->receptionist, 500000, 'Shift 1 Pagi');
        $this->assertEquals('open', $shift->status);

        // Record DP payment of Rp 300.000
        $dpPayment = BillingService::recordPayment(
            reservation: $reservation,
            amount: 300000,
            method: 'cash',
            notes: 'DP pemesanan website'
        );
        $this->assertEquals(300000, $dpPayment->amount);
        $reservation->refresh();
        $this->assertEquals(300000, $reservation->paid_amount);

        // 3. GUEST ARRIVES & CHECKS IN
        $physicalRoom = $roomType->rooms()->where('cleaning_status', 'clean')->firstOrFail();
        FrontOfficeService::checkIn($reservation, $physicalRoom, $this->receptionist->id);

        $reservation->refresh();
        $physicalRoom->refresh();
        $this->assertEquals('checked_in', $reservation->status);
        $this->assertFalse($physicalRoom->isAvailable($checkIn, $checkOut));

        // 4. GUEST ORDERS ADD-ON LAUNDRY & FINE DINING RESTAURANT
        BillingService::addServiceItem($reservation, 'Laundry Kilat', 2, 25000);

        $diningPkg = DiningPackage::firstOrFail();
        $diningRes = FineDiningService::createReservation([
            'customer_name' => $reservation->customer->name,
            'customer_phone' => $reservation->customer->phone,
            'dining_package_id' => $diningPkg->id,
            'reservation_date' => $checkIn,
            'time_slot' => '19:00',
            'pax' => 4,
            'notes' => 'Makan malam keluarga',
            'payment_method' => 'cash',
            'initial_paid_amount' => $diningPkg->price_per_pax * 4,
        ]);
        $this->assertEquals('confirmed', $diningRes->status);

        // 5. GUEST CHECKS OUT
        // Settle remaining balance
        $remaining = $reservation->balance;
        if ($remaining > 0) {
            BillingService::recordPayment(
                reservation: $reservation,
                amount: $remaining,
                method: 'cash',
                notes: 'Pelunasan checkout'
            );
        }

        FrontOfficeService::checkOut($reservation, $this->receptionist->id);
        $reservation->refresh();
        $physicalRoom->refresh();

        $this->assertEquals('checked_out', $reservation->status);
        $this->assertEquals('dirty', $physicalRoom->cleaning_status); // Auto-dirty applied!

        // 6. RECORD OPERATIONAL EXPENSE
        $expense = ExpenseService::createExpense([
            'category' => 'supplies',
            'amount' => 150000,
            'description' => 'Beli galon air mineral & tisu lobby',
        ], $this->receptionist);
        $this->assertEquals('approved', $expense->status); // <= Rp 500.000 auto-approved

        // 7. INVENTORY USAGE & FIXED ASSET DEPRECIATION
        $item = InventoryItem::create([
            'code' => 'INV-TEST-E2E',
            'name' => 'Sabun Mandi Tamu',
            'category' => 'amenities',
            'unit' => 'pcs',
            'current_stock' => 50,
            'minimum_stock' => 10,
            'average_cost' => 2000,
        ]);
        InventoryService::recordUsage($item->id, 2, notes: 'Pembersihan checkout', userId: $this->receptionist->id);
        $this->assertEquals(48, $item->fresh()->current_stock);

        $asset = FixedAsset::create([
            'code' => 'AST-E2E-001',
            'name' => 'AC Ruang Tamu',
            'category' => 'electronic',
            'purchase_date' => '2026-01-01',
            'purchase_cost' => 6000000,
            'useful_life_years' => 5,
            'salvage_value' => 600000,
            'accumulated_depreciation' => 0,
            'status' => 'active',
        ]);
        FixedAssetService::runMonthlyDepreciation(now()->toDateString(), $this->manager->id);
        $this->assertEquals(90000, $asset->fresh()->accumulated_depreciation);

        // 8. CLOSE CASHIER SHIFT
        $shiftSummary = CashierShiftService::calculateShiftSummary($shift);
        CashierShiftService::closeShift($shift, $shiftSummary['expected_cash'], 'Kas fisik cocok');
        $this->assertEquals('closed', $shift->fresh()->status);
        $this->assertEquals(0, $shift->fresh()->difference);

        // 9. FINANCIAL & OPERATIONAL REPORT INTEGRITY
        $entries = \App\Models\JournalEntry::with('lines.account')->get();
        $this->assertNotEmpty($entries);

        $trialBalance = ReportService::getTrialBalance(now()->toDateString());
        $this->assertTrue($trialBalance['is_balanced'], "Neraca saldo harus seimbang (Total Debit = Total Kredit).");
        $this->assertGreaterThan(0, $trialBalance['total_debit']);

        $incomeStmt = ReportService::getIncomeStatement(now()->startOfMonth()->toDateString(), now()->toDateString());
        $this->assertGreaterThan(0, $incomeStmt['total_revenue']);

        $operational = ReportService::getOperationalMetrics(now()->startOfMonth()->toDateString(), now()->toDateString());
        $this->assertGreaterThan(0, $operational['total_room_revenue']);
        $this->assertGreaterThan(0, $operational['total_bookings']);

        // 10. REPORT LIVEWIRE COMPONENT RENDERS
        $this->actingAs($this->manager);
        Livewire::test(ReportDashboard::class)
            ->assertStatus(200)
            ->assertSee('Laporan dan Analitik')
            ->assertSee('Kinerja Operasional')
            ->assertSee('Neraca Saldo');
    }
}
