<?php

namespace Tests\Feature;

use App\Livewire\AppSettings;
use App\Models\HotelSetting;
use App\Models\User;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
        ]);
        Storage::fake('public');
    }

    public function test_manager_can_access_app_settings(): void
    {
        $manager = User::where('email', 'manager@insitu.id')->first();

        $response = $this->actingAs($manager)->get('/settings');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Aplikasi');
        $response->assertSee('Identitas', false);
    }

    public function test_receptionist_is_forbidden_from_app_settings(): void
    {
        $receptionist = User::where('email', 'resepsionis@insitu.id')->first();

        $response = $this->actingAs($receptionist)->get('/settings');
        $response->assertStatus(403);
    }

    public function test_manager_can_update_hotel_identity(): void
    {
        $manager = User::where('email', 'manager@insitu.id')->first();

        Livewire::actingAs($manager)
            ->test(AppSettings::class)
            ->set('hotel_name', 'InSitu Luxury Syariah Garut')
            ->set('hotel_tagline', 'Pengalaman Menginap Istimewa')
            ->set('hotel_phone', '0262-998877')
            ->set('hotel_whatsapp', '628999888777')
            ->set('hotel_email', 'kontak@insituluxury.id')
            ->call('saveIdentity')
            ->assertHasNoErrors()
            ->assertSee('Identitas dan kontak resmi hotel berhasil disimpan.');

        $this->assertEquals('InSitu Luxury Syariah Garut', HotelSetting::get('hotel_name'));
        $this->assertEquals('628999888777', HotelSetting::get('hotel_whatsapp'));
        $this->assertEquals('kontak@insituluxury.id', HotelSetting::get('hotel_email'));
    }

    public function test_manager_can_manage_multi_bank_accounts(): void
    {
        $manager = User::where('email', 'manager@insitu.id')->first();

        $comp = Livewire::actingAs($manager)
            ->test(AppSettings::class)
            ->call('addBankAccount');

        $accounts = $comp->get('bank_accounts');
        $lastIdx = count($accounts) - 1;

        $comp->set("bank_accounts.{$lastIdx}.bank_name", 'Bank Muamalat')
            ->set("bank_accounts.{$lastIdx}.account_number", '1020304050')
            ->set("bank_accounts.{$lastIdx}.account_holder", 'PT Insitu Berkah Sejahtera')
            ->call('saveBankAccounts')
            ->assertHasNoErrors()
            ->assertSee('Daftar rekening transfer resmi hotel berhasil diperbarui.');

        $savedBanks = HotelSetting::get('bank_accounts', []);
        $muamalat = collect($savedBanks)->firstWhere('bank_name', 'Bank Muamalat');
        $this->assertNotNull($muamalat);
        $this->assertEquals('1020304050', $muamalat['account_number']);

        // Toggle and remove bank account
        $comp->call('toggleBankAccount', $lastIdx);
        $this->assertFalse($comp->get("bank_accounts.{$lastIdx}.is_active"));

        $comp->call('removeBankAccount', $lastIdx);
        $comp->call('saveBankAccounts');

        $savedBanksAfter = HotelSetting::get('bank_accounts', []);
        $this->assertNull(collect($savedBanksAfter)->firstWhere('bank_name', 'Bank Muamalat'));
    }

    public function test_manager_can_update_tax_percentage(): void
    {
        $manager = User::where('email', 'manager@insitu.id')->first();

        Livewire::actingAs($manager)
            ->test(AppSettings::class)
            ->set('tax_percentage', 12)
            ->set('service_charge_percentage', 5)
            ->call('saveTaxSettings')
            ->assertHasNoErrors()
            ->assertSee('Pengaturan persentase pajak dan service charge berhasil disimpan.');

        $this->assertEquals(12, (int) HotelSetting::get('tax_percentage'));
        $this->assertEquals(5, (int) HotelSetting::get('service_charge_percentage'));
    }

    public function test_manager_can_upload_and_delete_logo_and_kop_surat(): void
    {
        $manager = User::where('email', 'manager@insitu.id')->first();

        $logoFile = UploadedFile::fake()->image('logo.png', 400, 200);
        $portraitFile = UploadedFile::fake()->image('kop_portrait.png', 1200, 300);
        $thermalFile = UploadedFile::fake()->image('kop_thermal.png', 600, 150);

        $comp = Livewire::actingAs($manager)
            ->test(AppSettings::class)
            ->set('logo_file', $logoFile)
            ->call('uploadLogo')
            ->assertHasNoErrors();

        $logoPath = HotelSetting::get('hotel_logo_path');
        $this->assertNotEmpty($logoPath);
        Storage::disk('public')->assertExists($logoPath);

        // Upload portrait letterhead
        $comp->set('letterhead_portrait_file', $portraitFile)
            ->call('uploadLetterheadPortrait')
            ->assertHasNoErrors();

        $portraitPath = HotelSetting::get('letterhead_portrait_path');
        $this->assertNotEmpty($portraitPath);
        Storage::disk('public')->assertExists($portraitPath);

        // Upload thermal 80mm letterhead
        $comp->set('letterhead_thermal_file', $thermalFile)
            ->call('uploadLetterheadThermal')
            ->assertHasNoErrors();

        $thermalPath = HotelSetting::get('letterhead_thermal_path');
        $this->assertNotEmpty($thermalPath);
        Storage::disk('public')->assertExists($thermalPath);

        // Remove files
        $comp->call('removeLogo');
        $this->assertNull(HotelSetting::get('hotel_logo_path') ?: null);
        Storage::disk('public')->assertMissing($logoPath);

        $comp->call('removeLetterheadPortrait');
        $this->assertNull(HotelSetting::get('letterhead_portrait_path') ?: null);
        Storage::disk('public')->assertMissing($portraitPath);

        $comp->call('removeLetterheadThermal');
        $this->assertNull(HotelSetting::get('letterhead_thermal_path') ?: null);
        Storage::disk('public')->assertMissing($thermalPath);
    }
}
