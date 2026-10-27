<?php

namespace App\Services;

use App\Models\AssetDepreciation;
use App\Models\AuditLog;
use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FixedAssetService
{
    /**
     * Register a new fixed asset.
     */
    public static function registerAsset(array $data, ?string $paymentSource = 'bank', ?int $userId = null): FixedAsset
    {
        if (empty($data['code']) || empty($data['name'])) {
            throw new InvalidArgumentException("Kode dan nama aset wajib diisi.");
        }

        $purchaseCost = (int) ($data['purchase_cost'] ?? 0);
        if ($purchaseCost <= 0) {
            throw new InvalidArgumentException("Nilai perolehan aset harus lebih besar dari 0.");
        }

        $usefulLife = (int) ($data['useful_life_years'] ?? 5);
        if ($usefulLife <= 0) {
            throw new InvalidArgumentException("Masa manfaat aset minimal 1 tahun.");
        }

        $salvageValue = (int) ($data['salvage_value'] ?? 0);
        if ($salvageValue >= $purchaseCost) {
            throw new InvalidArgumentException("Nilai residu tidak boleh lebih besar atau sama dengan nilai perolehan.");
        }

        return DB::transaction(function () use ($data, $purchaseCost, $usefulLife, $salvageValue, $paymentSource, $userId) {
            $asset = FixedAsset::create([
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
                'category' => $data['category'] ?? 'furniture',
                'room_id' => $data['room_id'] ?? null,
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'purchase_cost' => $purchaseCost,
                'useful_life_years' => $usefulLife,
                'salvage_value' => $salvageValue,
                'accumulated_depreciation' => 0,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            // Automatic Journal Entry if COA exists
            if (ChartOfAccount::count() > 0) {
                $creditAccount = ($paymentSource === 'cash') ? '1110' : '1120';
                AccountingService::createBalancedEntry(
                    description: "Perolehan Aset Tetap: {$asset->name} ({$asset->code})",
                    entryDate: $asset->purchase_date->toDateString(),
                    lines: [
                        [
                            'account_code' => '1210', // Aset Tetap Peralatan & Furnitur
                            'debit' => $purchaseCost,
                            'credit' => 0,
                            'memo' => "Perolehan {$asset->name}",
                        ],
                        [
                            'account_code' => $creditAccount, // Bank / Kas
                            'debit' => 0,
                            'credit' => $purchaseCost,
                            'memo' => "Pembayaran aset via " . strtoupper($paymentSource ?? 'bank'),
                        ],
                    ],
                    refType: 'fixed_asset',
                    refId: $asset->id,
                    userId: $userId
                );
            }

            AuditLog::record('fixed_asset_registered', $asset, null, [
                'code' => $asset->code,
                'cost' => $purchaseCost,
            ], reason: null, userId: $userId);

            return $asset;
        });
    }

    /**
     * Calculate monthly straight-line depreciation for an asset.
     */
    public static function calculateMonthlyDepreciation(FixedAsset $asset): int
    {
        $depreciableBase = $asset->depreciable_base;
        $totalMonths = $asset->useful_life_years * 12;

        if ($totalMonths <= 0 || $depreciableBase <= 0) {
            return 0;
        }

        $standardMonthly = (int) round($depreciableBase / $totalMonths);
        $remainingDepreciable = max(0, $depreciableBase - $asset->accumulated_depreciation);

        return min($standardMonthly, $remainingDepreciable);
    }

    /**
     * Run monthly straight-line depreciation for all active assets for a given month.
     */
    public static function runMonthlyDepreciation(string $periodDate, ?int $userId = null): array
    {
        $period = Carbon::parse($periodDate)->startOfMonth()->toDateString();

        return DB::transaction(function () use ($period, $userId) {
            $assets = FixedAsset::where('status', 'active')->get();
            $processedCount = 0;
            $totalDepreciationAmount = 0;

            foreach ($assets as $asset) {
                // Check if already depreciated for this period
                $alreadyDone = AssetDepreciation::where('fixed_asset_id', $asset->id)
                    ->whereDate('period_date', $period)
                    ->exists();

                if ($alreadyDone) {
                    continue;
                }

                $amount = self::calculateMonthlyDepreciation($asset);
                if ($amount <= 0) {
                    continue;
                }

                $newAccumulated = $asset->accumulated_depreciation + $amount;
                $asset->accumulated_depreciation = $newAccumulated;
                $asset->save();

                $journalEntry = null;

                // Create Journal Entry if COA exists
                if (ChartOfAccount::count() > 0) {
                    $journalEntry = AccountingService::createBalancedEntry(
                        description: "Penyusutan Bulanan: {$asset->name} ({$asset->code}) Periode {$period}",
                        entryDate: $period,
                        lines: [
                            [
                                'account_code' => '5160', // Beban Penyusutan Aset Tetap
                                'debit' => $amount,
                                'credit' => 0,
                                'memo' => "Beban penyusutan {$asset->name}",
                            ],
                            [
                                'account_code' => '1220', // Akumulasi Penyusutan Aset Tetap
                                'debit' => 0,
                                'credit' => $amount,
                                'memo' => "Akumulasi penyusutan {$asset->name}",
                            ],
                        ],
                        refType: 'asset_depreciation',
                        refId: $asset->id,
                        userId: $userId
                    );
                }

                AssetDepreciation::create([
                    'fixed_asset_id' => $asset->id,
                    'period_date' => $period,
                    'depreciation_amount' => $amount,
                    'accumulated_after' => $newAccumulated,
                    'journal_entry_id' => $journalEntry?->id,
                ]);

                $processedCount++;
                $totalDepreciationAmount += $amount;
            }

            return [
                'period' => $period,
                'processed_assets' => $processedCount,
                'total_depreciation' => $totalDepreciationAmount,
            ];
        });
    }
}
