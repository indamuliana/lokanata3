<?php

namespace App\Livewire;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\StockOpname;
use App\Models\Vendor;
use App\Services\InventoryService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InventoryManager extends Component
{
    use WithPagination;

    public string $activeTab = 'items'; // 'items', 'opnames', 'movements'
    public string $categoryFilter = 'all';
    public string $search = '';

    // Modals
    public bool $showItemModal = false;
    public string $item_code = '';
    public string $item_name = '';
    public string $item_category = 'amenities';
    public string $item_unit = 'pcs';
    public int $item_minimum_stock = 10;

    public bool $showPurchaseModal = false;
    public ?int $purchase_item_id = null;
    public int $purchase_qty = 1;
    public int $purchase_unit_cost = 0;
    public ?int $purchase_vendor_id = null;
    public string $purchase_payment_source = 'cash';
    public string $purchase_notes = '';

    public bool $showUsageModal = false;
    public ?int $usage_item_id = null;
    public int $usage_qty = 1;
    public string $usage_notes = '';

    public bool $showWasteModal = false;
    public ?int $waste_item_id = null;
    public int $waste_qty = 1;
    public string $waste_reason = '';

    public bool $showOpnameModal = false;
    public string $opname_date = '';
    public string $opname_notes = '';
    public array $opname_counts = []; // [item_id => count]

    public function mount(): void
    {
        $this->opname_date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    // --- ITEM ACTIONS ---
    public function openNewItemModal(): void
    {
        $this->resetErrorBag();
        $this->item_code = 'INV-' . strtoupper(substr(uniqid(), -5));
        $this->item_name = '';
        $this->item_category = 'amenities';
        $this->item_unit = 'pcs';
        $this->item_minimum_stock = 10;
        $this->showItemModal = true;
    }

    public function saveItem(): void
    {
        $this->validate([
            'item_code' => 'required|string|unique:inventory_items,code',
            'item_name' => 'required|string|min:2',
            'item_category' => 'required|in:amenities,linen,fnb,maintenance',
            'item_unit' => 'required|string|max:10',
            'item_minimum_stock' => 'required|integer|min:0',
        ]);

        InventoryItem::create([
            'code' => strtoupper($this->item_code),
            'name' => $this->item_name,
            'category' => $this->item_category,
            'unit' => $this->item_unit,
            'current_stock' => 0,
            'minimum_stock' => $this->item_minimum_stock,
            'average_cost' => 0,
        ]);

        $this->showItemModal = false;
        session()->flash('message', "Barang {$this->item_name} berhasil ditambahkan.");
    }

    // --- PURCHASE ACTIONS ---
    public function openPurchaseModal(?int $itemId = null): void
    {
        $this->resetErrorBag();
        $this->purchase_item_id = $itemId ?? InventoryItem::first()?->id;
        $this->purchase_qty = 10;
        $this->purchase_unit_cost = 0;
        $this->purchase_vendor_id = Vendor::first()?->id;
        $this->purchase_payment_source = 'cash';
        $this->purchase_notes = '';
        $this->showPurchaseModal = true;
    }

    public function savePurchase(): void
    {
        $this->validate([
            'purchase_item_id' => 'required|exists:inventory_items,id',
            'purchase_qty' => 'required|integer|min:1',
            'purchase_unit_cost' => 'required|integer|min:0',
            'purchase_payment_source' => 'required|in:cash,bank',
        ]);

        try {
            InventoryService::recordPurchase(
                itemId: $this->purchase_item_id,
                quantity: $this->purchase_qty,
                unitCost: $this->purchase_unit_cost,
                vendorId: $this->purchase_vendor_id,
                notes: $this->purchase_notes ?: null,
                paymentSource: $this->purchase_payment_source,
                userId: auth()->id()
            );

            $this->showPurchaseModal = false;
            session()->flash('message', 'Penerimaan stok pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            $this->addError('purchase', $e->getMessage());
        }
    }

    // --- USAGE ACTIONS ---
    public function openUsageModal(?int $itemId = null): void
    {
        $this->resetErrorBag();
        $this->usage_item_id = $itemId ?? InventoryItem::first()?->id;
        $this->usage_qty = 1;
        $this->usage_notes = '';
        $this->showUsageModal = true;
    }

    public function saveUsage(): void
    {
        $this->validate([
            'usage_item_id' => 'required|exists:inventory_items,id',
            'usage_qty' => 'required|integer|min:1',
        ]);

        try {
            InventoryService::recordUsage(
                itemId: $this->usage_item_id,
                quantity: $this->usage_qty,
                refType: 'manual_usage',
                notes: $this->usage_notes ?: null,
                userId: auth()->id()
            );

            $this->showUsageModal = false;
            session()->flash('message', 'Pemakaian stok operasional berhasil dicatat.');
        } catch (\Exception $e) {
            $this->addError('usage', $e->getMessage());
        }
    }

    // --- WASTE ACTIONS ---
    public function openWasteModal(?int $itemId = null): void
    {
        $this->resetErrorBag();
        $this->waste_item_id = $itemId ?? InventoryItem::first()?->id;
        $this->waste_qty = 1;
        $this->waste_reason = 'Rusak / Kedaluwarsa';
        $this->showWasteModal = true;
    }

    public function saveWaste(): void
    {
        $this->validate([
            'waste_item_id' => 'required|exists:inventory_items,id',
            'waste_qty' => 'required|integer|min:1',
            'waste_reason' => 'required|string|min:3',
        ]);

        try {
            InventoryService::recordWaste(
                itemId: $this->waste_item_id,
                quantity: $this->waste_qty,
                reason: $this->waste_reason,
                userId: auth()->id()
            );

            $this->showWasteModal = false;
            session()->flash('message', 'Penghapusan stok rusak/kedaluwarsa berhasil dicatat.');
        } catch (\Exception $e) {
            $this->addError('waste', $e->getMessage());
        }
    }

    // --- STOCK OPNAME ACTIONS ---
    public function openNewOpnameModal(): void
    {
        $this->resetErrorBag();
        $this->opname_date = now()->toDateString();
        $this->opname_notes = '';
        $this->opname_counts = [];

        $items = InventoryItem::orderBy('name')->get();
        foreach ($items as $item) {
            $this->opname_counts[$item->id] = $item->current_stock;
        }

        $this->showOpnameModal = true;
    }

    public function saveOpname(): void
    {
        $this->validate([
            'opname_date' => 'required|date',
        ]);

        $itemsData = [];
        foreach ($this->opname_counts as $itemId => $count) {
            $itemsData[] = [
                'item_id' => (int) $itemId,
                'physical_stock' => (int) $count,
                'notes' => null,
            ];
        }

        try {
            InventoryService::createStockOpname(
                itemsData: $itemsData,
                date: $this->opname_date,
                notes: $this->opname_notes ?: null,
                userId: auth()->id()
            );

            $this->showOpnameModal = false;
            $this->activeTab = 'opnames';
            session()->flash('message', 'Draft Stock Opname berhasil dibuat dan menunggu persetujuan Manager.');
        } catch (\Exception $e) {
            $this->addError('opname', $e->getMessage());
        }
    }

    public function approveOpname(int $opnameId): void
    {
        try {
            InventoryService::approveStockOpname($opnameId, auth()->id());
            session()->flash('message', 'Stock Opname berhasil disetujui dan stok telah diperbarui.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function rejectOpname(int $opnameId): void
    {
        try {
            InventoryService::rejectStockOpname($opnameId, auth()->id(), 'Ditolak via sistem');
            session()->flash('message', 'Stock Opname telah ditolak.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $itemsQuery = InventoryItem::query();
        if ($this->categoryFilter !== 'all') {
            $itemsQuery->where('category', $this->categoryFilter);
        }
        if ($this->search) {
            $itemsQuery->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            });
        }
        $items = $itemsQuery->orderBy('name')->paginate(15);

        $opnames = StockOpname::with(['creator', 'approver', 'items.item'])
            ->latest()
            ->paginate(10);

        $movements = InventoryMovement::with(['item', 'vendor', 'creator'])
            ->latest()
            ->paginate(20);

        $allItems = InventoryItem::orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->count();
        $totalValuation = InventoryItem::all()->sum('total_value');

        return view('livewire.inventory-manager', [
            'items' => $items,
            'opnames' => $opnames,
            'movements' => $movements,
            'allItems' => $allItems,
            'vendors' => $vendors,
            'lowStockCount' => $lowStockCount,
            'totalValuation' => $totalValuation,
        ]);
    }
}
