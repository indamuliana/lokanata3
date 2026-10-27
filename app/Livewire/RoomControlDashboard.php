<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\DiningReservation;
use App\Models\MeetingReservation;
use App\Models\PmrRequest;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomWebAllotment;
use App\Services\BillingService;
use App\Services\FrontOfficeService;
use App\Services\SeasonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RoomControlDashboard extends Component
{
    public string $selectedFloor = 'all';
    public string $selectedStatus = 'all';
    public string $lastUpdated = '';
    public ?string $expandedSummary = null;

    // Action Modals State
    public bool $showCheckInModal = false;
    public bool $showCheckOutModal = false;
    public bool $showExtendModal = false;
    public bool $showMoveModal = false;
    public bool $showCleaningModal = false;
    public bool $showPmrModal = false;
    public bool $showMaintenanceModal = false;
    public bool $showDetailModal = false;
    public ?int $detailReservationId = null;

    // Plot Room Modal State (Unallocated / Website Booking)
    public bool $showPlotModal = false;
    public ?int $plotReservationId = null;
    public ?int $plotTargetRoomId = null;
    public bool $plotMarkConfirmed = true;
    public bool $plotMarkPaid = false;
    public bool $plotDirectCheckIn = false;

    // Setting Order via Web Modal State
    public bool $showWebOrderModal = false;
    public string $webOrderStartDate = '';
    public string $webOrderEndDate = '';
    public array $selectedWebRooms = [];
    public array $roomTypeWebStatus = [];

    public ?int $selectedRoomId = null;
    public ?int $selectedReservationId = null;

    // Form fields
    public string $extend_checkout_date = '';
    public ?int $target_room_id = null;
    public string $move_reason = '';
    public int $move_price_adjustment = 0;
    public string $new_cleaning_status = 'clean';
    public string $pmr_notes = '';
    public string $pmr_priority = 'normal';
    public string $maintenance_description = '';
    public string $maintenance_urgency = 'medium';

    public function mount(): void
    {
        $this->refreshRooms();
    }

    public function refreshRooms(): void
    {
        $this->lastUpdated = now()->format('H:i:s');
    }

    public function toggleSummary(string $tab): void
    {
        $this->expandedSummary = $this->expandedSummary === $tab ? null : $tab;
    }

    public function openDetailModal(int $reservationId): void
    {
        $this->detailReservationId = $reservationId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailReservationId = null;
    }

    // --- Action Openers ---

    public function openCheckIn(int $reservationId, ?int $roomId = null): void
    {
        $this->authorize('checkin_checkout.manage');
        $this->resetErrorBag();
        try {
            $reservation = Reservation::findOrFail($reservationId);
            $room = $roomId ? Room::findOrFail($roomId) : null;
            FrontOfficeService::checkIn($reservation, $room);
            $this->refreshRooms();
            session()->flash('message', "Check-in reservasi #{$reservation->reservation_number} berhasil.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openCheckOut(int $reservationId): void
    {
        $this->authorize('checkin_checkout.manage');
        $this->resetErrorBag();
        try {
            $reservation = Reservation::findOrFail($reservationId);
            FrontOfficeService::checkOut($reservation);
            $this->refreshRooms();
            session()->flash('message', "Checkout reservasi #{$reservation->reservation_number} berhasil. Kamar otomatis berstatus kotor.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openExtendModal(int $reservationId): void
    {
        $this->authorize('reservations.manage');
        $this->resetErrorBag();
        $this->selectedReservationId = $reservationId;
        $res = Reservation::findOrFail($reservationId);
        $this->extend_checkout_date = $res->check_out_date->addDay()->toDateString();
        $this->showExtendModal = true;
    }

    public function saveExtend(): void
    {
        $this->authorize('reservations.manage');
        $this->validate([
            'extend_checkout_date' => 'required|date',
        ]);

        try {
            $reservation = Reservation::findOrFail($this->selectedReservationId);
            FrontOfficeService::extendStay($reservation, $this->extend_checkout_date);
            $this->showExtendModal = false;
            $this->refreshRooms();
            session()->flash('message', "Perpanjangan inap reservasi #{$reservation->reservation_number} berhasil.");
        } catch (\Exception $e) {
            $this->addError('extend', $e->getMessage());
        }
    }

    public function openMoveModal(int $reservationId, int $currentRoomId): void
    {
        $this->authorize('reservations.manage');
        $this->resetErrorBag();
        $this->selectedReservationId = $reservationId;
        $this->selectedRoomId = $currentRoomId;
        $this->target_room_id = null;
        $this->move_reason = '';
        $this->move_price_adjustment = 0;
        $this->showMoveModal = true;
    }

    public function saveMove(): void
    {
        $this->authorize('reservations.manage');
        $this->validate([
            'target_room_id' => 'required|exists:rooms,id',
            'move_reason' => 'required|string|min:3',
        ]);

        try {
            $reservation = Reservation::findOrFail($this->selectedReservationId);
            $targetRoom = Room::findOrFail($this->target_room_id);
            FrontOfficeService::roomMove($reservation, $targetRoom, $this->move_reason, $this->move_price_adjustment);
            $this->showMoveModal = false;
            $this->refreshRooms();
            session()->flash('message', "Pindah kamar ke #{$targetRoom->room_number} berhasil.");
        } catch (\Exception $e) {
            $this->addError('move', $e->getMessage());
        }
    }

    public function openCleaningModal(int $roomId): void
    {
        $this->authorize('room_status.edit');
        $this->resetErrorBag();
        $this->selectedRoomId = $roomId;
        $room = Room::findOrFail($roomId);
        $this->new_cleaning_status = $room->cleaning_status;
        $this->showCleaningModal = true;
    }

    public function saveCleaning(): void
    {
        $this->authorize('room_status.edit');
        try {
            $room = Room::findOrFail($this->selectedRoomId);
            FrontOfficeService::updateCleaningStatus($room, $this->new_cleaning_status);
            $this->showCleaningModal = false;
            $this->refreshRooms();
            session()->flash('message', "Status kebersihan kamar {$room->room_number} diperbarui.");
        } catch (\Exception $e) {
            $this->addError('cleaning', $e->getMessage());
        }
    }

    public function openPmrModal(int $roomId, ?int $reservationId = null): void
    {
        $this->authorize('maintenance.manage');
        $this->resetErrorBag();
        $this->selectedRoomId = $roomId;
        $this->selectedReservationId = $reservationId;
        $this->pmr_notes = '';
        $this->pmr_priority = 'normal';
        $this->showPmrModal = true;
    }

    public function savePmr(): void
    {
        $this->authorize('maintenance.manage');
        $this->validate([
            'pmr_notes' => 'required|string|min:3',
        ]);

        try {
            $room = Room::findOrFail($this->selectedRoomId);
            FrontOfficeService::createPmr($room, $this->pmr_notes, $this->pmr_priority, $this->selectedReservationId);
            $this->showPmrModal = false;
            $this->refreshRooms();
            session()->flash('message', "Permintaan PMR untuk kamar {$room->room_number} telah dibuat.");
        } catch (\Exception $e) {
            $this->addError('pmr', $e->getMessage());
        }
    }

    public function completePmr(int $pmrId): void
    {
        $this->authorize('maintenance.manage');
        try {
            $pmr = PmrRequest::with('room')->findOrFail($pmrId);
            FrontOfficeService::completePmr($pmr, true);
            $this->refreshRooms();
            session()->flash('message', "Permintaan PMR kamar {$pmr->room->room_number} berhasil diselesaikan.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openMaintenanceModal(int $roomId): void
    {
        $this->authorize('maintenance.manage');
        $this->resetErrorBag();
        $this->selectedRoomId = $roomId;
        $this->maintenance_description = '';
        $this->maintenance_urgency = 'medium';
        $this->showMaintenanceModal = true;
    }

    public function saveMaintenance(): void
    {
        $this->authorize('maintenance.manage');
        $this->validate([
            'maintenance_description' => 'required|string|min:5',
        ]);

        try {
            $room = Room::findOrFail($this->selectedRoomId);
            FrontOfficeService::reportMaintenance($room, $this->maintenance_description, $this->maintenance_urgency);
            $this->showMaintenanceModal = false;
            $this->refreshRooms();
            session()->flash('message', "Laporan kerusakan kamar {$room->room_number} tercatat. Kamar kini berstatus maintenance.");
        } catch (\Exception $e) {
            $this->addError('maintenance', $e->getMessage());
        }
    }

    public function openPlotModal(int $reservationId, bool $directCheckIn = false): void
    {
        $this->authorize('room_plot.edit');
        if ($directCheckIn) {
            $this->authorize('checkin_checkout.manage');
        }
        $this->resetErrorBag();
        $this->plotReservationId = $reservationId;
        $this->plotDirectCheckIn = $directCheckIn;
        $this->plotTargetRoomId = null;
        $this->plotMarkConfirmed = true;
        $this->plotMarkPaid = false;
        $this->showPlotModal = true;
    }

    public function closePlotModal(): void
    {
        $this->showPlotModal = false;
        $this->plotReservationId = null;
        $this->plotTargetRoomId = null;
    }

    public function savePlotRoom(bool $directCheckIn = false): void
    {
        $this->authorize('room_plot.edit');
        if ($directCheckIn) {
            $this->authorize('checkin_checkout.manage');
        }
        $this->validate([
            'plotTargetRoomId' => 'required|exists:rooms,id',
        ], [
            'plotTargetRoomId.required' => 'Pilih salah satu nomor kamar fisik yang tersedia.',
        ]);

        try {
            DB::transaction(function () use ($directCheckIn) {
                $reservation = Reservation::findOrFail($this->plotReservationId);
                $targetRoom = Room::findOrFail($this->plotTargetRoomId);

                // 1. Verify availability
                if (!$targetRoom->isAvailable($reservation->check_in_date->toDateString(), $reservation->check_out_date->toDateString(), $reservation->id)) {
                    throw new \InvalidArgumentException("Kamar {$targetRoom->room_number} tidak tersedia pada tanggal {$reservation->check_in_date->format('d/m/Y')} s/d {$reservation->check_out_date->format('d/m/Y')}.");
                }

                // 2. Plot room allocation
                ReservationRoom::updateOrCreate(
                    ['reservation_id' => $reservation->id],
                    [
                        'room_id' => $targetRoom->id,
                        'assigned_from' => $reservation->check_in_date,
                        'assigned_to' => $reservation->check_out_date,
                        'notes' => 'Alokasi kamar dari Dashboard Kendali Kamar',
                    ]
                );

                // 3. Mark Confirmed if selected
                if ($this->plotMarkConfirmed && $reservation->status === 'pending') {
                    $reservation->status = 'confirmed';
                    $reservation->expires_at = null;
                }

                // 4. Mark Paid if selected
                if ($this->plotMarkPaid && ($reservation->total_amount - $reservation->paid_amount) > 0) {
                    BillingService::recordPayment(
                        $reservation,
                        $reservation->total_amount - $reservation->paid_amount,
                        'bank_transfer',
                        'Bank Transfer',
                        'Konfirmasi Booking Website',
                        null,
                        'Pelunasan otomatis saat plot kamar via Dashboard'
                    );
                } else {
                    $reservation->save();
                }

                \App\Models\AuditLog::record(
                    'room_allocation',
                    $reservation,
                    null,
                    ['room_id' => $targetRoom->id, 'room_number' => $targetRoom->room_number],
                    "Alokasi kamar fisik #{$targetRoom->room_number} untuk reservasi #{$reservation->reservation_number}"
                );

                // 5. Direct Check-in if requested
                if ($directCheckIn) {
                    FrontOfficeService::checkIn($reservation, $targetRoom);
                    session()->flash('message', "Kamar {$targetRoom->room_number} berhasil dialokasikan dan tamu {$reservation->customer->name} langsung di-check-in.");
                } else {
                    session()->flash('message', "Kamar {$targetRoom->room_number} berhasil dialokasikan untuk reservasi #{$reservation->reservation_number} ({$reservation->customer->name}).");
                }
            });

            $this->closePlotModal();
            $this->refreshRooms();
        } catch (\Exception $e) {
            $this->addError('plot', $e->getMessage());
        }
    }

    // --- FITUR SETTING ORDER VIA WEB (ALOKASI KAMAR FISIK PENJUALAN WEBSITE) ---
    public function openWebOrderModal(): void
    {
        $this->authorize('room_status.edit');
        $this->resetErrorBag();
        $this->webOrderStartDate = now()->toDateString();
        $this->webOrderEndDate = now()->addDay()->toDateString();
        $this->loadWebOrderData();
        $this->showWebOrderModal = true;
    }

    public function closeWebOrderModal(): void
    {
        $this->showWebOrderModal = false;
    }

    public function updatedWebOrderStartDate(): void
    {
        if ($this->webOrderStartDate && $this->webOrderEndDate && $this->webOrderStartDate >= $this->webOrderEndDate) {
            $this->webOrderEndDate = Carbon::parse($this->webOrderStartDate)->addDay()->toDateString();
        }
        $this->loadWebOrderData();
    }

    public function updatedWebOrderEndDate(): void
    {
        if ($this->webOrderStartDate && $this->webOrderEndDate && $this->webOrderEndDate <= $this->webOrderStartDate) {
            $this->webOrderStartDate = Carbon::parse($this->webOrderEndDate)->subDay()->toDateString();
        }
        $this->loadWebOrderData();
    }

    public function loadWebOrderData(): void
    {
        if (!$this->webOrderStartDate || !$this->webOrderEndDate) {
            return;
        }

        // 1. Load room types active status
        $this->roomTypeWebStatus = RoomType::pluck('is_active', 'id')->mapWithKeys(fn ($val, $k) => [(int) $k => (bool) $val])->toArray();

        // 2. Load existing allotments for the start date
        $anyRecordExists = RoomWebAllotment::whereDate('allotment_date', $this->webOrderStartDate)->exists();

        if ($anyRecordExists) {
            $this->selectedWebRooms = RoomWebAllotment::whereDate('allotment_date', $this->webOrderStartDate)
                ->where('is_enabled', true)
                ->pluck('room_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        } else {
            // Default: select all active available rooms for the dates
            $this->selectedWebRooms = Room::where('operational_status', 'active')
                ->get()
                ->filter(fn (Room $r) => $r->isAvailable($this->webOrderStartDate, $this->webOrderEndDate))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        }
    }

    public function toggleAllRoomsForType(int $typeId, bool $select): void
    {
        $availableTypeRoomIds = Room::where('room_type_id', $typeId)
            ->where('operational_status', 'active')
            ->get()
            ->filter(fn (Room $r) => $r->isAvailable($this->webOrderStartDate, $this->webOrderEndDate))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if ($select) {
            $this->selectedWebRooms = array_values(array_unique(array_merge($this->selectedWebRooms, $availableTypeRoomIds)));
        } else {
            $this->selectedWebRooms = array_values(array_diff($this->selectedWebRooms, $availableTypeRoomIds));
        }
    }

    public function saveWebOrderSettings(): void
    {
        $this->authorize('room_status.edit');
        $this->validate([
            'webOrderStartDate' => 'required|date',
            'webOrderEndDate' => 'required|date|after:webOrderStartDate',
        ]);

        try {
            DB::transaction(function () {
                // 1. Update room type website active status
                foreach ($this->roomTypeWebStatus as $typeId => $isActive) {
                    RoomType::where('id', $typeId)->update(['is_active' => (bool) $isActive]);
                }

                // 2. Save physical room allotments per date in the range
                $current = Carbon::parse($this->webOrderStartDate);
                $end = Carbon::parse($this->webOrderEndDate);
                $allRooms = Room::where('operational_status', 'active')->get();

                while ($current->lt($end)) {
                    $dateStr = $current->toDateString();
                    $nextDayStr = Carbon::parse($dateStr)->addDay()->toDateString();

                    foreach ($allRooms as $room) {
                        $isBooked = !$room->isAvailable($dateStr, $nextDayStr);
                        // If room is booked, force is_enabled to false
                        $isEnabled = !$isBooked && in_array((int) $room->id, array_map('intval', $this->selectedWebRooms));

                        $existing = RoomWebAllotment::whereDate('allotment_date', $dateStr)
                            ->where('room_id', $room->id)
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'is_enabled' => $isEnabled,
                                'created_by' => auth()->id(),
                            ]);
                        } else {
                            RoomWebAllotment::create([
                                'allotment_date' => $dateStr,
                                'room_id' => $room->id,
                                'is_enabled' => $isEnabled,
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }

                    $current->addDay();
                }

                AuditLog::record(
                    'update_web_order_allotment',
                    null,
                    null,
                    [
                        'start_date' => $this->webOrderStartDate,
                        'end_date' => $this->webOrderEndDate,
                        'selected_rooms' => $this->selectedWebRooms,
                        'types_active' => $this->roomTypeWebStatus,
                    ],
                    "Perbarui alokasi kamar web rentang {$this->webOrderStartDate} s/d {$this->webOrderEndDate}"
                );
            });

            $this->showWebOrderModal = false;
            $this->refreshRooms();
            session()->flash('message', 'Pengaturan kamar fisik untuk pemesanan website berhasil disimpan!');
        } catch (\Exception $e) {
            $this->addError('web_order', $e->getMessage());
        }
    }

    public function getWebOrderRoomTypesProperty()
    {
        if (!$this->showWebOrderModal || !$this->webOrderStartDate || !$this->webOrderEndDate) {
            return collect();
        }

        return RoomType::with(['rooms' => function ($q) {
            $q->orderBy('room_number');
        }])->get()->map(function ($type) {
            $rooms = $type->rooms->map(function ($room) {
                $isMaintenance = in_array($room->operational_status, ['maintenance', 'disabled']);
                $isAvailable = $room->isAvailable($this->webOrderStartDate, $this->webOrderEndDate);
                $isBooked = !$isAvailable && !$isMaintenance;

                $bookedRes = null;
                if ($isBooked) {
                    $bookedRes = Reservation::whereHas('allocatedRooms', fn ($q) => $q->where('room_id', $room->id))
                        ->whereNotIn('status', ['cancelled', 'expired'])
                        ->where(function ($q) {
                            $q->whereDate('check_in_date', '<', $this->webOrderEndDate)
                              ->whereDate('check_out_date', '>', $this->webOrderStartDate);
                        })
                        ->with('customer')
                        ->first();
                }

                return [
                    'room' => $room,
                    'isMaintenance' => $isMaintenance,
                    'isBooked' => $isBooked,
                    'bookedReservation' => $bookedRes,
                    'canSelect' => !$isMaintenance && !$isBooked,
                ];
            });

            return [
                'type' => $type,
                'rooms' => $rooms,
                'availableCount' => $rooms->where('canSelect', true)->count(),
                'selectedCount' => $rooms->where('canSelect', true)->filter(fn ($r) => in_array((int) $r['room']->id, array_map('intval', $this->selectedWebRooms)))->count(),
            ];
        });
    }

    public function render()
    {
        $today = now()->toDateString();

        $roomsQuery = Room::with(['roomType', 'allocations.reservation.customer'])->orderBy('room_number');

        if ($this->selectedFloor !== 'all') {
            $roomsQuery->where('floor', $this->selectedFloor);
        }

        $allRooms = $roomsQuery->get()->map(function (Room $room) use ($today) {
            // Find current active staying reservation
            $activeAllocation = $room->allocations->first(function ($alloc) use ($today) {
                return $alloc->reservation
                    && $alloc->reservation->status === 'checked_in'
                    && $alloc->assigned_from->toDateString() <= $today
                    && $alloc->assigned_to->toDateString() >= $today;
            });

            // Find reservation checking in today
            $checkInTodayReservation = Reservation::whereIn('status', ['confirmed', 'pending'])
                ->whereDate('check_in_date', $today)
                ->where(function ($q) use ($room) {
                    $q->where('room_type_id', $room->room_type_id)
                      ->whereHas('allocatedRooms', fn ($aq) => $aq->where('room_id', $room->id));
                })
                ->first();

            // High priority housekeeping: room is dirty AND guest checking in today
            $isHighPriority = in_array($room->cleaning_status, ['dirty', 'cleaning']) && ($checkInTodayReservation !== null);

            return [
                'room' => $room,
                'activeReservation' => $activeAllocation?->reservation,
                'checkInTodayReservation' => $checkInTodayReservation,
                'isHighPriority' => $isHighPriority,
                'isOccupied' => ($activeAllocation !== null),
            ];
        });

        // Filter status
        if ($this->selectedStatus === 'occupied') {
            $allRooms = $allRooms->filter(fn ($item) => $item['isOccupied']);
        } elseif ($this->selectedStatus === 'available') {
            $allRooms = $allRooms->filter(fn ($item) => !$item['isOccupied'] && $item['room']->operational_status === 'active' && in_array($item['room']->cleaning_status, ['clean', 'inspected']));
        } elseif ($this->selectedStatus === 'dirty') {
            $allRooms = $allRooms->filter(fn ($item) => in_array($item['room']->cleaning_status, ['dirty', 'cleaning']));
        } elseif ($this->selectedStatus === 'maintenance') {
            $allRooms = $allRooms->filter(fn ($item) => $item['room']->operational_status === 'maintenance');
        }

        // Available candidate rooms for move
        $candidateRooms = Room::where('operational_status', 'active')
            ->whereIn('cleaning_status', ['clean', 'inspected'])
            ->where('id', '!=', $this->selectedRoomId)
            ->get();

        // Unallocated reservations (Web bookings or manual reservations without physical room)
        $unallocatedReservations = Reservation::whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_out_date', '>=', $today)
            ->doesntHave('allocatedRooms')
            ->with(['customer', 'roomType'])
            ->orderBy('check_in_date')
            ->get();

        $plotReservation = $this->plotReservationId ? Reservation::with(['customer', 'roomType'])->find($this->plotReservationId) : null;
        $plotAvailableRooms = [];
        if ($plotReservation) {
            $plotAvailableRooms = Room::where('room_type_id', $plotReservation->room_type_id)
                ->where('operational_status', 'active')
                ->orderBy('room_number')
                ->get()
                ->filter(fn (Room $r) => $r->isAvailable(
                    $plotReservation->check_in_date->toDateString(),
                    $plotReservation->check_out_date->toDateString(),
                    $plotReservation->id
                ))
                ->values();
        }

        return view('livewire.room-control-dashboard', [
            'roomItems' => $allRooms,
            'candidateRooms' => $candidateRooms,
            'todaySeason' => SeasonService::getSeasonInfo($today),
            'todayPendingCheckIns' => Reservation::whereIn('status', ['confirmed', 'pending'])->whereDate('check_in_date', $today)->with(['customer', 'roomType', 'allocatedRooms.room'])->orderBy('check_in_time')->get(),
            'unallocatedReservations' => $unallocatedReservations,
            'plotReservation' => $plotReservation,
            'plotAvailableRooms' => $plotAvailableRooms,
            'activePmrs' => PmrRequest::where('status', 'requested')->with(['room', 'reservation.customer'])->get(),
            'todayMeetings' => MeetingReservation::whereDate('event_date', $today)->where('status', '!=', 'cancelled')->with(['customer', 'meetingRoom'])->orderBy('start_time')->get(),
            'todayDinings' => DiningReservation::whereDate('reservation_date', $today)->where('status', '!=', 'cancelled')->with(['customer', 'diningPackage'])->orderBy('time_slot')->get(),
            'detailReservation' => $this->detailReservationId ? Reservation::with(['customer', 'roomType', 'allocatedRooms.room', 'serviceItems.additionalService'])->find($this->detailReservationId) : null,
        ]);
    }
}
