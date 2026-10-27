<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ChartOfAccount;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\NumberingSequence;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Record purchase / stock arrival with Weighted Average Cost calculation.
     */
    public static function recordPurchase(
        int $itemId,
        int $quantity,
        int $unitCost,
        ?int $vendorId = null,
        ?string $notes = null,
        string $paymentSource = 'cash',
        ?int $userId = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Jumlah barang masuk harus lebih besar dari 0.");
        }
        if ($unitCost < 0) {
            throw new InvalidArgumentException("Harga satuan tidak boleh negatif.");
        }

        return DB::transaction(function () use ($itemId, $quantity, $unitCost, $vendorId, $notes, $paymentSource, $userId) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);

            $oldStock = $item->current_stock;
            $oldAvgCost = $item->average_cost;

            // Weighted Average Cost Formula: ((oldStock * oldCost) + (newQty * newCost)) / (oldStock + newQty)
            $newTotalStock = $oldStock + $quantity;
            if ($newTotalStock > 0) {
                $newAvgCost = (int) round((($oldStock * $oldAvgCost) + ($quantity * $unitCost)) / $newTotalStock);
            } else {
                $newAvgCost = $unitCost;
            }

            $totalCost = (int) ($quantity * $unitCost);

            $item->current_stock = $newTotalStock;
            $item->average_cost = $newAvgCost;
            $item->save();

            $movement = InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'vendor_id' => $vendorId,
                'type' => 'purchase',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'reference_type' => 'direct_purchase',
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
            ]);

            // Automatic Journal Entry if COA exists
            if (ChartOfAccount::count() > 0 && $totalCost > 0) {
                $creditAccount = ($paymentSource === 'bank') ? '1120' : '1110';
                AccountingService::createBalancedEntry(
                    description: "Pembelian Stok: {$item->name} ({$quantity} {$item->unit})",
                    entryDate: now()->toDateString(),
                    lines: [
                        [
                            'account_code' => '1150', // Persediaan Barang
                            'debit' => $totalCost,
                            'credit' => 0,
                            'memo' => "Penerimaan stok {$item->name}",
                        ],
                        [
                            'account_code' => $creditAccount, // Kas / Bank
                            'debit' => 0,
                            'credit' => $totalCost,
                            'memo' => "Pembayaran stok via " . strtoupper($paymentSource),
                        ],
                    ],
                    refType: 'inventory_movement',
                    refId: $movement->id,
                    userId: $userId
                );
            }

            AuditLog::record('inventory_purchase', $movement, null, [
                'item_id' => $item->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'new_average_cost' => $newAvgCost,
            ], reason: null, userId: $userId);

            return $movement;
        });
    }

    /**
     * Record inventory usage (e.g., room cleaning, amenities replenishment, restaurant prep).
     */
    public static function recordUsage(
        int $itemId,
        int $quantity,
        ?string $refType = null,
        ?int $refId = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Jumlah pemakaian harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($itemId, $quantity, $refType, $refId, $notes, $userId) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);

            if ($item->current_stock < $quantity) {
                throw new InvalidArgumentException("Stok tidak mencukupi untuk {$item->name}. Tersedia: {$item->current_stock}, Diminta: {$quantity}.");
            }

            $unitCost = $item->average_cost;
            $totalCost = (int) ($quantity * $unitCost);

            $item->current_stock -= $quantity;
            $item->save();

            $movement = InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'type' => 'usage',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'reference_type' => $refType ?? 'operational_usage',
                'reference_id' => $refId,
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
            ]);

            // Automatic Journal Entry if COA exists
            if (ChartOfAccount::count() > 0 && $totalCost > 0) {
                $expenseCode = ($item->category === 'fnb') ? '5150' : '5110';
                AccountingService::createBalancedEntry(
                    description: "Pemakaian Stok: {$item->name} ({$quantity} {$item->unit})",
                    entryDate: now()->toDateString(),
                    lines: [
                        [
                            'account_code' => $expenseCode, // Beban Perlengkapan / Dapur
                            'debit' => $totalCost,
                            'credit' => 0,
                            'memo' => "Beban operasional {$item->name}",
                        ],
                        [
                            'account_code' => '1150', // Persediaan Barang
                            'debit' => 0,
                            'credit' => $totalCost,
                            'memo' => "Pengurangan persediaan {$item->name}",
                        ],
                    ],
                    refType: 'inventory_movement',
                    refId: $movement->id,
                    userId: $userId
                );
            }

            AuditLog::record('inventory_usage', $movement, null, [
                'item_id' => $item->id,
                'quantity' => $quantity,
                'total_cost' => $totalCost,
            ], reason: null, userId: $userId);

            return $movement;
        });
    }

    /**
     * Record inventory waste / expired / damaged items.
     */
    public static function recordWaste(
        int $itemId,
        int $quantity,
        ?string $reason = null,
        ?int $userId = null
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Jumlah barang rusak/kedaluwarsa harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($itemId, $quantity, $reason, $userId) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);

            if ($item->current_stock < $quantity) {
                throw new InvalidArgumentException("Stok tidak mencukupi untuk penghapusan stok {$item->name}.");
            }

            $unitCost = $item->average_cost;
            $totalCost = (int) ($quantity * $unitCost);

            $item->current_stock -= $quantity;
            $item->save();

            $movement = InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'type' => 'waste',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'reference_type' => 'stock_waste',
                'notes' => $reason,
                'created_by' => $userId ?? auth()->id(),
            ]);

            if (ChartOfAccount::count() > 0 && $totalCost > 0) {
                AccountingService::createBalancedEntry(
                    description: "Penghapusan Stok Rusak: {$item->name} ({$quantity} {$item->unit})",
                    entryDate: now()->toDateString(),
                    lines: [
                        [
                            'account_code' => '5170', // Beban Penyesuaian & Selisih Persediaan
                            'debit' => $totalCost,
                            'credit' => 0,
                            'memo' => "Kerusakan/kedaluwarsa: " . ($reason ?? 'Tanpa alasan'),
                        ],
                        [
                            'account_code' => '1150', // Persediaan Barang
                            'debit' => 0,
                            'credit' => $totalCost,
                            'memo' => "Penghapusan persediaan {$item->name}",
                        ],
                    ],
                    refType: 'inventory_movement',
                    refId: $movement->id,
                    userId: $userId
                );
            }

            return $movement;
        });
    }

    /**
     * Create Stock Opname (Draft).
     */
    public static function createStockOpname(
        array $itemsData,
        string $date,
        ?string $notes = null,
        ?int $userId = null
    ): StockOpname {
        if (empty($itemsData)) {
            throw new InvalidArgumentException("Daftar barang stock opname tidak boleh kosong.");
        }

        return DB::transaction(function () use ($itemsData, $date, $notes, $userId) {
            $opnameNumber = NumberingSequence::next('SOP');

            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'date' => $date,
                'status' => 'draft',
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
            ]);

            foreach ($itemsData as $data) {
                $item = InventoryItem::findOrFail($data['item_id']);
                $systemStock = $item->current_stock;
                $physicalStock = (int) $data['physical_stock'];
                $diff = $physicalStock - $systemStock;

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'inventory_item_id' => $item->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $diff,
                    'unit_cost' => $item->average_cost,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            return $opname;
        });
    }

    /**
     * Approve Stock Opname (Requires Manager role).
     */
    public static function approveStockOpname(int $opnameId, int $managerUserId): StockOpname
    {
        $manager = User::findOrFail($managerUserId);
        if (!$manager->hasRole('Manager') && !$manager->hasRole('admin')) {
            throw new InvalidArgumentException("Hanya Manager yang berwenang menyetujui Stock Opname.");
        }

        return DB::transaction(function () use ($opnameId, $manager) {
            $opname = StockOpname::with('items.item')->lockForUpdate()->findOrFail($opnameId);

            if ($opname->status !== 'draft') {
                throw new InvalidArgumentException("Hanya Stock Opname berstatus draft yang dapat disetujui.");
            }

            $totalAdjustmentDebit = 0;
            $totalAdjustmentCredit = 0;

            foreach ($opname->items as $opItem) {
                if ($opItem->difference === 0) {
                    continue;
                }

                $item = $opItem->item;
                $diff = $opItem->difference;
                $absDiff = abs($diff);
                $adjCost = (int) ($absDiff * $opItem->unit_cost);

                // Update physical stock directly
                $item->current_stock = $opItem->physical_stock;
                $item->save();

                InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'adjustment',
                    'quantity' => $absDiff,
                    'unit_cost' => $opItem->unit_cost,
                    'total_cost' => $adjCost,
                    'reference_type' => 'stock_opname',
                    'reference_id' => $opname->id,
                    'notes' => "Penyesuaian Opname: {$opname->opname_number} (" . ($diff > 0 ? "+{$diff}" : "{$diff}") . ")",
                    'created_by' => $manager->id,
                ]);

                if ($diff < 0) {
                    // Shrinkage: Debit 5170, Credit 1150
                    $totalAdjustmentDebit += $adjCost;
                } else {
                    // Surplus: Debit 1150, Credit 5170
                    $totalAdjustmentCredit += $adjCost;
                }
            }

            // Journal posting for net discrepancy if COA exists
            if (ChartOfAccount::count() > 0) {
                if ($totalAdjustmentDebit > 0) {
                    AccountingService::createBalancedEntry(
                        description: "Penyesuaian Opname Selisih Kurang: {$opname->opname_number}",
                        entryDate: now()->toDateString(),
                        lines: [
                            ['account_code' => '5170', 'debit' => $totalAdjustmentDebit, 'credit' => 0, 'memo' => 'Selisih kurang opname'],
                            ['account_code' => '1150', 'debit' => 0, 'credit' => $totalAdjustmentDebit, 'memo' => 'Koreksi persediaan'],
                        ],
                        refType: 'stock_opname',
                        refId: $opname->id,
                        userId: $manager->id
                    );
                }

                if ($totalAdjustmentCredit > 0) {
                    AccountingService::createBalancedEntry(
                        description: "Penyesuaian Opname Selisih Lebih: {$opname->opname_number}",
                        entryDate: now()->toDateString(),
                        lines: [
                            ['account_code' => '1150', 'debit' => $totalAdjustmentCredit, 'credit' => 0, 'memo' => 'Koreksi penambahan persediaan'],
                            ['account_code' => '5170', 'debit' => 0, 'credit' => $totalAdjustmentCredit, 'memo' => 'Selisih lebih opname'],
                        ],
                        refType: 'stock_opname',
                        refId: $opname->id,
                        userId: $manager->id
                    );
                }
            }

            $opname->status = 'approved';
            $opname->approved_by = $manager->id;
            $opname->approved_at = now();
            $opname->save();

            AuditLog::record('stock_opname_approved', $opname, null, [
                'approved_by' => $manager->name,
                'items_count' => $opname->items->count(),
            ], reason: null, userId: $manager->id);

            return $opname;
        });
    }

    /**
     * Reject Stock Opname.
     */
    public static function rejectStockOpname(int $opnameId, int $managerUserId, ?string $reason = null): StockOpname
    {
        $manager = User::findOrFail($managerUserId);
        if (!$manager->hasRole('Manager') && !$manager->hasRole('admin')) {
            throw new InvalidArgumentException("Hanya Manager yang berwenang menolak Stock Opname.");
        }

        $opname = StockOpname::findOrFail($opnameId);
        if ($opname->status !== 'draft') {
            throw new InvalidArgumentException("Hanya Stock Opname berstatus draft yang dapat ditolak.");
        }

        $opname->status = 'rejected';
        $opname->approved_by = $manager->id;
        $opname->notes = ($opname->notes ? $opname->notes . " | " : "") . "Ditolak: " . ($reason ?? 'Tidak ada alasan');
        $opname->save();

        AuditLog::record('stock_opname_rejected', $opname, null, ['reason' => $reason], reason: $reason, userId: $manager->id);

        return $opname;
    }
}
