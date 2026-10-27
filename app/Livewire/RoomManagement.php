<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class RoomManagement extends Component
{
    use WithFileUploads;

    public const COLOR_PALETTE = [
        ['hex' => '#059669', 'name' => 'Emerald Green'],
        ['hex' => '#0d9488', 'name' => 'Teal'],
        ['hex' => '#0891b2', 'name' => 'Cyan'],
        ['hex' => '#0284c7', 'name' => 'Sky Blue'],
        ['hex' => '#2563eb', 'name' => 'Royal Blue'],
        ['hex' => '#4f46e5', 'name' => 'Indigo'],
        ['hex' => '#7c3aed', 'name' => 'Violet'],
        ['hex' => '#9333ea', 'name' => 'Purple'],
        ['hex' => '#c026d3', 'name' => 'Fuchsia'],
        ['hex' => '#db2777', 'name' => 'Pink'],
        ['hex' => '#e11d48', 'name' => 'Rose'],
        ['hex' => '#dc2626', 'name' => 'Crimson Red'],
        ['hex' => '#ea580c', 'name' => 'Terracotta Orange'],
        ['hex' => '#d97706', 'name' => 'Amber'],
        ['hex' => '#ca8a04', 'name' => 'Golden Yellow'],
        ['hex' => '#65a30d', 'name' => 'Lime Green'],
        ['hex' => '#16a34a', 'name' => 'Forest Green'],
        ['hex' => '#475569', 'name' => 'Slate Grey'],
        ['hex' => '#78716c', 'name' => 'Warm Stone'],
        ['hex' => '#3f3f46', 'name' => 'Deep Charcoal'],
    ];

    public string $activeTab = 'rooms'; // 'rooms' or 'types'

    // Room form state
    public bool $showRoomModal = false;
    public ?int $editingRoomId = null;
    public string $room_number = '';
    public int $room_type_id = 0;
    public string $floor = 'Lantai 1';
    public string $operational_status = 'active';
    public string $cleaning_status = 'clean';
    public string $notes = '';

    // RoomType form state
    public bool $showTypeModal = false;
    public ?int $editingTypeId = null;
    public string $type_name = '';
    public string $type_code = '';
    public string $type_description = '';
    public string $type_color = '#059669';
    public int $standard_capacity = 2;
    public int $max_capacity = 3;
    public int $base_price = 350000;
    public ?int $high_season_price = 350000;
    public bool $is_high_season_same = true;
    public int $extra_person_fee = 75000;
    public bool $is_active = true;
    public int $website_quota = 0;

    // Photos & Facilities
    public array $existingPhotos = [];
    public $uploadedPhotos = [];
    public string $coverType = 'existing'; // 'existing' or 'uploaded'
    public int $coverIndex = 0;
    public array $facilitiesList = [
        ['icon' => '🛜', 'text' => 'WiFi Cepat 100 Mbps'],
        ['icon' => '❄️', 'text' => 'AC Inverter'],
        ['icon' => '📺', 'text' => 'Smart TV'],
    ];

    // Delete confirmation state
    public ?int $confirmingDeleteRoomId = null;
    public ?int $confirmingDeleteTypeId = null;

    protected function rules(): array
    {
        if ($this->showRoomModal) {
            return [
                'room_number' => 'required|string|max:10|unique:rooms,room_number,' . $this->editingRoomId,
                'room_type_id' => 'required|exists:room_types,id',
                'floor' => 'nullable|string|max:50',
                'operational_status' => 'required|in:active,maintenance,disabled',
                'cleaning_status' => 'required|in:dirty,cleaning,clean,inspected',
                'notes' => 'nullable|string',
            ];
        }

        return [
            'type_name' => 'required|string|max:100',
            'type_code' => 'required|string|max:10|unique:room_types,code,' . $this->editingTypeId,
            'type_description' => 'nullable|string',
            'type_color' => 'required|string|max:30',
            'standard_capacity' => 'required|integer|min:1',
            'max_capacity' => 'required|integer|min:1',
            'base_price' => 'required|integer|min:0',
            'high_season_price' => 'nullable|integer|min:0',
            'is_high_season_same' => 'boolean',
            'extra_person_fee' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'website_quota' => 'required|integer|min:0',
            'uploadedPhotos.*' => 'nullable|image|max:4096',
        ];
    }

    public function openRoomModal(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->editingRoomId = $id;

        if ($id) {
            $room = Room::findOrFail($id);
            $this->room_number = $room->room_number;
            $this->room_type_id = $room->room_type_id ?? 0;
            $this->floor = $room->floor ?? 'Lantai 1';
            $this->operational_status = $room->operational_status;
            $this->cleaning_status = $room->cleaning_status;
            $this->notes = $room->notes ?? '';
        } else {
            $this->room_number = '';
            $this->room_type_id = RoomType::first()->id ?? 0;
            $this->floor = 'Lantai 1';
            $this->operational_status = 'active';
            $this->cleaning_status = 'clean';
            $this->notes = '';
        }

        $this->showRoomModal = true;
    }

    public function saveRoom(): void
    {
        $this->validate();

        $data = [
            'room_number' => $this->room_number,
            'room_type_id' => $this->room_type_id,
            'floor' => $this->floor,
            'operational_status' => $this->operational_status,
            'cleaning_status' => $this->cleaning_status,
            'notes' => $this->notes,
        ];

        if ($this->editingRoomId) {
            $room = Room::findOrFail($this->editingRoomId);
            $room->update($data);
            AuditLog::record('update_room', $room, null, $data, "Perbarui kamar {$room->room_number}");
        } else {
            $room = Room::create($data);
            AuditLog::record('create_room', $room, null, $data, "Tambah kamar baru {$room->room_number}");
        }

        $this->showRoomModal = false;
        session()->flash('message', 'Data kamar berhasil disimpan.');
    }

    public function confirmDeleteRoom(int $id): void
    {
        $this->confirmingDeleteRoomId = $id;
    }

    public function cancelDeleteRoom(): void
    {
        $this->confirmingDeleteRoomId = null;
    }

    public function deleteRoom(): void
    {
        if (!$this->confirmingDeleteRoomId) return;

        $room = Room::with('roomType')->findOrFail($this->confirmingDeleteRoomId);

        // Check if currently occupied
        $isOccupied = ReservationRoom::where('room_id', $room->id)
            ->whereHas('reservation', function ($q) {
                $q->where('status', 'checked_in');
            })->exists();

        if ($isOccupied) {
            session()->flash('error', "Kamar {$room->room_number} sedang ditempati tamu check-in. Check-out tamu terlebih dahulu.");
            $this->confirmingDeleteRoomId = null;
            return;
        }

        // Snapshot room details into all associated reservation_rooms
        ReservationRoom::where('room_id', $room->id)->each(function ($rr) use ($room) {
            $rr->update([
                'room_snapshot' => [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'floor' => $room->floor,
                    'room_type_name' => $room->roomType?->name ?? 'Standard',
                    'room_type_code' => $room->roomType?->code ?? 'STD',
                    'deleted_at' => now()->toDateTimeString(),
                ],
            ]);
        });

        AuditLog::record('delete_room', $room, $room->toArray(), null, "Hapus permanen kamar fisik {$room->room_number} dengan snapshot");
        $room->delete();

        $this->confirmingDeleteRoomId = null;
        session()->flash('message', "Kamar {$room->room_number} berhasil dihapus permanen. Riwayat transaksi masa lalu telah diamankan via snapshot.");
    }

    public function openTypeModal(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->editingTypeId = $id;

        if ($id) {
            $type = RoomType::findOrFail($id);
            $this->type_name = $type->name;
            $this->type_code = $type->code;
            $this->type_description = $type->description ?? '';
            $this->type_color = $type->color ?? '#059669';
            $this->standard_capacity = $type->standard_capacity;
            $this->max_capacity = $type->max_capacity;
            $this->base_price = $type->base_price;
            $this->high_season_price = $type->high_season_price ?: $type->base_price;
            $this->is_high_season_same = empty($type->high_season_price) || ($type->high_season_price == $type->base_price);
            $this->extra_person_fee = $type->extra_person_fee;
            $this->is_active = $type->is_active;
            $this->website_quota = (int) ($type->website_quota ?? $type->rooms()->where('operational_status', 'active')->count());

            $this->existingPhotos = $type->all_photos;
            $this->uploadedPhotos = [];
            $this->coverType = 'existing';
            $this->coverIndex = 0;
            $this->facilitiesList = $type->structured_facilities;
            if (empty($this->facilitiesList)) {
                $this->facilitiesList = [['icon' => '✓', 'text' => '']];
            }
        } else {
            $this->type_name = '';
            $this->type_code = '';
            $this->type_description = '';
            $this->type_color = '#059669';
            $this->standard_capacity = 2;
            $this->max_capacity = 3;
            $this->base_price = 400000;
            $this->high_season_price = 400000;
            $this->is_high_season_same = true;
            $this->extra_person_fee = 75000;
            $this->is_active = true;
            $this->website_quota = 0;

            $this->existingPhotos = [];
            $this->uploadedPhotos = [];
            $this->coverType = 'existing';
            $this->coverIndex = 0;
            $this->facilitiesList = [
                ['icon' => '🛜', 'text' => 'WiFi Cepat 100 Mbps'],
                ['icon' => '❄️', 'text' => 'AC Inverter'],
                ['icon' => '📺', 'text' => 'Smart TV'],
                ['icon' => '🚿', 'text' => 'Water Heater'],
                ['icon' => '🕋', 'text' => 'Sajadah & Arah Kiblat'],
            ];
        }

        $this->showTypeModal = true;
    }

    public function setCover(string $type, int $index): void
    {
        $this->coverType = $type;
        $this->coverIndex = $index;
    }

    public function addFacility(): void
    {
        $this->facilitiesList[] = ['icon' => '✓', 'text' => ''];
    }

    public function removeFacility(int $index): void
    {
        if (isset($this->facilitiesList[$index])) {
            unset($this->facilitiesList[$index]);
            $this->facilitiesList = array_values($this->facilitiesList);
        }
    }

    public function removeExistingPhoto(int $index): void
    {
        if (isset($this->existingPhotos[$index])) {
            unset($this->existingPhotos[$index]);
            $this->existingPhotos = array_values($this->existingPhotos);
            if ($this->coverType === 'existing' && $this->coverIndex >= count($this->existingPhotos)) {
                $this->coverIndex = max(0, count($this->existingPhotos) - 1);
            }
        }
    }

    public function removeUploadedPhoto(int $index): void
    {
        if (isset($this->uploadedPhotos[$index])) {
            unset($this->uploadedPhotos[$index]);
            $this->uploadedPhotos = array_values($this->uploadedPhotos);
            if ($this->coverType === 'uploaded' && $this->coverIndex >= count($this->uploadedPhotos)) {
                $this->coverType = !empty($this->existingPhotos) ? 'existing' : 'uploaded';
                $this->coverIndex = 0;
            }
        }
    }

    public function saveType(): void
    {
        $this->validate();

        // Validate photo count (min 1, max 10)
        $totalPhotos = count($this->existingPhotos) + count($this->uploadedPhotos);
        if ($totalPhotos < 1) {
            $this->addError('photos', 'Wajib mengunggah minimal 1 foto tipe kamar.');
            return;
        }
        if ($totalPhotos > 10) {
            $this->addError('photos', "Maksimal 10 foto tipe kamar (saat ini terdeteksi {$totalPhotos} foto).");
            return;
        }

        // Clean & validate facilities
        $cleanFacilities = [];
        foreach ($this->facilitiesList as $f) {
            $text = trim($f['text'] ?? '');
            if ($text !== '') {
                $cleanFacilities[] = [
                    'icon' => trim($f['icon'] ?? '') ?: '✓',
                    'text' => $text,
                ];
            }
        }

        if (empty($cleanFacilities)) {
            $this->addError('facilities', 'Wajib menambahkan minimal 1 fasilitas.');
            return;
        }

        // Store new uploaded photos
        $newPhotoPaths = [];
        foreach ($this->uploadedPhotos as $photo) {
            $path = $photo->store('room-types', 'public');
            $newPhotoPaths[] = '/storage/' . $path;
        }

        // Determine cover photo and arrange so cover is at index 0
        $coverUrl = null;
        $existingCopy = $this->existingPhotos;

        if ($this->coverType === 'uploaded' && isset($newPhotoPaths[$this->coverIndex])) {
            $coverUrl = $newPhotoPaths[$this->coverIndex];
            unset($newPhotoPaths[$this->coverIndex]);
        } elseif ($this->coverType === 'existing' && isset($existingCopy[$this->coverIndex])) {
            $coverUrl = $existingCopy[$this->coverIndex];
            unset($existingCopy[$this->coverIndex]);
        }

        $remaining = array_merge(array_values($existingCopy), array_values($newPhotoPaths));
        $allPhotos = $coverUrl ? array_merge([$coverUrl], $remaining) : $remaining;
        $highPrice = $this->is_high_season_same ? $this->base_price : ($this->high_season_price ?: $this->base_price);

        $data = [
            'name' => $this->type_name,
            'code' => strtoupper($this->type_code),
            'description' => $this->type_description,
            'color' => $this->type_color,
            'standard_capacity' => $this->standard_capacity,
            'max_capacity' => $this->max_capacity,
            'base_price' => $this->base_price,
            'high_season_price' => $highPrice,
            'extra_person_fee' => $this->extra_person_fee,
            'facilities' => $cleanFacilities,
            'photos' => $allPhotos,
            'photo_path' => $allPhotos[0] ?? null,
            'is_active' => $this->is_active,
            'website_quota' => $this->website_quota,
        ];

        if ($this->editingTypeId) {
            $type = RoomType::findOrFail($this->editingTypeId);
            $type->update($data);
            AuditLog::record('update_room_type', $type, null, $data, "Perbarui tipe kamar {$type->name}");
        } else {
            $type = RoomType::create($data);
            AuditLog::record('create_room_type', $type, null, $data, "Tambah tipe kamar baru {$type->name}");
        }

        $this->showTypeModal = false;
        session()->flash('message', 'Tipe kamar berhasil disimpan.');
    }

    public function confirmDeleteType(int $id): void
    {
        $this->confirmingDeleteTypeId = $id;
    }

    public function cancelDeleteType(): void
    {
        $this->confirmingDeleteTypeId = null;
    }

    public function deleteType(): void
    {
        if (!$this->confirmingDeleteTypeId) return;

        $type = RoomType::withCount('rooms')->findOrFail($this->confirmingDeleteTypeId);

        // Check if still has physical rooms
        if ($type->rooms_count > 0) {
            session()->flash('error', "Tipe kamar {$type->name} masih memiliki {$type->rooms_count} kamar fisik. Hapus atau pindahkan kamar fisik terlebih dahulu.");
            $this->confirmingDeleteTypeId = null;
            return;
        }

        // Snapshot room type into all associated reservations
        Reservation::where('room_type_id', $type->id)->each(function ($res) use ($type) {
            $res->update([
                'room_type_snapshot' => [
                    'room_type_id' => $type->id,
                    'name' => $type->name,
                    'code' => $type->code,
                    'base_price' => $type->base_price,
                    'high_season_price' => $type->high_season_price,
                    'facilities' => $type->facilities,
                    'deleted_at' => now()->toDateTimeString(),
                ],
            ]);
        });

        AuditLog::record('delete_room_type', $type, $type->toArray(), null, "Hapus permanen tipe kamar {$type->name} dengan snapshot");
        $type->delete();

        $this->confirmingDeleteTypeId = null;
        session()->flash('message', "Tipe kamar {$type->name} berhasil dihapus permanen. Riwayat masa lalu telah diamankan via snapshot.");
    }

    public function render()
    {
        return view('livewire.room-management', [
            'rooms' => Room::with('roomType')->orderBy('room_number')->get(),
            'roomTypes' => RoomType::withCount('rooms')->orderBy('base_price')->get(),
            'palette' => self::COLOR_PALETTE,
        ]);
    }
}
