<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\DiningPackage;
use App\Models\DiningReservation;
use App\Models\HotelSetting;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\MeetingPackage;
use App\Models\MeetingReservation;
use App\Models\MeetingRoom;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\FrontOfficeService;
use App\Services\NightAuditService;
use App\Services\ReportService;
use App\Services\ReservationService;
use Database\Seeders\ChartOfAccountSeeder;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NightAuditAndCheckoutAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
            ChartOfAccountSeeder::class,
        ]);
    }

    public function test_night_audit_and_checkout_avoids_double_revenue_recognition(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room101 = Room::where('room_number', '101')->first();
        $room101->update(['cleaning_status' => 'clean', 'operational_status' => 'active']);

        $today = now()->toDateString();
        $inTwoDays = now()->addDays(2)->toDateString();

        // 1. Create a 2-night reservation
        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'room_id' => $room101->id,
            'check_in_date' => $today,
            'check_out_date' => $inTwoDays,
            'source' => 'walk_in',
            'customer_name' => 'Tamu Akrual Test',
            'customer_phone' => '08999999991',
            'apply_tax' => true,
        ]);

        $this->assertEquals(2, $res->nights_count);
        $totalSubtotal = $res->subtotal;
        $totalTax = $res->tax_amount;
        $this->assertGreaterThan(0, $totalSubtotal);
        $this->assertGreaterThan(0, $totalTax);

        // 2. Perform Check-In
        FrontOfficeService::checkIn($res, $room101);
        $res->refresh();
        $this->assertEquals('checked_in', $res->status);

        // 3. Execute Night Audit for tonight
        $manager = User::where('email', 'manager@insitu.id')->first();
        $auditResult = NightAuditService::performNightAudit($manager, $today);
        $this->assertNotNull($auditResult);

        // Check Night Audit posted journals
        $roomRevenueAcc = ChartOfAccount::where('code', '4110')->first();
        $taxAcc = ChartOfAccount::where('code', '2120')->first();

        $auditRoomRevenueCredit = JournalLine::where('account_id', $roomRevenueAcc->id)
            ->whereHas('journalEntry', fn($q) => $q->where('reference_type', 'night_audit'))
            ->sum('credit');
        $this->assertGreaterThan(0, $auditRoomRevenueCredit);

        // 4. Perform Check-Out (e.g. today or next day)
        FrontOfficeService::checkOut($res);
        $res->refresh();
        $this->assertEquals('checked_out', $res->status);

        // Check total room revenue credited across ALL posted journals for this reservation / night audit
        $totalRevenue4110 = JournalLine::where('account_id', $roomRevenueAcc->id)
            ->whereHas('journalEntry', function ($q) use ($res) {
                $q->where('reference_id', $res->id)
                  ->whereIn('reference_type', ['night_audit', 'room_checkout']);
            })
            ->sum('credit');

        // Total 4110 credit MUST NOT exceed the reservation subtotal (i.e. no double counting 200%)!
        $this->assertEquals($totalSubtotal, (int) $totalRevenue4110);

        // Total 2120 tax credit MUST equal total reservation tax
        $totalTax2120 = JournalLine::where('account_id', $taxAcc->id)
            ->whereHas('journalEntry', function ($q) use ($res) {
                $q->where('reference_id', $res->id)
                  ->whereIn('reference_type', ['night_audit', 'room_checkout']);
            })
            ->sum('credit');

        $this->assertEquals($totalTax, (int) $totalTax2120);
    }

    public function test_sensitive_routes_protected_by_rbac_middleware(): void
    {
        // Unauthenticated guests are redirected to login
        $this->get('/night-audit')->assertRedirect('/login');
        $this->get('/reports')->assertRedirect('/login');
        $this->get('/web-admin')->assertRedirect('/login');
        $this->get('/settings')->assertRedirect('/login');

        // Staff user without Manager/Admin role receives 403 Forbidden
        $staff = User::factory()->create();
        $staff->assignRole('Resepsionis');

        $this->actingAs($staff)->get('/night-audit')->assertStatus(403);
        $this->actingAs($staff)->get('/reports')->assertStatus(403);
        $this->actingAs($staff)->get('/web-admin')->assertStatus(403);
        $this->actingAs($staff)->get('/settings')->assertStatus(403);

        // Manager / Admin receives 200 OK
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $this->actingAs($manager)->get('/night-audit')->assertStatus(200);
        $this->actingAs($manager)->get('/reports')->assertStatus(200);
        $this->actingAs($manager)->get('/web-admin')->assertStatus(200);
        $this->actingAs($manager)->get('/settings')->assertStatus(200);
    }

    public function test_report_service_operational_metrics_and_prorating(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room101 = Room::where('room_number', '101')->first();
        $room101->update(['cleaning_status' => 'clean', 'operational_status' => 'active']);

        $today = now()->toDateString();
        $inTwoDays = now()->addDays(2)->toDateString();

        // Create OTA reservation for 2 nights
        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'room_id' => $room101->id,
            'check_in_date' => $today,
            'check_out_date' => $inTwoDays,
            'source' => 'ota_manual',
            'ota_name' => 'Traveloka',
            'customer_name' => 'OTA Guest',
            'customer_phone' => '08777777777',
        ]);

        // Query metrics for just 1 day (today)
        $metrics = ReportService::getOperationalMetrics($today, $today);

        // Channel 'ota' should be 1
        $this->assertEquals(1, $metrics['channels']['ota']);
        $this->assertEquals(1, $metrics['occupied_room_nights']);

        // Revenue should be prorated for 1 of 2 nights (50% of total)
        $expectedProrated = (int) round($res->total_amount / 2);
        $this->assertEquals($expectedProrated, $metrics['total_room_revenue']);
    }

    public function test_dining_and_meeting_payment_pb1_tax_recognition(): void
    {
        $customer = Customer::create([
            'name' => 'Customer Resto Meeting',
            'phone' => '081234567890',
            'identity_type' => 'KTP',
        ]);

        // 1. Dining Reservation with Tax
        $dining = DiningReservation::create([
            'reservation_number' => 'DIN-TEST-001',
            'customer_id' => $customer->id,
            'reservation_date' => now()->toDateString(),
            'time_slot' => '12:00 - 14:00',
            'pax' => 4,
            'price_per_pax' => 100000,
            'subtotal' => 400000,
            'apply_tax' => true,
            'tax_amount' => 40000,
            'total_amount' => 440000,
            'paid_amount' => 440000,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $paymentDining = Payment::create([
            'payment_number' => 'PAY-DIN-001',
            'dining_reservation_id' => $dining->id,
            'amount' => 440000,
            'payment_method' => 'cash',
        ]);

        AccountingService::postPaymentJournal($paymentDining);

        // Verify Journal Lines: 1110 debit 440,000; 4130 credit 400,000; 2120 credit 40,000
        $taxAcc = ChartOfAccount::where('code', '2120')->first();
        $restoAcc = ChartOfAccount::where('code', '4130')->first();

        $taxCredit = JournalLine::where('account_id', $taxAcc->id)
            ->whereHas('journalEntry', fn($q) => $q->where('description', 'like', '%Penerimaan resto #PAY-DIN-001%'))
            ->sum('credit');
        $this->assertEquals(40000, (int) $taxCredit);

        $restoCredit = JournalLine::where('account_id', $restoAcc->id)
            ->whereHas('journalEntry', fn($q) => $q->where('description', 'like', '%Penerimaan resto #PAY-DIN-001%'))
            ->sum('credit');
        $this->assertEquals(400000, (int) $restoCredit);

        // 2. Meeting Reservation with Tax
        $meetingRoom = MeetingRoom::create([
            'name' => 'Ruang Rapat Al-Fath',
            'capacity' => 30,
            'hourly_rate' => 150000,
            'full_day_rate' => 1000000,
            'half_day_rate' => 600000,
            'is_active' => true,
        ]);
        $meeting = MeetingReservation::create([
            'reservation_number' => 'MTG-TEST-001',
            'customer_id' => $customer->id,
            'meeting_room_id' => $meetingRoom->id,
            'event_name' => 'Workshop Tech',
            'event_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'pax' => 20,
            'pricing_type' => 'all_in',
            'subtotal' => 1000000,
            'tax_amount' => 100000,
            'total_amount' => 1100000,
            'paid_amount' => 1100000,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $paymentMeeting = Payment::create([
            'payment_number' => 'PAY-MTG-001',
            'meeting_reservation_id' => $meeting->id,
            'amount' => 1100000,
            'payment_method' => 'bank_transfer',
        ]);

        AccountingService::postPaymentJournal($paymentMeeting);

        $meetingAcc = ChartOfAccount::where('code', '4120')->first();

        $taxMeetingCredit = JournalLine::where('account_id', $taxAcc->id)
            ->whereHas('journalEntry', fn($q) => $q->where('description', 'like', '%Penerimaan meeting #PAY-MTG-001%'))
            ->sum('credit');
        $this->assertEquals(100000, (int) $taxMeetingCredit);

        $meetingCredit = JournalLine::where('account_id', $meetingAcc->id)
            ->whereHas('journalEntry', fn($q) => $q->where('description', 'like', '%Penerimaan meeting #PAY-MTG-001%'))
            ->sum('credit');
        $this->assertEquals(1000000, (int) $meetingCredit);
    }
}
