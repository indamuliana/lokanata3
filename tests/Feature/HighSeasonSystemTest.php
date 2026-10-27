<?php

namespace Tests\Feature;

use App\Livewire\AppSettings;
use App\Livewire\DiningReservationManagement;
use App\Livewire\MeetingRoomManagement;
use App\Livewire\RoomControlDashboard;
use App\Livewire\RoomManagement;
use App\Livewire\UnifiedReservationCalendar;
use App\Models\DiningPackage;
use App\Models\HighSeasonDate;
use App\Models\MeetingPackage;
use App\Models\MeetingRoom;
use App\Models\RoomType;
use App\Models\User;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\RoleSeeder;
use App\Services\FineDiningService;
use App\Services\MeetingRoomService;
use App\Services\PricingService;
use App\Services\SeasonService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class HighSeasonSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
        ]);

        $this->manager = User::where('email', 'manager@insitu.id')->first();
    }

    public function test_season_service_identifies_weekend_and_holidays(): void
    {
        SeasonService::clearCache();

        // Saturday (Weekend)
        $saturday = '2026-10-24'; // Saturday
        $satInfo = SeasonService::getSeasonInfo($saturday);
        $this->assertTrue($satInfo['is_high_season']);
        $this->assertEquals('weekend', $satInfo['season_type']);

        // Sunday (Weekend)
        $sunday = '2026-10-25'; // Sunday
        $this->assertTrue(SeasonService::isHighSeason($sunday));

        // Monday (Weekday Normal)
        $monday = '2026-10-26'; // Monday
        $monInfo = SeasonService::getSeasonInfo($monday);
        $this->assertFalse($monInfo['is_high_season']);
        $this->assertEquals('normal', $monInfo['season_type']);

        // Register Monday as a Holiday
        HighSeasonDate::create([
            'date' => '2026-10-26',
            'name' => 'Hari Libur Spesial',
            'is_active' => true,
        ]);
        SeasonService::clearCache();

        $holidayInfo = SeasonService::getSeasonInfo('2026-10-26');
        $this->assertTrue($holidayInfo['is_high_season']);
        $this->assertEquals('holiday', $holidayInfo['season_type']);
        $this->assertEquals('Hari Libur Spesial', $holidayInfo['holiday_name']);
    }

    public function test_room_split_night_pricing_with_high_season(): void
    {
        SeasonService::clearCache();

        $type = RoomType::create([
            'name' => 'Deluxe High Season Test',
            'code' => 'DLX-HS',
            'base_price' => 300000,
            'high_season_price' => 500000,
            'extra_person_fee' => 50000,
            'standard_capacity' => 2,
            'max_capacity' => 3,
            'is_active' => true,
        ]);

        // Stay from Friday to Sunday (2 nights: Friday night + Saturday night)
        // Friday is weekday normal (300k), Saturday is weekend high season (500k)
        $friday = '2026-10-23';
        $sunday = '2026-10-25';

        $stay = PricingService::calculateStay($type, $friday, $sunday);

        $this->assertEquals(2, $stay['nights']);
        $this->assertEquals(800000, $stay['subtotal']); // 300k + 500k
        $this->assertFalse($stay['nightly_rates'][0]['is_high_season']);
        $this->assertTrue($stay['nightly_rates'][1]['is_high_season']);
    }

    public function test_meeting_room_pricing_adapts_to_high_season(): void
    {
        SeasonService::clearCache();

        $room = MeetingRoom::create([
            'name' => 'Ballroom Garuda',
            'capacity' => 100,
            'hourly_rate' => 200000,
            'high_hourly_rate' => 350000,
            'halfday_rate' => 800000,
            'high_halfday_rate' => 1200000,
            'fullday_rate' => 1500000,
            'high_fullday_rate' => 2200000,
            'is_active' => true,
        ]);

        $package = MeetingPackage::create([
            'name' => 'Paket Coffee Break Garuda',
            'price_per_pax' => 50000,
            'high_price_per_pax' => 75000,
            'min_pax' => 10,
            'is_active' => true,
        ]);

        // Weekday (Normal: 2026-10-22 Thursday)
        $normalPricing = MeetingRoomService::calculatePricing(
            $room,
            'all_in',
            20,
            '09:00',
            '13:00', // 4 hours -> halfday
            null,
            0,
            0,
            0,
            '2026-10-22'
        );
        $this->assertEquals(800000, $normalPricing['subtotal']);
        $this->assertFalse($normalPricing['is_high_season']);

        // Saturday (High Season: 2026-10-24)
        $highPricing = MeetingRoomService::calculatePricing(
            $room,
            'all_in',
            20,
            '09:00',
            '13:00', // 4 hours -> high halfday
            null,
            0,
            0,
            0,
            '2026-10-24'
        );
        $this->assertEquals(1200000, $highPricing['subtotal']);
        $this->assertTrue($highPricing['is_high_season']);

        // Per Pax on High Season
        $highPaxPricing = MeetingRoomService::calculatePricing(
            $room,
            'per_pax',
            20,
            '09:00',
            '13:00',
            $package,
            0,
            0,
            0,
            '2026-10-24'
        );
        $this->assertEquals(20 * 75000, $highPaxPricing['subtotal']);
    }

    public function test_fine_dining_pricing_adapts_to_high_season(): void
    {
        SeasonService::clearCache();

        $package = DiningPackage::create([
            'name' => 'Paket Nasi Liwet Spesial',
            'price_per_pax' => 60000,
            'high_price_per_pax' => 85000,
            'min_pax' => 5,
            'is_active' => true,
        ]);

        // Weekday (2026-10-22 Thursday)
        $normal = FineDiningService::calculatePricing($package, 10, 0, 0, 0, false, '2026-10-22');
        $this->assertEquals(600000, $normal['subtotal']);
        $this->assertFalse($normal['is_high_season']);

        // Saturday (2026-10-24)
        $high = FineDiningService::calculatePricing($package, 10, 0, 0, 0, false, '2026-10-24');
        $this->assertEquals(850000, $high['subtotal']);
        $this->assertTrue($high['is_high_season']);
    }

    public function test_app_settings_holiday_crud_and_import(): void
    {
        SeasonService::clearCache();

        $comp = Livewire::actingAs($this->manager)
            ->test(AppSettings::class)
            ->set('activeTab', 'season')
            ->set('holiday_date', '2026-12-25')
            ->set('holiday_name', 'Hari Raya Natal')
            ->set('holiday_notes', 'Libur Nasional 2026')
            ->call('addHoliday')
            ->assertHasNoErrors();

        $holiday = HighSeasonDate::where('name', 'Hari Raya Natal')->first();
        $this->assertNotNull($holiday);
        $this->assertEquals('2026-12-25', $holiday->date->toDateString());
        $this->assertTrue($holiday->is_active);

        // Toggle status
        $comp->call('toggleHolidayStatus', $holiday->id);
        $this->assertFalse($holiday->fresh()->is_active);

        // Delete holiday
        $comp->call('deleteHoliday', $holiday->id);
        $this->assertDatabaseMissing('high_season_dates', ['id' => $holiday->id]);

        // Test Template Download
        $response = $comp->call('downloadTemplate');
        $this->assertNotNull($response);

        // Test CSV Import
        $csvContent = "tanggal,nama_libur,keterangan\n2026-05-01,Hari Buruh Internasional,Libur Nasional\n2026-06-01,Hari Lahir Pancasila,Libur Nasional\n";
        $file = UploadedFile::fake()->createWithContent('holidays.csv', $csvContent);

        $comp->set('holiday_import_file', $file)
            ->call('importHolidays')
            ->assertHasNoErrors();

        $this->assertNotNull(HighSeasonDate::where('name', 'Hari Buruh Internasional')->first());
        $this->assertNotNull(HighSeasonDate::where('name', 'Hari Lahir Pancasila')->first());
    }

    public function test_room_management_saves_high_season_rate(): void
    {
        Livewire::actingAs($this->manager)
            ->test(RoomManagement::class)
            ->set('showTypeModal', true)
            ->set('type_name', 'Executive Suite')
            ->set('type_code', 'EXC')
            ->set('existingPhotos', ['/storage/room-types/sample.jpg'])
            ->set('base_price', 600000)
            ->set('is_high_season_same', false)
            ->set('high_season_price', 850000)
            ->set('standard_capacity', 2)
            ->set('max_capacity', 4)
            ->call('saveType')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('room_types', [
            'code' => 'EXC',
            'base_price' => 600000,
            'high_season_price' => 850000,
        ]);
    }

    public function test_calendar_and_dashboard_render_with_high_season_badges(): void
    {
        Livewire::actingAs($this->manager)
            ->test(UnifiedReservationCalendar::class)
            ->assertSee('WEEKEND')
            ->assertStatus(200);

        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->assertStatus(200);
    }
}
