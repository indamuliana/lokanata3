<?php

namespace Tests\Feature;

use App\Livewire\PublicBookingEngine;
use App\Livewire\RoomManagement;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RoomManagementRevampTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\DefaultSettingsSeeder::class,
        ]);

        $this->manager = User::where('email', 'manager@insitu.id')->first();
    }

    public function test_room_management_renders_tables_and_palette(): void
    {
        Livewire::actingAs($this->manager)
            ->test(RoomManagement::class)
            ->assertStatus(200)
            ->assertSee('Master Data Kamar')
            ->assertSee('Kamar Fisik')
            ->assertSee('Tipe Kamar')
            ->assertSeeHtml('<table');
    }

    public function test_room_type_validation_requires_photos_and_facilities(): void
    {
        Livewire::actingAs($this->manager)
            ->test(RoomManagement::class)
            ->set('showTypeModal', true)
            ->set('type_name', 'Grand Suite')
            ->set('type_code', 'GND')
            ->set('type_color', '#ea580c')
            ->set('existingPhotos', [])
            ->set('uploadedPhotos', [])
            ->call('saveType')
            ->assertHasErrors(['photos']);
    }

    public function test_can_create_room_type_with_color_photos_and_custom_facilities(): void
    {
        Storage::fake('public');

        $fakePhoto = UploadedFile::fake()->image('room1.jpg', 800, 600);

        Livewire::actingAs($this->manager)
            ->test(RoomManagement::class)
            ->set('showTypeModal', true)
            ->set('type_name', 'Presidential Penthouse')
            ->set('type_code', 'PNT')
            ->set('type_color', '#7c3aed')
            ->set('base_price', 1500000)
            ->set('high_season_price', 2000000)
            ->set('is_high_season_same', false)
            ->set('standard_capacity', 4)
            ->set('max_capacity', 6)
            ->set('uploadedPhotos', [$fakePhoto])
            ->set('facilitiesList', [
                ['icon' => '🛜', 'text' => 'Dedicated Fiber 200 Mbps'],
                ['icon' => '🛁', 'text' => 'Jacuzzi Air Panas Alami'],
                ['icon' => '🕋', 'text' => 'Musholla Pribadi'],
            ])
            ->call('saveType')
            ->assertHasNoErrors();

        $type = RoomType::where('code', 'PNT')->first();
        $this->assertNotNull($type);
        $this->assertEquals('#7c3aed', $type->color);
        $this->assertEquals(1500000, $type->base_price);
        $this->assertEquals(2000000, $type->high_season_price);
        $this->assertCount(1, $type->photos);
        $this->assertCount(3, $type->facilities);
        $this->assertEquals('🛜', $type->facilities[0]['icon']);
        $this->assertEquals('Dedicated Fiber 200 Mbps', $type->facilities[0]['text']);
    }

    public function test_can_set_custom_photo_as_cover(): void
    {
        $type = RoomType::create([
            'name' => 'Cover Test Suite',
            'code' => 'CTS',
            'color' => '#059669',
            'base_price' => 500000,
            'photos' => ['/storage/p1.jpg', '/storage/p2.jpg', '/storage/p3.jpg'],
            'photo_path' => '/storage/p1.jpg',
            'facilities' => [['icon' => '🛜', 'text' => 'WiFi']],
            'standard_capacity' => 2,
            'max_capacity' => 2,
        ]);

        Livewire::actingAs($this->manager)
            ->test(RoomManagement::class)
            ->call('openTypeModal', $type->id)
            ->assertSet('coverIndex', 0)
            ->call('setCover', 'existing', 2) // choose p3.jpg as cover
            ->call('saveType')
            ->assertHasNoErrors();

        $type->refresh();
        $this->assertEquals('/storage/p3.jpg', $type->photo_path);
        $this->assertEquals('/storage/p3.jpg', $type->photos[0]);
    }

    public function test_delete_room_with_snapshot_when_no_active_guest(): void
    {
        $type = RoomType::create([
            'name' => 'Deluxe Suite Test',
            'code' => 'DST',
            'color' => '#059669',
            'base_price' => 450000,
            'photos' => ['/storage/room-types/test.jpg'],
            'facilities' => [['icon' => '🛜', 'text' => 'WiFi']],
            'standard_capacity' => 2,
            'max_capacity' => 3,
        ]);

        $room = Room::create([
            'room_number' => '999T',
            'room_type_id' => $type->id,
            'floor' => 'Lantai 9',
            'operational_status' => 'active',
            'cleaning_status' => 'clean',
        ]);

        $customer = Customer::firstOrCreate(['phone' => '081299990001'], ['name' => 'Tamu Lama']);
        $res = Reservation::create([
            'reservation_number' => 'RES-TEST-DEL-01',
            'customer_id' => $customer->id,
            'room_type_id' => $type->id,
            'check_in_date' => now()->subDays(5)->toDateString(),
            'check_out_date' => now()->subDays(3)->toDateString(),
            'status' => 'checked_out',
            'subtotal' => 350000,
            'total_amount' => 350000,
            'paid_amount' => 350000,
            'payment_status' => 'paid',
        ]);

        $rr = ReservationRoom::create([
            'reservation_id' => $res->id,
            'room_id' => $room->id,
            'assigned_from' => now()->subDays(5)->toDateString(),
            'assigned_to' => now()->subDays(3)->toDateString(),
        ]);

        // Trigger permanent delete
        Livewire::actingAs($this->manager)
            ->test(RoomManagement::class)
            ->call('confirmDeleteRoom', $room->id)
            ->call('deleteRoom')
            ->assertHasNoErrors();

        // Room is deleted
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);

        // ReservationRoom remains with snapshot intact!
        $rr->refresh();
        $this->assertNull($rr->room_id);
        $this->assertNotNull($rr->room_snapshot);
        $this->assertEquals('999T', $rr->room_snapshot['room_number']);
        $this->assertEquals('999T', $rr->room_number); // Test model accessor
    }

    public function test_delete_room_type_with_snapshot(): void
    {
        $type = RoomType::create([
            'name' => 'Vintage Antique Suite',
            'code' => 'VNT',
            'color' => '#ca8a04',
            'base_price' => 500000,
            'photos' => ['/storage/room-types/vnt.jpg'],
            'facilities' => [['icon' => '🛜', 'text' => 'WiFi']],
            'standard_capacity' => 2,
            'max_capacity' => 2,
        ]);

        $customer = Customer::firstOrCreate(['phone' => '081299990002'], ['name' => 'Tamu Vintage']);
        $res = Reservation::create([
            'reservation_number' => 'RES-TEST-DEL-TYPE',
            'customer_id' => $customer->id,
            'room_type_id' => $type->id,
            'check_in_date' => now()->subDays(10)->toDateString(),
            'check_out_date' => now()->subDays(8)->toDateString(),
            'status' => 'checked_out',
            'subtotal' => 1000000,
            'total_amount' => 1000000,
            'paid_amount' => 1000000,
            'payment_status' => 'paid',
        ]);

        // Delete Room Type
        Livewire::actingAs($this->manager)
            ->test(RoomManagement::class)
            ->call('confirmDeleteType', $type->id)
            ->call('deleteType')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('room_types', ['id' => $type->id]);

        // Reservation snapshot holds history
        $res->refresh();
        $this->assertNull($res->room_type_id);
        $this->assertNotNull($res->room_type_snapshot);
        $this->assertEquals('Vintage Antique Suite', $res->room_type_snapshot['name']);
        $this->assertEquals('Vintage Antique Suite', $res->room_type_name); // Model accessor
    }

    public function test_landing_page_renders_room_cards_and_gallery(): void
    {
        $type = RoomType::create([
            'name' => 'Signature Garden Villa',
            'code' => 'SGV',
            'color' => '#059669',
            'base_price' => 750000,
            'photos' => ['/storage/room-types/villa1.jpg', '/storage/room-types/villa2.jpg'],
            'facilities' => [
                ['icon' => '🛜', 'text' => 'High Speed WiFi'],
                ['icon' => '🏊', 'text' => 'Private Hot Spring Pool'],
            ],
            'standard_capacity' => 2,
            'max_capacity' => 4,
            'is_active' => true,
        ]);

        $comp = Livewire::test(PublicBookingEngine::class)
            ->assertStatus(200)
            ->assertSee('2 Foto')
            ->assertSee('Signature Garden Villa')
            ->assertSee('Private Hot Spring Pool');

        $comp->call('openGallery', $type->id)
            ->assertSet('showGalleryModal', true)
            ->assertSee($type->name)
            ->call('nextPhoto')
            ->call('prevPhoto')
            ->call('closeGallery')
            ->assertSet('showGalleryModal', false);
    }
}
