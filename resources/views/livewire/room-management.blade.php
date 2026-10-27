<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Master Data Kamar</h1>
                <p class="text-sm text-gray-500 mt-1">Konfigurasi kamar fisik dan tipe kamar InSitu Hotel Syariah Garut</p>
            </div>
            
            <div class="flex items-center gap-3">
                @if($activeTab === 'rooms')
                    <button wire:click="openRoomModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Kamar Fisik
                    </button>
                @else
                    <button wire:click="openTypeModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Tipe Kamar
                    </button>
                @endif
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('message') }}
            </div>
        @endif

        @if(session()->has('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- Tab Selector -->
        <div class="flex border-b border-gray-200 space-x-8">
            <button wire:click="$set('activeTab', 'rooms')" class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'rooms' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                <span>Kamar Fisik</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'rooms' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $rooms->count() }}</span>
            </button>
            <button wire:click="$set('activeTab', 'types')" class="pb-3 text-sm font-semibold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'types' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                <span>Tipe Kamar</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'types' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $roomTypes->count() }}</span>
            </button>
        </div>

        <!-- TAB 1: TABEL KAMAR FISIK -->
        @if($activeTab === 'rooms')
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">No. Kamar</th>
                                <th class="px-4 py-3 text-left">Tipe Kamar</th>
                                <th class="px-4 py-3 text-left">Lantai / Lokasi</th>
                                <th class="px-4 py-3 text-center">Status Operasional</th>
                                <th class="px-4 py-3 text-center">Status Kebersihan</th>
                                <th class="px-4 py-3 text-left">Catatan</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($rooms as $room)
                                <tr class="hover:bg-gray-50/80 transition {{ $room->operational_status !== 'active' ? 'bg-red-50/20' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-base font-black text-gray-900 tracking-tight">{{ $room->room_number }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($room->roomType)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold text-white shadow-xs" style="background-color: {{ $room->roomType->color ?? '#059669' }};">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                {{ $room->roomType->name }} ({{ $room->roomType->code }})
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Tanpa Tipe</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 font-medium">
                                        {{ $room->floor ?? 'Lantai 1' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if($room->operational_status === 'active')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Active
                                            </span>
                                        @elseif($room->operational_status === 'maintenance')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Maintenance
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                                Disabled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if($room->cleaning_status === 'clean' || $room->cleaning_status === 'inspected')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ ucfirst($room->cleaning_status) }}
                                            </span>
                                        @elseif($room->cleaning_status === 'cleaning')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Cleaning
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Dirty
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">
                                        {{ $room->notes ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                        <button wire:click="openRoomModal({{ $room->id }})" class="px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-md transition">
                                            Edit
                                        </button>
                                        <button wire:click="confirmDeleteRoom({{ $room->id }})" class="px-2.5 py-1 text-xs font-semibold text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 rounded-md transition">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                        Belum ada kamar fisik. Klik <strong>Tambah Kamar Fisik</strong> untuk membuat kamar baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 2: TABEL TIPE KAMAR -->
        @if($activeTab === 'types')
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Tipe & Warna</th>
                                <th class="px-3 py-3 text-center w-16">Sampul</th>
                                <th class="px-4 py-3 text-left">Deskripsi</th>
                                <th class="px-4 py-3 text-center">Kapasitas</th>
                                <th class="px-4 py-3 text-right">Tarif Normal</th>
                                <th class="px-4 py-3 text-right">Tarif High Season</th>
                                <th class="px-4 py-3 text-left">Fasilitas</th>
                                <th class="px-4 py-3 text-center">Kamar Fisik</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($roomTypes as $type)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs border border-white" style="background-color: {{ $type->color ?? '#059669' }};"></span>
                                            <div>
                                                <div class="font-bold text-gray-900">{{ $type->name }}</div>
                                                <span class="text-[11px] font-mono font-semibold px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded">{{ $type->code }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-center">
                                        @php
                                            $coverPhoto = $type->primary_photo;
                                            $totalPhotos = count($type->all_photos);
                                        @endphp
                                        @if($coverPhoto)
                                            <div class="relative inline-block w-12 h-12 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 shadow-2xs">
                                                <img src="{{ $coverPhoto }}" alt="{{ $type->name }}" class="w-full h-full object-cover">
                                                <span class="absolute bottom-0 right-0 bg-black/75 text-white text-[8px] font-bold px-1 rounded-tl shadow">
                                                    {{ $totalPhotos }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="inline-flex w-12 h-12 rounded-lg bg-gray-50 border border-dashed border-gray-300 items-center justify-center text-gray-400 text-[10px]">
                                                No Foto
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="max-w-xs">
                                            <p class="text-xs text-gray-600 line-clamp-2">{{ $type->description ?: 'Belum ada deskripsi.' }}</p>
                                            <span class="text-[10px] text-gray-400 mt-0.5 block">Ekstra Orang: {{ rupiah($type->extra_person_fee) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-xs">
                                        <span class="font-bold text-gray-800">{{ $type->standard_capacity }}</span> org
                                        <span class="text-gray-400 block text-[11px]">(Maks {{ $type->max_capacity }})</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="text-xs">
                                            <span class="font-bold text-emerald-700 text-sm">{{ rupiah($type->base_price) }}</span>
                                            <span class="text-[10px] text-gray-500 block">🍳 Dgn Sarapan</span>
                                            <span class="text-[11px] font-medium text-gray-600 block mt-0.5">{{ rupiah($type->price_without_breakfast) }}</span>
                                            <span class="text-[10px] text-gray-400 block">☕ Tanpa Sarapan</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="text-xs">
                                            <span class="font-bold text-amber-700 text-sm">{{ rupiah($type->high_season_price ?: $type->base_price) }}</span>
                                            <span class="text-[10px] text-gray-500 block">🍳 Dgn Sarapan</span>
                                            <span class="text-[11px] font-medium text-amber-800 block mt-0.5">{{ rupiah($type->high_season_price_without_breakfast ?: $type->price_without_breakfast) }}</span>
                                            <span class="text-[10px] text-gray-400 block">☕ Tanpa Sarapan</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @php $facilities = $type->structured_facilities; @endphp
                                            @forelse(array_slice($facilities, 0, 3) as $fac)
                                                <span class="inline-flex items-center gap-1 text-[11px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded">
                                                    <span>{{ $fac['icon'] }}</span>
                                                    <span>{{ $fac['text'] }}</span>
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-400 italic">-</span>
                                            @endforelse
                                            @if(count($facilities) > 3)
                                                <span class="text-[10px] font-semibold text-gray-500 self-center">+{{ count($facilities) - 3 }} lainnya</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <span class="font-bold text-gray-900 text-xs px-2.5 py-0.5 bg-gray-100 rounded-full">
                                                {{ $type->rooms_count }} Kamar
                                            </span>
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ ($type->website_quota ?? 0) > 0 ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                🌐 Kuota Web: {{ $type->website_quota ?? 0 }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $type->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $type->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                        <button wire:click="openTypeModal({{ $type->id }})" class="px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-md transition">
                                            Edit
                                        </button>
                                        <button wire:click="confirmDeleteType({{ $type->id }})" class="px-2.5 py-1 text-xs font-semibold text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 rounded-md transition">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">
                                        Belum ada tipe kamar. Klik <strong>Tambah Tipe Kamar</strong> untuk membuat tipe kamar baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- MODAL KAMAR FISIK -->
        @if($showRoomModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $editingRoomId ? 'Edit Kamar Fisik' : 'Tambah Kamar Fisik Baru' }}
                    </h3>

                    <form wire:submit="saveRoom" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Kamar <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="room_number" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 font-bold" placeholder="Contoh: 101" required>
                            @error('room_number') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tipe Kamar <span class="text-red-500">*</span></label>
                            <select wire:model="room_type_id" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                                <option value="">Pilih Tipe</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ rupiah($type->base_price) }})</option>
                                @endforeach
                            </select>
                            @error('room_type_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Lantai / Lokasi</label>
                            <input type="text" wire:model="floor" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Lantai 1">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Operasional</label>
                                <select wire:model="operational_status" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="active">Active (Siap Pakai)</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Kebersihan</label>
                                <select wire:model="cleaning_status" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="clean">Bersih (Clean)</option>
                                    <option value="inspected">Diperiksa (Inspected)</option>
                                    <option value="cleaning">Sedang Dibersihkan</option>
                                    <option value="dirty">Kotor (Dirty)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea wire:model="notes" rows="2" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Catatan fasilitas khusus..."></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" wire:click="$set('showRoomModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                                Simpan Kamar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- MODAL TIPE KAMAR (PALET 20 WARNA, MULTI-FOTO, FASILITAS 2 KOLOM) -->
        @if($showTypeModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 space-y-5 my-8 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ $editingTypeId ? 'Edit Tipe Kamar' : 'Tambah Tipe Kamar Baru' }}
                            </h3>
                            <p class="text-xs text-gray-500">Konfigurasi tarif, kapasitas, foto showcase, dan fasilitas tipe kamar</p>
                        </div>
                        <button type="button" wire:click="$set('showTypeModal', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit="saveType" class="space-y-5">
                        <!-- 1. IDENTITAS DASAR & PALET 20 WARNA -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Tipe Kamar <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="type_name" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: Deluxe Room" required>
                                    @error('type_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Singkat <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="type_code" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 uppercase font-mono" placeholder="DLX" required>
                                    @error('type_code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- DROPDOWN 20 PILIHAN WARNA -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1 flex items-center justify-between">
                                    <span>Kode Warna Identitas Tipe Kamar (Pilih dari 20 Warna) <span class="text-red-500">*</span></span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-mono font-bold text-white shadow-xs" style="background-color: {{ $type_color }};">
                                        <span class="w-2 h-2 rounded-full bg-white"></span>
                                        {{ $type_color }}
                                    </span>
                                </label>
                                <div class="relative">
                                    <select wire:model.live="type_color" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 pl-9 font-medium" required>
                                        @foreach($palette as $item)
                                            <option value="{{ $item['hex'] }}">
                                                {{ $item['name'] }} ({{ $item['hex'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full border border-white shadow-xs pointer-events-none" style="background-color: {{ $type_color }};"></span>
                                </div>
                                @error('type_color') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Kamar</label>
                                <textarea wire:model="type_description" rows="2" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Deskripsi kenyamanan, suasana, dan pemandangan kamar..."></textarea>
                            </div>
                        </div>

                        <!-- 2. TARIF NORMAL & HIGH SEASON -->
                        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tarif Normal (Dgn Sarapan) <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="base_price" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 font-bold text-gray-900" placeholder="400000" required>
                                    <span class="text-[10px] text-gray-500">Harga default termasuk sarapan.</span>
                                    @error('base_price') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tarif Normal (Tanpa Sarapan)</label>
                                    <input type="number" wire:model="price_without_breakfast" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 font-semibold text-gray-700" placeholder="360000">
                                    <span class="text-[10px] text-gray-500">Kosongkan jika otomatis (-Rp 40.000).</span>
                                    @error('price_without_breakfast') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Biaya Ekstra Orang (Rp)</label>
                                    <input type="number" wire:model="extra_person_fee" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-200">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model.live="is_high_season_same" class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-xs font-semibold text-gray-800">Tarif High Season sama dengan tarif normal</span>
                                </label>

                                @if(!$is_high_season_same)
                                    <div class="mt-2.5 grid grid-cols-1 sm:grid-cols-2 gap-3 bg-amber-50/50 p-3 rounded-lg border border-amber-200">
                                        <div>
                                            <label class="block text-xs font-semibold text-amber-900 mb-1">High Season (Dgn Sarapan) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="high_season_price" class="w-full text-sm border-amber-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 bg-white font-bold text-amber-900" placeholder="Contoh: 450000" required>
                                            @error('high_season_price') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-amber-900 mb-1">High Season (Tanpa Sarapan)</label>
                                            <input type="number" wire:model="high_season_price_without_breakfast" class="w-full text-sm border-amber-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 bg-white font-semibold text-amber-800" placeholder="Contoh: 410000">
                                            <span class="text-[10px] text-amber-700">Kosongkan jika otomatis (-Rp 40.000).</span>
                                            @error('high_season_price_without_breakfast') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <p class="col-span-full text-[10px] text-amber-800">Otomatis berlaku di akhir pekan (Sabtu/Minggu) dan tanggal libur kalender.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 3. KAPASITAS -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Kapasitas Standar (Orang)</label>
                                <input type="number" wire:model="standard_capacity" min="1" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Kapasitas Maksimal (Orang)</label>
                                <input type="number" wire:model="max_capacity" min="1" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                            </div>
                        </div>

                        <!-- 4. ALOKASI KUOTA WEBSITE -->
                        <div class="space-y-2 bg-sky-50/60 p-4 rounded-xl border border-sky-200">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-sky-900">
                                    Kuota Kamar untuk Penjualan Website / Online <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] font-semibold text-sky-700 bg-sky-100 px-2 py-0.5 rounded">
                                    Allotment Online
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                                <div>
                                    <input type="number" wire:model="website_quota" min="0" class="w-full text-sm border-sky-300 rounded-lg shadow-sm focus:ring-sky-500 focus:border-sky-500 font-bold text-sky-900" placeholder="0" required>
                                    @error('website_quota') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                                </div>
                                <p class="text-[11px] text-sky-800 leading-tight">
                                    Jumlah maksimal kamar tipe ini yang dapat dipesan sekaligus oleh tamu publik di website. Sisa kamar fisik disimpan untuk reservasi Front Desk / Walk-in / Telepon. Set 0 jika tidak ingin menjual tipe ini di website.
                                </p>
                            </div>
                        </div>

                        <!-- 5. UPLOAD FOTO (1 - 10 FOTO, WAJIB MINIMAL 1) -->
                        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-xs font-bold text-gray-900">
                                        Galeri Foto Kamar untuk Landing Page <span class="text-red-500">*</span>
                                    </label>
                                    <p class="text-[11px] text-gray-500">Wajib minimal 1 foto, maksimal 10 foto (JPG, PNG, WebP maks 4MB per foto).</p>
                                </div>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ (count($existingPhotos) + count($uploadedPhotos) >= 1 && count($existingPhotos) + count($uploadedPhotos) <= 10) ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ count($existingPhotos) + count($uploadedPhotos) }} / 10 Foto
                                </span>
                            </div>

                            @error('photos')
                                <div class="text-xs text-red-600 font-semibold">{{ $message }}</div>
                            @enderror
                            @error('uploadedPhotos.*')
                                <div class="text-xs text-red-600 font-semibold">{{ $message }}</div>
                            @enderror

                            <!-- Preview List -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <!-- Existing Photos -->
                                @foreach($existingPhotos as $idx => $photoUrl)
                                    @php $isCover = ($coverType === 'existing' && $coverIndex === $idx); @endphp
                                    <div class="relative group rounded-xl overflow-hidden border-2 {{ $isCover ? 'border-emerald-600 ring-2 ring-emerald-500/30' : 'border-gray-200' }} bg-white aspect-video shadow-xs transition">
                                        <img src="{{ $photoUrl }}" alt="Foto {{ $idx + 1 }}" class="w-full h-full object-cover">
                                        
                                        <!-- Badge / Button Cover -->
                                        <div class="absolute bottom-1.5 left-1.5">
                                            @if($isCover)
                                                <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow flex items-center gap-1">
                                                    <span>★</span> Sampul
                                                </span>
                                            @else
                                                <button type="button" wire:click="setCover('existing', {{ $idx }})" class="bg-black/70 hover:bg-emerald-600 text-white text-[10px] font-medium px-2 py-0.5 rounded-md transition shadow backdrop-blur-xs">
                                                    Set Sampul
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Delete Button -->
                                        <button type="button" wire:click="removeExistingPhoto({{ $idx }})" class="absolute top-1.5 right-1.5 bg-red-600 hover:bg-red-700 text-white p-1 rounded-full opacity-80 group-hover:opacity-100 transition shadow" title="Hapus Foto">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- Uploaded Temporary Photos -->
                                @foreach($uploadedPhotos as $idx => $photo)
                                    @php $isCover = ($coverType === 'uploaded' && $coverIndex === $idx); @endphp
                                    <div class="relative group rounded-xl overflow-hidden border-2 {{ $isCover ? 'border-emerald-600 ring-2 ring-emerald-500/30' : 'border-blue-400' }} bg-white aspect-video shadow-xs transition">
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Baru {{ $idx + 1 }}" class="w-full h-full object-cover">
                                        
                                        <!-- Badge / Button Cover -->
                                        <div class="absolute bottom-1.5 left-1.5">
                                            @if($isCover)
                                                <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow flex items-center gap-1">
                                                    <span>★</span> Sampul
                                                </span>
                                            @else
                                                <button type="button" wire:click="setCover('uploaded', {{ $idx }})" class="bg-black/70 hover:bg-emerald-600 text-white text-[10px] font-medium px-2 py-0.5 rounded-md transition shadow backdrop-blur-xs">
                                                    Set Sampul
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Delete Button -->
                                        <button type="button" wire:click="removeUploadedPhoto({{ $idx }})" class="absolute top-1.5 right-1.5 bg-red-600 hover:bg-red-700 text-white p-1 rounded-full opacity-80 group-hover:opacity-100 transition shadow" title="Hapus Foto">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- Upload Button (if total < 10) -->
                                @if(count($existingPhotos) + count($uploadedPhotos) < 10)
                                    <label class="border-2 border-dashed border-gray-300 hover:border-emerald-500 rounded-lg aspect-video flex flex-col items-center justify-center cursor-pointer p-2 bg-white hover:bg-emerald-50/20 transition group">
                                        <svg class="w-6 h-6 text-gray-400 group-hover:text-emerald-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span class="text-[11px] font-medium text-gray-500 group-hover:text-emerald-700 mt-1">Unggah Foto</span>
                                        <input type="file" wire:model="uploadedPhotos" multiple accept="image/*" class="hidden">
                                    </label>
                                @endif
                            </div>

                            <div wire:loading wire:target="uploadedPhotos" class="text-xs text-emerald-600 font-semibold flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Sedang mengunggah pratinjau foto...
                            </div>
                        </div>

                        <!-- 6. DAFTAR FASILITAS MANUAL 2 KOLOM (IKON + KETERANGAN) -->
                        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-xs font-bold text-gray-900">
                                        Daftar Fasilitas Kamar (Tampil di Landing Page) <span class="text-red-500">*</span>
                                    </label>
                                    <p class="text-[11px] text-gray-500">Isi kolom 1 dengan Ikon / Emoji (misal: 🛜, ❄️, 📺, 🚿, 🕋, ☕) dan kolom 2 dengan Keterangan Fasilitas.</p>
                                </div>
                                <button type="button" wire:click="addFacility" class="px-2.5 py-1 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-md shadow-xs transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Baris
                                </button>
                            </div>

                            @error('facilities')
                                <div class="text-xs text-red-600 font-semibold">{{ $message }}</div>
                            @enderror

                            <div class="space-y-2">
                                @foreach($facilitiesList as $index => $item)
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 shrink-0">
                                            <input type="text" wire:model="facilitiesList.{{ $index }}.icon" class="w-full text-center text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Emoji" title="Ikon / Emoji">
                                        </div>
                                        <div class="flex-1">
                                            <input type="text" wire:model="facilitiesList.{{ $index }}.text" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Keterangan Fasilitas (Contoh: WiFi Cepat 100 Mbps)" required>
                                        </div>
                                        <button type="button" wire:click="removeFacility({{ $index }})" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus Fasilitas">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 7. STATUS AKTIF -->
                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" wire:model="is_active" id="type_is_active" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <label for="type_is_active" class="text-xs font-semibold text-gray-700">Tipe Kamar Aktif (Tampil di Formulir Booking & Landing Page)</label>
                        </div>

                        <!-- BUTTONS -->
                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" wire:click="$set('showTypeModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                                Simpan Tipe Kamar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- MODAL KONFIRMASI HAPUS PERMANEN KAMAR FISIK -->
        @if($confirmingDeleteRoomId)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center gap-3 text-rose-600">
                        <div class="p-3 bg-rose-100 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Hapus Permanen Kamar?</h3>
                            <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <span>🛡️</span> Fitur Snapshot Aktif:
                        </p>
                        <p>Riwayat transaksi dan data audit masa lalu yang pernah menggunakan kamar ini akan tetap utuh tersimpan sebagai snapshot (nomor kamar, lantai, dan tipe).</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="cancelDeleteRoom" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Batal
                        </button>
                        <button type="button" wire:click="deleteRoom" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm">
                            Ya, Hapus Permanen
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL KONFIRMASI HAPUS PERMANEN TIPE KAMAR -->
        @if($confirmingDeleteTypeId)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center gap-3 text-rose-600">
                        <div class="p-3 bg-rose-100 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Hapus Permanen Tipe Kamar?</h3>
                            <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <span>🛡️</span> Fitur Snapshot Aktif:
                        </p>
                        <p>Riwayat reservasi lampau yang pernah memesan tipe kamar ini akan tetap aman melalui snapshot (nama tipe, tarif, kode, dan fasilitas).</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="cancelDeleteType" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Batal
                        </button>
                        <button type="button" wire:click="deleteType" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm">
                            Ya, Hapus Permanen
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
