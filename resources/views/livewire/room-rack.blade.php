<div class="flex-1 flex flex-col h-full relative">
    <!-- Filter Toolbar -->
    <div class="bg-surface-soft p-4 flex flex-col md:flex-row md:items-center justify-between shadow-sm z-10 flex-none gap-4">
        <div class="flex items-center gap-2">
            <input type="text" wire:model.live="search" placeholder="Cari kamar / tamu..." class="rounded-md border-gray-300 shadow-sm focus:border-primary-accent focus:ring focus:ring-primary-accent/50 w-full md:w-64">
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach(['Semua', 'Ready', 'Check-in', 'Booked', 'Kotor', 'Sedang Dibersihkan'] as $filter)
                <button wire:click="$set('statusFilter', '{{ $filter }}')" 
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors 
                    {{ $statusFilter === $filter ? 'bg-primary-dark text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                    {{ $filter }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Room Grid Area -->
    <div class="flex-1 overflow-y-auto p-6 bg-surface-light">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($rooms as $room)
                @php
                    $bgColor = 'bg-white text-gray-800';
                    $borderColor = 'border-gray-200';
                    if($room->current_occupancy == 'Check-in') { $bgColor = 'bg-primary-dark text-white'; $borderColor = 'border-primary-dark'; }
                    elseif($room->current_occupancy == 'Booked') { $bgColor = 'bg-primary-accent text-white'; $borderColor = 'border-primary-accent'; }
                    
                    $badgeColor = 'bg-status-green text-white';
                    if($room->readiness_status == 'Kotor') $badgeColor = 'bg-status-red text-white animate-pulse';
                    elseif($room->readiness_status == 'Sedang Dibersihkan') $badgeColor = 'bg-surface-soft text-gray-800';
                @endphp
                <div class="rounded-xl border {{ $borderColor }} {{ $bgColor }} shadow-sm overflow-hidden flex flex-col transition-transform hover:scale-[1.02] cursor-pointer" wire:click="selectRoom({{ $room->id }})">
                    <div class="p-4 flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-2xl font-bold">{{ $room->room_number }}</span>
                            <!-- 1-click Readiness Toggle -->
                            <button wire:click.stop="toggleReadiness({{ $room->id }})" class="text-xs px-2 py-1 rounded-full font-semibold {{ $badgeColor }}">
                                {{ $room->readiness_status }}
                            </button>
                        </div>
                        <div class="text-sm opacity-80">{{ $room->roomType->name }}</div>
                        @if($room->current_occupancy != 'Available')
                            <div class="mt-4 pt-4 border-t border-white/20">
                                <div class="text-sm font-medium">Budi Santoso</div>
                                <div class="text-xs opacity-75">Out: 12 Okt</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Drawer Panel Kanan -->
    @if($showDrawer && $selectedRoom)
        <div class="absolute inset-0 z-40 flex justify-end" x-data="{ open: @entangle('showDrawer') }" x-show="open">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/20" x-show="open" x-transition.opacity wire:click="closeDrawer"></div>
            
            <!-- Slide-over -->
            <div class="relative w-full md:w-[420px] bg-white h-full shadow-2xl flex flex-col" 
                 x-show="open" 
                 x-transition:enter="transform transition ease-in-out duration-300" 
                 x-transition:enter-start="translate-x-full" 
                 x-transition:enter-end="translate-x-0" 
                 x-transition:leave="transform transition ease-in-out duration-300" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="translate-x-full">
                
                <div class="p-4 bg-primary-dark text-white flex justify-between items-center">
                    <h3 class="text-lg font-bold">Kamar {{ $selectedRoom->room_number }}</h3>
                    <button wire:click="closeDrawer" class="hover:bg-white/20 rounded-full p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 flex-1 overflow-y-auto">
                    @if($selectedRoom->current_occupancy == 'Available')
                        @if($selectedRoom->readiness_status == 'Ready')
                            <h4 class="font-bold text-gray-800 mb-4 text-xl">Quick Walk-in</h4>
                            <!-- Quick Form -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Tamu</label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-accent focus:ring focus:ring-primary-accent/50">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">No. WhatsApp</label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-accent focus:ring focus:ring-primary-accent/50">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Durasi (Malam)</label>
                                        <input type="number" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-accent focus:ring focus:ring-primary-accent/50">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Harga per Malam</label>
                                        <input type="text" value="Rp {{ number_format($selectedRoom->roomType->base_price_normal, 0, ',', '.') }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" readonly>
                                    </div>
                                </div>
                                <button wire:click="processCheckIn" class="w-full bg-primary-dark text-white font-bold py-3 px-4 rounded-lg hover:bg-primary-accent transition-colors mt-4 cursor-pointer">
                                    Check-in Sekarang
                                </button>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-status-red mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <h3 class="text-lg font-bold text-gray-800">Kamar Belum Siap!</h3>
                                <p class="text-gray-500 mt-2">Status saat ini: <span class="font-bold">{{ $selectedRoom->readiness_status }}</span>. Tidak dapat melakukan check-in hingga kamar dibersihkan.</p>
                            </div>
                        @endif
                    @elseif($selectedRoom->current_occupancy == 'Check-in')
                        <h4 class="font-bold text-gray-800 mb-4 text-xl">Live Folio</h4>
                        
                        @if (session()->has('error'))
                            <div class="mb-4 bg-red-100 border-l-4 border-status-red text-status-red p-3 rounded shadow-sm text-sm">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <div class="font-bold text-gray-800">Budi Santoso</div>
                                    <div class="text-sm text-gray-500">Check-in: 10 Okt • Out: 12 Okt</div>
                                </div>
                                <span class="bg-status-green text-white text-xs px-2 py-1 rounded-full font-bold">LUNAS</span>
                            </div>
                            
                            <div class="space-y-2 text-sm border-t border-gray-200 pt-4">
                                <div class="flex justify-between">
                                    <span>Kamar (2 Malam)</span>
                                    <span>Rp 500.000</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>F&B: Nasi Goreng</span>
                                    <span>Rp 25.000</span>
                                </div>
                                <div class="flex justify-between font-bold text-lg border-t border-gray-200 pt-2 mt-2">
                                    <span>Total Tagihan</span>
                                    <span>Rp 525.000</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button class="bg-white border-2 border-primary-dark text-primary-dark font-bold py-2 px-4 rounded-lg hover:bg-gray-50 cursor-pointer">
                                + Tambah F&B
                            </button>
                            <button class="bg-white border-2 border-primary-dark text-primary-dark font-bold py-2 px-4 rounded-lg hover:bg-gray-50 cursor-pointer">
                                Cetak Invoice
                            </button>
                            <button wire:click="processCheckOut" class="col-span-2 bg-status-red text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700 transition-colors cursor-pointer">
                                Check-out
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
