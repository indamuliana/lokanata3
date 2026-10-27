<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\MeetingPackage;
use App\Models\MeetingReservation;
use App\Models\MeetingReservationItem;
use App\Models\MeetingRoom;
use App\Models\MeetingService;
use App\Services\MeetingRoomService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MeetingRoomManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $viewTab = 'active'; // 'active' vs 'history'
    public string $statusFilter = 'all';
    public string $dateFilter = '';

    // Create Modal Form
    public bool $showCreateModal = false;
    public int $meeting_room_id = 0;
    public ?int $meeting_package_id = null;
    public string $event_name = '';
    public string $event_date = '';
    public string $start_time = '09:00';
    public string $end_time = '13:00';
    public int $pax = 10;
    public string $pricing_type = 'all_in';
    public int $custom_base_price = 0;
    public int $additional_charges = 0;
    public int $discount_amount = 0;
    public int $initial_paid_amount = 0;
    public string $payment_method = 'cash';
    public string $bank_account = '';
    public string $reference_number = '';
    public string $notes = '';
    public string $facility_notes = '';
    public array $service_items = []; // [['meeting_service_id' => null, 'name' => '', 'quantity' => 1, 'unit_price' => 0, 'subtotal' => 0]]

    // Customer
    public string $customer_name = '';
    public string $customer_phone = '';
    public string $customer_company = '';

    // Payment Modal Form
    public bool $showPaymentModal = false;
    public ?int $selectedReservationId = null;
    public int $payment_amount = 0;
    public string $payment_notes = '';

    // Detail & Addon Susulan Modal Form
    public bool $showDetailModal = false;
    public ?int $detailReservationId = null;
    public string $post_service_mode = 'master'; // 'master' or 'manual'
    public ?int $post_service_id = null;
    public string $post_item_name = '';
    public int $post_item_qty = 1;
    public int $post_item_price = 0;

    // Master Settings Modal Form
    public bool $showSettingsModal = false;
    public string $settingsTab = 'rooms'; // 'rooms', 'packages', or 'services'
    public string $roomsFilter = 'active'; // 'active' or 'trashed'
    public string $packagesFilter = 'active'; // 'active' or 'trashed'
    public string $servicesFilter = 'active'; // 'active' or 'trashed'
    
    // Room Form Properties
    public ?int $editing_room_id = null;
    public string $room_name = '';
    public int $room_capacity = 30;
    public int $room_hourly_rate = 250000;
    public ?int $room_high_hourly_rate = 250000;
    public int $room_halfday_rate = 900000;
    public ?int $room_high_halfday_rate = 900000;
    public int $room_fullday_rate = 1600000;
    public ?int $room_high_fullday_rate = 1600000;
    public bool $is_room_high_season_same = true;
    public string $room_facilities = '';
    public bool $room_is_active = true;

    // Package Form Properties
    public ?int $editing_pkg_id = null;
    public string $pkg_name = '';
    public int $pkg_price_per_pax = 65000;
    public ?int $pkg_high_price_per_pax = 65000;
    public bool $is_pkg_high_season_same = true;
    public int $pkg_min_pax = 10;
    public string $pkg_description = '';
    public bool $pkg_is_active = true;

    // Additional Service Form Properties (Master)
    public ?int $editing_svc_id = null;
    public string $svc_name = '';
    public int $svc_price = 0;
    public string $svc_unit = 'paket';
    public bool $svc_is_active = true;

    // Breakdown Preview & Availability status
    public array $breakdown = [];
    public bool $isAvailable = true;

    public function mount(): void
    {
        $this->event_date = now()->toDateString();
        $firstRoom = MeetingRoom::where('is_active', true)->first();
        $this->meeting_room_id = $firstRoom ? $firstRoom->id : 0;
        $this->updateCalculation();
    }

    public function updated($prop): void
    {
        if (in_array($prop, [
            'meeting_room_id', 'meeting_package_id', 'event_date', 'start_time',
            'end_time', 'pax', 'pricing_type', 'custom_base_price', 'additional_charges', 'discount_amount'
        ])) {
            $this->updateCalculation();
        }
    }

    public function updateCalculation(): void
    {
        if (!$this->meeting_room_id || !$this->event_date || !$this->start_time || !$this->end_time) {
            return;
        }

        $room = MeetingRoom::find($this->meeting_room_id);
        if (!$room) return;

        // Check availability
        $this->isAvailable = MeetingRoomService::checkAvailability(
            $room,
            $this->event_date,
            $this->start_time,
            $this->end_time
        );

        $package = $this->meeting_package_id ? MeetingPackage::find($this->meeting_package_id) : null;

        $this->breakdown = MeetingRoomService::calculatePricing(
            $room,
            $this->pricing_type,
            $this->pax,
            $this->start_time,
            $this->end_time,
            $package,
            $this->custom_base_price,
            $this->additional_charges,
            $this->discount_amount,
            $this->event_date
        );
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->facility_notes = '';
        $this->notes = '';
        $this->service_items = [];
        $this->additional_charges = 0;
        $this->initial_paid_amount = 0;
        $this->updateCalculation();
        $this->showCreateModal = true;
    }

    public function addServiceRow(): void
    {
        $this->addServiceRowFromMaster();
    }

    public function addServiceRowFromMaster(): void
    {
        $firstSvc = MeetingService::where('is_active', true)->orderBy('name')->first();
        $this->service_items[] = [
            'type' => 'master',
            'meeting_service_id' => $firstSvc?->id,
            'name' => $firstSvc?->name ?? '',
            'quantity' => 1,
            'unit_price' => $firstSvc?->default_price ?? 0,
            'subtotal' => $firstSvc?->default_price ?? 0,
        ];
        $this->recalculateServiceItems();
    }

    public function addServiceRowManual(): void
    {
        $this->service_items[] = [
            'type' => 'manual',
            'meeting_service_id' => null,
            'name' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ];
        $this->recalculateServiceItems();
    }

    public function removeServiceRow(int $index): void
    {
        unset($this->service_items[$index]);
        $this->service_items = array_values($this->service_items);
        $this->recalculateServiceItems();
    }

    public function selectMasterService(int $index, $serviceId): void
    {
        if (!empty($serviceId)) {
            $svc = MeetingService::find($serviceId);
            if ($svc) {
                $this->service_items[$index]['meeting_service_id'] = $svc->id;
                $this->service_items[$index]['name'] = $svc->name;
                $this->service_items[$index]['unit_price'] = $svc->default_price;
                $qty = max(1, (int) ($this->service_items[$index]['quantity'] ?? 1));
                $this->service_items[$index]['subtotal'] = $qty * $svc->default_price;
            }
        } else {
            $this->service_items[$index]['meeting_service_id'] = null;
        }
        $this->recalculateServiceItems();
    }

    public function updateServiceItem(int $index): void
    {
        if (isset($this->service_items[$index])) {
            $qty = max(1, (int) ($this->service_items[$index]['quantity'] ?? 1));
            $price = max(0, (int) ($this->service_items[$index]['unit_price'] ?? 0));
            $this->service_items[$index]['quantity'] = $qty;
            $this->service_items[$index]['unit_price'] = $price;
            $this->service_items[$index]['subtotal'] = $qty * $price;
        }
        $this->recalculateServiceItems();
    }

    public function recalculateServiceItems(): void
    {
        $total = 0;
        foreach ($this->service_items as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(0, (int) ($item['unit_price'] ?? 0));
            $total += ($qty * $price);
        }
        $this->additional_charges = $total;
        $this->updateCalculation();
    }

    public function saveReservation(): void
    {
        $this->validate([
            'meeting_room_id' => 'required|exists:meeting_rooms,id',
            'event_name' => 'required|string|min:3',
            'event_date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'pax' => 'required|integer|min:1',
            'customer_name' => 'required|string|min:3',
            'customer_phone' => 'required|string|min:8',
        ]);

        try {
            $reservation = MeetingRoomService::createReservation([
                'meeting_room_id' => $this->meeting_room_id,
                'meeting_package_id' => $this->meeting_package_id,
                'event_name' => $this->event_name,
                'event_date' => $this->event_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'pax' => $this->pax,
                'pricing_type' => $this->pricing_type,
                'custom_base_price' => $this->custom_base_price,
                'additional_charges' => $this->additional_charges,
                'discount_amount' => $this->discount_amount,
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone,
                'customer_company' => $this->customer_company,
                'initial_paid_amount' => $this->initial_paid_amount,
                'payment_method' => $this->payment_method,
                'bank_account' => $this->bank_account,
                'reference_number' => $this->reference_number,
                'notes' => $this->notes,
                'facility_notes' => $this->facility_notes,
                'items' => $this->service_items,
            ]);

            $this->showCreateModal = false;
            session()->flash('message', "Reservasi meeting #{$reservation->reservation_number} berhasil dibuat.");
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function openPaymentModal(int $resId): void
    {
        $this->resetErrorBag();
        $this->selectedReservationId = $resId;
        $res = MeetingReservation::findOrFail($resId);
        $this->payment_amount = $res->balance;
        $this->payment_method = 'cash';
        $this->bank_account = '';
        $this->reference_number = '';
        $this->payment_notes = '';
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $this->validate([
            'payment_amount' => 'required|integer|min:1000',
        ]);

        try {
            $res = MeetingReservation::findOrFail($this->selectedReservationId);
            MeetingRoomService::recordPayment(
                $res,
                $this->payment_amount,
                $this->payment_method,
                $this->bank_account ?: null,
                $this->reference_number ?: null,
                $this->payment_notes ?: null
            );

            $this->showPaymentModal = false;
            session()->flash('message', "Pembayaran meeting #{$res->reservation_number} berhasil dicatat.");
        } catch (\Exception $e) {
            $this->addError('payment', $e->getMessage());
        }
    }

    public function cancelReservation(int $id): void
    {
        $res = MeetingReservation::findOrFail($id);
        if ($res->status === 'cancelled') return;

        $res->update(['status' => 'cancelled']);
        AuditLog::record('cancel_meeting_reservation', $res, null, ['status' => 'cancelled'], "Dibatalkan oleh staf");
        session()->flash('message', "Reservasi meeting #{$res->reservation_number} berhasil dibatalkan.");
    }

    public function openSettingsModal(): void
    {
        $this->resetRoomForm();
        $this->resetMeetingPackageForm();
        $this->resetServiceForm();
        $this->showSettingsModal = true;
    }

    public function resetRoomForm(): void
    {
        $this->editing_room_id = null;
        $this->room_name = '';
        $this->room_capacity = 30;
        $this->room_hourly_rate = 250000;
        $this->room_high_hourly_rate = 250000;
        $this->room_halfday_rate = 900000;
        $this->room_high_halfday_rate = 900000;
        $this->room_fullday_rate = 1600000;
        $this->room_high_fullday_rate = 1600000;
        $this->is_room_high_season_same = true;
        $this->room_facilities = '';
        $this->room_is_active = true;
        $this->resetErrorBag();
    }

    public function editRoom(int $id): void
    {
        $r = MeetingRoom::findOrFail($id);
        $this->editing_room_id = $r->id;
        $this->room_name = $r->name;
        $this->room_capacity = (int) $r->capacity;
        $this->room_hourly_rate = (int) $r->hourly_rate;
        $this->room_high_hourly_rate = $r->high_hourly_rate ?: (int) $r->hourly_rate;
        $this->room_halfday_rate = (int) $r->halfday_rate;
        $this->room_high_halfday_rate = $r->high_halfday_rate ?: (int) $r->halfday_rate;
        $this->room_fullday_rate = (int) $r->fullday_rate;
        $this->room_high_fullday_rate = $r->high_fullday_rate ?: (int) $r->fullday_rate;
        $this->is_room_high_season_same = empty($r->high_hourly_rate) || ($r->high_hourly_rate == $r->hourly_rate);
        $this->room_facilities = is_array($r->facilities) ? implode(', ', $r->facilities) : '';
        $this->room_is_active = (bool) $r->is_active;
        $this->settingsTab = 'rooms';
        $this->resetErrorBag();
    }

    public function saveRoom(): void
    {
        $this->validate([
            'room_name' => 'required|string|min:3|max:255',
            'room_capacity' => 'required|integer|min:1',
            'room_hourly_rate' => 'required|integer|min:0',
            'room_high_hourly_rate' => 'nullable|integer|min:0',
            'room_halfday_rate' => 'required|integer|min:0',
            'room_high_halfday_rate' => 'nullable|integer|min:0',
            'room_fullday_rate' => 'required|integer|min:0',
            'room_high_fullday_rate' => 'nullable|integer|min:0',
        ]);

        $facilitiesArray = array_values(array_filter(array_map('trim', explode(',', $this->room_facilities))));

        $highHourly = $this->is_room_high_season_same ? $this->room_hourly_rate : ($this->room_high_hourly_rate ?: $this->room_hourly_rate);
        $highHalfday = $this->is_room_high_season_same ? $this->room_halfday_rate : ($this->room_high_halfday_rate ?: $this->room_halfday_rate);
        $highFullday = $this->is_room_high_season_same ? $this->room_fullday_rate : ($this->room_high_fullday_rate ?: $this->room_fullday_rate);

        if ($this->editing_room_id) {
            $r = MeetingRoom::findOrFail($this->editing_room_id);
            $r->update([
                'name' => $this->room_name,
                'capacity' => $this->room_capacity,
                'hourly_rate' => $this->room_hourly_rate,
                'high_hourly_rate' => $highHourly,
                'halfday_rate' => $this->room_halfday_rate,
                'high_halfday_rate' => $highHalfday,
                'fullday_rate' => $this->room_fullday_rate,
                'high_fullday_rate' => $highFullday,
                'facilities' => $facilitiesArray,
                'is_active' => $this->room_is_active,
            ]);
            session()->flash('settings_message', "Tarif ruangan {$r->name} berhasil diperbarui.");
        } else {
            MeetingRoom::create([
                'name' => $this->room_name,
                'capacity' => $this->room_capacity,
                'hourly_rate' => $this->room_hourly_rate,
                'high_hourly_rate' => $highHourly,
                'halfday_rate' => $this->room_halfday_rate,
                'high_halfday_rate' => $highHalfday,
                'fullday_rate' => $this->room_fullday_rate,
                'high_fullday_rate' => $highFullday,
                'facilities' => $facilitiesArray,
                'is_active' => $this->room_is_active,
            ]);
            session()->flash('settings_message', "Ruang meeting {$this->room_name} berhasil ditambahkan.");
        }

        $this->resetRoomForm();
        $this->updateCalculation();
    }

    public function toggleRoomStatus(int $id): void
    {
        $r = MeetingRoom::findOrFail($id);
        $r->update(['is_active' => !$r->is_active]);
        session()->flash('settings_message', "Status ruangan {$r->name} diubah menjadi " . ($r->is_active ? 'Aktif' : 'Non-Aktif') . ".");
        $this->updateCalculation();
    }

    public function deleteRoom(int $id): void
    {
        $r = MeetingRoom::findOrFail($id);
        $r->delete();
        session()->flash('settings_message', "Ruang meeting {$r->name} telah dipindahkan ke arsip (Soft Delete).");
        $this->resetRoomForm();
        $this->updateCalculation();
    }

    public function restoreRoom(int $id): void
    {
        $r = MeetingRoom::onlyTrashed()->findOrFail($id);
        $r->restore();
        session()->flash('settings_message', "Ruang meeting {$r->name} berhasil dipulihkan dari arsip.");
        $this->updateCalculation();
    }

    public function resetMeetingPackageForm(): void
    {
        $this->editing_pkg_id = null;
        $this->pkg_name = '';
        $this->pkg_price_per_pax = 65000;
        $this->pkg_high_price_per_pax = 65000;
        $this->is_pkg_high_season_same = true;
        $this->pkg_min_pax = 10;
        $this->pkg_description = '';
        $this->pkg_is_active = true;
        $this->resetErrorBag();
    }

    public function editMeetingPackage(int $id): void
    {
        $pkg = MeetingPackage::findOrFail($id);
        $this->editing_pkg_id = $pkg->id;
        $this->pkg_name = $pkg->name;
        $this->pkg_price_per_pax = (int) $pkg->price_per_pax;
        $this->pkg_high_price_per_pax = $pkg->high_price_per_pax ?: (int) $pkg->price_per_pax;
        $this->is_pkg_high_season_same = empty($pkg->high_price_per_pax) || ($pkg->high_price_per_pax == $pkg->price_per_pax);
        $this->pkg_min_pax = (int) $pkg->min_pax;
        $this->pkg_description = $pkg->description ?? '';
        $this->pkg_is_active = (bool) $pkg->is_active;
        $this->settingsTab = 'packages';
        $this->resetErrorBag();
    }

    public function saveMeetingPackage(): void
    {
        $this->validate([
            'pkg_name' => 'required|string|min:3|max:255',
            'pkg_price_per_pax' => 'required|integer|min:0',
            'pkg_high_price_per_pax' => 'nullable|integer|min:0',
            'pkg_min_pax' => 'required|integer|min:1',
            'pkg_description' => 'nullable|string',
        ]);

        $highPrice = $this->is_pkg_high_season_same ? $this->pkg_price_per_pax : ($this->pkg_high_price_per_pax ?: $this->pkg_price_per_pax);

        if ($this->editing_pkg_id) {
            $pkg = MeetingPackage::findOrFail($this->editing_pkg_id);
            $pkg->update([
                'name' => $this->pkg_name,
                'price_per_pax' => $this->pkg_price_per_pax,
                'high_price_per_pax' => $highPrice,
                'min_pax' => $this->pkg_min_pax,
                'description' => $this->pkg_description,
                'is_active' => $this->pkg_is_active,
            ]);
            session()->flash('settings_message', "Paket meeting {$pkg->name} berhasil diperbarui.");
        } else {
            MeetingPackage::create([
                'name' => $this->pkg_name,
                'price_per_pax' => $this->pkg_price_per_pax,
                'high_price_per_pax' => $highPrice,
                'min_pax' => $this->pkg_min_pax,
                'description' => $this->pkg_description,
                'is_active' => $this->pkg_is_active,
            ]);
            session()->flash('settings_message', "Paket meeting {$this->pkg_name} berhasil ditambahkan.");
        }

        $this->resetMeetingPackageForm();
        $this->updateCalculation();
    }

    public function toggleMeetingPackageStatus(int $id): void
    {
        $pkg = MeetingPackage::findOrFail($id);
        $pkg->update(['is_active' => !$pkg->is_active]);
        session()->flash('settings_message', "Status paket {$pkg->name} diubah menjadi " . ($pkg->is_active ? 'Aktif' : 'Non-Aktif') . ".");
        $this->updateCalculation();
    }

    public function deleteMeetingPackage(int $id): void
    {
        $pkg = MeetingPackage::findOrFail($id);
        $pkg->delete();
        session()->flash('settings_message', "Paket meeting {$pkg->name} telah dipindahkan ke arsip (Soft Delete).");
        $this->resetMeetingPackageForm();
        $this->updateCalculation();
    }

    public function restoreMeetingPackage(int $id): void
    {
        $pkg = MeetingPackage::onlyTrashed()->findOrFail($id);
        $pkg->restore();
        session()->flash('settings_message', "Paket meeting {$pkg->name} berhasil dipulihkan dari arsip.");
        $this->updateCalculation();
    }

    // Master Additional Services Methods
    public function resetServiceForm(): void
    {
        $this->editing_svc_id = null;
        $this->svc_name = '';
        $this->svc_price = 0;
        $this->svc_unit = 'paket';
        $this->svc_is_active = true;
        $this->resetErrorBag();
    }

    public function editService(int $id): void
    {
        $svc = MeetingService::findOrFail($id);
        $this->editing_svc_id = $svc->id;
        $this->svc_name = $svc->name;
        $this->svc_price = (int) $svc->default_price;
        $this->svc_unit = $svc->unit ?: 'paket';
        $this->svc_is_active = (bool) $svc->is_active;
        $this->settingsTab = 'services';
        $this->resetErrorBag();
    }

    public function saveService(): void
    {
        $this->validate([
            'svc_name' => 'required|string|min:3|max:255',
            'svc_price' => 'required|integer|min:0',
            'svc_unit' => 'required|string|max:50',
        ]);

        if ($this->editing_svc_id) {
            $svc = MeetingService::findOrFail($this->editing_svc_id);
            $svc->update([
                'name' => $this->svc_name,
                'default_price' => $this->svc_price,
                'unit' => $this->svc_unit,
                'is_active' => $this->svc_is_active,
            ]);
            session()->flash('settings_message', "Layanan tambahan {$svc->name} berhasil diperbarui.");
        } else {
            MeetingService::create([
                'name' => $this->svc_name,
                'default_price' => $this->svc_price,
                'unit' => $this->svc_unit,
                'is_active' => $this->svc_is_active,
            ]);
            session()->flash('settings_message', "Layanan tambahan {$this->svc_name} berhasil ditambahkan.");
        }

        $this->resetServiceForm();
    }

    public function toggleServiceStatus(int $id): void
    {
        $svc = MeetingService::findOrFail($id);
        $svc->update(['is_active' => !$svc->is_active]);
        session()->flash('settings_message', "Status layanan {$svc->name} diubah menjadi " . ($svc->is_active ? 'Aktif' : 'Non-Aktif') . ".");
    }

    public function deleteService(int $id): void
    {
        $svc = MeetingService::findOrFail($id);
        $svc->delete();
        session()->flash('settings_message', "Layanan {$svc->name} telah dipindahkan ke arsip (Soft Delete).");
        $this->resetServiceForm();
    }

    public function restoreService(int $id): void
    {
        $svc = MeetingService::onlyTrashed()->findOrFail($id);
        $svc->restore();
        session()->flash('settings_message', "Layanan {$svc->name} berhasil dipulihkan dari arsip.");
    }

    // Detail & Post-Booking Addon Methods
    public function openDetailModal(int $resId): void
    {
        $this->detailReservationId = $resId;
        $this->setPostServiceMode('master');
        $this->post_item_qty = 1;
        $this->resetErrorBag();
        $this->showDetailModal = true;
    }

    public function setPostServiceMode(string $mode): void
    {
        $this->post_service_mode = $mode;
        if ($mode === 'manual') {
            $this->post_service_id = null;
            $this->post_item_name = '';
            $this->post_item_price = 0;
        } else {
            $firstSvc = MeetingService::where('is_active', true)->orderBy('name')->first();
            if ($firstSvc) {
                $this->post_service_id = $firstSvc->id;
                $this->post_item_name = $firstSvc->name;
                $this->post_item_price = (int) $firstSvc->default_price;
            } else {
                $this->post_service_id = null;
                $this->post_item_name = '';
                $this->post_item_price = 0;
            }
        }
    }

    public function onPostServiceSelected($serviceId): void
    {
        if (!empty($serviceId)) {
            $svc = MeetingService::find($serviceId);
            if ($svc) {
                $this->post_service_id = $svc->id;
                $this->post_item_name = $svc->name;
                $this->post_item_price = (int) $svc->default_price;
            }
        } else {
            $this->post_service_id = null;
        }
    }

    public function addPostBookingItem(): void
    {
        $this->validate([
            'post_item_name' => 'required|string|min:2',
            'post_item_qty' => 'required|integer|min:1',
            'post_item_price' => 'required|integer|min:0',
        ]);

        try {
            $res = MeetingReservation::findOrFail($this->detailReservationId);
            MeetingRoomService::addServiceItem($res, [
                'meeting_service_id' => $this->post_service_id,
                'name' => $this->post_item_name,
                'quantity' => $this->post_item_qty,
                'unit_price' => $this->post_item_price,
            ]);

            $this->post_service_id = null;
            $this->post_item_name = '';
            $this->post_item_qty = 1;
            $this->post_item_price = 0;
            session()->flash('detail_message', "Layanan tambahan berhasil ditambahkan ke reservasi.");
        } catch (\Exception $e) {
            $this->addError('post_service', $e->getMessage());
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingViewTab(): void { $this->resetPage(); $this->statusFilter = 'all'; }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingDateFilter(): void { $this->resetPage(); }

    public function render()
    {
        $today = now()->toDateString();
        $query = MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage', 'items', 'payments'])->latest();

        if ($this->viewTab === 'active') {
            $query->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                  ->whereDate('event_date', '>=', $today);
        } elseif ($this->viewTab === 'history') {
            $query->where(function ($sub) use ($today) {
                $sub->whereIn('status', ['completed', 'cancelled'])
                    ->orWhereDate('event_date', '<', $today);
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->dateFilter)) {
            $query->whereDate('event_date', $this->dateFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reservation_number', 'like', "%{$this->search}%")
                  ->orWhere('event_name', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('phone', 'like', "%{$this->search}%")
                         ->orWhere('company', 'like', "%{$this->search}%");
                  });
            });
        }

        $allMeetingRooms = $this->roomsFilter === 'trashed'
            ? MeetingRoom::onlyTrashed()->orderBy('name')->get()
            : MeetingRoom::orderBy('name')->get();

        $allMeetingPackages = $this->packagesFilter === 'trashed'
            ? MeetingPackage::onlyTrashed()->orderBy('name')->get()
            : MeetingPackage::orderBy('name')->get();

        $allMeetingServices = $this->servicesFilter === 'trashed'
            ? MeetingService::onlyTrashed()->orderBy('name')->get()
            : MeetingService::orderBy('name')->get();

        $activeCount = MeetingReservation::whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->whereDate('event_date', '>=', $today)->count();
        $historyCount = MeetingReservation::where(function ($sub) use ($today) {
            $sub->whereIn('status', ['completed', 'cancelled'])
                ->orWhereDate('event_date', '<', $today);
        })->count();

        return view('livewire.meeting-room-management', [
            'reservations' => $query->paginate(10),
            'meetingRooms' => MeetingRoom::where('is_active', true)->get(),
            'meetingPackages' => MeetingPackage::where('is_active', true)->get(),
            'allMeetingRooms' => $allMeetingRooms,
            'allMeetingPackages' => $allMeetingPackages,
            'allMeetingServices' => $allMeetingServices,
            'availableServices' => MeetingService::where('is_active', true)->orderBy('name')->get(),
            'activeRoomsCount' => MeetingRoom::count(),
            'trashedRoomsCount' => MeetingRoom::onlyTrashed()->count(),
            'activePackagesCount' => MeetingPackage::count(),
            'trashedPackagesCount' => MeetingPackage::onlyTrashed()->count(),
            'activeServicesCount' => MeetingService::count(),
            'trashedServicesCount' => MeetingService::onlyTrashed()->count(),
            'activeCount' => $activeCount,
            'historyCount' => $historyCount,
            'selectedReservation' => $this->selectedReservationId ? MeetingReservation::with(['customer', 'meetingRoom', 'items', 'payments'])->find($this->selectedReservationId) : null,
            'detailReservation' => $this->detailReservationId ? MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage', 'items.meetingService', 'payments'])->find($this->detailReservationId) : null,
        ]);
    }
}
