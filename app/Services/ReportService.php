<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get Income Statement (Laporan Laba Rugi) for a period.
     */
    public static function getIncomeStatement(string $startDate, string $endDate): array
    {
        $revenueAccounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->get();
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->where('is_active', true)->get();

        $linesAgg = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereDate('journal_entries.entry_date', '>=', $startDate)
            ->whereDate('journal_entries.entry_date', '<=', $endDate)
            ->where('journal_entries.is_posted', true)
            ->groupBy('journal_lines.account_id')
            ->selectRaw('journal_lines.account_id, SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->get()
            ->keyBy('account_id');

        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $acc) {
            // Revenue normal balance is credit: credit - debit
            $line = $linesAgg->get($acc->id);
            $credit = (int) ($line->total_credit ?? 0);
            $debit = (int) ($line->total_debit ?? 0);

            $net = $credit - $debit;
            if ($net !== 0) {
                $revenues[] = [
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $net,
                ];
                $totalRevenue += $net;
            }
        }

        $expenses = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $acc) {
            // Expense normal balance is debit: debit - credit
            $line = $linesAgg->get($acc->id);
            $debit = (int) ($line->total_debit ?? 0);
            $credit = (int) ($line->total_credit ?? 0);

            $net = $debit - $credit;
            if ($net !== 0) {
                $expenses[] = [
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'amount' => $net,
                ];
                $totalExpense += $net;
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenses,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
        ];
    }

    /**
     * Get Trial Balance (Neraca Saldo) as of a specific date.
     */
    public static function getTrialBalance(string $asOfDate): array
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $lines = [];
        $totalDebitBalance = 0;
        $totalCreditBalance = 0;

        $linesAgg = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereDate('journal_entries.entry_date', '<=', $asOfDate)
            ->where('journal_entries.is_posted', true)
            ->groupBy('journal_lines.account_id')
            ->selectRaw('journal_lines.account_id, SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->get()
            ->keyBy('account_id');

        foreach ($accounts as $acc) {
            $agg = $linesAgg->get($acc->id);
            $totalDebit = (int) ($agg->total_debit ?? 0);
            $totalCredit = (int) ($agg->total_credit ?? 0);

            if ($totalDebit === 0 && $totalCredit === 0) {
                continue;
            }

            $debitBalance = 0;
            $creditBalance = 0;

            if ($acc->normal_balance === 'debit') {
                $net = $totalDebit - $totalCredit;
                if ($net >= 0) {
                    $debitBalance = $net;
                } else {
                    $creditBalance = abs($net);
                }
            } else {
                $net = $totalCredit - $totalDebit;
                if ($net >= 0) {
                    $creditBalance = $net;
                } else {
                    $debitBalance = abs($net);
                }
            }

            $lines[] = [
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => $acc->type,
                'debit_balance' => $debitBalance,
                'credit_balance' => $creditBalance,
            ];

            $totalDebitBalance += $debitBalance;
            $totalCreditBalance += $creditBalance;
        }

        return [
            'as_of_date' => $asOfDate,
            'lines' => $lines,
            'total_debit' => $totalDebitBalance,
            'total_credit' => $totalCreditBalance,
            'is_balanced' => $totalDebitBalance === $totalCreditBalance,
        ];
    }

    /**
     * Get Balance Sheet (Neraca Keuangan) as of a date.
     */
    public static function getBalanceSheet(string $asOfDate): array
    {
        $trialBalance = self::getTrialBalance($asOfDate);
        $assets = [];
        $liabilities = [];
        $equities = [];

        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquities = 0;

        foreach ($trialBalance['lines'] as $l) {
            if ($l['type'] === 'asset') {
                // Contra asset has credit balance
                $val = $l['debit_balance'] - $l['credit_balance'];
                $assets[] = ['code' => $l['code'], 'name' => $l['name'], 'amount' => $val];
                $totalAssets += $val;
            } elseif ($l['type'] === 'liability') {
                $val = $l['credit_balance'] - $l['debit_balance'];
                $liabilities[] = ['code' => $l['code'], 'name' => $l['name'], 'amount' => $val];
                $totalLiabilities += $val;
            } elseif ($l['type'] === 'equity') {
                $val = $l['credit_balance'] - $l['debit_balance'];
                $equities[] = ['code' => $l['code'], 'name' => $l['name'], 'amount' => $val];
                $totalEquities += $val;
            }
        }

        // Add current period net income to equity
        $yearStart = Carbon::parse($asOfDate)->startOfYear()->toDateString();
        $incomeStmt = self::getIncomeStatement($yearStart, $asOfDate);
        $currentEarnings = $incomeStmt['net_profit'];

        $equities[] = [
            'code' => 'RE-CURR',
            'name' => 'Laba / Rugi Periode Berjalan',
            'amount' => $currentEarnings,
        ];
        $totalEquities += $currentEarnings;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equities' => $equities,
            'total_equities' => $totalEquities,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquities,
            'is_balanced' => $totalAssets === ($totalLiabilities + $totalEquities),
        ];
    }

    /**
     * Get Operational Metrics: Occupancy Rate, ADR, RevPAR, and Channel Distribution.
     */
    public static function getOperationalMetrics(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $daysInPeriod = max(1, (int) $start->diffInDays($end) + 1);

        $totalRooms = Room::where('operational_status', 'active')->count();
        if ($totalRooms === 0) {
            $totalRooms = 20; // Default capacity
        }

        $totalAvailableRoomNights = (int) ($totalRooms * $daysInPeriod);

        // Fetch reservations overlapping this period that were active
        $reservations = Reservation::where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereDate('check_in_date', '>=', $startDate)
                        ->whereDate('check_in_date', '<=', $endDate);
                })
                ->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereDate('check_out_date', '>=', $startDate)
                        ->whereDate('check_out_date', '<=', $endDate);
                })
                ->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->whereDate('check_in_date', '<=', $startDate)
                        ->whereDate('check_out_date', '>=', $endDate);
                });
            })->get();

        $occupiedRoomNights = 0;
        $totalRoomRevenue = 0;

        $channels = [
            'walk_in' => 0,
            'ota' => 0,
            'whatsapp' => 0,
            'website' => 0,
        ];

        foreach ($reservations as $res) {
            $ch = $res->source ?? 'walk_in';
            if ($ch === 'ota_manual') {
                $ch = 'ota';
            } elseif ($ch === 'whatsapp_manual') {
                $ch = 'whatsapp';
            }

            if (isset($channels[$ch])) {
                $channels[$ch]++;
            } else {
                $channels['walk_in']++;
            }

            // Calculate nights overlapping with the period
            $rStart = Carbon::parse($res->check_in_date)->startOfDay();
            $rEnd = Carbon::parse($res->check_out_date)->startOfDay();

            $overlapStart = $rStart->greaterThan($start) ? $rStart : $start;
            $overlapEnd = $rEnd->lessThan($end) ? $rEnd : $end;

            $overlapNights = max(0, (int) $overlapStart->diffInDays($overlapEnd));
            if ($overlapNights === 0 && $rStart->isSameDay($overlapStart)) {
                $overlapNights = 1;
            }

            $occupiedRoomNights += (int) $overlapNights;

            // Prorated room revenue for the overlapping stay nights
            $totalStayNights = max(1, $res->nights_count);
            $proratedRevenue = (int) round(($res->total_amount / $totalStayNights) * $overlapNights);
            $totalRoomRevenue += $proratedRevenue;
        }

        $occupancyRate = $totalAvailableRoomNights > 0 
            ? round(($occupiedRoomNights / $totalAvailableRoomNights) * 100, 1) 
            : 0;

        $adr = $occupiedRoomNights > 0 
            ? (int) round($totalRoomRevenue / $occupiedRoomNights) 
            : 0;

        $revPar = $totalAvailableRoomNights > 0 
            ? (int) round($totalRoomRevenue / $totalAvailableRoomNights) 
            : 0;

        $totalBookings = array_sum($channels);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_in_period' => $daysInPeriod,
            'total_rooms' => $totalRooms,
            'total_available_room_nights' => $totalAvailableRoomNights,
            'occupied_room_nights' => $occupiedRoomNights,
            'occupancy_rate' => $occupancyRate,
            'total_room_revenue' => $totalRoomRevenue,
            'adr' => $adr,
            'rev_par' => $revPar,
            'channels' => $channels,
            'total_bookings' => $totalBookings,
        ];
    }
}
