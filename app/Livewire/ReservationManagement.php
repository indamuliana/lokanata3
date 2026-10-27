<?php

namespace App\Livewire;

use App\Models\AdditionalService;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\BillingService;
use App\Services\FrontOfficeService;
use App\Services\PricingService;
use App\Services\ReservationService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ReservationManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $viewTab = 'active'; // 'active' vs 'history'
    public string $statusFilter = 'all';
    public string $periodFilter = 'all'; // all, today, this_week, this_month
    public string $channelFilter = 'all'; // all, walk_in, ota_manual, whatsapp_manual, website
    public string $paymentFilter = 'all'; // all, paid, partially_paid, unpaid

    // Checklist
    public array $selectedRows = [];
    public bool $selectAll = false;

    // Modals
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showPaymentModal = false;
    public bool $showAddonModal = false;
    public bool $showMasterAddonModal = false;
    public bool $showDetailModal = false;
    public ?int $selectedReservationId = null;
    public ?int $detailReservationId = null;

    // Quick Check-In & Room Plotting Modal
    public bool $showQuickCheckInModal = false;
    public ?int $quickCheckInReservationId = null;
    public ?int $quickCheckInRoomId = null;
    public array $quickCheckInAvailableRooms = [];

    // --- FORM RESERVASI BARU ---
    public string $source = 'walk_in'; // walk_in, ota_manual, whatsapp_manual, website
    public int $room_type_id = 0;
    public ?int $room_id = null;
    public string $check_in_date = '';
    public string $check_in_time = '14:00';
    public string $check_out_date = '';
    public int $adults = 1;
    public int $children = 0;
    public int $infants = 0;
    public int $extra_guests = 0;

    // Customer
    public string $customer_name = '';
    public string $customer_phone = '';
    public string $customer_identity_type = 'KTP'; // KTP, SIM, Paspor
    public string $customer_identity_number = '';
    public string $customer_company = '';
    public string $customer_address = '';

    // OTA Manual specific
    public string $ota_name = 'Traveloka';
    public string $ota_reference_number = '';
    public ?int $ota_gross_amount = 0;
    public ?int $ota_commission = 0;
    public ?int $ota_net_expected = 0;
    public string $ota_settlement_status = 'pending';

    // Pricing, Custom Night Rate & Tax Checkbox
    public bool $include_breakfast = true;
    public ?int $custom_night_rate = 0;
    public bool $apply_tax = true;
    public string $promo_code = '';
    public ?int $manual_discount = 0;
    public ?int $initial_paid_amount = 0;
    public string $notes = '';

    // Dynamic Multi Addons on new reservation
    public array $booking_addon_items = [];
    public int $booking_addons_total = 0;

    // Calculated breakdown preview
    public array $breakdown = [];
    public array $availableRooms = [];
    public int $availableCount = 0;

    // --- FORM EDIT RESERVASI ---
    public ?int $editingReservationId = null;
    public bool $edit_include_breakfast = true;
    public string $edit_customer_name = '';
    public string $edit_customer_phone = '';
    public string $edit_customer_identity_type = 'KTP';
    public string $edit_customer_identity_number = '';
    public string $edit_customer_company = '';
    public string $edit_customer_address = '';
    public string $edit_source = 'walk_in';
    public string $edit_ota_name = 'Traveloka';
    public string $edit_ota_reference_number = '';
    public ?int $edit_ota_gross_amount = 0;
    public ?int $edit_ota_commission = 0;
    public ?int $edit_ota_net_expected = 0;
    public int $edit_room_type_id = 0;
    public ?int $edit_room_id = null;
    public string $edit_check_in_date = '';
    public string $edit_check_in_time = '14:00';
    public string $edit_check_out_date = '';
    public int $edit_adults = 1;
    public int $edit_children = 0;
    public int $edit_infants = 0;
    public ?int $edit_custom_night_rate = 0;
    public bool $edit_apply_tax = true;
    public ?int $edit_manual_discount = 0;
    public string $edit_notes = '';
    public array $edit_breakdown = [];
    public array $edit_availableRooms = [];
    public int $edit_availableCount = 0;

    // --- MASTER LAYANAN TAMBAHAN MODAL ---
    public string $master_addon_tab = 'active'; // 'active' or 'trash'
    public ?int $master_addon_id = null;
    public string $master_addon_name = '';
    public string $master_addon_category = 'amenities';
    public int $master_addon_price = 0;
    public string $master_addon_unit = 'pcs';
    public bool $master_addon_active = true;

    // --- MODAL PEMBAYARAN & REFUND ---
    public int $payment_amount = 0;
    public string $payment_method = 'cash';
    public string $bank_account = '';
    public string $payment_reference = '';
    public string $payment_notes = '';
    public bool $is_refund_mode = false;
    public string $refund_reason = '';

    // --- MODAL ADDON SATUAN PADA RESERVASI AKTIF ---
    public ?int $addon_service_id = null;
    public string $addon_item_name = '';
    public int $addon_quantity = 1;
    public int $addon_unit_price = 0;

    public function mount(): void
    {
        $this->check_in_date = now()->toDateString();
        $this->check_out_date = now()->addDay()->toDateString();
        $firstRoomType = RoomType::first();
        if ($firstRoomType) {
            $this->room_type_id = $firstRoomType->id;
            $this->custom_night_rate = $firstRoomType->base_price;
        }
        $this->updateCalculation();
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'booking_addon_items')) {
            $this->recalculateBookingAddons();
            return;
        }

        if (in_array($propertyName, [
            'room_type_id', 'check_in_date', 'check_out_date', 'extra_guests',
            'promo_code', 'manual_discount', 'source', 'ota_gross_amount',
            'ota_commission', 'custom_night_rate', 'apply_tax'
        ])) {
            if ($propertyName === 'room_type_id') {
                $rt = RoomType::find($this->room_type_id);
                if ($rt) {
                    $this->custom_night_rate = $rt->base_price;
                }
            }
            $this->updateCalculation();
        }

        if (str_starts_with($propertyName, 'edit_')) {
            if (in_array($propertyName, [
                'edit_room_type_id', 'edit_check_in_date', 'edit_check_out_date',
                'edit_source', 'edit_ota_gross_amount', 'edit_ota_commission',
                'edit_custom_night_rate', 'edit_apply_tax', 'edit_manual_discount'
            ])) {
                if ($propertyName === 'edit_room_type_id') {
                    $rt = RoomType::find($this->edit_room_type_id);
                    if ($rt) {
                        $this->edit_custom_night_rate = $rt->base_price;
                    }
                }
                $this->updateEditCalculation();
            }
        }
    }

    public function updateCalculation(): void
    {
        if (!$this->room_type_id || !$this->check_in_date || !$this->check_out_date) {
            return;
        }

        $roomType = RoomType::find($this->room_type_id);
        if (!$roomType) {
            return;
        }

        $this->availableCount = ReservationService::getAvailableRoomCount($roomType, $this->check_in_date, $this->check_out_date);
        $this->availableRooms = ReservationService::getAvailableRooms($roomType, $this->check_in_date, $this->check_out_date);

        // Sum booking addons
        $addonTotal = 0;
        foreach ($this->booking_addon_items as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(0, (int) ($item['unit_price'] ?? 0));
            $addonTotal += ($qty * $price);
        }
        $this->booking_addons_total = $addonTotal;

        if ($this->source === 'ota_manual') {
            $gross = (int) ($this->ota_gross_amount ?? 0);
            $comm = (int) ($this->ota_commission ?? 0);
            $this->ota_net_expected = max(0, $gross - $comm);
            $nights = max(1, Carbon::parse($this->check_in_date)->diffInDays(Carbon::parse($this->check_out_date)));
            $this->breakdown = [
                'nights' => $nights,
                'nightly_rates' => [],
                'subtotal' => $gross,
                'discount_amount' => 0,
                'taxable_amount' => $gross,
                'tax_amount' => 0,
                'service_charge_amount' => 0,
                'additional_charges' => $addonTotal,
                'total_amount' => $gross + $addonTotal,
                'apply_tax' => false,
                'custom_rate_per_night' => (int) ($this->custom_night_rate ?? 0),
            ];
        } else {
            $customRate = ($this->custom_night_rate > 0) ? $this->custom_night_rate : null;
            $this->breakdown = PricingService::calculateStay(
                $roomType,
                $this->check_in_date,
                $this->check_out_date,
                $this->promo_code ?: null,
                $this->manual_discount,
                $this->extra_guests,
                $customRate,
                $this->apply_tax,
                $addonTotal,
                $this->include_breakfast
            );
        }
    }

    public function openCreateModal(): void
    {
        $this->authorize('reservations.manage');
        $this->resetErrorBag();
        if ($this->custom_night_rate <= 0 && $this->room_type_id) {
            $rt = RoomType::find($this->room_type_id);
            if ($rt) $this->custom_night_rate = $rt->base_price;
        }
        $this->updateCalculation();
        $this->showCreateModal = true;
    }

    // --- MULTI ADDONS IN CREATE RESERVATION ---
    public function addBookingAddonFromMaster(): void
    {
        $firstService = AdditionalService::where('is_active', true)->first();
        $this->booking_addon_items[] = [
            'additional_service_id' => $firstService ? $firstService->id : null,
            'name' => $firstService ? $firstService->name : '',
            'quantity' => 1,
            'unit_price' => $firstService ? (int) $firstService->default_price : 0,
        ];
        $this->recalculateBookingAddons();
    }

    public function addBookingAddonManual(): void
    {
        $this->booking_addon_items[] = [
            'additional_service_id' => null,
            'name' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
        $this->recalculateBookingAddons();
    }

    public function selectMasterAddon(int $index, ?int $serviceId): void
    {
        if (isset($this->booking_addon_items[$index])) {
            $service = AdditionalService::find($serviceId);
            if ($service) {
                $this->booking_addon_items[$index]['additional_service_id'] = $service->id;
                $this->booking_addon_items[$index]['name'] = $service->name;
                $this->booking_addon_items[$index]['unit_price'] = (int) $service->default_price;
            } else {
                $this->booking_addon_items[$index]['additional_service_id'] = null;
            }
            $this->recalculateBookingAddons();
        }
    }

    public function removeBookingAddon(int $index): void
    {
        if (isset($this->booking_addon_items[$index])) {
            unset($this->booking_addon_items[$index]);
            $this->booking_addon_items = array_values($this->booking_addon_items);
            $this->recalculateBookingAddons();
        }
    }

    public function recalculateBookingAddons(): void
    {
        $total = 0;
        foreach ($this->booking_addon_items as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(0, (int) ($item['unit_price'] ?? 0));
            $total += ($qty * $price);
        }
        $this->booking_addons_total = $total;
        $this->updateCalculation();
    }

    public function saveReservation(): void
    {
        $this->authorize('reservations.manage');
        $this->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|min:8|max:20',
            'customer_identity_type' => 'required|in:KTP,SIM,Paspor',
            'customer_identity_number' => 'nullable|string|max:50',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_in_time' => 'nullable|string|max:10',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'infants' => 'nullable|integer|min:0',
            'source' => 'required|in:walk_in,ota_manual,website,whatsapp_manual',
            'ota_name' => 'required_if:source,ota_manual',
            'custom_night_rate' => 'nullable|integer|min:0',
        ]);

        try {
            $addonPayload = [];
            foreach ($this->booking_addon_items as $item) {
                $name = trim($item['name'] ?? '');
                if ($name === '') continue;
                $addonPayload[] = [
                    'additional_service_id' => $item['additional_service_id'] ?? null,
                    'item_name' => $name,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                    'unit_price' => max(0, (int) ($item['unit_price'] ?? 0)),
                ];
            }

            $reservation = ReservationService::createReservation([
                'room_type_id' => $this->room_type_id,
                'room_id' => $this->room_id,
                'check_in_date' => $this->check_in_date,
                'check_in_time' => $this->check_in_time ?: '14:00',
                'check_out_date' => $this->check_out_date,
                'source' => $this->source,
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone,
                'customer_identity_type' => $this->customer_identity_type,
                'customer_identity_number' => $this->customer_identity_number,
                'customer_company' => $this->customer_company,
                'customer_address' => $this->customer_address,
                'ota_name' => $this->ota_name,
                'ota_reference_number' => $this->ota_reference_number,
                'ota_gross_amount' => $this->ota_gross_amount,
                'ota_commission' => $this->ota_commission,
                'ota_net_expected' => $this->ota_net_expected,
                'ota_settlement_status' => $this->ota_settlement_status,
                'adults' => $this->adults,
                'children' => $this->children,
                'infants' => $this->infants,
                'extra_guests' => $this->extra_guests,
                'include_breakfast' => $this->include_breakfast,
                'promo_code' => $this->promo_code,
                'manual_discount' => $this->manual_discount,
                'custom_rate_per_night' => $this->custom_night_rate > 0 ? $this->custom_night_rate : null,
                'apply_tax' => $this->apply_tax,
                'addon_items' => $addonPayload,
                'initial_paid_amount' => $this->initial_paid_amount,
                'notes' => $this->notes,
            ]);

            $this->showCreateModal = false;
            $this->booking_addon_items = [];
            $this->booking_addons_total = 0;
            session()->flash('message', "Reservasi #{$reservation->reservation_number} berhasil dibuat.");
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    // --- FITUR EDIT PESANAN ---
    public function openEditModal(int $reservationId): void
    {
        $this->authorize('reservations.manage');
        $this->resetErrorBag();
        $res = Reservation::with(['customer', 'roomType', 'allocatedRooms.room'])->findOrFail($reservationId);
        $this->editingReservationId = $res->id;

        $this->edit_customer_name = $res->customer->name ?? '';
        $this->edit_customer_phone = $res->customer->phone ?? '';
        $this->edit_customer_identity_type = $res->customer->identity_type ?? 'KTP';
        $this->edit_customer_identity_number = $res->customer->identity_number ?? '';
        $this->edit_customer_company = $res->customer->company ?? '';
        $this->edit_customer_address = $res->customer->address ?? '';

        $this->edit_source = $res->source;
        $this->edit_ota_name = $res->ota_name ?? 'Traveloka';
        $this->edit_ota_reference_number = $res->ota_reference_number ?? '';
        $this->edit_ota_gross_amount = (int) ($res->ota_gross_amount ?? 0);
        $this->edit_ota_commission = (int) ($res->ota_commission ?? 0);
        $this->edit_ota_net_expected = (int) ($res->ota_net_expected ?? 0);

        $this->edit_room_type_id = $res->room_type_id;
        $this->edit_room_id = $res->allocatedRooms->first()?->room_id;
        $this->edit_check_in_date = $res->check_in_date->toDateString();
        $this->edit_check_in_time = $res->check_in_time ?? '14:00';
        $this->edit_check_out_date = $res->check_out_date->toDateString();
        $this->edit_adults = $res->adults;
        $this->edit_children = $res->children;
        $this->edit_infants = $res->infants;
        $this->edit_include_breakfast = (bool) ($res->include_breakfast ?? true);

        $nights = max(1, $res->check_in_date->diffInDays($res->check_out_date));
        $this->edit_custom_night_rate = (int) round($res->subtotal / $nights);
        $this->edit_apply_tax = ($res->tax_amount > 0);
        $this->edit_manual_discount = (int) ($res->discount_amount ?? 0);
        $this->edit_notes = $res->notes ?? '';

        $this->updateEditCalculation();
        $this->showEditModal = true;
    }

    public function updateEditCalculation(): void
    {
        if (!$this->edit_room_type_id || !$this->edit_check_in_date || !$this->edit_check_out_date) {
            return;
        }

        $roomType = RoomType::find($this->edit_room_type_id);
        if (!$roomType) return;

        $res = Reservation::find($this->editingReservationId);
        $addonTotal = $res ? (int) $res->serviceItems()->sum('subtotal') : 0;

        $this->edit_availableCount = ReservationService::getAvailableRoomCount($roomType, $this->edit_check_in_date, $this->edit_check_out_date);
        $this->edit_availableRooms = ReservationService::getAvailableRooms($roomType, $this->edit_check_in_date, $this->edit_check_out_date);

        // Include current room if already assigned so staff doesn't lose it
        if ($this->edit_room_id && !collect($this->edit_availableRooms)->contains('id', $this->edit_room_id)) {
            $curRoom = Room::find($this->edit_room_id);
            if ($curRoom) {
                $this->edit_availableRooms[] = $curRoom;
            }
        }

        if ($this->edit_source === 'ota_manual') {
            $gross = (int) ($this->edit_ota_gross_amount ?? 0);
            $comm = (int) ($this->edit_ota_commission ?? 0);
            $this->edit_ota_net_expected = max(0, $gross - $comm);
            $nights = max(1, Carbon::parse($this->edit_check_in_date)->diffInDays(Carbon::parse($this->edit_check_out_date)));
            $this->edit_breakdown = [
                'nights' => $nights,
                'subtotal' => $gross,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'service_charge_amount' => 0,
                'additional_charges' => $addonTotal,
                'total_amount' => $gross + $addonTotal,
                'apply_tax' => false,
                'custom_rate_per_night' => $this->edit_custom_night_rate,
            ];
        } else {
            $customRate = ($this->edit_custom_night_rate > 0) ? $this->edit_custom_night_rate : null;
            $this->edit_breakdown = PricingService::calculateStay(
                $roomType,
                $this->edit_check_in_date,
                $this->edit_check_out_date,
                null,
                $this->edit_manual_discount,
                0,
                $customRate,
                $this->edit_apply_tax,
                $addonTotal,
                $this->edit_include_breakfast
            );
        }
    }

    public function saveEditedReservation(): void
    {
        $this->authorize('reservations.manage');
        $this->validate([
            'edit_customer_name' => 'required|string|max:100',
            'edit_customer_phone' => 'required|string|min:8|max:20',
            'edit_customer_identity_type' => 'required|in:KTP,SIM,Paspor',
            'edit_customer_identity_number' => 'nullable|string|max:50',
            'edit_room_type_id' => 'required|exists:room_types,id',
            'edit_check_in_date' => 'required|date',
            'edit_check_in_time' => 'nullable|string|max:10',
            'edit_check_out_date' => 'required|date|after:edit_check_in_date',
            'edit_adults' => 'required|integer|min:1',
            'edit_children' => 'nullable|integer|min:0',
            'edit_infants' => 'nullable|integer|min:0',
            'edit_source' => 'required|in:walk_in,ota_manual,website,whatsapp_manual',
            'edit_ota_name' => 'required_if:edit_source,ota_manual',
            'edit_custom_night_rate' => 'nullable|integer|min:0',
        ]);

        try {
            $res = Reservation::findOrFail($this->editingReservationId);
            ReservationService::updateReservation($res, [
                'customer_name' => $this->edit_customer_name,
                'customer_phone' => $this->edit_customer_phone,
                'customer_identity_type' => $this->edit_customer_identity_type,
                'customer_identity_number' => $this->edit_customer_identity_number,
                'customer_company' => $this->edit_customer_company,
                'customer_address' => $this->edit_customer_address,
                'source' => $this->edit_source,
                'ota_name' => $this->edit_ota_name,
                'ota_reference_number' => $this->edit_ota_reference_number,
                'ota_gross_amount' => $this->edit_ota_gross_amount,
                'ota_commission' => $this->edit_ota_commission,
                'room_type_id' => $this->edit_room_type_id,
                'room_id' => $this->edit_room_id,
                'check_in_date' => $this->edit_check_in_date,
                'check_in_time' => $this->edit_check_in_time ?: '14:00',
                'check_out_date' => $this->edit_check_out_date,
                'adults' => $this->edit_adults,
                'children' => $this->edit_children,
                'infants' => $this->edit_infants,
                'include_breakfast' => $this->edit_include_breakfast,
                'custom_rate_per_night' => $this->edit_custom_night_rate > 0 ? $this->edit_custom_night_rate : null,
                'apply_tax' => $this->edit_apply_tax,
                'manual_discount' => $this->edit_manual_discount,
                'notes' => $this->edit_notes,
            ]);

            $this->showEditModal = false;
            session()->flash('message', "Perubahan data reservasi #{$res->reservation_number} berhasil disimpan.");
        } catch (\Exception $e) {
            $this->addError('edit_general', $e->getMessage());
        }
    }

    // --- MASTER LAYANAN TAMBAHAN (ADDON) MANAGEMENT ---
    public function openMasterAddonModal(): void
    {
        $this->authorize('reservations.manage');
        $this->resetMasterAddonForm();
        $this->showMasterAddonModal = true;
    }

    public function resetMasterAddonForm(): void
    {
        $this->resetErrorBag();
        $this->master_addon_id = null;
        $this->master_addon_name = '';
        $this->master_addon_category = 'amenities';
        $this->master_addon_price = 0;
        $this->master_addon_unit = 'pcs';
        $this->master_addon_active = true;
    }

    public function editMasterAddon(int $id): void
    {
        $this->authorize('reservations.manage');
        $addon = AdditionalService::withTrashed()->findOrFail($id);
        $this->master_addon_id = $addon->id;
        $this->master_addon_name = $addon->name;
        $this->master_addon_category = $addon->category ?? 'amenities';
        $this->master_addon_price = (int) $addon->default_price;
        $this->master_addon_unit = $addon->unit ?? 'pcs';
        $this->master_addon_active = (bool) $addon->is_active;
    }

    public function saveMasterAddon(): void
    {
        $this->authorize('reservations.manage');
        $this->validate([
            'master_addon_name' => 'required|string|max:100',
            'master_addon_category' => 'required|string|max:50',
            'master_addon_price' => 'required|integer|min:0',
            'master_addon_unit' => 'required|string|max:30',
        ]);

        if ($this->master_addon_id) {
            $addon = AdditionalService::withTrashed()->findOrFail($this->master_addon_id);
            $addon->update([
                'name' => $this->master_addon_name,
                'category' => $this->master_addon_category,
                'default_price' => $this->master_addon_price,
                'unit' => $this->master_addon_unit,
                'is_active' => $this->master_addon_active,
            ]);
            session()->flash('addon_modal_message', "Layanan '{$addon->name}' berhasil diperbarui.");
        } else {
            $addon = AdditionalService::create([
                'name' => $this->master_addon_name,
                'category' => $this->master_addon_category,
                'default_price' => $this->master_addon_price,
                'unit' => $this->master_addon_unit,
                'is_active' => $this->master_addon_active,
            ]);
            session()->flash('addon_modal_message', "Layanan baru '{$addon->name}' berhasil ditambahkan.");
        }

        $this->resetMasterAddonForm();
    }

    public function toggleMasterAddonStatus(int $id): void
    {
        $this->authorize('reservations.manage');
        $addon = AdditionalService::withTrashed()->findOrFail($id);
        $addon->update(['is_active' => !$addon->is_active]);
        session()->flash('addon_modal_message', "Status layanan '{$addon->name}' diubah menjadi: " . ($addon->is_active ? 'Aktif' : 'Nonaktif'));
    }

    public function deleteMasterAddon(int $id): void
    {
        $this->authorize('reservations.manage');
        $addon = AdditionalService::findOrFail($id);
        $name = $addon->name;
        $addon->delete();
        session()->flash('addon_modal_message', "Layanan '{$name}' dipindahkan ke arsip/sampah.");
    }

    public function restoreMasterAddon(int $id): void
    {
        $this->authorize('reservations.manage');
        $addon = AdditionalService::onlyTrashed()->findOrFail($id);
        $addon->restore();
        session()->flash('addon_modal_message', "Layanan '{$addon->name}' berhasil dipulihkan dari arsip.");
    }

    // --- OTHER ACTIONS ---
    public function triggerManualReleaseExpired(): void
    {
        $count = Reservation::releaseExpired();
        session()->flash('message', "Selesai memeriksa: {$count} reservasi pending yang kedaluwarsa berhasil dilepaskan.");
    }

    public function toggleOtaSettlement(int $reservationId): void
    {
        $reservation = Reservation::findOrFail($reservationId);
        if ($reservation->source !== 'ota_manual') return;

        $newStatus = ($reservation->ota_settlement_status === 'settled') ? 'pending' : 'settled';
        $reservation->update(['ota_settlement_status' => $newStatus]);
        AuditLog::record('ota_settlement_update', $reservation, null, ['ota_settlement_status' => $newStatus], "Status settlement OTA #{$reservation->reservation_number} diubah menjadi {$newStatus}");
        session()->flash('message', "Status pencairan dana OTA #{$reservation->reservation_number} diubah menjadi: " . strtoupper($newStatus));
    }

    public function checkIn(int $reservationId): void
    {
        $this->authorize('checkin_checkout.manage');
        $this->resetErrorBag();
        try {
            $reservation = Reservation::with(['allocatedRooms.room', 'customer', 'roomType'])->findOrFail($reservationId);
            $allocatedRoom = $reservation->allocatedRooms->first()?->room;

            if ($allocatedRoom) {
                FrontOfficeService::checkIn($reservation, $allocatedRoom);
                session()->flash('message', "Check-in berhasil! Tamu {$reservation->customer->name} menempati Kamar {$allocatedRoom->room_number}.");
            } else {
                // No physical room allocated yet (e.g. from website booking)
                $this->quickCheckInReservationId = $reservation->id;
                $available = ReservationService::getAvailableRooms(
                    $reservation->roomType,
                    $reservation->check_in_date->toDateString(),
                    $reservation->check_out_date->toDateString()
                );

                $this->quickCheckInAvailableRooms = $available;
                $this->quickCheckInRoomId = !empty($available) ? $available[0]->id : null;
                $this->showQuickCheckInModal = true;
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function confirmQuickCheckIn(): void
    {
        $this->authorize('checkin_checkout.manage');
        $this->validate([
            'quickCheckInRoomId' => 'required|exists:rooms,id',
        ], [
            'quickCheckInRoomId.required' => 'Pilih nomor kamar fisik untuk check-in.',
        ]);

        try {
            $reservation = Reservation::findOrFail($this->quickCheckInReservationId);
            $room = Room::findOrFail($this->quickCheckInRoomId);
            FrontOfficeService::checkIn($reservation, $room);

            $this->showQuickCheckInModal = false;
            $this->quickCheckInReservationId = null;
            $this->quickCheckInRoomId = null;
            $this->quickCheckInAvailableRooms = [];

            session()->flash('message', "Plot kamar & Check-in berhasil! Tamu {$reservation->customer->name} menempati Kamar {$room->room_number}.");
        } catch (\Exception $e) {
            $this->addError('quick_check_in', $e->getMessage());
        }
    }

    public function closeQuickCheckInModal(): void
    {
        $this->showQuickCheckInModal = false;
        $this->quickCheckInReservationId = null;
        $this->quickCheckInRoomId = null;
        $this->quickCheckInAvailableRooms = [];
    }

    public function checkOut(int $reservationId): void
    {
        $this->authorize('checkin_checkout.manage');
        try {
            $reservation = Reservation::with(['allocatedRooms.room', 'customer'])->findOrFail($reservationId);
            FrontOfficeService::checkOut($reservation);
            session()->flash('message', "Check-out tamu {$reservation->customer->name} berhasil diselesaikan. Kamar otomatis ditandai kotor (dirty).");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelReservation(int $id): void
    {
        $this->authorize('reservations.manage');
        $reservation = Reservation::findOrFail($id);
        if (in_array($reservation->status, ['checked_out', 'cancelled'])) {
            return;
        }

        $reservation->update(['status' => 'cancelled']);
        AuditLog::record('cancel_reservation', $reservation, null, ['status' => 'cancelled'], 'Dibatalkan oleh petugas');
        session()->flash('message', "Reservasi #{$reservation->reservation_number} berhasil dibatalkan.");
    }

    public function openPaymentModal(int $reservationId, bool $isRefund = false): void
    {
        $this->authorize('reservation_payment.manage');
        $this->resetErrorBag();
        $this->selectedReservationId = $reservationId;
        $this->is_refund_mode = $isRefund;
        $res = Reservation::findOrFail($reservationId);
        $this->payment_amount = $isRefund ? min($res->paid_amount, 100000) : max(0, $res->total_amount - $res->paid_amount);
        $this->payment_method = 'cash';
        $this->bank_account = '';
        $this->payment_reference = '';
        $this->payment_notes = '';
        $this->refund_reason = '';
        $this->showPaymentModal = true;
    }

    public function setQuickPayment(string $type): void
    {
        if (!$this->selectedReservationId) return;
        $res = Reservation::findOrFail($this->selectedReservationId);
        $remaining = max(0, $res->total_amount - $res->paid_amount);
        if ($type === 'full') {
            $this->payment_amount = $remaining;
        } elseif ($type === 'half') {
            $this->payment_amount = (int) round($res->total_amount * 0.5);
        }
    }

    public function savePayment(): void
    {
        $this->authorize('reservation_payment.manage');
        $res = Reservation::findOrFail($this->selectedReservationId);

        if ($this->is_refund_mode) {
            $this->validate([
                'payment_amount' => 'required|integer|min:1000|max:' . max(1000, $res->paid_amount),
                'refund_reason' => 'required|string|min:3',
            ]);

            try {
                BillingService::recordRefund($res, $this->payment_amount, $this->refund_reason);
                $this->showPaymentModal = false;
                session()->flash('message', "Refund sebesar Rp " . number_format($this->payment_amount, 0, ',', '.') . " berhasil diproses.");
            } catch (\Exception $e) {
                $this->addError('payment', $e->getMessage());
            }
        } else {
            $this->validate([
                'payment_amount' => 'required|integer|min:1000',
                'payment_method' => 'required|in:cash,bank_transfer,qris_manual,card_manual',
            ]);

            try {
                BillingService::recordPayment(
                    $res,
                    $this->payment_amount,
                    $this->payment_method,
                    $this->bank_account ?: null,
                    $this->payment_reference ?: null,
                    null,
                    $this->payment_notes ?: null
                );
                $this->showPaymentModal = false;
                session()->flash('message', "Pembayaran sebesar Rp " . number_format($this->payment_amount, 0, ',', '.') . " berhasil dicatat.");
            } catch (\Exception $e) {
                $this->addError('payment', $e->getMessage());
            }
        }
    }

    public function openAddonModal(int $reservationId): void
    {
        $this->authorize('reservation_payment.manage');
        $this->resetErrorBag();
        $this->selectedReservationId = $reservationId;
        $this->addon_service_id = null;
        $this->addon_item_name = '';
        $this->addon_quantity = 1;
        $this->addon_unit_price = 0;
        $this->showAddonModal = true;
    }

    public function updatedAddonServiceId($val): void
    {
        if ($val) {
            $service = AdditionalService::find($val);
            if ($service) {
                $this->addon_item_name = $service->name;
                $this->addon_unit_price = $service->default_price;
            }
        }
    }

    public function saveAddon(): void
    {
        $this->authorize('reservation_payment.manage');
        $this->validate([
            'addon_item_name' => 'required|string|min:2',
            'addon_quantity' => 'required|integer|min:1',
            'addon_unit_price' => 'required|integer|min:0',
        ]);

        try {
            $res = Reservation::findOrFail($this->selectedReservationId);
            BillingService::addServiceItem(
                $res,
                $this->addon_item_name,
                $this->addon_quantity,
                $this->addon_unit_price,
                $this->addon_service_id ?: null
            );
            $this->showAddonModal = false;
            session()->flash('message', "Layanan {$this->addon_item_name} berhasil ditambahkan ke reservasi.");
        } catch (\Exception $e) {
            $this->addError('addon', $e->getMessage());
        }
    }

    // --- FILTER RESET PAGINATION ---
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingViewTab(): void { $this->resetPage(); $this->statusFilter = 'all'; }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingPeriodFilter(): void { $this->resetPage(); }
    public function updatingChannelFilter(): void { $this->resetPage(); }
    public function updatingPaymentFilter(): void { $this->resetPage(); }

    // --- CHECKLIST MULTI-SELECT HANDLERS ---
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $query = $this->buildReservationQuery();
            $this->selectedRows = $query->paginate(10)->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function clearSelectedRows(): void
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    // --- MODAL DETIL HANDLERS ---
    public function openDetailModal(int $id): void
    {
        $this->detailReservationId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailReservationId = null;
    }

    protected function buildReservationQuery()
    {
        $query = Reservation::with(['customer', 'roomType', 'allocatedRooms.room', 'serviceItems', 'invoices'])
            ->latest();

        $today = now()->toDateString();
        if ($this->viewTab === 'active') {
            $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                  ->whereDate('check_out_date', '>=', $today);
        } elseif ($this->viewTab === 'history') {
            $query->where(function ($sub) use ($today) {
                $sub->whereIn('status', ['checked_out', 'cancelled', 'expired'])
                    ->orWhereDate('check_out_date', '<', $today);
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->periodFilter === 'today') {
            $today = now()->toDateString();
            $query->whereDate('check_in_date', '<=', $today)
                  ->whereDate('check_out_date', '>=', $today);
        } elseif ($this->periodFilter === 'this_week') {
            $startOfWeek = now()->startOfWeek()->toDateString();
            $endOfWeek = now()->endOfWeek()->toDateString();
            $query->whereDate('check_in_date', '<=', $endOfWeek)
                  ->whereDate('check_out_date', '>=', $startOfWeek);
        } elseif ($this->periodFilter === 'this_month') {
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();
            $query->whereDate('check_in_date', '<=', $endOfMonth)
                  ->whereDate('check_out_date', '>=', $startOfMonth);
        }

        if ($this->channelFilter !== 'all') {
            $query->where('source', $this->channelFilter);
        }

        if ($this->paymentFilter !== 'all') {
            $query->where('payment_status', $this->paymentFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reservation_number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('phone', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query;
    }

    public function render()
    {
        $query = $this->buildReservationQuery();

        $activeAddons = AdditionalService::where('is_active', true)->orderBy('name')->get();
        $allMasterAddons = ($this->master_addon_tab === 'trash')
            ? AdditionalService::onlyTrashed()->orderBy('name')->get()
            : AdditionalService::withoutTrashed()->orderBy('is_active', 'desc')->orderBy('name')->get();

        $trashedAddonCount = AdditionalService::onlyTrashed()->count();
        $activeAddonCount = AdditionalService::withoutTrashed()->count();

        $today = now()->toDateString();
        $activeCount = Reservation::whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->whereDate('check_out_date', '>=', $today)->count();
        $historyCount = Reservation::where(function ($sub) use ($today) {
            $sub->whereIn('status', ['checked_out', 'cancelled', 'expired'])
                ->orWhereDate('check_out_date', '<', $today);
        })->count();

        return view('livewire.reservation-management', [
            'reservations' => $query->paginate(10),
            'roomTypes' => RoomType::where('is_active', true)->get(),
            'additionalServices' => $activeAddons,
            'masterAddonList' => $allMasterAddons,
            'trashedAddonCount' => $trashedAddonCount,
            'activeAddonCount' => $activeAddonCount,
            'activeCount' => $activeCount,
            'historyCount' => $historyCount,
            'selectedReservation' => $this->selectedReservationId ? Reservation::with(['customer', 'serviceItems', 'payments'])->find($this->selectedReservationId) : null,
            'detailReservation' => $this->detailReservationId ? Reservation::with(['customer', 'roomType', 'allocatedRooms.room', 'serviceItems.additionalService', 'invoices', 'payments'])->find($this->detailReservationId) : null,
        ]);
    }
}
