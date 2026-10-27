<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\DiningPackage;
use App\Models\DiningReservation;
use App\Models\DiningService;
use App\Models\DiningServiceItem;
use App\Services\FineDiningService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DiningReservationManagement extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $viewTab = 'active'; // 'active' vs 'history'
    public string $statusFilter = 'all';
    public string $dateFilter = '';

    // Create Modal Form
    public bool $showCreateModal = false;
    public ?int $dining_package_id = null;
    public string $custom_menu_name = '';
    public string $group_name = '';
    public string $pic_name = '';
    public string $pic_phone = '';
    public string $activity_link = '';
    public bool $is_wikrama_teachers = false;
    public string $table_number_or_area = 'Area Utama Restoran';
    public string $reservation_date = '';
    public string $time_slot = '12:30';
    public ?int $pax = 4;
    public ?int $custom_price_per_pax = 0;
    public bool $apply_tax = true;
    public ?int $discount_amount = 0;
    public string $special_requests = '';
    public ?int $initial_paid_amount = 0;
    public string $payment_method = 'cash';
    public string $notes = '';

    // Customer info in Create Modal
    public string $customer_name = '';
    public string $customer_phone = '';

    // Add-on extra services in Create Modal
    public array $booking_addon_items = [];
    public ?int $booking_addons_total = 0;

    // Calculation breakdown for Create
    public array $breakdown = [];

    // Edit Modal Form
    public bool $showEditModal = false;
    public ?int $editing_reservation_id = null;
    public string $edit_group_name = '';
    public string $edit_pic_name = '';
    public string $edit_pic_phone = '';
    public string $edit_activity_link = '';
    public bool $edit_is_wikrama_teachers = false;
    public ?int $edit_dining_package_id = null;
    public string $edit_custom_menu_name = '';
    public string $edit_table_number_or_area = '';
    public string $edit_reservation_date = '';
    public string $edit_time_slot = '';
    public ?int $edit_pax = 1;
    public ?int $edit_custom_price_per_pax = 0;
    public bool $edit_apply_tax = true;
    public ?int $edit_discount_amount = 0;
    public string $edit_special_requests = '';
    public string $edit_notes = '';
    public string $edit_customer_name = '';
    public string $edit_customer_phone = '';
    public array $edit_addon_items = [];
    public ?int $edit_addons_total = 0;
    public array $edit_breakdown = [];

    // Detail Modal
    public bool $showDetailModal = false;
    public ?int $detailReservationId = null;

    // Payment Modal
    public bool $showPaymentModal = false;
    public ?int $selectedReservationId = null;
    public ?int $payment_amount = 0;

    // Master Settings Modal (Paket & Layanan Tambahan)
    public bool $showMasterModal = false;
    public bool $showPackageModal = false; // Alias for backward compatibility
    public string $masterTab = 'packages'; // 'packages' or 'services'
    public string $packageFilter = 'active'; // 'active' or 'trashed'
    public string $serviceFilter = 'active'; // 'active' or 'trashed'

    // Form Master Paket
    public ?int $editing_package_id = null;
    public string $package_name = '';
    public ?int $package_price_per_pax = 50000;
    public ?int $package_high_price_per_pax = 50000;
    public bool $is_package_high_season_same = true;
    public ?int $package_min_pax = 2;
    public string $package_menu_details = '';
    public bool $package_is_active = true;
    public $package_photo = null;
    public ?string $existing_image_path = null;

    // Form Master Layanan Tambahan
    public ?int $editing_service_id = null;
    public string $service_name = '';
    public ?int $service_default_price = 0;
    public string $service_unit = 'porsi';
    public bool $service_is_active = true;

    public function mount(): void
    {
        $this->reservation_date = now()->toDateString();
        $firstPkg = DiningPackage::where('is_active', true)->first();
        $this->dining_package_id = $firstPkg ? $firstPkg->id : null;
        $this->updateCalculation();
    }

    public function updated($prop): void
    {
        if (str_starts_with($prop, 'booking_addon_items')) {
            $this->recalculateAddons();
            return;
        }

        if (str_starts_with($prop, 'edit_addon_items')) {
            $this->recalculateEditAddons();
            return;
        }

        if (in_array($prop, [
            'dining_package_id', 'pax', 'custom_price_per_pax',
            'discount_amount', 'apply_tax'
        ])) {
            $this->updateCalculation();
        }

        if (in_array($prop, [
            'edit_dining_package_id', 'edit_pax', 'edit_custom_price_per_pax',
            'edit_discount_amount', 'edit_apply_tax'
        ])) {
            $this->updateEditCalculation();
        }
    }

    // --- CALCULATION LOGIC ---

    public function updateCalculation(): void
    {
        $pkg = $this->dining_package_id ? DiningPackage::find($this->dining_package_id) : null;
        $pax = max(1, (int) ($this->pax ?? 1));
        $customPrice = (int) ($this->custom_price_per_pax ?? 0);
        $addonTotal = (int) ($this->booking_addons_total ?? 0);
        $discount = (int) ($this->discount_amount ?? 0);

        $this->breakdown = FineDiningService::calculatePricing(
            $pkg,
            $pax,
            $customPrice,
            $addonTotal,
            $discount,
            $this->apply_tax,
            $this->reservation_date
        );
    }

    public function updateEditCalculation(): void
    {
        $pkg = $this->edit_dining_package_id ? DiningPackage::find($this->edit_dining_package_id) : null;
        $pax = max(1, (int) ($this->edit_pax ?? 1));
        $customPrice = (int) ($this->edit_custom_price_per_pax ?? 0);
        $addonTotal = (int) ($this->edit_addons_total ?? 0);
        $discount = (int) ($this->edit_discount_amount ?? 0);

        $this->edit_breakdown = FineDiningService::calculatePricing(
            $pkg,
            $pax,
            $customPrice,
            $addonTotal,
            $discount,
            $this->edit_apply_tax,
            $this->edit_reservation_date
        );
    }

    // --- MULTI-ADDONS IN CREATE MODAL ---

    public function addAddonFromMaster(): void
    {
        $firstSvc = DiningService::where('is_active', true)->first();
        $this->booking_addon_items[] = [
            'dining_service_id' => $firstSvc ? $firstSvc->id : null,
            'name' => $firstSvc ? $firstSvc->name : '',
            'quantity' => 1,
            'unit_price' => $firstSvc ? (int) $firstSvc->default_price : 0,
        ];
        $this->recalculateAddons();
    }

    public function addAddonManual(): void
    {
        $this->booking_addon_items[] = [
            'dining_service_id' => null,
            'name' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
        $this->recalculateAddons();
    }

    public function selectMasterAddon(int $index, ?int $serviceId): void
    {
        if (isset($this->booking_addon_items[$index])) {
            $svc = DiningService::find($serviceId);
            if ($svc) {
                $this->booking_addon_items[$index]['dining_service_id'] = $svc->id;
                $this->booking_addon_items[$index]['name'] = $svc->name;
                $this->booking_addon_items[$index]['unit_price'] = (int) $svc->default_price;
            } else {
                $this->booking_addon_items[$index]['dining_service_id'] = null;
            }
            $this->recalculateAddons();
        }
    }

    public function removeAddon(int $index): void
    {
        if (isset($this->booking_addon_items[$index])) {
            unset($this->booking_addon_items[$index]);
            $this->booking_addon_items = array_values($this->booking_addon_items);
            $this->recalculateAddons();
        }
    }

    public function recalculateAddons(): void
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

    // --- MULTI-ADDONS IN EDIT MODAL ---

    public function addEditAddonFromMaster(): void
    {
        $firstSvc = DiningService::where('is_active', true)->first();
        $this->edit_addon_items[] = [
            'dining_service_id' => $firstSvc ? $firstSvc->id : null,
            'name' => $firstSvc ? $firstSvc->name : '',
            'quantity' => 1,
            'unit_price' => $firstSvc ? (int) $firstSvc->default_price : 0,
        ];
        $this->recalculateEditAddons();
    }

    public function addEditAddonManual(): void
    {
        $this->edit_addon_items[] = [
            'dining_service_id' => null,
            'name' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
        $this->recalculateEditAddons();
    }

    public function selectEditMasterAddon(int $index, ?int $serviceId): void
    {
        if (isset($this->edit_addon_items[$index])) {
            $svc = DiningService::find($serviceId);
            if ($svc) {
                $this->edit_addon_items[$index]['dining_service_id'] = $svc->id;
                $this->edit_addon_items[$index]['name'] = $svc->name;
                $this->edit_addon_items[$index]['unit_price'] = (int) $svc->default_price;
            } else {
                $this->edit_addon_items[$index]['dining_service_id'] = null;
            }
            $this->recalculateEditAddons();
        }
    }

    public function removeEditAddon(int $index): void
    {
        if (isset($this->edit_addon_items[$index])) {
            unset($this->edit_addon_items[$index]);
            $this->edit_addon_items = array_values($this->edit_addon_items);
            $this->recalculateEditAddons();
        }
    }

    public function recalculateEditAddons(): void
    {
        $total = 0;
        foreach ($this->edit_addon_items as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(0, (int) ($item['unit_price'] ?? 0));
            $total += ($qty * $price);
        }
        $this->edit_addons_total = $total;
        $this->updateEditCalculation();
    }

    // --- CREATE RESERVATION ---

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->updateCalculation();
        $this->showCreateModal = true;
    }

    public function saveReservation(): void
    {
        $this->validate([
            'reservation_date' => 'required|date',
            'time_slot' => 'required|string',
            'pax' => 'required|integer|min:1',
            'customer_name' => 'required|string|min:3',
            'customer_phone' => 'required|string|min:8',
            'activity_link' => 'nullable|url',
        ]);

        try {
            $res = FineDiningService::createReservation([
                'dining_package_id' => $this->dining_package_id,
                'custom_menu_name' => $this->custom_menu_name,
                'group_name' => $this->group_name,
                'pic_name' => $this->pic_name,
                'pic_phone' => $this->pic_phone,
                'activity_link' => $this->activity_link,
                'is_wikrama_teachers' => $this->is_wikrama_teachers,
                'table_number_or_area' => $this->table_number_or_area,
                'reservation_date' => $this->reservation_date,
                'time_slot' => $this->time_slot,
                'pax' => (int) ($this->pax ?? 1),
                'custom_price_per_pax' => (int) ($this->custom_price_per_pax ?? 0),
                'apply_tax' => $this->apply_tax,
                'additional_charges' => (int) ($this->booking_addons_total ?? 0),
                'discount_amount' => (int) ($this->discount_amount ?? 0),
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone,
                'special_requests' => $this->special_requests,
                'initial_paid_amount' => (int) ($this->initial_paid_amount ?? 0),
                'payment_method' => $this->payment_method,
                'notes' => $this->notes,
                'service_items' => $this->booking_addon_items,
            ]);

            $this->showCreateModal = false;
            $this->reset([
                'group_name', 'pic_name', 'pic_phone', 'activity_link',
                'is_wikrama_teachers', 'custom_menu_name', 'special_requests',
                'customer_name', 'customer_phone', 'notes', 'booking_addon_items',
                'booking_addons_total', 'initial_paid_amount'
            ]);
            session()->flash('message', "Reservasi resto syariah #{$res->reservation_number} berhasil dibuat.");
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    // --- EDIT RESERVATION ---

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $res = DiningReservation::with(['customer', 'items'])->findOrFail($id);

        $this->editing_reservation_id = $res->id;
        $this->edit_group_name = $res->group_name ?? '';
        $this->edit_pic_name = $res->pic_name ?? '';
        $this->edit_pic_phone = $res->pic_phone ?? '';
        $this->edit_activity_link = $res->activity_link ?? '';
        $this->edit_is_wikrama_teachers = (bool) $res->is_wikrama_teachers;
        $this->edit_dining_package_id = $res->dining_package_id;
        $this->edit_custom_menu_name = $res->custom_menu_name ?? '';
        $this->edit_table_number_or_area = $res->table_number_or_area ?? 'Area Utama Restoran';
        $this->edit_reservation_date = $res->reservation_date ? $res->reservation_date->format('Y-m-d') : '';
        $this->edit_time_slot = $res->time_slot ?? '12:30';
        $this->edit_pax = (int) $res->pax;
        $this->edit_custom_price_per_pax = $res->dining_package_id ? 0 : (int) $res->price_per_pax;
        $this->edit_apply_tax = (bool) $res->apply_tax;
        $this->edit_discount_amount = (int) $res->discount_amount;
        $this->edit_special_requests = $res->special_requests ?? '';
        $this->edit_notes = $res->notes ?? '';
        $this->edit_customer_name = $res->customer ? $res->customer->name : '';
        $this->edit_customer_phone = $res->customer ? $res->customer->phone : '';

        // Load existing addon items
        $this->edit_addon_items = [];
        foreach ($res->items as $item) {
            $this->edit_addon_items[] = [
                'dining_service_id' => $item->dining_service_id,
                'name' => $item->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (int) $item->unit_price,
            ];
        }
        $this->recalculateEditAddons();
        $this->showEditModal = true;
    }

    public function saveEditedReservation(): void
    {
        $this->validate([
            'edit_reservation_date' => 'required|date',
            'edit_time_slot' => 'required|string',
            'edit_pax' => 'required|integer|min:1',
            'edit_customer_name' => 'required|string|min:3',
            'edit_customer_phone' => 'required|string|min:8',
            'edit_activity_link' => 'nullable|url',
        ]);

        try {
            $res = DiningReservation::findOrFail($this->editing_reservation_id);

            FineDiningService::updateReservation($res, [
                'group_name' => $this->edit_group_name,
                'pic_name' => $this->edit_pic_name,
                'pic_phone' => $this->edit_pic_phone,
                'activity_link' => $this->edit_activity_link,
                'is_wikrama_teachers' => $this->edit_is_wikrama_teachers,
                'dining_package_id' => $this->edit_dining_package_id,
                'custom_menu_name' => $this->edit_custom_menu_name,
                'table_number_or_area' => $this->edit_table_number_or_area,
                'reservation_date' => $this->edit_reservation_date,
                'time_slot' => $this->edit_time_slot,
                'pax' => (int) ($this->edit_pax ?? 1),
                'custom_price_per_pax' => (int) ($this->edit_custom_price_per_pax ?? 0),
                'apply_tax' => $this->edit_apply_tax,
                'discount_amount' => (int) ($this->edit_discount_amount ?? 0),
                'special_requests' => $this->edit_special_requests,
                'notes' => $this->edit_notes,
                'customer_name' => $this->edit_customer_name,
                'customer_phone' => $this->edit_customer_phone,
                'service_items' => $this->edit_addon_items,
            ]);

            $this->showEditModal = false;
            session()->flash('message', "Pesanan resto #{$res->reservation_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            $this->addError('edit_general', $e->getMessage());
        }
    }

    // --- DETAIL MODAL ---

    public function openDetailModal(int $id): void
    {
        $this->detailReservationId = $id;
        $this->showDetailModal = true;
    }

    // --- PAYMENT RECORDING ---

    public function openPaymentModal(int $id): void
    {
        $this->resetErrorBag();
        $this->selectedReservationId = $id;
        $res = DiningReservation::findOrFail($id);
        $this->payment_amount = $res->balance;
        $this->payment_method = 'cash';
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $this->validate([
            'payment_amount' => 'required|integer|min:1000',
        ]);

        try {
            $res = DiningReservation::findOrFail($this->selectedReservationId);
            FineDiningService::recordPayment($res, (int) $this->payment_amount, $this->payment_method);
            $this->showPaymentModal = false;
            session()->flash('message', "Pembayaran resto #{$res->reservation_number} berhasil dicatat.");
        } catch (\Exception $e) {
            $this->addError('payment', $e->getMessage());
        }
    }

    public function updateStatus(int $id, string $status): void
    {
        $res = DiningReservation::findOrFail($id);
        $res->update(['status' => $status]);
        AuditLog::record('dining_status_update', $res, null, ['status' => $status], "Status resto diubah menjadi {$status}");
        session()->flash('message', "Status reservasi #{$res->reservation_number} diubah menjadi {$status}.");
    }

    // --- MASTER MANAGEMENT MODAL (PAKET & LAYANAN TAMBAHAN) ---

    public function openMasterModal(string $tab = 'packages'): void
    {
        $this->masterTab = $tab;
        $this->resetPackageForm();
        $this->resetServiceForm();
        $this->showMasterModal = true;
        $this->showPackageModal = ($tab === 'packages');
    }

    public function openPackageModal(): void
    {
        $this->openMasterModal('packages');
    }

    public function closeMasterModal(): void
    {
        $this->showMasterModal = false;
        $this->showPackageModal = false;
    }

    public function resetPackageForm(): void
    {
        $this->editing_package_id = null;
        $this->package_name = '';
        $this->package_price_per_pax = 50000;
        $this->package_high_price_per_pax = 50000;
        $this->is_package_high_season_same = true;
        $this->package_min_pax = 2;
        $this->package_menu_details = '';
        $this->package_is_active = true;
        $this->package_photo = null;
        $this->existing_image_path = null;
        $this->resetErrorBag();
    }

    public function editPackage(int $id): void
    {
        $pkg = DiningPackage::findOrFail($id);
        $this->editing_package_id = $pkg->id;
        $this->package_name = $pkg->name;
        $this->package_price_per_pax = (int) $pkg->price_per_pax;
        $this->package_high_price_per_pax = $pkg->high_price_per_pax ?: (int) $pkg->price_per_pax;
        $this->is_package_high_season_same = empty($pkg->high_price_per_pax) || ($pkg->high_price_per_pax == $pkg->price_per_pax);
        $this->package_min_pax = (int) $pkg->min_pax;
        $this->package_menu_details = $pkg->menu_details ?? '';
        $this->package_is_active = (bool) $pkg->is_active;
        $this->existing_image_path = $pkg->image_path;
        $this->package_photo = null;
        $this->resetErrorBag();
    }

    public function savePackage(): void
    {
        $this->validate([
            'package_name' => 'required|string|min:3|max:255',
            'package_price_per_pax' => 'required|integer|min:0',
            'package_high_price_per_pax' => 'nullable|integer|min:0',
            'package_min_pax' => 'required|integer|min:1',
            'package_menu_details' => 'nullable|string',
            'package_photo' => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->existing_image_path;
        if ($this->package_photo) {
            $imagePath = $this->package_photo->store('dining_packages', 'public');
        }

        $highPrice = $this->is_package_high_season_same
            ? (int) $this->package_price_per_pax
            : (int) ($this->package_high_price_per_pax ?: $this->package_price_per_pax);

        if ($this->editing_package_id) {
            $pkg = DiningPackage::findOrFail($this->editing_package_id);
            $pkg->update([
                'name' => $this->package_name,
                'price_per_pax' => (int) $this->package_price_per_pax,
                'high_price_per_pax' => $highPrice,
                'min_pax' => (int) $this->package_min_pax,
                'menu_details' => $this->package_menu_details,
                'image_path' => $imagePath,
                'is_active' => $this->package_is_active,
            ]);
            session()->flash('package_message', "Paket {$pkg->name} berhasil diperbarui.");
        } else {
            DiningPackage::create([
                'name' => $this->package_name,
                'price_per_pax' => (int) $this->package_price_per_pax,
                'high_price_per_pax' => $highPrice,
                'min_pax' => (int) $this->package_min_pax,
                'menu_details' => $this->package_menu_details,
                'image_path' => $imagePath,
                'is_active' => $this->package_is_active,
            ]);
            session()->flash('package_message', "Paket menu {$this->package_name} berhasil ditambahkan.");
        }

        $this->resetPackageForm();
        $this->updateCalculation();
    }

    public function togglePackageStatus(int $id): void
    {
        $pkg = DiningPackage::findOrFail($id);
        $pkg->update(['is_active' => !$pkg->is_active]);
        session()->flash('package_message', "Status paket {$pkg->name} diubah menjadi " . ($pkg->is_active ? 'Aktif' : 'Non-Aktif') . ".");
        $this->updateCalculation();
    }

    public function deletePackage(int $id): void
    {
        $pkg = DiningPackage::findOrFail($id);
        $pkg->delete();
        session()->flash('package_message', "Paket {$pkg->name} telah dipindahkan ke arsip (Soft Delete).");
        $this->resetPackageForm();
        $this->updateCalculation();
    }

    public function restorePackage(int $id): void
    {
        $pkg = DiningPackage::onlyTrashed()->findOrFail($id);
        $pkg->restore();
        session()->flash('package_message', "Paket {$pkg->name} berhasil dipulihkan dari arsip.");
        $this->updateCalculation();
    }

    // --- MASTER LAYANAN TAMBAHAN DINING ---

    public function resetServiceForm(): void
    {
        $this->editing_service_id = null;
        $this->service_name = '';
        $this->service_default_price = 0;
        $this->service_unit = 'porsi';
        $this->service_is_active = true;
        $this->resetErrorBag();
    }

    public function editService(int $id): void
    {
        $svc = DiningService::findOrFail($id);
        $this->editing_service_id = $svc->id;
        $this->service_name = $svc->name;
        $this->service_default_price = (int) $svc->default_price;
        $this->service_unit = $svc->unit ?? 'porsi';
        $this->service_is_active = (bool) $svc->is_active;
        $this->resetErrorBag();
    }

    public function saveService(): void
    {
        $this->validate([
            'service_name' => 'required|string|min:3|max:255',
            'service_default_price' => 'required|integer|min:0',
            'service_unit' => 'required|string|max:50',
        ]);

        if ($this->editing_service_id) {
            $svc = DiningService::findOrFail($this->editing_service_id);
            $svc->update([
                'name' => $this->service_name,
                'default_price' => (int) $this->service_default_price,
                'unit' => $this->service_unit,
                'is_active' => $this->service_is_active,
            ]);
            session()->flash('service_message', "Layanan tambahan {$svc->name} berhasil diperbarui.");
        } else {
            DiningService::create([
                'name' => $this->service_name,
                'default_price' => (int) $this->service_default_price,
                'unit' => $this->service_unit,
                'is_active' => $this->service_is_active,
            ]);
            session()->flash('service_message', "Layanan tambahan {$this->service_name} berhasil ditambahkan.");
        }

        $this->resetServiceForm();
    }

    public function toggleServiceStatus(int $id): void
    {
        $svc = DiningService::findOrFail($id);
        $svc->update(['is_active' => !$svc->is_active]);
        session()->flash('service_message', "Status layanan {$svc->name} diubah.");
    }

    public function deleteService(int $id): void
    {
        $svc = DiningService::findOrFail($id);
        $svc->delete();
        session()->flash('service_message', "Layanan {$svc->name} telah dipindahkan ke arsip (Soft Delete).");
        $this->resetServiceForm();
    }

    public function restoreService(int $id): void
    {
        $svc = DiningService::onlyTrashed()->findOrFail($id);
        $svc->restore();
        session()->flash('service_message', "Layanan {$svc->name} berhasil dipulihkan.");
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingViewTab(): void { $this->resetPage(); $this->statusFilter = 'all'; }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingDateFilter(): void { $this->resetPage(); }

    public function render()
    {
        $today = now()->toDateString();
        $query = DiningReservation::with(['customer', 'diningPackage', 'items'])->latest();

        if ($this->viewTab === 'active') {
            $query->whereIn('status', ['pending', 'confirmed', 'seated'])
                  ->whereDate('reservation_date', '>=', $today);
        } elseif ($this->viewTab === 'history') {
            $query->where(function ($sub) use ($today) {
                $sub->whereIn('status', ['completed', 'cancelled'])
                    ->orWhereDate('reservation_date', '<', $today);
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->dateFilter)) {
            $query->whereDate('reservation_date', $this->dateFilter);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reservation_number', 'like', "%{$this->search}%")
                  ->orWhere('group_name', 'like', "%{$this->search}%")
                  ->orWhere('pic_name', 'like', "%{$this->search}%")
                  ->orWhere('table_number_or_area', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('phone', 'like', "%{$this->search}%");
                  });
            });
        }

        $allPackages = $this->packageFilter === 'trashed'
            ? DiningPackage::onlyTrashed()->orderBy('name')->get()
            : DiningPackage::orderBy('name')->get();

        $allServices = $this->serviceFilter === 'trashed'
            ? DiningService::onlyTrashed()->orderBy('name')->get()
            : DiningService::orderBy('name')->get();

        $activeCount = DiningReservation::whereIn('status', ['pending', 'confirmed', 'seated'])
            ->whereDate('reservation_date', '>=', $today)->count();
        $historyCount = DiningReservation::where(function ($sub) use ($today) {
            $sub->whereIn('status', ['completed', 'cancelled'])
                ->orWhereDate('reservation_date', '<', $today);
        })->count();

        return view('livewire.dining-reservation-management', [
            'reservations' => $query->paginate(10),
            'diningPackages' => DiningPackage::where('is_active', true)->get(),
            'masterServices' => DiningService::where('is_active', true)->orderBy('name')->get(),
            'allPackages' => $allPackages,
            'allServices' => $allServices,
            'activePackagesCount' => DiningPackage::count(),
            'trashedPackagesCount' => DiningPackage::onlyTrashed()->count(),
            'activeServicesCount' => DiningService::count(),
            'trashedServicesCount' => DiningService::onlyTrashed()->count(),
            'activeCount' => $activeCount,
            'historyCount' => $historyCount,
            'selectedReservation' => $this->selectedReservationId ? DiningReservation::with(['customer', 'diningPackage', 'payments', 'items'])->find($this->selectedReservationId) : null,
            'detailReservation' => $this->detailReservationId ? DiningReservation::with(['customer', 'diningPackage', 'payments', 'items'])->find($this->detailReservationId) : null,
        ]);
    }
}
