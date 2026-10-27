<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ChartOfAccount;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\NumberingSequence;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * Create balanced double-entry journal entry atomically
     *
     * @param array $lines [ ['account_code' => '1110', 'debit' => 100000, 'credit' => 0, 'memo' => '...'], ... ]
     */
    public static function createBalancedEntry(
        string $description,
        string $entryDate,
        array $lines,
        ?string $refType = null,
        ?int $refId = null,
        ?int $userId = null
    ): JournalEntry {
        return DB::transaction(function () use ($description, $entryDate, $lines, $refType, $refId, $userId) {
            $totalDebit = 0;
            $totalCredit = 0;
            $preparedLines = [];

            foreach ($lines as $line) {
                $account = null;
                if (!empty($line['account_code'])) {
                    $account = ChartOfAccount::findByCode($line['account_code']);
                } elseif (!empty($line['account_id'])) {
                    $account = ChartOfAccount::find($line['account_id']);
                }

                if (!$account) {
                    throw new InvalidArgumentException("Akun akuntansi tidak ditemukan untuk kode/ID: " . ($line['account_code'] ?? $line['account_id'] ?? 'null'));
                }

                $debit = (int) ($line['debit'] ?? 0);
                $credit = (int) ($line['credit'] ?? 0);

                if ($debit < 0 || $credit < 0) {
                    throw new InvalidArgumentException("Nominal debit dan credit tidak boleh negatif.");
                }

                $totalDebit += $debit;
                $totalCredit += $credit;

                $preparedLines[] = [
                    'account_id' => $account->id,
                    'debit' => $debit,
                    'credit' => $credit,
                    'memo' => $line['memo'] ?? null,
                ];
            }

            // Strict double-entry balance constraint
            if ($totalDebit !== $totalCredit) {
                throw new InvalidArgumentException("Jurnal tidak seimbang! Total Debit (Rp " . number_format($totalDebit, 0, ',', '.') . ") != Total Credit (Rp " . number_format($totalCredit, 0, ',', '.') . ")");
            }

            if ($totalDebit === 0) {
                throw new InvalidArgumentException("Total jurnal tidak boleh Rp 0.");
            }

            $entryNumber = NumberingSequence::next('JRN');

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $description,
                'is_posted' => true,
                'created_by' => $userId ?? auth()->id(),
            ]);

            foreach ($preparedLines as $pl) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $pl['account_id'],
                    'debit' => $pl['debit'],
                    'credit' => $pl['credit'],
                    'memo' => $pl['memo'],
                ]);
            }

            AuditLog::record(
                'create_journal_entry',
                $entry,
                null,
                $entry->toArray(),
                "Jurnal #{$entryNumber} berhasil dibukukan: {$description} (Total Rp " . number_format($totalDebit, 0, ',', '.') . ")"
            );

            return $entry;
        });
    }

    /**
     * Automatic journal for guest payment receipt
     */
    public static function postPaymentJournal(Payment $payment): JournalEntry
    {
        $cashOrBankCode = ($payment->payment_method === 'cash') ? '1110' : '1120';
        $amount = $payment->amount;

        if ($payment->dining_reservation_id) {
            // Restoran Syariah: Diakui sebagai pendapatan resto dan utang pajak PB1 (jika ada)
            $dining = $payment->diningReservation;
            $taxPortion = 0;
            if ($dining && $dining->total_amount > 0 && $dining->tax_amount > 0) {
                $taxPortion = (int) round(($payment->amount / $dining->total_amount) * $dining->tax_amount);
            }
            $netRevenue = max(0, $amount - $taxPortion);

            $lines = [
                ['account_code' => $cashOrBankCode, 'debit' => $amount, 'credit' => 0, 'memo' => 'Penerimaan Kas/Bank dari Tamu Resto'],
                ['account_code' => '4130', 'debit' => 0, 'credit' => $netRevenue, 'memo' => 'Pendapatan Restoran Syariah'],
            ];
            if ($taxPortion > 0) {
                $lines[] = ['account_code' => '2120', 'debit' => 0, 'credit' => $taxPortion, 'memo' => 'Utang Pajak Daerah PB1 Restoran'];
            }
            $desc = "Penerimaan resto #{$payment->payment_number} ({$payment->payment_method})";
        } elseif ($payment->meeting_reservation_id) {
            // Ruang Meeting: Diakui sebagai pendapatan meeting dan utang pajak PB1 (jika ada)
            $meeting = $payment->meetingReservation;
            $taxPortion = 0;
            if ($meeting && $meeting->total_amount > 0 && $meeting->tax_amount > 0) {
                $taxPortion = (int) round(($payment->amount / $meeting->total_amount) * $meeting->tax_amount);
            }
            $netRevenue = max(0, $amount - $taxPortion);

            $lines = [
                ['account_code' => $cashOrBankCode, 'debit' => $amount, 'credit' => 0, 'memo' => 'Penerimaan Kas/Bank Ruang Meeting'],
                ['account_code' => '4120', 'debit' => 0, 'credit' => $netRevenue, 'memo' => 'Pendapatan Ruang Meeting'],
            ];
            if ($taxPortion > 0) {
                $lines[] = ['account_code' => '2120', 'debit' => 0, 'credit' => $taxPortion, 'memo' => 'Utang Pajak Daerah PB1 Ruang Meeting'];
            }
            $desc = "Penerimaan meeting #{$payment->payment_number} ({$payment->payment_method})";
        } elseif ($payment->is_refund) {
            // Refund: Debit Uang Muka Tamu (2110), Credit Kas (1110)
            $lines = [
                ['account_code' => '2110', 'debit' => $amount, 'credit' => 0, 'memo' => 'Pengurangan Uang Muka Tamu'],
                ['account_code' => $cashOrBankCode, 'debit' => 0, 'credit' => $amount, 'memo' => 'Pengeluaran Kas/Bank Refund'],
            ];
            $desc = "Refund pembayaran #{$payment->payment_number} kepada tamu";
        } else {
            // Receipt Kamar: Debit Kas/Bank (1110/1120), Credit Uang Muka Tamu (2110)
            $lines = [
                ['account_code' => $cashOrBankCode, 'debit' => $amount, 'credit' => 0, 'memo' => 'Penerimaan Kas/Bank dari Tamu'],
                ['account_code' => '2110', 'debit' => 0, 'credit' => $amount, 'memo' => 'Uang Muka / Deposit Reservasi'],
            ];
            $desc = "Penerimaan pembayaran #{$payment->payment_number} ({$payment->payment_method})";
        }

        return self::createBalancedEntry(
            $desc,
            now()->toDateString(),
            $lines,
            'payment',
            $payment->id
        );
    }

    /**
     * Automatic journal for operational expense
     */
    public static function postExpenseJournal(Expense $expense): JournalEntry
    {
        $expenseAccountCode = match ($expense->category) {
            'supplies' => '5110',
            'utilities' => '5120',
            'maintenance' => '5130',
            'fnb' => '5150',
            default => '5190',
        };

        $cashOrBankCode = ($expense->payment_method === 'cash') ? '1110' : '1120';

        $lines = [
            ['account_code' => $expenseAccountCode, 'debit' => $expense->amount, 'credit' => 0, 'memo' => $expense->description],
            ['account_code' => $cashOrBankCode, 'debit' => 0, 'credit' => $expense->amount, 'memo' => 'Kas/Bank Keluar untuk Operasional'],
        ];

        return self::createBalancedEntry(
            "Pengeluaran #{$expense->expense_number}: {$expense->description}",
            now()->toDateString(),
            $lines,
            'expense',
            $expense->id
        );
    }

    /**
     * Recognize stay revenue (Accrual basis)
     */
    public static function postStayRevenueRecognition(
        Reservation $reservation,
        int $roomRevenue,
        int $taxAmount,
        string $auditDate
    ): JournalEntry {
        $total = $roomRevenue + $taxAmount;

        $lines = [
            ['account_code' => '2110', 'debit' => $total, 'credit' => 0, 'memo' => 'Pengakuan Uang Muka Tamu Kamar #' . $reservation->reservation_number],
            ['account_code' => '4110', 'debit' => 0, 'credit' => $roomRevenue, 'memo' => 'Pendapatan Sewa Kamar'],
            ['account_code' => '2120', 'debit' => 0, 'credit' => $taxAmount, 'memo' => 'Kewajiban Pajak Daerah PB1 (10%)'],
        ];

        return self::createBalancedEntry(
            "Pengakuan pendapatan menginap reservasi #{$reservation->reservation_number}",
            $auditDate,
            $lines,
            'night_audit',
            $reservation->id
        );
    }
}
