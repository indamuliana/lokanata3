<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\HighSeasonDate;
use App\Models\HotelSetting;
use App\Services\SeasonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class AppSettings extends Component
{
    use WithFileUploads;

    public string $activeTab = 'identity'; // identity, bank, tax, branding, season

    // Identitas Hotel
    public string $hotel_name = '';
    public string $hotel_tagline = '';
    public string $hotel_address = '';
    public string $hotel_phone = '';
    public string $hotel_whatsapp = '';
    public string $hotel_email = '';

    // Multi-rekening Bank
    public array $bank_accounts = [];

    // Pajak & Biaya Layanan
    public int $tax_percentage = 10;
    public int $service_charge_percentage = 0;

    // Logo & Kop Surat (Path yang tersimpan)
    public ?string $hotel_logo_path = null;
    public ?string $letterhead_portrait_path = null;
    public ?string $letterhead_landscape_path = null;
    public ?string $letterhead_thermal_path = null;

    // Temporary upload properties
    public $logo_file = null;
    public $letterhead_portrait_file = null;
    public $letterhead_landscape_file = null;
    public $letterhead_thermal_file = null;

    // High Season / Kalender Libur
    public string $holiday_date = '';
    public string $holiday_end_date = '';
    public string $holiday_name = '';
    public string $holiday_notes = '';
    public string $season_year = '';
    public string $season_search = '';
    public $holiday_import_file = null;

    public function mount(): void
    {
        $this->authorizeAccess();

        // Load Hotel Identity
        $this->hotel_name = (string) HotelSetting::get('hotel_name', 'InSitu Hotel Syariah Garut');
        $this->hotel_tagline = (string) HotelSetting::get('hotel_tagline', 'Hotel Syariah Nyaman dan Berkah di Garut');
        $this->hotel_address = (string) HotelSetting::get('hotel_address', 'Jl. Situ Indah No. 1, Garut, Jawa Barat');
        $this->hotel_phone = (string) HotelSetting::get('hotel_phone', '0262-123456');
        $this->hotel_whatsapp = (string) HotelSetting::get('hotel_whatsapp', '628123456789');
        $this->hotel_email = (string) HotelSetting::get('hotel_email', 'info@insituhotel.id');

        // Load Bank Accounts
        $banks = HotelSetting::get('bank_accounts', []);
        $this->bank_accounts = is_array($banks) ? $banks : [];

        // Load Tax & Service Charge
        $this->tax_percentage = (int) HotelSetting::get('tax_percentage', 10);
        $this->service_charge_percentage = (int) HotelSetting::get('service_charge_percentage', 0);

        // Load Branding Images
        $this->hotel_logo_path = HotelSetting::get('hotel_logo_path') ?: null;
        $this->letterhead_portrait_path = HotelSetting::get('letterhead_portrait_path') ?: null;
        $this->letterhead_landscape_path = HotelSetting::get('letterhead_landscape_path') ?: null;
        $this->letterhead_thermal_path = HotelSetting::get('letterhead_thermal_path') ?: null;

        $this->season_year = (string) date('Y');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if ($user && method_exists($user, 'hasRole')) {
            if (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin')) {
                abort(403, 'Akses terbatas. Hanya Manager yang dapat mengubah Pengaturan Aplikasi.');
            }
        }
    }

    public function saveIdentity(): void
    {
        $this->authorizeAccess();

        $this->validate([
            'hotel_name' => 'required|string|max:100',
            'hotel_tagline' => 'nullable|string|max:150',
            'hotel_address' => 'required|string|max:255',
            'hotel_phone' => 'required|string|max:30',
            'hotel_whatsapp' => 'required|string|max:30',
            'hotel_email' => 'required|email|max:100',
        ]);

        HotelSetting::set('hotel_name', $this->hotel_name, 'string', 'general');
        HotelSetting::set('hotel_tagline', $this->hotel_tagline, 'string', 'general');
        HotelSetting::set('hotel_address', $this->hotel_address, 'string', 'general');
        HotelSetting::set('hotel_phone', $this->hotel_phone, 'string', 'general');
        HotelSetting::set('hotel_whatsapp', $this->hotel_whatsapp, 'string', 'general');
        HotelSetting::set('hotel_email', $this->hotel_email, 'string', 'general');

        session()->flash('message', 'Identitas dan kontak resmi hotel berhasil disimpan.');
    }

    public function addBankAccount(): void
    {
        $this->bank_accounts[] = [
            'bank_name' => '',
            'account_number' => '',
            'account_holder' => $this->hotel_name,
            'is_active' => true,
        ];
    }

    public function removeBankAccount(int $index): void
    {
        if (isset($this->bank_accounts[$index])) {
            unset($this->bank_accounts[$index]);
            $this->bank_accounts = array_values($this->bank_accounts);
        }
    }

    public function toggleBankAccount(int $index): void
    {
        if (isset($this->bank_accounts[$index])) {
            $this->bank_accounts[$index]['is_active'] = !($this->bank_accounts[$index]['is_active'] ?? true);
        }
    }

    public function saveBankAccounts(): void
    {
        $this->authorizeAccess();

        $this->validate([
            'bank_accounts' => 'array',
            'bank_accounts.*.bank_name' => 'required|string|max:50',
            'bank_accounts.*.account_number' => 'required|string|max:50',
            'bank_accounts.*.account_holder' => 'required|string|max:100',
        ]);

        HotelSetting::set('bank_accounts', $this->bank_accounts, 'json', 'finance');

        session()->flash('message', 'Daftar rekening transfer resmi hotel berhasil diperbarui.');
    }

    public function saveTaxSettings(): void
    {
        $this->authorizeAccess();

        $this->validate([
            'tax_percentage' => 'required|integer|min:0|max:100',
            'service_charge_percentage' => 'required|integer|min:0|max:100',
        ]);

        HotelSetting::set('tax_percentage', $this->tax_percentage, 'integer', 'tax');
        HotelSetting::set('service_charge_percentage', $this->service_charge_percentage, 'integer', 'tax');

        session()->flash('message', 'Pengaturan persentase pajak dan service charge berhasil disimpan.');
    }

    public function uploadLogo(): void
    {
        $this->authorizeAccess();

        $this->validate([
            'logo_file' => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        if ($this->hotel_logo_path && Storage::disk('public')->exists($this->hotel_logo_path)) {
            Storage::disk('public')->delete($this->hotel_logo_path);
        }

        $path = $this->logo_file->store('branding', 'public');
        HotelSetting::set('hotel_logo_path', $path, 'string', 'branding');
        $this->hotel_logo_path = $path;
        $this->logo_file = null;

        session()->flash('message', 'Logo resmi hotel berhasil diperbarui.');
    }

    public function removeLogo(): void
    {
        $this->authorizeAccess();

        if ($this->hotel_logo_path && Storage::disk('public')->exists($this->hotel_logo_path)) {
            Storage::disk('public')->delete($this->hotel_logo_path);
        }

        HotelSetting::set('hotel_logo_path', '', 'string', 'branding');
        $this->hotel_logo_path = null;

        session()->flash('message', 'Logo berhasil dihapus.');
    }

    public function uploadLetterheadPortrait(): void
    {
        $this->authorizeAccess();

        $this->validate([
            'letterhead_portrait_file' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($this->letterhead_portrait_path && Storage::disk('public')->exists($this->letterhead_portrait_path)) {
            Storage::disk('public')->delete($this->letterhead_portrait_path);
        }

        $path = $this->letterhead_portrait_file->store('branding', 'public');
        HotelSetting::set('letterhead_portrait_path', $path, 'string', 'branding');
        $this->letterhead_portrait_path = $path;
        $this->letterhead_portrait_file = null;

        session()->flash('message', 'Kop surat Portrait (A4) berhasil diperbarui.');
    }

    public function removeLetterheadPortrait(): void
    {
        $this->authorizeAccess();

        if ($this->letterhead_portrait_path && Storage::disk('public')->exists($this->letterhead_portrait_path)) {
            Storage::disk('public')->delete($this->letterhead_portrait_path);
        }

        HotelSetting::set('letterhead_portrait_path', '', 'string', 'branding');
        $this->letterhead_portrait_path = null;

        session()->flash('message', 'Kop surat Portrait berhasil dihapus.');
    }

    public function uploadLetterheadLandscape(): void
    {
        $this->authorizeAccess();

        $this->validate([
            'letterhead_landscape_file' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($this->letterhead_landscape_path && Storage::disk('public')->exists($this->letterhead_landscape_path)) {
            Storage::disk('public')->delete($this->letterhead_landscape_path);
        }

        $path = $this->letterhead_landscape_file->store('branding', 'public');
        HotelSetting::set('letterhead_landscape_path', $path, 'string', 'branding');
        $this->letterhead_landscape_path = $path;
        $this->letterhead_landscape_file = null;

        session()->flash('message', 'Kop surat Landscape (A4) berhasil diperbarui.');
    }

    public function removeLetterheadLandscape(): void
    {
        $this->authorizeAccess();

        if ($this->letterhead_landscape_path && Storage::disk('public')->exists($this->letterhead_landscape_path)) {
            Storage::disk('public')->delete($this->letterhead_landscape_path);
        }

        HotelSetting::set('letterhead_landscape_path', '', 'string', 'branding');
        $this->letterhead_landscape_path = null;

        session()->flash('message', 'Kop surat Landscape berhasil dihapus.');
    }

    public function uploadLetterheadThermal(): void
    {
        $this->authorizeAccess();

        $this->validate([
            'letterhead_thermal_file' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($this->letterhead_thermal_path && Storage::disk('public')->exists($this->letterhead_thermal_path)) {
            Storage::disk('public')->delete($this->letterhead_thermal_path);
        }

        $path = $this->letterhead_thermal_file->store('branding', 'public');
        HotelSetting::set('letterhead_thermal_path', $path, 'string', 'branding');
        $this->letterhead_thermal_path = $path;
        $this->letterhead_thermal_file = null;

        session()->flash('message', 'Kop surat Struk Thermal 80mm berhasil diperbarui.');
    }

    public function removeLetterheadThermal(): void
    {
        $this->authorizeAccess();

        if ($this->letterhead_thermal_path && Storage::disk('public')->exists($this->letterhead_thermal_path)) {
            Storage::disk('public')->delete($this->letterhead_thermal_path);
        }

        HotelSetting::set('letterhead_thermal_path', '', 'string', 'branding');
        $this->letterhead_thermal_path = null;

        session()->flash('message', 'Kop surat Thermal 80mm berhasil dihapus.');
    }

    public function addHoliday(): void
    {
        $this->authorizeAccess();
        $this->validate([
            'holiday_date' => 'required|date',
            'holiday_end_date' => 'nullable|date|after_or_equal:holiday_date',
            'holiday_name' => 'required|string|max:150',
            'holiday_notes' => 'nullable|string|max:255',
        ]);

        $startDate = Carbon::parse($this->holiday_date);
        $endDate = $this->holiday_end_date ? Carbon::parse($this->holiday_end_date) : $startDate;

        $count = 0;
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            HighSeasonDate::updateOrCreate(
                ['date' => $current->toDateString()],
                [
                    'name' => $this->holiday_name,
                    'notes' => $this->holiday_notes,
                    'is_active' => true,
                ]
            );
            $count++;
            $current->addDay();
        }

        SeasonService::clearCache();

        AuditLog::record(
            'high_season_date_create',
            null,
            null,
            null,
            "Menambahkan {$count} tanggal libur/high season: {$this->holiday_name}"
        );

        $this->reset(['holiday_date', 'holiday_end_date', 'holiday_name', 'holiday_notes']);
        session()->flash('message', "Berhasil menambahkan {$count} tanggal High Season.");
    }

    public function toggleHolidayStatus(int $id): void
    {
        $this->authorizeAccess();
        $h = HighSeasonDate::findOrFail($id);
        $h->is_active = !$h->is_active;
        $h->save();

        SeasonService::clearCache();
        session()->flash('message', "Status tanggal {$h->date->format('d/m/Y')} berhasil diperbarui.");
    }

    public function deleteHoliday(int $id): void
    {
        $this->authorizeAccess();
        $h = HighSeasonDate::findOrFail($id);
        $dateStr = $h->date->format('d/m/Y');
        $name = $h->name;
        $h->delete();

        SeasonService::clearCache();

        AuditLog::record(
            'high_season_date_delete',
            null,
            null,
            null,
            "Menghapus tanggal libur: {$dateStr} ({$name})"
        );

        session()->flash('message', "Tanggal libur {$dateStr} berhasil dihapus.");
    }

    public function downloadTemplate(): StreamedResponse
    {
        $this->authorizeAccess();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_high_season.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['tanggal', 'nama_libur', 'keterangan']);
            fputcsv($handle, ['2026-01-01', 'Tahun Baru Masehi', 'Libur Nasional']);
            fputcsv($handle, ['2026-03-20', 'Hari Raya Idul Fitri 1447 H', 'Libur Nasional']);
            fputcsv($handle, ['2026-03-21', 'Hari Raya Idul Fitri 1447 H', 'Libur Nasional']);
            fputcsv($handle, ['2026-08-17', 'Hari Kemerdekaan RI', 'Libur Nasional']);
            fputcsv($handle, ['2026-12-25', 'Hari Raya Natal', 'Libur Nasional']);
            fclose($handle);
        }, 200, $headers);
    }

    public function importHolidays(): void
    {
        $this->authorizeAccess();
        $this->validate([
            'holiday_import_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $this->holiday_import_file->getRealPath();
        $file = fopen($path, 'r');
        if (!$file) {
            session()->flash('error', 'Gagal membaca file yang diunggah.');
            return;
        }

        $inserted = 0;
        $rowNum = 0;

        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            $rowNum++;
            if ($rowNum === 1) {
                // If separator is semicolon
                if (count($row) === 1 && str_contains($row[0], ';')) {
                    rewind($file);
                    $this->importWithDelimiter($file, ';');
                    return;
                }
                continue; // Skip header
            }

            if (empty($row[0]) || empty($row[1])) {
                continue;
            }

            $dateRaw = trim($row[0]);
            $name = trim($row[1]);
            $notes = isset($row[2]) ? trim($row[2]) : null;

            try {
                $parsedDate = Carbon::parse($dateRaw)->toDateString();
                HighSeasonDate::updateOrCreate(
                    ['date' => $parsedDate],
                    [
                        'name' => $name,
                        'notes' => $notes,
                        'is_active' => true,
                    ]
                );
                $inserted++;
            } catch (\Exception $e) {
                continue;
            }
        }
        fclose($file);

        SeasonService::clearCache();
        $this->holiday_import_file = null;

        AuditLog::record(
            'high_season_import',
            null,
            null,
            null,
            "Mengimpor {$inserted} tanggal libur dari file CSV"
        );

        session()->flash('message', "Sukses mengimpor {$inserted} tanggal High Season!");
    }

    private function importWithDelimiter($file, string $delimiter): void
    {
        $inserted = 0;
        $rowNum = 0;
        while (($row = fgetcsv($file, 1000, $delimiter)) !== false) {
            $rowNum++;
            if ($rowNum === 1) continue;
            if (empty($row[0]) || empty($row[1])) continue;

            $dateRaw = trim($row[0]);
            $name = trim($row[1]);
            $notes = isset($row[2]) ? trim($row[2]) : null;

            try {
                $parsedDate = Carbon::parse($dateRaw)->toDateString();
                HighSeasonDate::updateOrCreate(
                    ['date' => $parsedDate],
                    [
                        'name' => $name,
                        'notes' => $notes,
                        'is_active' => true,
                    ]
                );
                $inserted++;
            } catch (\Exception $e) {
                continue;
            }
        }
        fclose($file);

        SeasonService::clearCache();
        $this->holiday_import_file = null;

        AuditLog::record(
            'high_season_import',
            null,
            null,
            null,
            "Mengimpor {$inserted} tanggal libur dari file CSV"
        );

        session()->flash('message', "Sukses mengimpor {$inserted} tanggal High Season!");
    }

    public function render()
    {
        $holidayQuery = HighSeasonDate::query();
        if ($this->season_year && $this->season_year !== 'all') {
            $holidayQuery->whereYear('date', (int) $this->season_year);
        }
        if ($this->season_search) {
            $term = '%' . $this->season_search . '%';
            $holidayQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('notes', 'like', $term);
            });
        }
        $holidays = $holidayQuery->orderBy('date')->get();

        return view('livewire.app-settings', [
            'holidays' => $holidays,
        ]);
    }
}
