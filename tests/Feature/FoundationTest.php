<?php

namespace Tests\Feature;

use App\Helpers\Rupiah;
use App\Models\AuditLog;
use App\Models\HotelSetting;
use App\Models\NumberingSequence;
use App\Models\User;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
        ]);
    }

    public function test_rupiah_formatting_and_parsing(): void
    {
        $this->assertEquals('Rp 1.250.000', rupiah(1250000));
        $this->assertEquals('1.250.000', rupiah(1250000, false));
        $this->assertEquals(1250000, parse_rupiah('Rp 1.250.000'));
        $this->assertEquals(500000, parse_rupiah('500.000'));
        $this->assertEquals(75000, parse_rupiah('75000'));
        $this->assertEquals(0, parse_rupiah(''));
    }

    public function test_numbering_sequence_atomic_generation(): void
    {
        $num1 = NumberingSequence::next('TEST_DOC');
        $num2 = NumberingSequence::next('TEST_DOC');

        $today = now()->format('Ymd');
        $this->assertEquals("TEST_DOC-{$today}-0001", $num1);
        $this->assertEquals("TEST_DOC-{$today}-0002", $num2);
    }

    public function test_hotel_settings_retrieval_and_casting(): void
    {
        $this->assertEquals('InSitu Hotel Syariah Garut', HotelSetting::get('hotel_name'));
        $this->assertEquals(10, HotelSetting::get('tax_percentage'));
        $this->assertTrue(HotelSetting::get('website_booking_enabled'));
        $this->assertEquals(30, HotelSetting::get('dashboard_polling_seconds'));
    }

    public function test_roles_and_seeded_users_exist(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'Manager']);
        $this->assertDatabaseHas('roles', ['name' => 'Resepsionis']);
        $this->assertDatabaseHas('roles', ['name' => 'Housekeeping']);

        $manager = User::where('email', 'manager@insitu.id')->first();
        $this->assertNotNull($manager);
        $this->assertTrue($manager->hasRole('Manager'));

        $receptionist = User::where('email', 'resepsionis@insitu.id')->first();
        $this->assertNotNull($receptionist);
        $this->assertTrue($receptionist->hasRole('Resepsionis'));

        $housekeeping = User::where('email', 'housekeeping@insitu.id')->first();
        $this->assertNotNull($housekeeping);
        $this->assertTrue($housekeeping->hasRole('Housekeeping'));
    }

    public function test_audit_log_recording(): void
    {
        $log = AuditLog::record('test_action', null, ['old' => 1], ['new' => 2], 'test reason');
        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'test_action',
            'reason' => 'test reason',
        ]);
    }

    public function test_uploads_disk_configured_without_symlinks(): void
    {
        $disk = config('filesystems.disks.uploads');
        $this->assertNotNull($disk);
        $this->assertEquals(public_path('uploads'), $disk['root']);
    }
}
