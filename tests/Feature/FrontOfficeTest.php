<?php

namespace Tests\Feature;

use App\Livewire\RoomControlDashboard;
use App\Models\MaintenanceReport;
use App\Models\PmrRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\FrontOfficeService;
use App\Services\ReservationService;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FrontOfficeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
        ]);
    }

    public function test_checkin_and_checkout_lifecycle_with_auto_dirty(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room101 = Room::where('room_number', '101')->first();
        $room101->update(['cleaning_status' => 'clean', 'operational_status' => 'active']);

        // 1. Create reservation
        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'room_id' => $room101->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Checkin',
            'customer_phone' => '08123456789',
        ]);

        // 2. Perform Check-In
        FrontOfficeService::checkIn($res, $room101);
        $res->refresh();
        $this->assertEquals('checked_in', $res->status);

        // 3. Perform Check-Out: Must automatically mark room as 'dirty'
        FrontOfficeService::checkOut($res);
        $res->refresh();
        $room101->refresh();

        $this->assertEquals('checked_out', $res->status);
        $this->assertEquals('dirty', $room101->cleaning_status); // Auto dirty verified!
    }

    public function test_checkin_prevented_if_room_is_dirty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $stdType = RoomType::where('code', 'STD')->first();
        $room102 = Room::where('room_number', '102')->first();
        $room102->update(['cleaning_status' => 'dirty']); // Kamar masih kotor

        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'room_id' => $room102->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Tolak Kotor',
            'customer_phone' => '08129999999',
        ]);

        FrontOfficeService::checkIn($res, $room102);
    }

    public function test_extend_stay_recalculates_cost_and_date(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room103 = Room::where('room_number', '103')->first();
        $room103->update(['cleaning_status' => 'clean']);

        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'room_id' => $room103->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(), // 1 night
            'source' => 'walk_in',
            'customer_name' => 'Tamu Extend',
            'customer_phone' => '08133333333',
        ]);

        FrontOfficeService::checkIn($res, $room103);
        $initialTotal = $res->total_amount;

        // Extend by 2 more days
        $newCheckOut = now()->addDays(3)->toDateString();
        FrontOfficeService::extendStay($res, $newCheckOut);

        $res->refresh();
        $this->assertEquals($newCheckOut, $res->check_out_date->toDateString());
        $this->assertGreaterThan($initialTotal, $res->total_amount);
    }

    public function test_room_move_reassigns_and_marks_old_room_dirty(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room104 = Room::where('room_number', '104')->first();
        $room105 = Room::where('room_number', '105')->first();
        $room104->update(['cleaning_status' => 'clean']);
        $room105->update(['cleaning_status' => 'clean']);

        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'room_id' => $room104->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Pindah',
            'customer_phone' => '08144444444',
        ]);

        FrontOfficeService::checkIn($res, $room104);

        // Move to Room 105
        $move = FrontOfficeService::roomMove($res, $room105, 'Tamu minta kasir lantai 1 dekat lobi');

        $this->assertNotNull($move->id);
        $this->assertEquals($room104->id, $move->from_room_id);
        $this->assertEquals($room105->id, $move->to_room_id);

        $room104->refresh();
        $room105->refresh();
        $res->refresh();

        $this->assertEquals('dirty', $room104->cleaning_status); // Old room must be dirty
        $this->assertEquals($room105->id, $res->allocatedRooms()->first()->room_id);
    }

    public function test_housekeeping_pmr_and_maintenance_lifecycle(): void
    {
        $room106 = Room::where('room_number', '106')->first();

        // 1. Update cleaning status
        FrontOfficeService::updateCleaningStatus($room106, 'cleaning');
        $this->assertEquals('cleaning', $room106->fresh()->cleaning_status);

        FrontOfficeService::updateCleaningStatus($room106, 'inspected');
        $this->assertEquals('inspected', $room106->fresh()->cleaning_status);

        // 2. Request PMR
        $pmr = FrontOfficeService::createPmr($room106, 'Ganti handuk', 'high');
        $this->assertEquals('requested', $pmr->status);
        $this->assertEquals('high', $pmr->priority);

        FrontOfficeService::completePmr($pmr);
        $this->assertEquals('completed', $pmr->fresh()->status);

        // 3. Maintenance report
        $report = FrontOfficeService::reportMaintenance($room106, 'AC tidak dingin', 'high');
        $this->assertEquals('reported', $report->status);
        $this->assertEquals('maintenance', $room106->fresh()->operational_status);

        // Room 106 cannot be used while in maintenance
        $this->assertFalse($room106->fresh()->isAvailable(now()->toDateString(), now()->addDay()->toDateString()));

        // Resolve maintenance
        FrontOfficeService::resolveMaintenance($report, 'AC sudah diisi freon');
        $this->assertEquals('repaired', $report->fresh()->status);
        $this->assertEquals('active', $room106->fresh()->operational_status);
        $this->assertEquals('dirty', $room106->fresh()->cleaning_status); // Must be cleaned before inspected
    }

    public function test_room_control_dashboard_renders_with_manual_refresh(): void
    {
        $manager = User::where('email', 'manager@insitu.id')->first();

        Livewire::actingAs($manager)
            ->test(RoomControlDashboard::class)
            ->assertOk()
            ->assertSee('Kendali Kamar (Room Rack)')
            ->assertSee('Refresh Status Kamar') // Manual refresh button exists
            ->call('refreshRooms')
            ->assertSet('lastUpdated', now()->format('H:i:s'));
    }

    public function test_pmr_auto_completed_when_room_cleaned_or_manually_completed(): void
    {
        $manager = User::where('email', 'manager@insitu.id')->first();
        $room = Room::where('room_number', '101')->first();
        $room->update(['cleaning_status' => 'dirty']);

        // Case 1: PMR is auto completed when room cleaning status is updated to clean/inspected
        $pmr1 = FrontOfficeService::createPmr($room, 'Minta sprei baru');
        $this->assertEquals('requested', $pmr1->status);

        // Through Livewire cleaning modal
        Livewire::actingAs($manager)
            ->test(RoomControlDashboard::class)
            ->call('openCleaningModal', $room->id)
            ->set('new_cleaning_status', 'clean')
            ->call('saveCleaning')
            ->assertHasNoErrors();

        $pmr1->refresh();
        $this->assertEquals('completed', $pmr1->status);
        $this->assertNotNull($pmr1->completed_at);

        // Case 2: PMR is manually completed via completePmr 1-click button
        $room->update(['cleaning_status' => 'dirty']);
        $pmr2 = FrontOfficeService::createPmr($room, 'Perlu sapu & pel');
        $this->assertEquals('requested', $pmr2->status);

        Livewire::actingAs($manager)
            ->test(RoomControlDashboard::class)
            ->call('completePmr', $pmr2->id)
            ->assertHasNoErrors();

        $pmr2->refresh();
        $this->assertEquals('completed', $pmr2->status);
        $this->assertEquals('clean', $room->fresh()->cleaning_status);
    }
}
