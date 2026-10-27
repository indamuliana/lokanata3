<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Services\ExpenseService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ExpenseManager extends Component
{
    use WithPagination;

    public string $categoryFilter = 'all';
    public string $statusFilter = 'all';

    // New Expense Form
    public bool $showCreateModal = false;
    public string $category = 'supplies';
    public int $amount = 0;
    public string $payment_method = 'cash';
    public string $description = '';

    // Reject Modal
    public bool $showRejectModal = false;
    public ?int $selectedExpenseId = null;
    public string $rejection_reason = '';

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->amount = 0;
        $this->category = 'supplies';
        $this->payment_method = 'cash';
        $this->description = '';
        $this->showCreateModal = true;
    }

    public function saveExpense(): void
    {
        $this->validate([
            'amount' => 'required|integer|min:1000',
            'description' => 'required|string|min:4',
            'category' => 'required|in:supplies,utilities,maintenance,fnb,transport,other',
            'payment_method' => 'required|in:cash,bank_transfer',
        ]);

        try {
            $exp = ExpenseService::createExpense([
                'category' => $this->category,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'description' => $this->description,
            ], auth()->user());

            $this->showCreateModal = false;

            if ($exp->status === 'approved') {
                session()->flash('message', "Pengeluaran #{$exp->expense_number} sebesar Rp " . number_format($exp->amount, 0, ',', '.') . " langsung disetujui & dibukukan ke jurnal.");
            } else {
                session()->flash('message', "Pengeluaran #{$exp->expense_number} melebihi batas Rp 500.000 dan menunggu persetujuan Manager.");
            }
        } catch (\Exception $e) {
            $this->addError('expense', $e->getMessage());
        }
    }

    public function approve(int $id): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin'))) {
            session()->flash('error', "Akses ditolak. Hanya Manager atau Admin yang berwenang menyetujui pengeluaran kas.");
            return;
        }

        try {
            $exp = Expense::findOrFail($id);
            ExpenseService::approveExpense($exp, $user);
            session()->flash('message', "Pengeluaran #{$exp->expense_number} berhasil disetujui.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openRejectModal(int $id): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin'))) {
            session()->flash('error', "Akses ditolak. Hanya Manager atau Admin yang berwenang menolak pengeluaran kas.");
            return;
        }

        $this->resetErrorBag();
        $this->selectedExpenseId = $id;
        $this->rejection_reason = '';
        $this->showRejectModal = true;
    }

    public function saveReject(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin'))) {
            session()->flash('error', "Akses ditolak. Hanya Manager atau Admin yang berwenang menolak pengeluaran kas.");
            return;
        }

        $this->validate([
            'rejection_reason' => 'required|string|min:4',
        ]);

        try {
            $exp = Expense::findOrFail($this->selectedExpenseId);
            ExpenseService::rejectExpense($exp, $user, $this->rejection_reason);
            $this->showRejectModal = false;
            session()->flash('message', "Pengeluaran #{$exp->expense_number} berhasil ditolak.");
        } catch (\Exception $e) {
            $this->addError('reject', $e->getMessage());
        }
    }

    public function render()
    {
        $query = Expense::with(['requester', 'approver'])->latest();

        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.expense-manager', [
            'expenses' => $query->paginate(10),
            'isManager' => auth()->user()->hasRole('Manager') || auth()->user()->hasRole('admin'),
        ]);
    }
}
