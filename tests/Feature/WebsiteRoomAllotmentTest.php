<?php

namespace Tests\Feature;

use App\Livewire\PublicBookingEngine;
use App\Livewire\RoomManagement;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\ReservationService;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class WebsiteRoomAllotmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected RoomType $testType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
        ]);

        $this->adminUser = User::where('email', 'manager@insitu.id')->first();

        // Prepare test room type with 4 active rooms
        $this->testType = RoomType::create([
            'name' => 'Superior Suite',
            'code' => 'SUP-TEST',
            'description' => 'Kamar superior untuk pengujian kuota web',
            'color' => '#059669',
            'facilities' => [['icon' => '🛜', 'text' => 'WiFi']],
            'photos' => ['/storage/room-types/test-suite.jpg'],
            'standard_capacity' => 2,
            'max_capacity' => 3,
            'base_price' => 500000,
            'high_season_price' => 500000,
            'extra_person_fee' => 50000,
            'is_active' => true,
            'website_quota' => 2, // 2 out of 4 rooms can be sold online
        ]);

        for ($i = 1; $i <= 4; $i++) {
            Room::create([
                'room_number' => '90' . $i,
                'room_type_id' => $this->testType->id,
                'floor' => 'Lantai 2',
                'operational_status' => 'active',
                'cleaning_status' => 'clean',
            ]);
        }
    }

    public function test_room_type_has_website_quota_attribute_and_cast(): void
    {
        $this->assertEquals(2, $this->testType->website_quota);
        $this->assertIsInt($this->testType->website_quota);
    }

    public function test_availability_calculation_respects_website_quota(): void
    {
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(6)->toDateString();

        // All channels (physical inventory) has 4 rooms
        $allAvail = ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'all');
        $this->assertEquals(4, $allAvail);

        // Website channel is capped at website_quota = 2
        $webAvail = ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'website');
        $this->assertEquals(2, $webAvail);
    }

    public function test_website_booking_reduces_website_quota(): void
    {
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(6)->toDateString();

        // 1. Create one website booking
        ReservationService::createReservation([
            'room_type_id' => $this->testType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults' => 2,
            'customer_name' => 'Tamu Web 1',
            'customer_phone' => '081234567891',
            'source' => 'website',
        ]);

        // Physical availability should now be 4 - 1 = 3
        $this->assertEquals(3, ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'all'));

        // Website availability should now be 2 - 1 = 1
        $this->assertEquals(1, ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'website'));

        // 2. Create second website booking
        ReservationService::createReservation([
            'room_type_id' => $this->testType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults' => 2,
            'customer_name' => 'Tamu Web 2',
            'customer_phone' => '081234567892',
            'source' => 'website',
        ]);

        // Website quota is now exhausted (2/2 booked)
        $this->assertEquals(0, ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'website'));

        // But physical rooms are still available for Front Desk (4 - 2 = 2)!
        $this->assertEquals(2, ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'all'));
    }

    public function test_website_booking_prevented_when_quota_exhausted_even_if_physical_rooms_exist(): void
    {
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(6)->toDateString();

        // Book 2 website rooms
        for ($i = 1; $i <= 2; $i++) {
            ReservationService::createReservation([
                'room_type_id' => $this->testType->id,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'adults' => 2,
                'customer_name' => "Tamu Web {$i}",
                'customer_phone' => "08123456789{$i}",
                'source' => 'website',
            ]);
        }

        // Attempt 3rd website booking - must throw exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('kuota kamar tipe Superior Suite untuk pemesanan online tidak tersedia atau sudah habis');

        ReservationService::createReservation([
            'room_type_id' => $this->testType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults' => 2,
            'customer_name' => 'Tamu Web 3',
            'customer_phone' => '081234567893',
            'source' => 'website',
        ]);
    }

    public function test_walk_in_can_book_remaining_physical_rooms_after_web_quota_exhausted(): void
    {
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(6)->toDateString();

        // Exhaust website quota (2 rooms)
        for ($i = 1; $i <= 2; $i++) {
            ReservationService::createReservation([
                'room_type_id' => $this->testType->id,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'adults' => 2,
                'customer_name' => "Tamu Web {$i}",
                'customer_phone' => "08123456789{$i}",
                'source' => 'website',
            ]);
        }

        // Front desk walk-in booking should succeed for the remaining 2 physical rooms
        $walkInRes = ReservationService::createReservation([
            'room_type_id' => $this->testType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults' => 2,
            'customer_name' => 'Tamu Walk-in Front Desk',
            'customer_phone' => '089876543210',
            'source' => 'walk_in',
        ]);

        $this->assertInstanceOf(Reservation::class, $walkInRes);
        $this->assertEquals(1, ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'all'));
    }

    public function test_room_type_with_zero_quota_is_never_available_online(): void
    {
        $this->testType->update(['website_quota' => 0]);

        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(6)->toDateString();

        // Web availability is 0
        $this->assertEquals(0, ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'website'));

        // But physical rooms are still 4
        $this->assertEquals(4, ReservationService::getAvailableRoomCount($this->testType, $checkIn, $checkOut, 'all'));
    }

    public function test_public_booking_engine_renders_badge_and_disables_button_when_sold_out_online(): void
    {
        $this->testType->update(['website_quota' => 0]);

        $checkIn = now()->addDays(2)->toDateString();
        $checkOut = now()->addDays(3)->toDateString();

        Livewire::test(PublicBookingEngine::class)
            ->set('check_in_date', $checkIn)
            ->set('check_out_date', $checkOut)
            ->assertSeeHtml('Penuh / Tidak Tersedia Online')
            ->call('selectRoomForBooking', $this->testType->id)
            ->assertHasErrors()
            ->assertSet('modalStep', 1); // Does not advance to step 2
    }

    public function test_room_management_updates_website_quota(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(RoomManagement::class)
            ->set('activeTab', 'types')
            ->call('openTypeModal', $this->testType->id)
            ->assertSet('website_quota', 2)
            ->set('website_quota', 3)
            ->call('saveType')
            ->assertHasNoErrors();

        $this->testType->refresh();
        $this->assertEquals(3, $this->testType->website_quota);
    }
}
