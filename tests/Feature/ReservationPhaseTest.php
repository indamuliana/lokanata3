<?php

namespace Tests\Feature;

use App\Livewire\ReservationManagement;
use App\Models\AdditionalService;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\PricingService;
use App\Services\ReservationService;
use Carbon\Carbon;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReservationPhaseTest extends TestCase
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

    public function test_pricing_calculation_applies_weekend_rule_and_tax(): void
    {
        $stdType = RoomType::where('code', 'STD')->first(); // Base price 350000

        // Test weekday night (e.g. Wednesday)
        $wednesday = Carbon::parse('2026-10-21'); // Wednesday
        $wedRate = PricingService::getNightRate($stdType, $wednesday);
        $this->assertEquals(350000, $wedRate);

        // Test weekend night (Friday +15%)
        $friday = Carbon::parse('2026-10-23'); // Friday
        $friRate = PricingService::getNightRate($stdType, $friday);
        $this->assertEquals((int) round(350000 * 1.15), $friRate);

        // Test stay calculation for 1 night weekday with 10% tax
        $calc = PricingService::calculateStay($stdType, '2026-10-21', '2026-10-22');
        $this->assertEquals(350000, $calc['subtotal']);
        $this->assertEquals(35000, $calc['tax_amount']); // 10% tax
        $this->assertEquals(385000, $calc['total_amount']);
    }

    public function test_promo_discount_applies_correctly(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();

        // 1 night with SYARIAH10 (-10%)
        $calc = PricingService::calculateStay($stdType, '2026-10-21', '2026-10-22', 'SYARIAH10');
        $this->assertEquals(350000, $calc['subtotal']);
        $this->assertEquals(35000, $calc['discount_amount']); // 10% of 350000
        $this->assertEquals(315000, $calc['taxable_amount']);
        $this->assertEquals(31500, $calc['tax_amount']); // 10% of 315000
        $this->assertEquals(346500, $calc['total_amount']);
    }

    public function test_can_create_walk_in_reservation(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();

        $reservation = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-27', // 2 nights
            'source' => 'walk_in',
            'customer_name' => 'Ahmad Hidayat',
            'customer_phone' => '08123456789',
            'customer_identity_type' => 'KTP',
            'customer_identity_number' => '3205012345678901',
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'initial_paid_amount' => 100000, // DP 100k
        ]);

        $this->assertNotNull($reservation->id);
        $this->assertStringStartsWith('RES-', $reservation->reservation_number);
        $this->assertEquals('confirmed', $reservation->status);
        $this->assertEquals('partially_paid', $reservation->payment_status);
        $this->assertEquals('628123456789', $reservation->customer->phone);
        $this->assertEquals('3205012345678901', $reservation->customer->identity_number);
        $this->assertEquals(2, $reservation->adults);
        $this->assertEquals(1, $reservation->children);
    }

    public function test_can_create_ota_manual_reservation(): void
    {
        $dlxType = RoomType::where('code', 'DLX')->first();

        $reservation = ReservationService::createReservation([
            'room_type_id' => $dlxType->id,
            'check_in_date' => '2026-11-01',
            'check_out_date' => '2026-11-02',
            'source' => 'ota_manual',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '628987654321',
            'ota_name' => 'Traveloka',
            'ota_reference_number' => 'TRV-998811',
            'ota_gross_amount' => 600000,
            'ota_commission' => 90000,
            'ota_net_expected' => 510000,
            'ota_settlement_status' => 'pending',
        ]);

        $this->assertEquals('ota_manual', $reservation->source);
        $this->assertEquals('Traveloka', $reservation->ota_name);
        $this->assertEquals(510000, $reservation->ota_net_expected);
        $this->assertEquals(0, $reservation->tax_amount); // OTA manual has 0 internal PB1
    }

    public function test_custom_night_rate_and_tax_exemption_in_pricing_and_reservation(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();

        // 1 night with custom rate 400,000 and tax = false
        $calcExempt = PricingService::calculateStay(
            $stdType,
            '2026-10-25',
            '2026-10-26',
            null,
            0,
            0,
            400000,
            false // applyTax = false
        );

        $this->assertEquals(400000, $calcExempt['subtotal']);
        $this->assertEquals(0, $calcExempt['tax_amount']);
        $this->assertEquals(400000, $calcExempt['total_amount']);

        // Create reservation with custom night rate & tax exempt
        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-26',
            'source' => 'walk_in',
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '08555555555',
            'custom_rate_per_night' => 400000,
            'apply_tax' => false,
        ]);

        $this->assertEquals(400000, $res->subtotal);
        $this->assertEquals(0, $res->tax_amount);
        $this->assertEquals(400000, $res->total_amount);
    }

    public function test_create_reservation_with_dynamic_multi_addons(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $extraBedService = AdditionalService::firstOrCreate(
            ['name' => 'Extra Bed'],
            ['category' => 'amenities', 'default_price' => 150000, 'unit' => 'pcs', 'is_active' => true]
        );

        $reservation = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-26',
            'source' => 'walk_in',
            'customer_name' => 'Rahmat Fauzi',
            'customer_phone' => '08777777777',
            'custom_rate_per_night' => 350000,
            'apply_tax' => false,
            'addon_items' => [
                [
                    'additional_service_id' => $extraBedService->id,
                    'item_name' => 'Extra Bed Standar',
                    'quantity' => 1,
                    'unit_price' => 150000,
                ],
                [
                    'additional_service_id' => null,
                    'item_name' => 'Catering Spesial Malam',
                    'quantity' => 2,
                    'unit_price' => 75000,
                ],
            ],
        ]);

        $this->assertEquals(2, $reservation->serviceItems()->count());
        // 350k room + 150k extra bed + 150k catering = 650k
        $this->assertEquals(650000, $reservation->total_amount);

        $items = $reservation->serviceItems->keyBy('item_name');
        $this->assertEquals(150000, $items['Extra Bed Standar']->subtotal);
        $this->assertEquals(150000, $items['Catering Spesial Malam']->subtotal);
    }

    public function test_room_allocation_and_double_booking_prevention(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();
        $room101 = Room::where('room_number', '101')->first();

        $this->assertTrue($room101->isAvailable('2026-10-25', '2026-10-27'));

        // Reserve room 101
        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'room_id' => $room101->id,
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-27',
            'source' => 'walk_in',
            'customer_name' => 'Tamu 1',
            'customer_phone' => '08111111111',
        ]);

        $this->assertFalse($room101->isAvailable('2026-10-25', '2026-10-27'));
        $this->assertFalse($room101->isAvailable('2026-10-26', '2026-10-28')); // Overlapping
        $this->assertTrue($room101->isAvailable('2026-10-27', '2026-10-29')); // Non-overlapping checkout day
    }

    public function test_manual_release_of_expired_website_reservations(): void
    {
        $stdType = RoomType::where('code', 'STD')->first();

        // Create expired pending reservation
        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-26',
            'source' => 'website',
            'customer_name' => 'Tamu Web',
            'customer_phone' => '08222222222',
        ]);

        $this->assertEquals('pending', $res->status);

        // Manually artificially simulate 3 hours passed
        $res->update(['expires_at' => now()->subHours(3)]);

        $releasedCount = Reservation::releaseExpired();
        $this->assertEquals(1, $releasedCount);

        $res->refresh();
        $this->assertEquals('expired', $res->status);
    }

    public function test_ota_commission_and_gross_amount_calculation_in_livewire(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ReservationManagement::class)
            ->call('openCreateModal')
            ->set('source', 'ota_manual')
            ->set('ota_name', 'Booking.com')
            ->set('ota_gross_amount', 1000000)
            ->set('ota_commission', 150000)
            ->assertSet('ota_net_expected', 850000)
            ->assertSee('Net Diharapkan');
    }

    public function test_master_addon_management_and_soft_delete_in_livewire(): void
    {
        $user = User::factory()->create();

        $comp = Livewire::actingAs($user)
            ->test(ReservationManagement::class)
            ->call('openMasterAddonModal')
            ->set('master_addon_name', 'Penyewaan Sepeda Listrik')
            ->set('master_addon_category', 'others')
            ->set('master_addon_price', 75000)
            ->set('master_addon_unit', 'jam')
            ->call('saveMasterAddon');

        $addon = AdditionalService::where('name', 'Penyewaan Sepeda Listrik')->first();
        $this->assertNotNull($addon);
        $this->assertEquals(75000, $addon->default_price);
        $this->assertTrue($addon->is_active);

        // Toggle active
        $comp->call('toggleMasterAddonStatus', $addon->id);
        $addon->refresh();
        $this->assertFalse($addon->is_active);

        // Soft delete
        $comp->call('deleteMasterAddon', $addon->id);
        $this->assertTrue($addon->fresh()->trashed());

        // Restore
        $comp->call('restoreMasterAddon', $addon->id);
        $this->assertFalse($addon->fresh()->trashed());
    }

    public function test_edit_reservation_lifecycle_in_livewire(): void
    {
        $user = User::factory()->create();
        $stdType = RoomType::where('code', 'STD')->first();

        $res = ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'check_in_date' => '2026-10-25',
            'check_out_date' => '2026-10-26',
            'source' => 'walk_in',
            'customer_name' => 'Fulan bin Fulan',
            'customer_phone' => '08123456780',
        ]);

        // Open edit modal and update data
        Livewire::actingAs($user)
            ->test(ReservationManagement::class)
            ->call('openEditModal', $res->id)
            ->assertSet('edit_customer_name', 'Fulan bin Fulan')
            ->set('edit_customer_name', 'Ustadz Fulan Al-Garuti')
            ->set('edit_customer_identity_type', 'SIM')
            ->set('edit_customer_identity_number', 'SIM-99887766')
            ->set('edit_adults', 3)
            ->set('edit_custom_night_rate', 500000)
            ->set('edit_apply_tax', false)
            ->call('saveEditedReservation')
            ->assertHasNoErrors();

        $res->refresh();
        $this->assertEquals('Ustadz Fulan Al-Garuti', $res->customer->name);
        $this->assertEquals('SIM', $res->customer->identity_type);
        $this->assertEquals('SIM-99887766', $res->customer->identity_number);
        $this->assertEquals(3, $res->adults);
        $this->assertEquals(500000, $res->subtotal);
        $this->assertEquals(0, $res->tax_amount);
        $this->assertEquals(500000, $res->total_amount);
    }
}
