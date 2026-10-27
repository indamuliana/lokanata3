<?php

namespace Tests\Feature;

use App\Livewire\DiningReservationManagement;
use App\Livewire\MeetingRoomManagement;
use App\Models\Customer;
use App\Models\DiningPackage;
use App\Models\DiningReservation;
use App\Models\DiningService;
use App\Models\DiningServiceItem;
use App\Models\MeetingPackage;
use App\Models\MeetingReservation;
use App\Models\MeetingReservationItem;
use App\Models\MeetingRoom;
use App\Models\MeetingService;
use App\Models\User;
use App\Services\FineDiningService;
use App\Services\MeetingRoomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class MeetingAndDiningTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;
    protected MeetingRoom $room;
    protected MeetingPackage $meetingPackage;
    protected DiningPackage $diningPackage;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\DefaultSettingsSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $this->seed(\Database\Seeders\AdditionalServiceSeeder::class);
        $this->seed(\Database\Seeders\MeetingAndDiningSeeder::class);

        $this->staff = User::where('username', 'resepsionis')->first() ?? User::factory()->create(['username' => 'staff1']);
        $this->actingAs($this->staff);

        $this->room = MeetingRoom::where('name', 'Ruang Rapat Cimanuk')->first();
        $this->meetingPackage = MeetingPackage::first();
        $this->diningPackage = DiningPackage::first();

        $this->customer = Customer::create([
            'name' => 'Dr. Hendra Gunawan',
            'phone' => '6281122334455',
            'company' => 'Dinas Kesehatan Garut',
        ]);
    }

    public function test_can_create_meeting_reservation_with_all_in_pricing(): void
    {
        $res = MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'event_name' => 'Seminar Kesehatan Reproduksi',
            'event_date' => now()->addDays(3)->toDateString(),
            'start_time' => '08:00',
            'end_time' => '12:00', // 4 jam -> halfday_rate = 900.000
            'pax' => 25,
            'pricing_type' => 'all_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'customer_company' => $this->customer->company,
            'initial_paid_amount' => 500000,
        ]);

        $this->assertNotNull($res);
        $this->assertEquals('Seminar Kesehatan Reproduksi', $res->event_name);
        $this->assertEquals(900000, $res->subtotal);
        $this->assertEquals(90000, $res->tax_amount); // 10%
        $this->assertEquals(990000, $res->total_amount);
        $this->assertEquals(500000, $res->paid_amount);
        $this->assertEquals('partially_paid', $res->payment_status);
        $this->assertEquals(490000, $res->balance);

        $this->assertDatabaseHas('payments', [
            'meeting_reservation_id' => $res->id,
            'amount' => 500000,
        ]);
    }

    public function test_can_create_meeting_reservation_with_per_pax_pricing(): void
    {
        // $this->meetingPackage is Rp 65.000 / pax
        $res = MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'meeting_package_id' => $this->meetingPackage->id,
            'event_name' => 'Workshop IT Garut',
            'event_date' => now()->addDays(5)->toDateString(),
            'start_time' => '13:00',
            'end_time' => '17:00',
            'pax' => 20,
            'pricing_type' => 'per_pax',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
        ]);

        $expectedSubtotal = 20 * $this->meetingPackage->price_per_pax; // 20 * 65000 = 1.300.000
        $expectedTax = (int) round($expectedSubtotal * 0.10); // 130.000
        $expectedTotal = $expectedSubtotal + $expectedTax; // 1.430.000

        $this->assertEquals($expectedSubtotal, $res->subtotal);
        $this->assertEquals($expectedTax, $res->tax_amount);
        $this->assertEquals($expectedTotal, $res->total_amount);
    }

    public function test_prevents_meeting_schedule_overlap_conflict(): void
    {
        $date = now()->addDays(7)->toDateString();

        // 1. Booking pertama: 09:00 - 13:00
        MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'event_name' => 'Acara Pagi',
            'event_date' => $date,
            'start_time' => '09:00',
            'end_time' => '13:00',
            'pax' => 15,
            'pricing_type' => 'all_in',
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
        ]);

        // 2. Booking kedua bentrok: 11:00 - 15:00
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Jadwal bentrok');

        MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'event_name' => 'Acara Bentrok',
            'event_date' => $date,
            'start_time' => '11:00',
            'end_time' => '15:00',
            'pax' => 15,
            'pricing_type' => 'all_in',
            'customer_name' => 'Doni',
            'customer_phone' => '081299998888',
        ]);
    }

    public function test_prevents_meeting_capacity_exceeded(): void
    {
        // Kapasitas Cimanuk adalah 30 pax
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('melebihi kapasitas maksimum');

        MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'event_name' => 'Acara Raksasa',
            'event_date' => now()->addDays(10)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'pax' => 60, // 60 > 30!
            'pricing_type' => 'all_in',
            'customer_name' => 'Faisal',
            'customer_phone' => '081333334444',
        ]);
    }

    public function test_can_record_fine_dining_reservation_and_payment(): void
    {
        // $this->diningPackage: "Paket Nasi Liwet Garut Komplit", 55.000 / pax, min 2 pax
        $res = FineDiningService::createReservation([
            'dining_package_id' => $this->diningPackage->id,
            'table_number_or_area' => 'Saung Lesehan 2',
            'reservation_date' => now()->addDays(2)->toDateString(),
            'time_slot' => '19:00',
            'pax' => 6,
            'customer_name' => 'Keluarga Bpk. Rahmat',
            'customer_phone' => '081288776655',
            'special_requests' => 'Penyekat ikhwan akhwat & sambal pisah',
            'initial_paid_amount' => 100000,
        ]);

        $expectedSubtotal = 6 * 55000; // 330.000
        $expectedTax = (int) round($expectedSubtotal * 0.10); // 33.000
        $expectedTotal = $expectedSubtotal + $expectedTax; // 363.000

        $this->assertEquals($expectedSubtotal, $res->subtotal);
        $this->assertEquals($expectedTax, $res->tax_amount);
        $this->assertEquals($expectedTotal, $res->total_amount);
        $this->assertEquals(100000, $res->paid_amount);
        $this->assertEquals('partially_paid', $res->payment_status);

        // Pelunasan resto
        $remaining = $res->balance;
        FineDiningService::recordPayment($res, $remaining, 'cash');

        $res->refresh();
        $this->assertEquals('paid', $res->payment_status);
        $this->assertEquals(0, $res->balance);
    }

    public function test_dining_minimum_pax_validation(): void
    {
        // Paket Prasmanan butuh min 20 pax
        $buffet = DiningPackage::where('name', 'Buffet Prasmanan Khazanah Syariah')->first();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Minimal pemesanan');

        FineDiningService::createReservation([
            'dining_package_id' => $buffet->id,
            'reservation_date' => now()->addDays(4)->toDateString(),
            'time_slot' => '12:00',
            'pax' => 5, // 5 < 20!
            'customer_name' => 'Kelompok Kecil',
            'customer_phone' => '081233445566',
        ]);
    }

    public function test_livewire_meeting_and_dining_components_render(): void
    {
        Livewire::test(MeetingRoomManagement::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Ruang Meeting')
            ->call('openCreateModal')
            ->assertSee('Ruang Rapat Cimanuk');

        Livewire::test(DiningReservationManagement::class)
            ->assertStatus(200)
            ->assertSee('Restoran dan Fine Dining Syariah')
            ->call('openCreateModal')
            ->assertSee('Paket Nasi Liwet Garut Komplit');
    }

    public function test_meeting_thermal_and_pdf_receipt(): void
    {
        $res = MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'event_name' => 'Raker Syariah',
            'event_date' => now()->addDays(2)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '13:00',
            'pax' => 15,
            'pricing_type' => 'all_in',
            'customer_name' => 'Ust. Salman',
            'customer_phone' => '081234567890',
        ]);

        $thermal = $this->get(route('meeting.thermal', $res->id));
        $thermal->assertStatus(200);
        $thermal->assertSee('INSITU HOTEL SYARIAH GARUT');
        $thermal->assertSee('Raker Syariah');
        $thermal->assertSee($res->reservation_number);

        $pdf = $this->get(route('meeting.pdf', $res->id));
        $pdf->assertStatus(200);
        $pdf->assertHeader('content-type', 'application/pdf');
    }

    public function test_dining_thermal_and_pdf_receipt(): void
    {
        $res = FineDiningService::createReservation([
            'dining_package_id' => $this->diningPackage->id,
            'table_number_or_area' => 'Meja VIP 1',
            'reservation_date' => now()->addDays(2)->toDateString(),
            'time_slot' => '19:00',
            'pax' => 4,
            'customer_name' => 'Bpk. Ridwan',
            'customer_phone' => '081299887766',
        ]);

        $thermal = $this->get(route('dining.thermal', $res->id));
        $thermal->assertStatus(200);
        $thermal->assertSee('INSITU HOTEL SYARIAH GARUT');
        $thermal->assertSee('Restoran Halal');
        $thermal->assertSee($res->reservation_number);

        $pdf = $this->get(route('dining.pdf', $res->id));
        $pdf->assertStatus(200);
        $pdf->assertHeader('content-type', 'application/pdf');
    }

    public function test_public_qr_verification_for_meeting_and_dining(): void
    {
        $meeting = MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'event_name' => 'Kajian Rutin',
            'event_date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'pax' => 20,
            'pricing_type' => 'all_in',
            'customer_name' => 'Ahmad Syarif',
            'customer_phone' => '6281234567890',
        ]);

        $token = $meeting->getOrCreateQrToken();

        $this->app['auth']->logout();
        $response = $this->get(route('document.verify', ['token' => $token]));
        $response->assertStatus(200);
        $response->assertSee('InSitu Hotel Syariah Garut');
        $response->assertSee('Dokumen Terverifikasi Sah dan Asli');
        $response->assertSee($meeting->reservation_number);
        $response->assertSee('Kajian Rutin');

        // No PII leak
        $response->assertDontSee('6281234567890');
    }

    public function test_unified_calendar_view_renders(): void
    {
        $this->actingAs($this->staff);

        // Create an allocated room reservation to ensure tape chart cells are executed
        $roomType = \App\Models\RoomType::first();
        $physRoom = \App\Models\Room::first();
        $res = \App\Services\ReservationService::createReservation([
            'room_type_id' => $roomType->id,
            'room_id' => $physRoom->id,
            'check_in_date' => now()->startOfWeek()->toDateString(),
            'check_out_date' => now()->startOfWeek()->addDays(2)->toDateString(),
            'customer_name' => 'Budi Timeline',
            'customer_phone' => '08123456789',
        ]);

        Livewire::test(\App\Livewire\UnifiedReservationCalendar::class)
            ->assertStatus(200)
            ->assertSee('Kalender Reservasi Terpadu')
            ->assertSee('Timeline Tape Chart Terpadu')
            ->assertSee('Kamar Menginap Hotel')
            ->assertSee('Ruang Meeting dan Ballroom')
            ->assertSee('Restoran dan Area Meja Syariah')
            ->assertSee('Budi Timeline')
            ->call('openDetail', $res->id, 'room')
            ->assertSet('showDetailModal', true)
            ->assertSee($res->reservation_number)
            ->set('resourceFilter', 'meeting')
            ->assertSee('Ruang Meeting dan Ballroom')
            ->set('resourceFilter', 'dining')
            ->assertSee('Restoran dan Area Meja Syariah')
            ->set('resourceFilter', 'all')
            ->call('setDaysCount', 14)
            ->assertSet('daysCount', 14)
            ->call('changePeriod', 1)
            ->call('goToToday')
            ->set('activeView', 'agenda')
            ->assertSee('Semua Layanan');
    }

    public function test_ota_settlement_status_toggle(): void
    {
        $roomType = \App\Models\RoomType::first();
        $res = \App\Services\ReservationService::createReservation([
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'ota_manual',
            'ota_name' => 'Traveloka',
            'ota_reference_number' => 'TRV-12345',
            'ota_gross_amount' => 500000,
            'ota_commission' => 75000,
            'ota_net_expected' => 425000,
            'ota_settlement_status' => 'pending',
            'customer_name' => 'Tamu OTA',
            'customer_phone' => '081234567890',
        ]);

        $this->assertEquals('pending', $res->ota_settlement_status);

        Livewire::test(\App\Livewire\ReservationManagement::class)
            ->call('toggleOtaSettlement', $res->id);

        $res->refresh();
        $this->assertEquals('settled', $res->ota_settlement_status);
    }

    public function test_can_manage_dining_packages_in_livewire(): void
    {
        $this->actingAs($this->staff);

        Livewire::test(DiningReservationManagement::class)
            ->call('openPackageModal')
            ->assertSet('showPackageModal', true)
            ->set('package_name', 'Paket Kambing Guling Syariah')
            ->set('package_price_per_pax', 125000)
            ->set('package_min_pax', 15)
            ->set('package_menu_details', 'Kambing guling muda, lontong, sambal kecap pedas')
            ->call('savePackage')
            ->assertHasNoErrors();

        $pkg = DiningPackage::where('name', 'Paket Kambing Guling Syariah')->first();
        $this->assertNotNull($pkg);
        $this->assertEquals(125000, $pkg->price_per_pax);
        $this->assertEquals(15, $pkg->min_pax);

        // Edit Package
        Livewire::test(DiningReservationManagement::class)
            ->call('editPackage', $pkg->id)
            ->assertSet('package_name', 'Paket Kambing Guling Syariah')
            ->set('package_price_per_pax', 135000)
            ->call('savePackage')
            ->assertHasNoErrors();

        $pkg->refresh();
        $this->assertEquals(135000, $pkg->price_per_pax);

        // Toggle Status
        Livewire::test(DiningReservationManagement::class)
            ->call('togglePackageStatus', $pkg->id);

        $pkg->refresh();
        $this->assertFalse($pkg->is_active);
    }

    public function test_can_manage_meeting_rooms_and_packages_in_livewire(): void
    {
        $this->actingAs($this->staff);

        // Manage Room
        Livewire::test(MeetingRoomManagement::class)
            ->call('openSettingsModal')
            ->assertSet('showSettingsModal', true)
            ->set('room_name', 'Ruang Rapat VIP Malabar')
            ->set('room_capacity', 20)
            ->set('room_hourly_rate', 200000)
            ->set('room_halfday_rate', 700000)
            ->set('room_fullday_rate', 1300000)
            ->set('room_facilities', 'Smart TV 75 inch, Soundbar, AC')
            ->call('saveRoom')
            ->assertHasNoErrors();

        $newRoom = MeetingRoom::where('name', 'Ruang Rapat VIP Malabar')->first();
        $this->assertNotNull($newRoom);
        $this->assertEquals(200000, $newRoom->hourly_rate);
        $this->assertContains('Smart TV 75 inch', $newRoom->facilities);

        // Manage Meeting Package
        Livewire::test(MeetingRoomManagement::class)
            ->call('openSettingsModal')
            ->set('pkg_name', 'Paket Executive Snack & Coffee')
            ->set('pkg_price_per_pax', 45000)
            ->set('pkg_min_pax', 8)
            ->set('pkg_description', 'Kopi Garut tubruk, kue balok, risoles')
            ->call('saveMeetingPackage')
            ->assertHasNoErrors();

        $newPkg = MeetingPackage::where('name', 'Paket Executive Snack & Coffee')->first();
        $this->assertNotNull($newPkg);
        $this->assertEquals(45000, $newPkg->price_per_pax);

        // Toggle Status
        Livewire::test(MeetingRoomManagement::class)
            ->call('toggleMeetingPackageStatus', $newPkg->id);

        $newPkg->refresh();
        $this->assertFalse($newPkg->is_active);
    }

    public function test_can_soft_delete_and_restore_dining_package(): void
    {
        $this->actingAs($this->staff);

        $pkg = DiningPackage::create([
            'name' => 'Paket Uji Hapus',
            'price_per_pax' => 50000,
            'min_pax' => 5,
            'menu_details' => 'Menu tester',
            'is_active' => true,
        ]);

        // Soft delete via Livewire
        Livewire::test(DiningReservationManagement::class)
            ->call('deletePackage', $pkg->id);

        $this->assertSoftDeleted('dining_packages', ['id' => $pkg->id]);
        $this->assertNull(DiningPackage::find($pkg->id));
        $this->assertNotNull(DiningPackage::withTrashed()->find($pkg->id));

        // Restore via Livewire
        Livewire::test(DiningReservationManagement::class)
            ->call('restorePackage', $pkg->id);

        $this->assertNotSoftDeleted('dining_packages', ['id' => $pkg->id]);
        $this->assertNotNull(DiningPackage::find($pkg->id));
    }

    public function test_can_soft_delete_and_restore_meeting_room_and_package(): void
    {
        $this->actingAs($this->staff);

        $room = MeetingRoom::create([
            'name' => 'Ruang Uji Hapus',
            'capacity' => 15,
            'hourly_rate' => 100000,
            'halfday_rate' => 350000,
            'fullday_rate' => 600000,
            'is_active' => true,
        ]);

        $pkg = MeetingPackage::create([
            'name' => 'Paket Uji Meeting Hapus',
            'price_per_pax' => 30000,
            'min_pax' => 5,
            'is_active' => true,
        ]);

        // Soft delete room & package via Livewire
        Livewire::test(MeetingRoomManagement::class)
            ->call('deleteRoom', $room->id)
            ->call('deleteMeetingPackage', $pkg->id);

        $this->assertSoftDeleted('meeting_rooms', ['id' => $room->id]);
        $this->assertSoftDeleted('meeting_packages', ['id' => $pkg->id]);

        // Restore room & package via Livewire
        Livewire::test(MeetingRoomManagement::class)
            ->call('restoreRoom', $room->id)
            ->call('restoreMeetingPackage', $pkg->id);

        $this->assertNotSoftDeleted('meeting_rooms', ['id' => $room->id]);
        $this->assertNotSoftDeleted('meeting_packages', ['id' => $pkg->id]);
    }

    public function test_can_manage_master_meeting_services_in_livewire(): void
    {
        $this->actingAs($this->staff);

        Livewire::test(MeetingRoomManagement::class)
            ->call('openSettingsModal')
            ->set('settingsTab', 'services')
            ->set('svc_name', 'Sewa Proyektor Laser 5000 Lumens')
            ->set('svc_price', 350000)
            ->set('svc_unit', 'unit')
            ->call('saveService')
            ->assertHasNoErrors();

        $svc = MeetingService::where('name', 'Sewa Proyektor Laser 5000 Lumens')->first();
        $this->assertNotNull($svc);
        $this->assertEquals(350000, $svc->default_price);
        $this->assertEquals('unit', $svc->unit);

        // Edit
        Livewire::test(MeetingRoomManagement::class)
            ->call('editService', $svc->id)
            ->set('svc_price', 400000)
            ->call('saveService')
            ->assertHasNoErrors();

        $svc->refresh();
        $this->assertEquals(400000, $svc->default_price);

        // Toggle
        Livewire::test(MeetingRoomManagement::class)
            ->call('toggleServiceStatus', $svc->id);

        $svc->refresh();
        $this->assertFalse($svc->is_active);

        // Soft Delete
        Livewire::test(MeetingRoomManagement::class)
            ->call('deleteService', $svc->id);

        $this->assertSoftDeleted('meeting_services', ['id' => $svc->id]);

        // Restore
        Livewire::test(MeetingRoomManagement::class)
            ->call('restoreService', $svc->id);

        $this->assertNotSoftDeleted('meeting_services', ['id' => $svc->id]);
    }

    public function test_can_create_meeting_reservation_with_facility_notes_and_additional_services(): void
    {
        $this->actingAs($this->staff);

        $bannerSvc = MeetingService::where('name', 'like', '%Banner%')->first();
        $this->assertNotNull($bannerSvc);

        $res = MeetingRoomService::createReservation([
            'meeting_room_id' => $this->room->id,
            'event_name' => 'Raker Akbar Fasilitas Khusus',
            'event_date' => now()->addDays(4)->toDateString(),
            'start_time' => '08:00',
            'end_time' => '12:00', // 4 jam halfday = 900.000
            'pax' => 20,
            'pricing_type' => 'all_in',
            'customer_name' => 'Bapak Ir. Ridwan',
            'customer_phone' => '081333444555',
            'facility_notes' => 'Perlu meja saja susunan U-shape tanpa kursi, 5 titik terminal colokan listrik per meja.',
            'items' => [
                [
                    'meeting_service_id' => $bannerSvc->id,
                    'name' => $bannerSvc->name,
                    'quantity' => 2,
                    'unit_price' => $bannerSvc->default_price, // 2 x 150000 = 300000
                ],
                [
                    'meeting_service_id' => null, // Kustom manual
                    'name' => 'Colokan Listrik Extension Gratis',
                    'quantity' => 5,
                    'unit_price' => 0, // Rp 0
                ]
            ],
        ]);

        $this->assertNotNull($res);
        $this->assertEquals('Perlu meja saja susunan U-shape tanpa kursi, 5 titik terminal colokan listrik per meja.', $res->facility_notes);
        $this->assertEquals(2, $res->items()->count());

        $this->assertEquals(300000, $res->additional_charges);
        // Subtotal = 900.000, tax 10% = 90.000, total = 900.000 + 90.000 + 300.000 (flat) = 1.290.000
        $this->assertEquals(900000, $res->subtotal);
        $this->assertEquals(90000, $res->tax_amount);
        $this->assertEquals(1290000, $res->total_amount);

        // Test Livewire component can add post-booking addon with manual mode
        Livewire::test(MeetingRoomManagement::class)
            ->call('openDetailModal', $res->id)
            ->assertSet('showDetailModal', true)
            ->call('setPostServiceMode', 'manual')
            ->assertSet('post_service_mode', 'manual')
            ->set('post_item_name', 'Extra Kopi Hitam Termos')
            ->set('post_item_qty', 2)
            ->set('post_item_price', 50000)
            ->call('addPostBookingItem')
            ->assertHasNoErrors();

        // Test Livewire booking modal dynamic rows (Master vs Manual)
        Livewire::test(MeetingRoomManagement::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->call('addServiceRowFromMaster')
            ->call('addServiceRowManual')
            ->assertCount('service_items', 2);

        $res->refresh();
        $this->assertEquals(3, $res->items()->count());
        $this->assertEquals(400000, $res->additional_charges); // 300.000 + 100.000
        $this->assertEquals(1390000, $res->total_amount);

        // Verify PDF and Thermal receipt contain notes and items
        $thermalResponse = $this->get(route('meeting.thermal', $res->id));
        $thermalResponse->assertOk();
        $thermalResponse->assertSee('Perlu meja saja susunan U-shape');
        $thermalResponse->assertSee($bannerSvc->name);
        $thermalResponse->assertSee('Extra Kopi Hitam Termos');

        $pdfResponse = $this->get(route('meeting.pdf', $res->id));
        $pdfResponse->assertOk();
    }

    public function test_can_create_dining_reservation_with_wikrama_panitia_and_multi_addons(): void
    {
        $this->actingAs($this->staff);

        $liwetExtra = DiningService::where('name', 'like', '%Kastrol Tambahan%')->first();
        $this->assertNotNull($liwetExtra);

        $res = FineDiningService::createReservation([
            'group_name' => 'Panitia LKS Wikrama Garut',
            'pic_name' => 'Bpk. Ahmad Fauzi',
            'pic_phone' => '081234567899',
            'activity_link' => 'https://drive.google.com/rundown-lks-wikrama',
            'is_wikrama_teachers' => true,
            'dining_package_id' => $this->diningPackage->id, // 55.000 / pax
            'table_number_or_area' => 'Saung Utama 1 & 2',
            'reservation_date' => now()->addDays(2)->toDateString(),
            'time_slot' => '12:00',
            'pax' => 10,
            'apply_tax' => false, // Bebas pajak
            'customer_name' => 'SMK Wikrama 1 Garut',
            'customer_phone' => '081299887766',
            'service_items' => [
                [
                    'dining_service_id' => $liwetExtra->id,
                    'name' => $liwetExtra->name,
                    'quantity' => 2,
                    'unit_price' => $liwetExtra->default_price, // 2 x 85.000 = 170.000
                ],
                [
                    'dining_service_id' => null,
                    'name' => 'Custom Es Kopi Susu Aren Gula Merah',
                    'quantity' => 10,
                    'unit_price' => 12000, // 10 x 12.000 = 120.000
                ]
            ],
            'initial_paid_amount' => 200000,
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertNotNull($res);
        $this->assertEquals('Panitia LKS Wikrama Garut', $res->group_name);
        $this->assertEquals('Bpk. Ahmad Fauzi', $res->pic_name);
        $this->assertTrue($res->is_wikrama_teachers);
        $this->assertFalse($res->apply_tax);
        $this->assertEquals(0, $res->tax_amount); // Bebas pajak!

        // Subtotal = 10 * 55000 = 550.000
        $this->assertEquals(550000, $res->subtotal);
        // Addons = 170.000 + 120.000 = 290.000
        $this->assertEquals(290000, $res->additional_charges);
        // Total = 550.000 + 290.000 = 840.000
        $this->assertEquals(840000, $res->total_amount);
        $this->assertEquals(200000, $res->paid_amount);
        $this->assertEquals(640000, $res->balance);
        $this->assertEquals(2, $res->items()->count());

        // Test Livewire component can add and remove multi addons
        Livewire::test(DiningReservationManagement::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->call('addAddonFromMaster')
            ->call('addAddonManual')
            ->assertCount('booking_addon_items', 2)
            ->call('removeAddon', 1)
            ->assertCount('booking_addon_items', 1);
    }

    public function test_can_edit_dining_reservation_and_recalculate_totals(): void
    {
        $this->actingAs($this->staff);

        $res = FineDiningService::createReservation([
            'dining_package_id' => $this->diningPackage->id, // 55.000 / pax
            'table_number_or_area' => 'Meja 5',
            'reservation_date' => now()->addDays(1)->toDateString(),
            'time_slot' => '13:00',
            'pax' => 4,
            'apply_tax' => true,
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
        ]);

        $this->assertEquals(4 * 55000, $res->subtotal); // 220.000
        $this->assertEquals(22000, $res->tax_amount); // 10%
        $this->assertEquals(242000, $res->total_amount);

        // Edit via Livewire to correct typo, change pax, and add group name
        Livewire::test(DiningReservationManagement::class)
            ->call('openEditModal', $res->id)
            ->assertSet('showEditModal', true)
            ->set('edit_group_name', 'Keluarga Besar Bpk. Budi Santoso')
            ->set('edit_pax', 6)
            ->set('edit_apply_tax', false) // ubah jadi bebas pajak
            ->call('saveEditedReservation')
            ->assertHasNoErrors();

        $res->refresh();
        $this->assertEquals('Keluarga Besar Bpk. Budi Santoso', $res->group_name);
        $this->assertEquals(6, $res->pax);
        $this->assertFalse($res->apply_tax);
        $this->assertEquals(6 * 55000, $res->subtotal); // 330.000
        $this->assertEquals(0, $res->tax_amount);
        $this->assertEquals(330000, $res->total_amount);
    }

    public function test_can_manage_dining_services_master_and_upload_package_image(): void
    {
        Storage::fake('public');
        $this->actingAs($this->staff);

        // 1. Create Dining Package with Photo
        $fakeImage = UploadedFile::fake()->image('menu_liwet.jpg');

        Livewire::test(DiningReservationManagement::class)
            ->call('openMasterModal', 'packages')
            ->assertSet('showMasterModal', true)
            ->set('package_name', 'Paket Nasi Liwet Spesial Bebek')
            ->set('package_price_per_pax', 80000)
            ->set('package_min_pax', 4)
            ->set('package_menu_details', 'Nasi liwet, bebek goreng rempah, lalab, sambal')
            ->set('package_photo', $fakeImage)
            ->call('savePackage')
            ->assertHasNoErrors();

        $newPkg = DiningPackage::where('name', 'Paket Nasi Liwet Spesial Bebek')->first();
        $this->assertNotNull($newPkg);
        $this->assertNotNull($newPkg->image_path);
        Storage::disk('public')->assertExists($newPkg->image_path);

        // 2. Master Layanan Tambahan (CRUD, Toggle, Soft-delete, Restore)
        Livewire::test(DiningReservationManagement::class)
            ->call('openMasterModal', 'services')
            ->assertSet('masterTab', 'services')
            ->set('service_name', 'Kambing Guling Utuh Barakah')
            ->set('service_default_price', 2750000)
            ->set('service_unit', 'ekor')
            ->call('saveService')
            ->assertHasNoErrors();

        $svc = DiningService::where('name', 'Kambing Guling Utuh Barakah')->first();
        $this->assertNotNull($svc);
        $this->assertEquals(2750000, $svc->default_price);
        $this->assertEquals('ekor', $svc->unit);

        // Edit
        Livewire::test(DiningReservationManagement::class)
            ->call('editService', $svc->id)
            ->set('service_default_price', 3000000)
            ->call('saveService')
            ->assertHasNoErrors();

        $svc->refresh();
        $this->assertEquals(3000000, $svc->default_price);

        // Toggle
        Livewire::test(DiningReservationManagement::class)
            ->call('toggleServiceStatus', $svc->id);

        $svc->refresh();
        $this->assertFalse($svc->is_active);

        // Soft Delete
        Livewire::test(DiningReservationManagement::class)
            ->call('deleteService', $svc->id);

        $this->assertSoftDeleted('dining_services', ['id' => $svc->id]);

        // Restore
        Livewire::test(DiningReservationManagement::class)
            ->call('restoreService', $svc->id);

        $this->assertNotSoftDeleted('dining_services', ['id' => $svc->id]);
    }

    public function test_dining_thermal_and_pdf_display_wikrama_and_group_info(): void
    {
        $res = FineDiningService::createReservation([
            'group_name' => 'Guru-Guru Wikrama Garut',
            'pic_name' => 'Ibu Siti Wikrama',
            'pic_phone' => '081999888777',
            'activity_link' => 'https://drive.google.com/susunan-acara',
            'is_wikrama_teachers' => true,
            'dining_package_id' => $this->diningPackage->id,
            'table_number_or_area' => 'Gazebo Kolam',
            'reservation_date' => now()->addDays(2)->toDateString(),
            'time_slot' => '11:30',
            'pax' => 8,
            'customer_name' => 'Yayasan Prawitama',
            'customer_phone' => '081222333444',
            'service_items' => [
                [
                    'dining_service_id' => null,
                    'name' => 'Kelapa Muda Batok Murni',
                    'quantity' => 8,
                    'unit_price' => 15000,
                ]
            ],
        ]);

        $thermal = $this->get(route('dining.thermal', $res->id));
        $thermal->assertOk();
        $thermal->assertSee('Guru-Guru Wikrama Garut');
        $thermal->assertSee('PANITIA GURU WIKRAMA');
        $thermal->assertSee('Kelapa Muda Batok Murni');

        $pdf = $this->get(route('dining.pdf', $res->id));
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');
    }

    public function test_dining_detail_modal_whatsapp_and_activity_link_button(): void
    {
        $this->actingAs($this->staff);

        // Reservation 1: With activity link
        $res1 = FineDiningService::createReservation([
            'group_name' => 'Alumni Wikrama Angkatan 2020',
            'pic_name' => 'Fajar',
            'pic_phone' => '081299991111',
            'activity_link' => 'https://drive.google.com/rundown-reuni-wikrama',
            'is_wikrama_teachers' => false,
            'dining_package_id' => $this->diningPackage->id,
            'table_number_or_area' => 'Meja Panjang 1',
            'reservation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '13:00',
            'pax' => 12,
            'customer_name' => 'Fajar Ramadhan',
            'customer_phone' => '081288882222',
        ]);

        // Reservation 2: Without activity link
        $res2 = FineDiningService::createReservation([
            'dining_package_id' => $this->diningPackage->id,
            'table_number_or_area' => 'Meja 2',
            'reservation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '19:00',
            'pax' => 2,
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '081277773333',
        ]);

        Livewire::test(DiningReservationManagement::class)
            ->assertSee('Alumni Wikrama Angkatan 2020')
            ->assertSee('wa.me/6281288882222') // WhatsApp pemesan link
            ->assertSee('https://drive.google.com/rundown-reuni-wikrama') // Clickable activity link
            ->assertSee('disabled') // Disabled button for res 2
            ->call('openDetailModal', $res1->id)
            ->assertSet('showDetailModal', true)
            ->assertSee('Detil Reservasi Resto')
            ->assertSee($res1->reservation_number)
            ->assertSee('Chat WhatsApp Pemesan')
            ->assertSee('Chat WhatsApp PIC');
    }
}
