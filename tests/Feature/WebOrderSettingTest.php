<?php

namespace Tests\Feature;

use App\Livewire\PublicBookingEngine;
use App\Livewire\RoomControlDashboard;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomWebAllotment;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Database\Seeders\AdditionalServiceSeeder;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WebOrderSettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
            AdditionalServiceSeeder::class,
        ]);

        $this->manager = User::where('email', 'manager@insitu.id')->first();
    }

    public function test_can_open_web_order_modal_and_view_available_rooms(): void
    {
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->assertSet('showWebOrderModal', false)
            ->call('openWebOrderModal')
            ->assertSet('showWebOrderModal', true)
            ->assertSee('Setting Penjualan Kamar via Website')
            ->assertSee('Online Allotment');
    }

    public function test_booked_room_is_disabled_and_marked_as_terpesan(): void
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $stdType = RoomType::where('code', 'STD')->first();
        $room101 = Room::where('room_number', '101')->first();

        // Book Room 101 for today - tomorrow
        $customer = Customer::create(['name' => 'Bpk. Hendra Gunawan', 'phone' => '081234567899']);
        $res = Reservation::create([
            'reservation_number' => 'RES-TEST-001',
            'customer_id' => $customer->id,
            'room_type_id' => $stdType->id,
            'source' => 'walk_in',
            'check_in_date' => $today,
            'check_out_date' => $tomorrow,
            'status' => 'confirmed',
            'subtotal' => 350000,
            'total_amount' => 350000,
            'paid_amount' => 350000,
            'payment_status' => 'paid',
        ]);
        ReservationRoom::create([
            'reservation_id' => $res->id,
            'room_id' => $room101->id,
            'assigned_from' => $today,
            'assigned_to' => $tomorrow,
        ]);

        // Open modal
        $test = Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openWebOrderModal')
            ->set('webOrderStartDate', $today)
            ->set('webOrderEndDate', $tomorrow);

        // Room 101 should show as Terpesan with guest name
        $test->assertSee('Terpesan (Bpk. Hendra Gunawan)');
    }

    public function test_can_save_selected_physical_rooms_to_web_allotment(): void
    {
        $date1 = now()->addDays(10)->toDateString();
        $date2 = now()->addDays(12)->toDateString(); // 2 nights

        $room102 = Room::where('room_number', '102')->first();
        $room103 = Room::where('room_number', '103')->first();

        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openWebOrderModal')
            ->set('webOrderStartDate', $date1)
            ->set('webOrderEndDate', $date2)
            ->set('selectedWebRooms', [$room102->id, $room103->id])
            ->call('saveWebOrderSettings')
            ->assertHasNoErrors()
            ->assertSet('showWebOrderModal', false);

        // Check allotment table for both nights
        $allot1 = RoomWebAllotment::whereDate('allotment_date', $date1)->where('room_id', $room102->id)->first();
        $this->assertNotNull($allot1);
        $this->assertTrue($allot1->is_enabled);

        $allot2 = RoomWebAllotment::whereDate('allotment_date', $date1)->where('room_id', $room103->id)->first();
        $this->assertNotNull($allot2);
        $this->assertTrue($allot2->is_enabled);

        $nextDate = Carbon::parse($date1)->addDay()->toDateString();
        $allot3 = RoomWebAllotment::whereDate('allotment_date', $nextDate)->where('room_id', $room102->id)->first();
        $this->assertNotNull($allot3);
        $this->assertTrue($allot3->is_enabled);
    }

    public function test_reservation_service_uses_configured_physical_allotment_count(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $date1 = now()->addDays(15)->toDateString();
        $date2 = now()->addDays(16)->toDateString();

        // 1. Initial before setting allotment: uses default quota
        $initialWebAvail = ReservationService::getAvailableRoomCount($stdType, $date1, $date2, 'website');
        $this->assertGreaterThan(0, $initialWebAvail);

        // 2. Select only 1 physical room (Room 101) for this date
        $room101 = Room::where('room_number', '101')->first();
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openWebOrderModal')
            ->set('webOrderStartDate', $date1)
            ->set('webOrderEndDate', $date2)
            ->set('selectedWebRooms', [$room101->id])
            ->call('saveWebOrderSettings')
            ->assertHasNoErrors();

        // Now website availability for STD on that date should be exactly 1!
        $updatedWebAvail = ReservationService::getAvailableRoomCount($stdType, $date1, $date2, 'website');
        $this->assertEquals(1, $updatedWebAvail);

        // 3. If we select 0 rooms for that type (uncheck all)
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openWebOrderModal')
            ->set('webOrderStartDate', $date1)
            ->set('webOrderEndDate', $date2)
            ->set('selectedWebRooms', [])
            ->call('saveWebOrderSettings')
            ->assertHasNoErrors();

        $zeroAvail = ReservationService::getAvailableRoomCount($stdType, $date1, $date2, 'website');
        $this->assertEquals(0, $zeroAvail);
    }

    public function test_toggle_room_type_web_visibility(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $date1 = now()->addDays(20)->toDateString();
        $date2 = now()->addDays(21)->toDateString();

        // Deactivate STD from web
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openWebOrderModal')
            ->set('webOrderStartDate', $date1)
            ->set('webOrderEndDate', $date2)
            ->set("roomTypeWebStatus.{$stdType->id}", false)
            ->call('saveWebOrderSettings')
            ->assertHasNoErrors();

        $this->assertFalse((bool) $stdType->fresh()->is_active);

        // Available count on website should be 0 because type is inactive
        $avail = ReservationService::getAvailableRoomCount($stdType, $date1, $date2, 'website');
        $this->assertEquals(0, $avail);
    }
}
