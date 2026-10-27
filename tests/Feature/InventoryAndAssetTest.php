<?php

namespace Tests\Feature;

use App\Livewire\FixedAssetManager;
use App\Livewire\InventoryManager;
use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\JournalEntry;
use App\Models\StockOpname;
use App\Models\User;
use App\Models\Vendor;
use App\Services\FixedAssetService;
use App\Services\InventoryService;
use Database\Seeders\ChartOfAccountSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryAndAssetTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $receptionist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(ChartOfAccountSeeder::class);

        $this->manager = User::factory()->create(['name' => 'Pak Manager']);
        $this->manager->assignRole('Manager');

        $this->receptionist = User::factory()->create(['name' => 'Mba Resepsionis']);
        $this->receptionist->assignRole('Resepsionis');
    }

    public function test_weighted_average_cost_calculation_on_purchase(): void
    {
        $vendor = Vendor::create(['name' => 'CV Garut Sejahtera']);
        $item = InventoryItem::create([
            'code' => 'TEST-001',
            'name' => 'Sabun Mandi Uji',
            'category' => 'amenities',
            'unit' => 'pcs',
            'current_stock' => 0,
            'minimum_stock' => 10,
            'average_cost' => 0,
        ]);

        // Purchase 1: 100 pcs @ Rp 2.000
        InventoryService::recordPurchase(
            itemId: $item->id,
            quantity: 100,
            unitCost: 2000,
            vendorId: $vendor->id,
            notes: 'Batch 1',
            paymentSource: 'cash',
            userId: $this->manager->id
        );

        $item->refresh();
        $this->assertEquals(100, $item->current_stock);
        $this->assertEquals(2000, $item->average_cost);

        // Purchase 2: 50 pcs @ Rp 3.500
        // New avg: ((100 * 2000) + (50 * 3500)) / (100 + 50) = (200000 + 175000) / 150 = 375000 / 150 = 2500
        InventoryService::recordPurchase(
            itemId: $item->id,
            quantity: 50,
            unitCost: 3500,
            vendorId: $vendor->id,
            notes: 'Batch 2',
            paymentSource: 'bank',
            userId: $this->manager->id
        );

        $item->refresh();
        $this->assertEquals(150, $item->current_stock);
        $this->assertEquals(2500, $item->average_cost);
        $this->assertEquals(375000, $item->total_value);

        // Verify Journal Entries
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'inventory_movement',
        ]);
    }

    public function test_inventory_usage_and_prevent_overdraw(): void
    {
        $item = InventoryItem::create([
            'code' => 'TEST-002',
            'name' => 'Sikat Gigi Uji',
            'category' => 'amenities',
            'unit' => 'pcs',
            'current_stock' => 50,
            'minimum_stock' => 10,
            'average_cost' => 3000,
        ]);

        // Use 20 pcs
        $mov = InventoryService::recordUsage(
            itemId: $item->id,
            quantity: 20,
            notes: 'Kamar 101-105',
            userId: $this->receptionist->id
        );

        $item->refresh();
        $this->assertEquals(30, $item->current_stock);
        $this->assertEquals('usage', $mov->type);
        $this->assertEquals(60000, $mov->total_cost); // 20 * 3000

        // Prevent overdraw
        $this->expectException(InvalidArgumentException::class);
        InventoryService::recordUsage(
            itemId: $item->id,
            quantity: 50, // exceeds available 30
            userId: $this->receptionist->id
        );
    }

    public function test_inventory_waste_records_shrinkage(): void
    {
        $item = InventoryItem::create([
            'code' => 'TEST-003',
            'name' => 'Shampoo Botol Bocor',
            'category' => 'amenities',
            'unit' => 'botol',
            'current_stock' => 20,
            'minimum_stock' => 5,
            'average_cost' => 5000,
        ]);

        $mov = InventoryService::recordWaste(
            itemId: $item->id,
            quantity: 3,
            reason: 'Kemasan pecah saat pengiriman',
            userId: $this->receptionist->id
        );

        $item->refresh();
        $this->assertEquals(17, $item->current_stock);
        $this->assertEquals('waste', $mov->type);
        $this->assertEquals(15000, $mov->total_cost);
    }

    public function test_stock_opname_lifecycle_and_manager_approval(): void
    {
        $item = InventoryItem::create([
            'code' => 'TEST-004',
            'name' => 'Sandal Syariah Uji',
            'category' => 'amenities',
            'unit' => 'pasang',
            'current_stock' => 40,
            'minimum_stock' => 10,
            'average_cost' => 8000,
        ]);

        // Receptionist conducts physical count: found 36 pairs (difference: -4)
        $opname = InventoryService::createStockOpname(
            itemsData: [
                ['item_id' => $item->id, 'physical_stock' => 36, 'notes' => 'Kurang 4 pasang'],
            ],
            date: now()->toDateString(),
            notes: 'Opname Mingguan',
            userId: $this->receptionist->id
        );

        $this->assertEquals('draft', $opname->status);
        $item->refresh();
        $this->assertEquals(40, $item->current_stock); // Unchanged until approval

        // Non-manager cannot approve
        try {
            InventoryService::approveStockOpname($opname->id, $this->receptionist->id);
            $this->fail('Receptionist should not be allowed to approve stock opname.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Hanya Manager', $e->getMessage());
        }

        // Manager approves
        $approvedOpname = InventoryService::approveStockOpname($opname->id, $this->manager->id);
        $this->assertEquals('approved', $approvedOpname->status);

        $item->refresh();
        $this->assertEquals(36, $item->current_stock); // Stock adjusted to physical

        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'adjustment',
            'inventory_item_id' => $item->id,
            'quantity' => 4,
            'total_cost' => 32000, // 4 * 8000
        ]);
    }

    public function test_fixed_asset_registration_and_journal(): void
    {
        $asset = FixedAssetService::registerAsset(
            data: [
                'code' => 'AST-TEST-001',
                'name' => 'Smart TV 50 Inch Kamar 201',
                'category' => 'electronic',
                'purchase_date' => now()->toDateString(),
                'purchase_cost' => 6000000,
                'useful_life_years' => 5,
                'salvage_value' => 600000,
            ],
            paymentSource: 'bank',
            userId: $this->manager->id
        );

        $this->assertEquals(6000000, $asset->purchase_cost);
        $this->assertEquals(0, $asset->accumulated_depreciation);
        $this->assertEquals(6000000, $asset->book_value);
        $this->assertEquals(5400000, $asset->depreciable_base); // 6.000.000 - 600.000

        // Verify Journal Debit 1210 & Credit 1120
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'fixed_asset',
            'reference_id' => $asset->id,
        ]);
    }

    public function test_straight_line_depreciation_execution(): void
    {
        // 5.400.000 depreciable base / (5 years * 12 months = 60 months) = 90.000 / month
        $asset = FixedAsset::create([
            'code' => 'AST-TEST-002',
            'name' => 'Sofa Lobby',
            'category' => 'furniture',
            'purchase_date' => '2026-01-01',
            'purchase_cost' => 6000000,
            'useful_life_years' => 5,
            'salvage_value' => 600000,
            'accumulated_depreciation' => 0,
            'status' => 'active',
        ]);

        $monthly = FixedAssetService::calculateMonthlyDepreciation($asset);
        $this->assertEquals(90000, $monthly);

        // Run depreciation for October 2026
        $res = FixedAssetService::runMonthlyDepreciation('2026-10-01', $this->manager->id);
        $this->assertEquals(1, $res['processed_assets']);
        $this->assertEquals(90000, $res['total_depreciation']);

        $asset->refresh();
        $this->assertEquals(90000, $asset->accumulated_depreciation);
        $this->assertEquals(5910000, $asset->book_value);

        // Run for the same month again should do nothing (idempotent)
        $res2 = FixedAssetService::runMonthlyDepreciation('2026-10-01', $this->manager->id);
        $this->assertEquals(0, $res2['processed_assets']);
        $this->assertEquals(0, $res2['total_depreciation']);
    }

    public function test_livewire_inventory_and_asset_components_render(): void
    {
        $this->actingAs($this->manager);

        Livewire::test(InventoryManager::class)
            ->assertStatus(200)
            ->assertSee('Inventaris dan Persediaan Hotel')
            ->assertSee('Daftar Barang (Master Stok)')
            ->call('openPurchaseModal')
            ->assertSet('showPurchaseModal', true);

        Livewire::test(FixedAssetManager::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Aset Tetap')
            ->assertSee('Nilai Buku Bersih')
            ->call('openRegisterModal')
            ->assertSet('showRegisterModal', true);
    }
}
