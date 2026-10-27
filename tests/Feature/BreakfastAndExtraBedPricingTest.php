<?php

namespace Tests\Feature;

use App\Livewire\PublicBookingEngine;
use App\Models\AdditionalService;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\PricingService;
use App\Services\ReservationService;
use Carbon\Carbon;
use Database\Seeders\AdditionalServiceSeeder;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BreakfastAndExtraBedPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
            AdditionalServiceSeeder::class,
        ]);
    }

    public function test_room_type_supports_breakfast_and_without_breakfast_pricing(): void
    {
        $deluxe = RoomType::create([
            'name' => 'Deluxe Suite Test',
            'code' => 'DST',
            'color' => '#10b981',
            'standard_capacity' => 2,
            'max_capacity' => 3,
            'base_price' => 400000,
            'price_without_breakfast' => 360000,
            'high_season_price' => 450000,
            'high_season_price_without_breakfast' => 410000,
            'extra_person_fee' => 50000,
            'is_active' => true,
            'website_quota' => 5,
        ]);

        $this->assertEquals(400000, $deluxe->base_price);
        $this->assertEquals(360000, $deluxe->price_without_breakfast);
        $this->assertEquals(450000, $deluxe->high_season_price);
        $this->assertEquals(410000, $deluxe->high_season_price_without_breakfast);
    }

    public function test_pricing_service_calculates_with_and_without_breakfast(): void
    {
        $roomType = RoomType::create([
            'name' => 'Superior Test',
            'code' => 'SPT',
            'base_price' => 400000,
            'price_without_breakfast' => 360000,
            'high_season_price' => 400000,
            'high_season_price_without_breakfast' => 360000,
            'is_active' => true,
        ]);

        $checkIn = Carbon::parse('2026-11-03')->toDateString(); // Tuesday
        $checkOut = Carbon::parse('2026-11-04')->toDateString(); // Wednesday (1 night)

        // Default / Include Breakfast = true
        $stayWith = PricingService::calculateStay(
            roomType: $roomType,
            checkIn: $checkIn,
            checkOut: $checkOut,
            applyTax: false,
            includeBreakfast: true
        );
        $this->assertEquals(400000, $stayWith['subtotal']);
        $this->assertTrue($stayWith['include_breakfast']);

        // Without Breakfast = false
        $stayWithout = PricingService::calculateStay(
            roomType: $roomType,
            checkIn: $checkIn,
            checkOut: $checkOut,
            applyTax: false,
            includeBreakfast: false
        );
        $this->assertEquals(360000, $stayWithout['subtotal']);
        $this->assertFalse($stayWithout['include_breakfast']);
    }

    public function test_reservation_creation_persists_include_breakfast(): void
    {
        $roomType = RoomType::first();

        // 1. With breakfast
        $resWith = ReservationService::createReservation([
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->addDays(5)->toDateString(),
            'check_out_date' => now()->addDays(6)->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Sarapan',
            'customer_phone' => '08111111111',
            'include_breakfast' => true,
        ]);
        $this->assertTrue($resWith->include_breakfast);

        // 2. Without breakfast
        $resWithout = ReservationService::createReservation([
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(8)->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Room Only',
            'customer_phone' => '08222222222',
            'include_breakfast' => false,
        ]);
        $this->assertFalse($resWithout->include_breakfast);
    }

    public function test_dual_extrabed_services_exist_with_correct_prices(): void
    {
        $extraBedBreakfast = AdditionalService::where('name', 'Extra Bed (Dengan Sarapan)')->first();
        $extraBedRoomOnly = AdditionalService::where('name', 'Extra Bed (Tanpa Sarapan)')->first();

        $this->assertNotNull($extraBedBreakfast);
        $this->assertEquals(150000, $extraBedBreakfast->default_price);

        $this->assertNotNull($extraBedRoomOnly);
        $this->assertEquals(135000, $extraBedRoomOnly->default_price);
    }

    public function test_public_booking_engine_handles_breakfast_and_extrabed_selection(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $extraBedWith = AdditionalService::where('name', 'Extra Bed (Dengan Sarapan)')->first();

        $tomorrow = now()->addDay()->toDateString();
        $dayAfter = now()->addDays(2)->toDateString();

        Livewire::test(PublicBookingEngine::class)
            ->set('check_in_date', $tomorrow)
            ->set('check_out_date', $dayAfter)
            ->set('selectedRoomTypeId', $stdType->id)
            ->set('include_breakfast', false)
            ->set('selected_extrabed_service_id', $extraBedWith->id)
            ->set('extrabed_qty', 1)
            ->set('guest_name', 'Bpk. Ahmad Fauzi')
            ->set('guest_phone', '081299887766')
            ->call('submitBooking')
            ->assertHasNoErrors();

        $res = Reservation::whereHas('customer', function ($q) {
            $q->where('name', 'Bpk. Ahmad Fauzi');
        })->first();

        $this->assertNotNull($res);
        $this->assertFalse($res->include_breakfast);
        $this->assertDatabaseHas('reservation_service_items', [
            'reservation_id' => $res->id,
            'additional_service_id' => $extraBedWith->id,
            'item_name' => 'Extra Bed (Dengan Sarapan)',
            'unit_price' => 150000,
        ]);
    }
}
