<?php

namespace Tests\Feature;

use App\Livewire\DiningReservationManagement;
use App\Livewire\MeetingRoomManagement;
use App\Livewire\ReportDashboard;
use App\Livewire\ReservationManagement;
use App\Models\DiningPackage;
use App\Models\DiningReservation;
use App\Models\MeetingPackage;
use App\Models\MeetingReservation;
use App\Models\MeetingRoom;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\FineDiningService;
use App\Services\MeetingRoomService;
use App\Services\ReservationService;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\MeetingAndDiningSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionHistoryAndExportTest extends TestCase
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

    public function test_room_reservations_tab_active_and_history_separation(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $dlxType = RoomType::where('code', 'DLX')->first();

        // 1. Active reservation (future date)
        $activeRes = ReservationService::createReservation([
            'customer_name' => 'Tamu Aktif Masa Depan',
            'customer_phone' => '081200000001',
            'room_type_id' => $stdType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'adults' => 2,
            'source' => 'walk_in',
        ]);

        // 2. History reservation (checked_out)
        $historyRes = ReservationService::createReservation([
            'customer_name' => 'Tamu Selesai Checkout',
            'customer_phone' => '081200000002',
            'room_type_id' => $dlxType->id,
            'check_in_date' => now()->subDays(5)->toDateString(),
            'check_out_date' => now()->subDays(3)->toDateString(),
            'adults' => 1,
            'source' => 'walk_in',
        ]);
        $historyRes->update(['status' => 'checked_out']);

        // Test Livewire tab switching
        Livewire::actingAs($this->manager)
            ->test(ReservationManagement::class)
            ->assertSet('viewTab', 'active')
            ->assertSee('Tamu Aktif Masa Depan')
            ->assertDontSee('Tamu Selesai Checkout')
            // Switch to history tab
            ->set('viewTab', 'history')
            ->assertSet('viewTab', 'history')
            ->assertSee('Tamu Selesai Checkout')
            ->assertDontSee('Tamu Aktif Masa Depan')
            ->assertSee('Ekspor Excel')
            ->assertSee('Ekspor PDF');
    }

    public function test_meeting_reservations_tab_active_and_history_separation(): void
    {
        $room = MeetingRoom::first();
        $pkg = MeetingPackage::first();

        // 1. Active meeting (today/future)
        MeetingRoomService::createReservation([
            'meeting_room_id' => $room->id,
            'meeting_package_id' => $pkg->id,
            'customer_name' => 'Acara Rapat Aktif',
            'customer_phone' => '081200000011',
            'event_name' => 'Rapat Kerja Tahunan',
            'event_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'pax' => 20,
        ]);

        // 2. Past meeting (history)
        $historyMeeting = MeetingRoomService::createReservation([
            'meeting_room_id' => $room->id,
            'meeting_package_id' => $pkg->id,
            'customer_name' => 'Acara Sudah Lewat',
            'customer_phone' => '081200000012',
            'event_name' => 'Workshop Masa Lalu',
            'event_date' => now()->subDays(4)->toDateString(),
            'start_time' => '13:00',
            'end_time' => '17:00',
            'pax' => 15,
        ]);
        $historyMeeting->update(['status' => 'completed']);

        Livewire::actingAs($this->manager)
            ->test(MeetingRoomManagement::class)
            ->assertSet('viewTab', 'active')
            ->assertSee('Acara Rapat Aktif')
            ->assertDontSee('Acara Sudah Lewat')
            ->set('viewTab', 'history')
            ->assertSet('viewTab', 'history')
            ->assertSee('Acara Sudah Lewat')
            ->assertDontSee('Acara Rapat Aktif')
            ->assertSee('Ekspor Excel')
            ->assertSee('Ekspor PDF');
    }

    public function test_dining_reservations_tab_active_and_history_separation(): void
    {
        $pkg = DiningPackage::first();

        // 1. Active dining
        FineDiningService::createReservation([
            'dining_package_id' => $pkg->id,
            'customer_name' => 'Santap Malam Aktif',
            'customer_phone' => '081200000021',
            'reservation_date' => now()->toDateString(),
            'time_slot' => '19:00',
            'pax' => 4,
            'table_number_or_area' => 'Meja 1',
        ]);

        // 2. History dining
        $historyDining = FineDiningService::createReservation([
            'dining_package_id' => $pkg->id,
            'customer_name' => 'Santap Siang Selesai',
            'customer_phone' => '081200000022',
            'reservation_date' => now()->subDays(2)->toDateString(),
            'time_slot' => '12:00',
            'pax' => 6,
            'table_number_or_area' => 'Meja 2',
        ]);
        $historyDining->update(['status' => 'completed']);

        Livewire::actingAs($this->manager)
            ->test(DiningReservationManagement::class)
            ->assertSet('viewTab', 'active')
            ->assertSee('Santap Malam Aktif')
            ->assertDontSee('Santap Siang Selesai')
            ->set('viewTab', 'history')
            ->assertSet('viewTab', 'history')
            ->assertSee('Santap Siang Selesai')
            ->assertDontSee('Santap Malam Aktif')
            ->assertSee('Ekspor Excel')
            ->assertSee('Ekspor PDF');
    }

    public function test_report_dashboard_history_tab_and_revenue_metrics(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room = MeetingRoom::first();
        $mPkg = MeetingPackage::first();
        $dPkg = DiningPackage::first();

        // Seed 1 room, 1 meeting, 1 dining
        ReservationService::createReservation([
            'customer_name' => 'Tamu Kamar Rekap',
            'customer_phone' => '081200000031',
            'room_type_id' => $stdType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'adults' => 2,
            'source' => 'walk_in',
        ]);

        MeetingRoomService::createReservation([
            'meeting_room_id' => $room->id,
            'meeting_package_id' => $mPkg->id,
            'customer_name' => 'Pemesan Meeting Rekap',
            'customer_phone' => '081200000032',
            'event_name' => 'Meeting Rekap',
            'event_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'pax' => 10,
        ]);

        FineDiningService::createReservation([
            'dining_package_id' => $dPkg->id,
            'customer_name' => 'Pemesan Dining Rekap',
            'customer_phone' => '081200000033',
            'reservation_date' => now()->toDateString(),
            'time_slot' => '18:00',
            'pax' => 2,
            'table_number_or_area' => 'Meja 5',
        ]);

        Livewire::actingAs($this->manager)
            ->test(ReportDashboard::class)
            ->set('activeTab', 'history')
            ->assertSee('Riwayat Pesanan Terpadu')
            ->assertSee('Omzet Kamar Hotel')
            ->assertSee('Omzet Ruang Meeting')
            ->assertSee('Omzet Fine Dining')
            ->assertSee('Grand Total Omzet')
            ->assertSee('Tamu Kamar Rekap')
            ->assertSee('Meeting Rekap')
            ->assertSee('Pemesan Dining Rekap');
    }

    public function test_csv_export_endpoint_returns_valid_stream(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.export.csv', [
            'unit' => 'all',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_pdf_export_endpoint_returns_pdf_document(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.export.pdf', [
            'unit' => 'all',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
