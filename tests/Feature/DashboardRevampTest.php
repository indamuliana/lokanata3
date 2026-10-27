<?php

namespace Tests\Feature;

use App\Livewire\ReservationManagement;
use App\Livewire\RoomControlDashboard;
use App\Models\DiningPackage;
use App\Models\DiningReservation;
use App\Models\MeetingPackage;
use App\Models\MeetingReservation;
use App\Models\MeetingRoom;
use App\Models\PmrRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\FineDiningService;
use App\Services\FrontOfficeService;
use App\Services\MeetingRoomService;
use App\Services\ReservationService;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\MeetingAndDiningSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardRevampTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
            MeetingAndDiningSeeder::class,
        ]);

        $this->manager = User::where('email', 'manager@insitu.id')->first();
    }

    private function createDummyReservation(): Reservation
    {
        $dlxType = RoomType::where('code', 'DLX')->first();
        $room201 = Room::where('room_number', '201')->first();

        return ReservationService::createReservation([
            'customer_name' => 'Tamu Percobaan',
            'customer_phone' => '081234567899',
            'room_type_id' => $dlxType->id,
            'room_id' => $room201?->id,
            'check_in_date' => now()->toDateString(),
            'check_in_time' => '14:00',
            'check_out_date' => now()->addDays(2)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'infants' => 0,
            'source' => 'walk_in',
        ]);
    }

    public function test_dashboard_renders_quick_action_bar_and_navigation(): void
    {
        $response = $this->actingAs($this->manager)->get(route('dashboard'));
        $response->assertOk();

        // 1. Quick action bar items
        $response->assertSee('Input Reservasi Baru');
        $response->assertSee('Booking Ruang Meeting');
        $response->assertSee('Reservasi Fine Dining');

        // 2. Navigation dropdown z-index & positioning
        $response->assertSee('relative z-50 bg-white border-b border-gray-100');
        $response->assertSee('top-full z-[100] mt-1.5');
    }

    public function test_dashboard_compact_summary_accordion_toggle(): void
    {
        $today = now()->toDateString();
        $room101 = Room::where('room_number', '101')->first();
        $stdType = RoomType::where('code', 'STD')->first();

        // Create sample PMR
        FrontOfficeService::createPmr($room101, 'Ganti selimut ekstra', 'high');

        // Create sample Meeting
        $meetingRoom = MeetingRoom::first();
        $meetingPkg = MeetingPackage::first();
        MeetingRoomService::createReservation([
            'meeting_room_id' => $meetingRoom->id,
            'meeting_package_id' => $meetingPkg->id,
            'customer_name' => 'PT Wikrama Jaya',
            'customer_phone' => '0812345678',
            'event_name' => 'Workshop IT',
            'event_date' => $today,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'pax' => 20,
            'pricing_type' => 'all_in',
        ]);

        // Create sample Dining
        $diningPkg = DiningPackage::first();
        FineDiningService::createReservation([
            'dining_package_id' => $diningPkg?->id,
            'customer_name' => 'Bapak Ahmad',
            'customer_phone' => '0812345679',
            'reservation_date' => $today,
            'time_slot' => '12:30',
            'pax' => 4,
            'table_number_or_area' => 'Meja VIP 1',
        ]);

        $test = Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->assertOk()
            ->assertSee('Check-In Hari Ini')
            ->assertSee('PMR Aktif')
            ->assertSee('Ruang Meeting')
            ->assertSee('Fine Dining')
            ->assertSet('expandedSummary', null);

        // Toggle PMR
        $test->call('toggleSummary', 'pmr')
            ->assertSet('expandedSummary', 'pmr')
            ->assertSee('Ganti selimut ekstra')
            ->assertSee('Tutup ✕');

        // Toggle Meeting
        $test->call('toggleSummary', 'meeting')
            ->assertSet('expandedSummary', 'meeting')
            ->assertSee('Workshop IT')
            ->assertSee('PT Wikrama Jaya');

        // Toggle Dining
        $test->call('toggleSummary', 'dining')
            ->assertSet('expandedSummary', 'dining')
            ->assertSee('Bapak Ahmad')
            ->assertSee('Meja VIP 1');

        // Close
        $test->call('toggleSummary', 'dining')
            ->assertSet('expandedSummary', null);
    }

    public function test_room_card_compact_styling_elements(): void
    {
        $test = Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->assertOk();

        // 1. Grid 5 columns
        $test->assertSee('lg:grid-cols-5');

        // 2. 6px ribbon bar for cleanliness
        $test->assertSee('h-1.5 w-full absolute top-0 left-0');

        // 3. Room type color border outline & shadow style (3px)
        $test->assertSee('border: 3px solid');
        $test->assertSee('box-shadow: 0 4px 16px -2px');

        // 4. Pastel backgrounds (at least one clean available room exists from seeder)
        $test->assertSee('bg-emerald-50/70');
    }

    public function test_dashboard_room_detail_modal_open_and_close(): void
    {
        $reservation = $this->createDummyReservation();

        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->assertSet('showDetailModal', false)
            ->call('openDetailModal', $reservation->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('detailReservationId', $reservation->id)
            ->assertSee($reservation->customer->name)
            ->assertSee('Detil Reservasi Tamu')
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false)
            ->assertSet('detailReservationId', null);
    }

    public function test_reservation_management_revamp_features(): void
    {
        $reservation = $this->createDummyReservation();

        // Test filtering, new columns, WA icon, Invoice toggle, and detail modal
        Livewire::actingAs($this->manager)
            ->test(ReservationManagement::class)
            ->assertOk()
            ->assertSee('Semua Periode')
            ->assertSee('Semua Kanal')
            ->assertSee('Semua Status Bayar')
            ->set('periodFilter', 'this_month')
            ->assertSet('periodFilter', 'this_month')
            ->set('channelFilter', 'walk_in')
            ->assertSet('channelFilter', 'walk_in')
            ->set('paymentFilter', 'paid')
            ->assertSet('paymentFilter', 'paid')
            ->set('paymentFilter', 'all')
            ->set('channelFilter', 'all')
            ->set('periodFilter', 'all')
            // Test selection
            ->set('selectedRows', [(string) $reservation->id])
            ->assertSee('reservasi terpilih')
            ->call('clearSelectedRows')
            ->assertSet('selectedRows', [])
            // Test detail modal
            ->assertSet('showDetailModal', false)
            ->call('openDetailModal', $reservation->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('detailReservationId', $reservation->id)
            ->assertSee($reservation->customer->name)
            ->assertSee('Detil Reservasi Tamu')
            ->call('closeDetailModal')
            ->assertSet('showDetailModal', false);
    }

    public function test_reservation_check_in_time_lifecycle_and_display(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room101 = Room::where('room_number', '101')->first();

        // 1. Create reservation via Livewire with estimated check-in time 16:30
        $test = Livewire::actingAs($this->manager)
            ->test(ReservationManagement::class)
            ->set('customer_name', 'Budi Santoso')
            ->set('customer_phone', '081234567800')
            ->set('customer_identity_type', 'KTP')
            ->set('room_type_id', $stdType->id)
            ->set('room_id', $room101->id)
            ->set('check_in_date', now()->toDateString())
            ->set('check_in_time', '16:30')
            ->set('check_out_date', now()->addDay()->toDateString())
            ->set('source', 'walk_in')
            ->call('saveReservation')
            ->assertHasNoErrors();

        $res = Reservation::where('customer_id', function ($q) {
            $q->select('id')->from('customers')->where('name', 'Budi Santoso');
        })->first();

        $this->assertNotNull($res);
        $this->assertEquals('16:30', $res->check_in_time);

        // 2. See check-in time rendered in reservations table
        $test->assertSee('16:30 WIB');

        // 3. Edit reservation to 19:00
        Livewire::actingAs($this->manager)
            ->test(ReservationManagement::class)
            ->call('openEditModal', $res->id)
            ->assertSet('edit_check_in_time', '16:30')
            ->set('edit_check_in_time', '19:00')
            ->call('saveEditedReservation')
            ->assertHasNoErrors();

        $this->assertEquals('19:00', $res->fresh()->check_in_time);

        // 4. See check-in time rendered in RoomControlDashboard today pending check-in list
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('toggleSummary', 'checkin')
            ->assertSee('19:00 WIB');
    }

    public function test_cleaning_status_distinguishes_clean_and_inspected_cards(): void
    {
        $room101 = Room::where('room_number', '101')->first();
        $room102 = Room::where('room_number', '102')->first();

        // 1. Room 101 as 'inspected' (Siap Pakai), Room 102 as 'clean' (Bersih menunggu inspeksi)
        $room101->update(['cleaning_status' => 'inspected']);
        $room102->update(['cleaning_status' => 'clean']);

        $test = Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->assertOk();

        // Inspected room: Siap Pakai badge and emerald styling
        $test->assertSee('Siap Pakai');
        $test->assertSee('bg-emerald-500');
        $test->assertSee('bg-emerald-50/70');

        // Clean room: Bersih badge and teal styling
        $test->assertSee('Bersih');
        $test->assertSee('bg-teal-500');
        $test->assertSee('bg-teal-50/70');

        // 2. Change room 101 to clean via Livewire modal
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openCleaningModal', $room101->id)
            ->set('new_cleaning_status', 'clean')
            ->call('saveCleaning')
            ->assertHasNoErrors();

        $this->assertEquals('clean', $room101->fresh()->cleaning_status);
    }
}
