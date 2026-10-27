<?php

namespace App\Livewire;

use App\Models\DiningReservation;
use App\Models\MeetingReservation;
use App\Models\MeetingRoom;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Services\SeasonService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UnifiedReservationCalendar extends Component
{
    public string $startDate = '';
    public int $daysCount = 7; // 7, 14, 30
    public string $activeView = 'tape_chart'; // 'tape_chart' or 'agenda'
    public string $resourceFilter = 'all'; // 'all', 'rooms', 'meeting', 'dining'

    // Agenda View
    public string $agendaDate = '';
    public string $typeFilter = 'all'; // all, rooms, meeting, dining

    // Modal Detail
    public ?int $selectedReservationId = null;
    public string $selectedReservationType = 'room'; // 'room', 'meeting', 'dining'
    public bool $showDetailModal = false;

    public function mount(): void
    {
        $this->startDate = now()->startOfWeek()->toDateString();
        $this->agendaDate = now()->toDateString();
    }

    public function changePeriod(int $multiplier): void
    {
        $this->startDate = Carbon::parse($this->startDate)->addDays($this->daysCount * $multiplier)->toDateString();
    }

    public function setDaysCount(int $count): void
    {
        if (in_array($count, [7, 14, 30])) {
            $this->daysCount = $count;
        }
    }

    public function goToToday(): void
    {
        $this->startDate = now()->startOfWeek()->toDateString();
        $this->agendaDate = now()->toDateString();
    }

    public function changeAgendaDate(int $days): void
    {
        $this->agendaDate = Carbon::parse($this->agendaDate)->addDays($days)->toDateString();
    }

    public function openDetail(int $reservationId, string $type = 'room'): void
    {
        $this->selectedReservationId = $reservationId;
        $this->selectedReservationType = $type;
        $this->showDetailModal = true;
    }

    public function render()
    {
        // 1. TIMELINE TAPE CHART LOGIC
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = $start->copy()->addDays($this->daysCount - 1)->endOfDay();

        $timelineDates = [];
        for ($i = 0; $i < $this->daysCount; $i++) {
            $d = $start->copy()->addDays($i);
            $season = SeasonService::getSeasonInfo($d);
            $timelineDates[] = [
                'date' => $d->toDateString(),
                'day_name' => $d->locale('id')->isoFormat('dd'),
                'full_day' => $d->locale('id')->isoFormat('dddd'),
                'day_number' => $d->format('d/m'),
                'is_today' => $d->isToday(),
                'is_weekend' => $d->isWeekend(),
                'is_high_season' => $season['is_high_season'],
                'season_type' => $season['season_type'],
                'season_label' => $season['label'],
                'holiday_name' => $season['holiday_name'],
            ];
        }

        // Rooms & Allocations
        $rooms = Room::with('roomType')->orderBy('room_number')->get();
        $allocations = ReservationRoom::with(['reservation.customer', 'reservation.roomType'])
            ->whereDate('assigned_from', '<=', $end->toDateString())
            ->whereDate('assigned_to', '>=', $start->toDateString())
            ->whereHas('reservation', function ($q) {
                $q->whereNotIn('status', ['cancelled', 'expired']);
            })
            ->get();
        $allocationsByRoom = $allocations->groupBy('room_id');

        // Unassigned room reservations
        $unassignedReservations = Reservation::with(['customer', 'roomType'])
            ->whereDoesntHave('allocatedRooms')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->whereDate('check_in_date', '<=', $end->toDateString())
            ->whereDate('check_out_date', '>=', $start->toDateString())
            ->get();

        // Meeting Rooms & Bookings
        $meetingRooms = MeetingRoom::where('is_active', true)->orderBy('name')->get();
        $meetingBookings = MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage'])
            ->whereDate('event_date', '<=', $end->toDateString())
            ->whereDate('event_date', '>=', $start->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy('meeting_room_id');

        // Dining Areas & Bookings
        $dbAreas = DiningReservation::distinct()->pluck('table_number_or_area')->filter()->values()->all();
        $defaultAreas = ['Area Utama Restoran', 'Saung Lesehan 1', 'Saung Lesehan 2', 'Ruang VIP Resto'];
        $diningAreas = array_values(array_unique(array_merge($defaultAreas, $dbAreas)));

        $diningBookings = DiningReservation::with(['customer', 'diningPackage'])
            ->whereDate('reservation_date', '<=', $end->toDateString())
            ->whereDate('reservation_date', '>=', $start->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy('table_number_or_area');

        // 2. AGENDA VIEW (JADWAL HARIAN) LOGIC
        $schedules = collect();
        if ($this->activeView === 'agenda') {
            $aDate = $this->agendaDate;

            if (in_array($this->typeFilter, ['all', 'rooms'])) {
                $rBookings = Reservation::with(['customer', 'roomType', 'allocatedRooms.room'])
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('check_in_date', '<=', $aDate)
                    ->whereDate('check_out_date', '>=', $aDate)
                    ->get()
                    ->map(fn($r) => [
                        'category' => 'kamar',
                        'type' => $r->check_in_date->toDateString() === $aDate ? 'Check-In' : ($r->check_out_date->toDateString() === $aDate ? 'Check-Out' : 'In-House'),
                        'title' => "{$r->customer->name} ({$r->roomType->name})",
                        'subtitle' => $r->allocatedRooms->isNotEmpty() ? 'Kamar: ' . $r->allocatedRooms->pluck('room.room_number')->join(', ') : 'Belum plot kamar',
                        'time' => $r->check_in_date->toDateString() === $aDate ? 'Check-In (14:00)' : ($r->check_out_date->toDateString() === $aDate ? 'Check-Out (12:00)' : 'Menginap'),
                        'ref' => $r->reservation_number,
                        'status' => $r->status,
                        'total' => $r->total_amount,
                        'paid' => $r->paid_amount,
                        'id' => $r->id,
                        'res_type' => 'room',
                    ]);
                $schedules = $schedules->concat($rBookings);
            }

            if (in_array($this->typeFilter, ['all', 'meeting'])) {
                $mBookingsAgenda = MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage'])
                    ->whereDate('event_date', $aDate)
                    ->where('status', '!=', 'cancelled')
                    ->get()
                    ->map(fn($m) => [
                        'category' => 'meeting',
                        'type' => 'Meeting & Acara',
                        'title' => $m->event_name,
                        'subtitle' => "{$m->meetingRoom->name} · {$m->pax} Pax ({$m->customer->name})",
                        'time' => "{$m->start_time} - {$m->end_time} WIB",
                        'ref' => $m->reservation_number,
                        'status' => $m->status,
                        'total' => $m->total_amount,
                        'paid' => $m->paid_amount,
                        'id' => $m->id,
                        'res_type' => 'meeting',
                    ]);
                $schedules = $schedules->concat($mBookingsAgenda);
            }

            if (in_array($this->typeFilter, ['all', 'dining'])) {
                $dBookingsAgenda = DiningReservation::with(['customer', 'diningPackage'])
                    ->whereDate('reservation_date', $aDate)
                    ->where('status', '!=', 'cancelled')
                    ->get()
                    ->map(fn($d) => [
                        'category' => 'dining',
                        'type' => 'Restoran Syariah',
                        'title' => $d->diningPackage ? $d->diningPackage->name : 'Pesanan Ala Carte',
                        'subtitle' => "Meja: {$d->table_number_or_area} · {$d->pax} Orang ({$d->customer->name})",
                        'time' => "{$d->time_slot} WIB",
                        'ref' => $d->reservation_number,
                        'status' => $d->status,
                        'total' => $d->total_amount,
                        'paid' => $d->paid_amount,
                        'id' => $d->id,
                        'res_type' => 'dining',
                    ]);
                $schedules = $schedules->concat($dBookingsAgenda);
            }
        }

        // Details for Modals
        $selectedReservation = null;
        $selectedMeeting = null;
        $selectedDining = null;

        if ($this->selectedReservationId) {
            if ($this->selectedReservationType === 'meeting') {
                $selectedMeeting = MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage', 'payments'])->find($this->selectedReservationId);
            } elseif ($this->selectedReservationType === 'dining') {
                $selectedDining = DiningReservation::with(['customer', 'diningPackage', 'payments'])->find($this->selectedReservationId);
            } else {
                $selectedReservation = Reservation::with(['customer', 'roomType', 'allocatedRooms.room', 'payments'])->find($this->selectedReservationId);
            }
        }

        return view('livewire.unified-reservation-calendar', [
            'timelineDates' => $timelineDates,
            'rooms' => $rooms,
            'allocationsByRoom' => $allocationsByRoom,
            'unassignedReservations' => $unassignedReservations,
            'meetingRooms' => $meetingRooms,
            'meetingBookings' => $meetingBookings,
            'diningAreas' => $diningAreas,
            'diningBookings' => $diningBookings,
            'selectedReservation' => $selectedReservation,
            'selectedMeeting' => $selectedMeeting,
            'selectedDining' => $selectedDining,
            'schedules' => $schedules,
            'currentPeriodLabel' => $start->locale('id')->isoFormat('D MMMM Y') . ' - ' . $end->locale('id')->isoFormat('D MMMM Y'),
            'agendaCurrentDate' => Carbon::parse($this->agendaDate),
        ]);
    }
}
