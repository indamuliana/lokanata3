<?php

namespace Tests\Feature;

use App\Livewire\PublicBookingEngine;
use App\Models\HotelSetting;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickBookingBarTest extends TestCase
{
    use RefreshDatabase;

    protected RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        HotelSetting::set('website_booking_enabled', '1');
        HotelSetting::set('tax_percentage', '10');

        $this->roomType = RoomType::create([
            'code' => 'BAL',
            'name' => 'Balcony Room Guntur',
            'base_price' => 350000,
            'high_season_price' => 450000,
            'standard_capacity' => 2,
            'max_capacity' => 3,
            'is_active' => true,
        ]);

        Room::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => '201',
            'floor' => 2,
            'cleanliness_status' => 'clean',
            'operational_status' => 'active',
        ]);

        Room::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => '202',
            'floor' => 2,
            'cleanliness_status' => 'clean',
            'operational_status' => 'active',
        ]);
    }

    public function test_quick_booking_bar_renders_on_landing_page(): void
    {
        Livewire::test(PublicBookingEngine::class)
            ->assertStatus(200)
            ->assertSee('Tanggal Menginap')
            ->assertSee('Kamar & Tamu')
            ->assertSee('Kode Voucher')
            ->assertSee('Cari Kamar')
            ->assertSee('Tersedia 2 Kamar')
            ->assertSee('Balcony Room Guntur');
    }

    public function test_counter_actions_increment_and_decrement(): void
    {
        Livewire::test(PublicBookingEngine::class)
            ->assertSet('rooms_count', 1)
            ->assertSet('adults', 2)
            ->assertSet('children', 0)
            ->call('incrementRooms')
            ->assertSet('rooms_count', 2)
            ->call('decrementRooms')
            ->assertSet('rooms_count', 1)
            ->call('incrementAdults')
            ->assertSet('adults', 3)
            ->call('decrementAdults')
            ->assertSet('adults', 2)
            ->call('incrementChildren')
            ->assertSet('children', 1)
            ->call('decrementChildren')
            ->assertSet('children', 0);
    }

    public function test_voucher_code_can_be_applied_and_cleared(): void
    {
        Promotion::create([
            'code' => 'DISC10',
            'name' => 'Diskon 10 Persen',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        Livewire::test(PublicBookingEngine::class)
            ->set('promo_code', 'DISC10')
            ->assertSet('promo_code', 'DISC10')
            ->call('clearPromoCode')
            ->assertSet('promo_code', '');
    }

    public function test_open_quick_booking_modal_and_submit_reservation(): void
    {
        $checkIn = Carbon::now()->addDays(2)->toDateString();
        $checkOut = Carbon::now()->addDays(4)->toDateString();

        $comp = Livewire::test(PublicBookingEngine::class)
            ->set('check_in_date', $checkIn)
            ->set('check_out_date', $checkOut)
            ->call('openQuickBookingModal', $this->roomType->id)
            ->assertSet('showQuickBookingModal', true)
            ->assertSet('selectedRoomTypeId', $this->roomType->id)
            ->assertSee('Konfirmasi Pemesanan Kamar')
            ->assertSee('Balcony Room Guntur')
            ->set('guest_name', 'Budi Santoso')
            ->set('guest_phone', '081234567890')
            ->set('guest_email', 'budi@example.com')
            ->call('submitBooking')
            ->assertHasNoErrors()
            ->assertSee('Alhamdulillah, Pemesanan Berhasil!')
            ->assertSee('Masa Tahan: 2 Jam');

        $reservation = Reservation::latest()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals('6281234567890', $reservation->customer->phone);
        $this->assertEquals('Budi Santoso', $reservation->customer->name);
        $this->assertEquals('pending', $reservation->status);
        $this->assertEquals($this->roomType->id, $reservation->room_type_id);

        // Reset booking closes modal
        $comp->call('resetBooking')
            ->assertSet('showQuickBookingModal', false)
            ->assertSet('confirmedReservation', null);
    }
}
