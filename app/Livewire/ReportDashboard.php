<?php

namespace App\Livewire;

use App\Models\DiningReservation;
use App\Models\MeetingReservation;
use App\Models\Reservation;
use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReportDashboard extends Component
{
    public string $activeTab = 'operational'; // 'operational', 'income_statement', 'trial_balance', 'balance_sheet', 'history'
    public string $start_date = '';
    public string $end_date = '';
    public string $as_of_date = '';

    // History Tab Filter
    public string $historyUnitFilter = 'all'; // 'all', 'room', 'meeting', 'dining'
    public string $historySearch = '';

    public function mount(): void
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();
        $this->as_of_date = now()->toDateString();
    }

    public function setPeriod(string $period): void
    {
        if ($period === 'today') {
            $this->start_date = now()->toDateString();
            $this->end_date = now()->toDateString();
            $this->as_of_date = now()->toDateString();
        } elseif ($period === 'this_month') {
            $this->start_date = now()->startOfMonth()->toDateString();
            $this->end_date = now()->toDateString();
            $this->as_of_date = now()->toDateString();
        } elseif ($period === 'this_year') {
            $this->start_date = now()->startOfYear()->toDateString();
            $this->end_date = now()->toDateString();
            $this->as_of_date = now()->toDateString();
        }
    }

    public function render()
    {
        $operational = ReportService::getOperationalMetrics($this->start_date, $this->end_date);
        $incomeStatement = ReportService::getIncomeStatement($this->start_date, $this->end_date);
        $trialBalance = ReportService::getTrialBalance($this->as_of_date);
        $balanceSheet = ReportService::getBalanceSheet($this->as_of_date);

        // Agregasi Omzet Per Unit untuk Tab Riwayat
        $roomRevenue = (int) Reservation::whereDate('check_in_date', '>=', $this->start_date)
            ->whereDate('check_in_date', '<=', $this->end_date)
            ->whereNotIn('status', ['cancelled'])->sum('total_amount');
        $meetingRevenue = (int) MeetingReservation::whereDate('event_date', '>=', $this->start_date)
            ->whereDate('event_date', '<=', $this->end_date)
            ->where('status', '!=', 'cancelled')->sum('total_amount');
        $diningRevenue = (int) DiningReservation::whereDate('reservation_date', '>=', $this->start_date)
            ->whereDate('reservation_date', '<=', $this->end_date)
            ->where('status', '!=', 'cancelled')->sum('total_amount');
        $grandTotalRevenue = $roomRevenue + $meetingRevenue + $diningRevenue;

        // Data transaksi gabungan untuk preview tabel riwayat
        $historyItems = collect();
        if ($this->activeTab === 'history') {
            if (in_array($this->historyUnitFilter, ['all', 'room'])) {
                $rq = Reservation::with(['customer', 'roomType'])
                    ->whereDate('check_in_date', '>=', $this->start_date)
                    ->whereDate('check_in_date', '<=', $this->end_date);
                if (!empty($this->historySearch)) {
                    $rq->where(fn($q) => $q->where('reservation_number', 'like', "%{$this->historySearch}%")->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$this->historySearch}%")));
                }
                foreach ($rq->latest('check_in_date')->take(20)->get() as $r) {
                    $historyItems->push([
                        'unit' => 'Kamar',
                        'unit_code' => 'ROOM',
                        'date' => $r->check_in_date->format('d/m/Y'),
                        'code' => $r->reservation_number,
                        'customer' => $r->customer->name ?? '-',
                        'detail' => $r->room_type_name,
                        'total' => $r->total_amount,
                        'paid' => $r->paid_amount,
                        'status' => $r->status,
                        'payment_status' => $r->payment_status,
                    ]);
                }
            }

            if (in_array($this->historyUnitFilter, ['all', 'meeting'])) {
                $mq = MeetingReservation::with(['customer', 'meetingRoom'])
                    ->whereDate('event_date', '>=', $this->start_date)
                    ->whereDate('event_date', '<=', $this->end_date);
                if (!empty($this->historySearch)) {
                    $mq->where(fn($q) => $q->where('reservation_number', 'like', "%{$this->historySearch}%")->orWhere('event_name', 'like', "%{$this->historySearch}%")->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$this->historySearch}%")));
                }
                foreach ($mq->latest('event_date')->take(20)->get() as $r) {
                    $historyItems->push([
                        'unit' => 'Meeting',
                        'unit_code' => 'MEETING',
                        'date' => $r->event_date->format('d/m/Y'),
                        'code' => $r->reservation_number,
                        'customer' => $r->customer->name ?? '-',
                        'detail' => $r->meetingRoom?->name ?? 'Meeting Room',
                        'total' => $r->total_amount,
                        'paid' => $r->paid_amount,
                        'status' => $r->status,
                        'payment_status' => $r->payment_status,
                    ]);
                }
            }

            if (in_array($this->historyUnitFilter, ['all', 'dining'])) {
                $dq = DiningReservation::with(['customer', 'diningPackage'])
                    ->whereDate('reservation_date', '>=', $this->start_date)
                    ->whereDate('reservation_date', '<=', $this->end_date);
                if (!empty($this->historySearch)) {
                    $dq->where(fn($q) => $q->where('reservation_number', 'like', "%{$this->historySearch}%")->orWhere('group_name', 'like', "%{$this->historySearch}%")->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$this->historySearch}%")));
                }
                foreach ($dq->latest('reservation_date')->take(20)->get() as $r) {
                    $historyItems->push([
                        'unit' => 'Dining',
                        'unit_code' => 'DINING',
                        'date' => $r->reservation_date->format('d/m/Y'),
                        'code' => $r->reservation_number,
                        'customer' => $r->customer->name ?? '-',
                        'detail' => $r->diningPackage?->name ?? 'Dining Package',
                        'total' => $r->total_amount,
                        'paid' => $r->paid_amount,
                        'status' => $r->status,
                        'payment_status' => $r->payment_status,
                    ]);
                }
            }
        }

        return view('livewire.report-dashboard', [
            'operational' => $operational,
            'incomeStatement' => $incomeStatement,
            'trialBalance' => $trialBalance,
            'balanceSheet' => $balanceSheet,
            'roomRevenue' => $roomRevenue,
            'meetingRevenue' => $meetingRevenue,
            'diningRevenue' => $diningRevenue,
            'grandTotalRevenue' => $grandTotalRevenue,
            'historyItems' => $historyItems,
        ]);
    }
}
