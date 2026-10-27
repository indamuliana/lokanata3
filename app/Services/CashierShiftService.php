<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashierShiftService
{
    /**
     * Open a new cashier shift
     */
    public static function openShift(
        User $user,
        int $startingCash,
        string $shiftName = 'Shift Pagi',
        ?string $notes = null
    ): CashierShift {
        $activeShift = CashierShift::getActiveShift($user->id);
        if ($activeShift) {
            throw new InvalidArgumentException("Petugas {$user->name} masih memiliki shift kasir aktif yang belum ditutup (#{$activeShift->id}).");
        }

        if ($startingCash < 0) {
            throw new InvalidArgumentException("Modal awal kas tidak boleh negatif.");
        }

        $shift = CashierShift::create([
            'user_id' => $user->id,
            'shift_name' => $shiftName,
            'opened_at' => now(),
            'starting_cash' => $startingCash,
            'expected_cash' => $startingCash,
            'status' => 'open',
            'notes' => $notes,
        ]);

        AuditLog::record(
            'open_cashier_shift',
            $shift,
            null,
            $shift->toArray(),
            "Buka shift kasir {$shiftName} oleh {$user->name} dengan modal awal Rp " . number_format($startingCash, 0, ',', '.')
        );

        return $shift;
    }

    /**
     * Calculate expected cash balance in drawer for this shift
     */
    public static function calculateShiftSummary(CashierShift $shift): array
    {
        $startTime = $shift->opened_at;
        $endTime = $shift->closed_at ?? now();

        // 1. Cash received from room, meeting, and dining payments
        $cashPayments = Payment::where('payment_method', 'cash')
            ->where('is_refund', false)
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->get();

        $totalCashIn = $cashPayments->sum('amount');

        // 2. Cash refunds given to guests
        $cashRefunds = Payment::where('payment_method', 'cash')
            ->where('is_refund', true)
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->get();

        $totalCashRefund = $cashRefunds->sum('amount');

        // 3. Cash operational expenses paid during this shift
        $cashExpenses = Expense::where('payment_method', 'cash')
            ->where('status', 'approved')
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->get();

        $totalCashOut = $cashExpenses->sum('amount');

        // Expected in drawer = Modal Awal + Uang Masuk - Refund - Biaya Kasir
        $expectedCash = $shift->starting_cash + $totalCashIn - $totalCashRefund - $totalCashOut;

        return [
            'starting_cash' => $shift->starting_cash,
            'total_cash_in' => $totalCashIn,
            'total_cash_refund' => $totalCashRefund,
            'total_cash_out' => $totalCashOut,
            'expected_cash' => max(0, $expectedCash),
            'payments_count' => $cashPayments->count(),
            'expenses_count' => $cashExpenses->count(),
        ];
    }

    /**
     * Close cashier shift with physical cash count
     */
    public static function closeShift(
        CashierShift $shift,
        int $actualCash,
        ?string $reason = null
    ): CashierShift {
        return DB::transaction(function () use ($shift, $actualCash, $reason) {
            if ($shift->status === 'closed') {
                throw new InvalidArgumentException("Shift kasir ini sudah ditutup sebelumnya.");
            }

            if ($actualCash < 0) {
                throw new InvalidArgumentException("Hitungan kas fisik aktual tidak boleh negatif.");
            }

            $summary = self::calculateShiftSummary($shift);
            $difference = $actualCash - $summary['expected_cash'];

            $shift->update([
                'closed_at' => now(),
                'expected_cash' => $summary['expected_cash'],
                'actual_cash' => $actualCash,
                'difference' => $difference,
                'difference_reason' => $reason,
                'status' => 'closed',
            ]);

            AuditLog::record(
                'close_cashier_shift',
                $shift,
                null,
                $shift->toArray(),
                "Tutup shift kasir {$shift->shift_name}. Fisik: Rp " . number_format($actualCash, 0, ',', '.') . ", Ekspektasi: Rp " . number_format($summary['expected_cash'], 0, ',', '.') . ", Selisih: Rp " . number_format($difference, 0, ',', '.')
            );

            return $shift;
        });
    }
}
