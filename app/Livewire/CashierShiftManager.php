<?php

namespace App\Livewire;

use App\Models\CashierShift;
use App\Services\CashierShiftService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CashierShiftManager extends Component
{
    use WithPagination;

    // Open Shift Form
    public bool $showOpenModal = false;
    public string $shift_name = 'Shift 1 (Pagi)';
    public int $starting_cash = 500000;
    public string $open_notes = '';

    // Close Shift Form
    public bool $showCloseModal = false;
    public int $actual_cash = 0;
    public string $difference_reason = '';

    public function openNewShiftModal(): void
    {
        $this->resetErrorBag();
        $this->starting_cash = 500000;
        $this->shift_name = 'Shift 1 (Pagi)';
        $this->open_notes = '';
        $this->showOpenModal = true;
    }

    public function saveOpenShift(): void
    {
        $this->validate([
            'starting_cash' => 'required|integer|min:0',
            'shift_name' => 'required|string|min:3',
        ]);

        try {
            CashierShiftService::openShift(
                auth()->user(),
                $this->starting_cash,
                $this->shift_name,
                $this->open_notes ?: null
            );

            $this->showOpenModal = false;
            session()->flash('message', "Shift kasir {$this->shift_name} berhasil dibuka.");
        } catch (\Exception $e) {
            $this->addError('open_shift', $e->getMessage());
        }
    }

    public function openCloseShiftModal(): void
    {
        $this->resetErrorBag();
        $activeShift = CashierShift::getActiveShift(auth()->id());
        if (!$activeShift) {
            session()->flash('error', "Tidak ada shift kasir aktif.");
            return;
        }

        $summary = CashierShiftService::calculateShiftSummary($activeShift);
        $this->actual_cash = $summary['expected_cash'];
        $this->difference_reason = '';
        $this->showCloseModal = true;
    }

    public function saveCloseShift(): void
    {
        $this->validate([
            'actual_cash' => 'required|integer|min:0',
        ]);

        $activeShift = CashierShift::getActiveShift(auth()->id());
        if (!$activeShift) return;

        $summary = CashierShiftService::calculateShiftSummary($activeShift);
        $diff = $this->actual_cash - $summary['expected_cash'];

        if ($diff !== 0 && empty($this->difference_reason)) {
            $this->addError('difference_reason', "Terdapat selisih kas sebesar Rp " . number_format(abs($diff), 0, ',', '.') . ". Alasan selisih wajib diisi.");
            return;
        }

        try {
            CashierShiftService::closeShift(
                $activeShift,
                $this->actual_cash,
                $this->difference_reason ?: null
            );

            $this->showCloseModal = false;
            session()->flash('message', "Shift kasir berhasil ditutup.");
        } catch (\Exception $e) {
            $this->addError('close_shift', $e->getMessage());
        }
    }

    public function render()
    {
        $activeShift = CashierShift::getActiveShift(auth()->id());
        $activeSummary = $activeShift ? CashierShiftService::calculateShiftSummary($activeShift) : null;

        $pastShifts = CashierShift::with('user')
            ->latest('opened_at')
            ->paginate(10);

        return view('livewire.cashier-shift-manager', [
            'activeShift' => $activeShift,
            'activeSummary' => $activeSummary,
            'pastShifts' => $pastShifts,
        ]);
    }
}
