<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\Expense;
use App\Models\NumberingSequence;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExpenseService
{
    /**
     * Threshold where expense requires Manager approval (PRD 11.2)
     */
    public const AUTO_APPROVAL_LIMIT = 500000;

    /**
     * Create an operational expense
     */
    public static function createExpense(array $data, User $requester): Expense
    {
        return DB::transaction(function () use ($data, $requester) {
            $amount = (int) ($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new InvalidArgumentException("Nominal pengeluaran harus lebih besar dari Rp 0.");
            }

            $expenseNumber = NumberingSequence::next('EXP');

            // Auto-approval logic: <= limit approved langsung, > limit butuh approval Manager
            $approvalLimit = (int) \App\Models\HotelSetting::get('expense_approval_limit', self::AUTO_APPROVAL_LIMIT);
            $isAutoApproved = ($amount <= $approvalLimit);
            $status = $isAutoApproved ? 'approved' : 'pending_approval';

            // Find current active shift of requester if any
            $activeShift = CashierShift::getActiveShift($requester->id);

            $expense = Expense::create([
                'expense_number' => $expenseNumber,
                'cashier_shift_id' => $activeShift?->id,
                'category' => $data['category'] ?? 'other',
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'description' => $data['description'],
                'proof_file_path' => $data['proof_file_path'] ?? null,
                'status' => $status,
                'requested_by' => $requester->id,
                'approved_by' => $isAutoApproved ? $requester->id : null,
                'approved_at' => $isAutoApproved ? now() : null,
            ]);

            // If auto-approved, post balanced accounting journal immediately!
            if ($isAutoApproved) {
                AccountingService::postExpenseJournal($expense);
            }

            AuditLog::record(
                'create_expense',
                $expense,
                null,
                $expense->toArray(),
                "Pengajuan biaya operasional #{$expenseNumber} sebesar Rp " . number_format($amount, 0, ',', '.') . " ({$status})"
            );

            return $expense;
        });
    }

    /**
     * Manager approves pending expense
     */
    public static function approveExpense(Expense $expense, User $approver): Expense
    {
        if (!$approver->hasRole(['Manager', 'Admin', 'admin'])) {
            throw new InvalidArgumentException("Hanya Manager atau Admin yang berwenang menyetujui pengeluaran.");
        }

        return DB::transaction(function () use ($expense, $approver) {
            if ($expense->status !== 'pending_approval') {
                throw new InvalidArgumentException("Hanya pengeluaran berstatus pending yang dapat disetujui.");
            }

            $expense->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Post journal entry upon approval
            AccountingService::postExpenseJournal($expense);

            AuditLog::record(
                'approve_expense',
                $expense,
                ['status' => 'pending_approval'],
                $expense->toArray(),
                "Pengeluaran #{$expense->expense_number} disetujui oleh {$approver->name}"
            );

            return $expense;
        });
    }

    /**
     * Manager rejects pending expense
     */
    public static function rejectExpense(Expense $expense, User $approver, string $reason): Expense
    {
        if (!$approver->hasRole(['Manager', 'Admin', 'admin'])) {
            throw new InvalidArgumentException("Hanya Manager atau Admin yang berwenang menolak pengeluaran.");
        }

        return DB::transaction(function () use ($expense, $approver, $reason) {
            if ($expense->status !== 'pending_approval') {
                throw new InvalidArgumentException("Hanya pengeluaran berstatus pending yang dapat ditolak.");
            }

            $expense->update([
                'status' => 'rejected',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            AuditLog::record(
                'reject_expense',
                $expense,
                ['status' => 'pending_approval'],
                $expense->toArray(),
                "Pengeluaran #{$expense->expense_number} ditolak oleh {$approver->name}. Alasan: {$reason}"
            );

            return $expense;
        });
    }
}
