<?php

namespace App\Livewire;

use App\Models\AssetDepreciation;
use App\Models\FixedAsset;
use App\Models\Room;
use App\Services\FixedAssetService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class FixedAssetManager extends Component
{
    use WithPagination;

    public string $categoryFilter = 'all';
    public string $statusFilter = 'all';
    public string $search = '';

    // Register Modal
    public bool $showRegisterModal = false;
    public string $asset_code = '';
    public string $asset_name = '';
    public string $asset_category = 'furniture';
    public ?int $asset_room_id = null;
    public string $asset_purchase_date = '';
    public int $asset_purchase_cost = 0;
    public int $asset_useful_life = 5;
    public int $asset_salvage_value = 0;
    public string $asset_payment_source = 'bank';
    public string $asset_notes = '';

    // Depreciation Run Modal
    public bool $showDepreciationModal = false;
    public string $depreciation_period = '';

    // Asset Detail / History Modal
    public ?FixedAsset $selectedAsset = null;
    public bool $showHistoryModal = false;

    public function mount(): void
    {
        $this->asset_purchase_date = now()->toDateString();
        $this->depreciation_period = now()->startOfMonth()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openRegisterModal(): void
    {
        $this->resetErrorBag();
        $this->asset_code = 'AST-' . strtoupper(substr(uniqid(), -5));
        $this->asset_name = '';
        $this->asset_category = 'furniture';
        $this->asset_room_id = null;
        $this->asset_purchase_date = now()->toDateString();
        $this->asset_purchase_cost = 2500000;
        $this->asset_useful_life = 5;
        $this->asset_salvage_value = 0;
        $this->asset_payment_source = 'bank';
        $this->asset_notes = '';
        $this->showRegisterModal = true;
    }

    public function saveAsset(): void
    {
        $this->validate([
            'asset_code' => 'required|string|unique:fixed_assets,code',
            'asset_name' => 'required|string|min:3',
            'asset_category' => 'required|in:electronic,furniture,building,vehicle,other',
            'asset_purchase_date' => 'required|date',
            'asset_purchase_cost' => 'required|integer|min:1000',
            'asset_useful_life' => 'required|integer|min:1|max:50',
            'asset_salvage_value' => 'required|integer|min:0|lt:asset_purchase_cost',
        ]);

        try {
            FixedAssetService::registerAsset(
                data: [
                    'code' => $this->asset_code,
                    'name' => $this->asset_name,
                    'category' => $this->asset_category,
                    'room_id' => $this->asset_room_id,
                    'purchase_date' => $this->asset_purchase_date,
                    'purchase_cost' => $this->asset_purchase_cost,
                    'useful_life_years' => $this->asset_useful_life,
                    'salvage_value' => $this->asset_salvage_value,
                    'notes' => $this->asset_notes ?: null,
                ],
                paymentSource: $this->asset_payment_source,
                userId: auth()->id()
            );

            $this->showRegisterModal = false;
            session()->flash('message', "Aset tetap {$this->asset_name} berhasil didaftarkan dan dijurnal.");
        } catch (\Exception $e) {
            $this->addError('register_asset', $e->getMessage());
        }
    }

    public function openDepreciationModal(): void
    {
        $this->depreciation_period = now()->startOfMonth()->toDateString();
        $this->showDepreciationModal = true;
    }

    public function runDepreciation(): void
    {
        $this->validate([
            'depreciation_period' => 'required|date',
        ]);

        try {
            $result = FixedAssetService::runMonthlyDepreciation($this->depreciation_period, auth()->id());
            $this->showDepreciationModal = false;

            $totalFmt = number_format($result['total_depreciation'], 0, ',', '.');
            session()->flash('message', "Penyusutan periode {$result['period']} selesai diproses: {$result['processed_assets']} aset disusutkan senilai Rp {$totalFmt}.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function viewHistory(int $assetId): void
    {
        $this->selectedAsset = FixedAsset::with(['room', 'depreciations.journalEntry'])->findOrFail($assetId);
        $this->showHistoryModal = true;
    }

    public function render()
    {
        $query = FixedAsset::with('room');

        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            });
        }

        $assets = $query->latest('purchase_date')->paginate(15);
        $rooms = Room::orderBy('room_number')->get();

        $totalAssetCost = FixedAsset::where('status', '!=', 'disposed')->sum('purchase_cost');
        $totalAccumDepreciation = FixedAsset::where('status', '!=', 'disposed')->sum('accumulated_depreciation');
        $totalBookValue = max(0, $totalAssetCost - $totalAccumDepreciation);

        return view('livewire.fixed-asset-manager', [
            'assets' => $assets,
            'rooms' => $rooms,
            'totalAssetCost' => $totalAssetCost,
            'totalAccumDepreciation' => $totalAccumDepreciation,
            'totalBookValue' => $totalBookValue,
        ]);
    }
}
