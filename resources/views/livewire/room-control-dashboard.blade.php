<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header & Manual Refresh (Sesuai Instruksi User: Polling Otomatis Dibatalkan, Dibuat Manual) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kendali Kamar (Room Rack)</h1>
                <p class="text-sm text-gray-500 mt-1">Status operasional, kebersihan, dan okupansi 20 kamar fisik InSitu Hotel Syariah</p>
            </div>
            
            <div class="flex items-center gap-3">
                @if($todaySeason['is_high_season'])
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-bold shadow-xs">
                        <span>🔥 High Season</span>
                        <span class="text-[10px] font-normal opacity-90">({{ $todaySeason['label'] }})</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs">
                        <span>🌿 Normal Season</span>
                    </div>
                @endif

                <div class="text-xs text-gray-500 hidden sm:block text-right">
                    <span>Terakhir diperbarui:</span>
                    <span class="font-bold text-gray-800">{{ $lastUpdated }} WIB</span>
                </div>

                <!-- Tombol Refresh Manual -->
                <button wire:click="refreshRooms" wire:loading.attr="disabled" class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                    <svg wire:loading.class="animate-spin text-emerald-600" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Refresh Status Kamar</span>
                </button>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('message') }}
            </div>
        @endif

        @if(session()->has('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- BANNER NOTIFIKASI BOOKING WEB & PLOT KAMAR FISIK (COMPACT) -->
        @if($unallocatedReservations->isNotEmpty())
            @php
                $webCount = $unallocatedReservations->where('source', 'website')->count();
                $todayUnallocCount = $unallocatedReservations->filter(fn($r) => $r->check_in_date->toDateString() === now()->toDateString())->count();
            @endphp
            <div class="bg-gradient-to-r from-amber-50 via-orange-50/60 to-amber-50 border border-amber-300 rounded-xl p-3 shadow-2xs space-y-2.5">
                <!-- Compact Header -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                        <h3 class="text-xs sm:text-sm font-extrabold text-amber-950 flex items-center gap-1.5">
                            <span>{{ $unallocatedReservations->count() }} Reservasi Menunggu Plot Kamar Fisik</span>
                        </h3>
                        <div class="flex items-center gap-1.5">
                            @if($webCount > 0)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    🌐 {{ $webCount }} Web
                                </span>
                            @endif
                            @if($todayUnallocCount > 0)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">
                                    ⚡ {{ $todayUnallocCount }} Hari Ini
                                </span>
                            @endif
                        </div>
                    </div>
                    <span class="text-[11px] text-amber-800/70 hidden md:inline">Tentukan nomor kamar fisik agar tamu siap check-in</span>
                </div>

                <!-- Compact Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($unallocatedReservations as $unalloc)
                        @php
                            $isToday = $unalloc->check_in_date->toDateString() === now()->toDateString();
                        @endphp
                        <div class="bg-white rounded-lg p-2.5 border border-amber-200/90 shadow-2xs flex items-center justify-between gap-2.5 hover:border-amber-400 transition">
                            <div class="min-w-0 flex-1 space-y-0.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold text-gray-900 truncate">{{ $unalloc->customer->name }}</span>
                                    @if($unalloc->source === 'website')
                                        <span class="px-1 py-0.2 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-100 shrink-0">Web</span>
                                    @endif
                                    @if($unalloc->include_breakfast)
                                        <span class="px-1 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0" title="Termasuk Sarapan">🍳 Sarapan</span>
                                    @else
                                        <span class="px-1 py-0.2 rounded text-[9px] font-medium bg-gray-100 text-gray-600 border border-gray-200 shrink-0" title="Tanpa Sarapan">☕ Room Only</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-600 truncate flex items-center gap-1.5">
                                    <span class="font-semibold text-emerald-800 truncate">{{ $unalloc->roomType->name }}</span>
                                    <span class="text-gray-300">&bull;</span>
                                    <span class="{{ $isToday ? 'font-bold text-rose-600' : 'text-gray-500' }} shrink-0">
                                        {{ $isToday ? 'Hari Ini' : $unalloc->check_in_date->format('d/m') }} ({{ $unalloc->nights_count }} Mlm)
                                    </span>
                                </div>
                                <div class="text-[10px] text-gray-400 flex items-center gap-1.5">
                                    <span>#{{ $unalloc->reservation_number }}</span>
                                    <span>&bull;</span>
                                    <span class="font-mono font-semibold text-gray-700">Rp {{ number_format($unalloc->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @can('room_plot.edit')
                            <button type="button" 
                                    wire:click="openPlotModal({{ $unalloc->id }}, {{ $isToday ? 'true' : 'false' }})" 
                                    class="shrink-0 px-2.5 py-1.5 {{ $isToday ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700' }} text-white font-bold text-[11px] rounded-lg shadow-2xs transition flex items-center gap-1 cursor-pointer">
                                <span>{{ $isToday ? 'Plot & Check-In' : 'Plot Kamar' }}</span>
                                <span>&rarr;</span>
                            </button>
                            @else
                            <span class="text-[10px] text-amber-700 bg-amber-100/80 px-2 py-1 rounded font-medium shrink-0">Menunggu Plotting</span>
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- PITA TOMBOL AKSES CEPAT (Quick Action Bar) -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-gray-50 to-white p-3 rounded-xl border border-gray-200/80 shadow-xs">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <div>
                    <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Akses Cepat</h2>
                    <p class="text-[11px] text-gray-500">Shortcut transaksi langsung</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('room_status.edit')
                <button type="button" wire:click="openWebOrderModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-100 hover:bg-sky-200 text-sky-800 border border-sky-300 text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <span>Setting Order via Web</span>
                </button>
                @endcan
                @can('reservations.manage')
                <a href="{{ route('reservations.index') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Input Reservasi Baru</span>
                </a>
                @endcan
                @can('meeting.manage')
                <a href="{{ route('meeting-rooms.index') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Booking Ruang Meeting</span>
                </a>
                @endcan
                @can('dining.manage')
                <a href="{{ route('dining.index') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Reservasi Fine Dining</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- BARIS WIDGET RINGKASAN RAMPING (Compact Summary Strip with Accordion) -->
        <div class="space-y-3">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
                <!-- 1. Check-In Hari Ini -->
                <button type="button" wire:click="toggleSummary('checkin')" class="flex items-center justify-between p-2.5 rounded-xl border text-left transition cursor-pointer {{ $expandedSummary === 'checkin' ? 'bg-blue-100/90 border-blue-400 ring-2 ring-blue-300' : 'bg-blue-50/50 hover:bg-blue-50 border-blue-200' }}">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="p-1.5 bg-blue-600 text-white rounded-lg shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <div class="truncate">
                            <div class="text-[11px] font-bold text-blue-900 truncate">Check-In Hari Ini</div>
                            <div class="text-[10px] text-blue-600 truncate">{{ $todayPendingCheckIns->count() }} Tamu Menunggu</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ms-1">
                        <span class="px-1.5 py-0.5 text-xs font-black rounded-md {{ $todayPendingCheckIns->count() > 0 ? 'bg-blue-600 text-white' : 'bg-blue-200 text-blue-800' }}">
                            {{ $todayPendingCheckIns->count() }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-blue-500 transition-transform duration-200 {{ $expandedSummary === 'checkin' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                <!-- 2. Permintaan PMR Aktif -->
                <button type="button" wire:click="toggleSummary('pmr')" class="flex items-center justify-between p-2.5 rounded-xl border text-left transition cursor-pointer {{ $expandedSummary === 'pmr' ? 'bg-amber-100/90 border-amber-400 ring-2 ring-amber-300' : 'bg-amber-50/50 hover:bg-amber-50 border-amber-200' }}">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="p-1.5 bg-amber-600 text-white rounded-lg shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </span>
                        <div class="truncate">
                            <div class="text-[11px] font-bold text-amber-900 truncate">PMR Aktif</div>
                            <div class="text-[10px] text-amber-600 truncate">{{ $activePmrs->count() }} Kamar Make-Up</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ms-1">
                        <span class="px-1.5 py-0.5 text-xs font-black rounded-md {{ $activePmrs->count() > 0 ? 'bg-amber-600 text-white' : 'bg-amber-200 text-amber-800' }}">
                            {{ $activePmrs->count() }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-amber-500 transition-transform duration-200 {{ $expandedSummary === 'pmr' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                <!-- 3. Ruang Meeting Hari Ini -->
                <button type="button" wire:click="toggleSummary('meeting')" class="flex items-center justify-between p-2.5 rounded-xl border text-left transition cursor-pointer {{ $expandedSummary === 'meeting' ? 'bg-emerald-100/90 border-emerald-400 ring-2 ring-emerald-300' : 'bg-emerald-50/50 hover:bg-emerald-50 border-emerald-200' }}">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="p-1.5 bg-emerald-600 text-white rounded-lg shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <div class="truncate">
                            <div class="text-[11px] font-bold text-emerald-900 truncate">Ruang Meeting</div>
                            <div class="text-[10px] text-emerald-600 truncate">{{ $todayMeetings->count() }} Sesi Hari Ini</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ms-1">
                        <span class="px-1.5 py-0.5 text-xs font-black rounded-md {{ $todayMeetings->count() > 0 ? 'bg-emerald-600 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                            {{ $todayMeetings->count() }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-emerald-500 transition-transform duration-200 {{ $expandedSummary === 'meeting' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                <!-- 4. Fine Dining Hari Ini -->
                <button type="button" wire:click="toggleSummary('dining')" class="flex items-center justify-between p-2.5 rounded-xl border text-left transition cursor-pointer {{ $expandedSummary === 'dining' ? 'bg-orange-100/90 border-orange-400 ring-2 ring-orange-300' : 'bg-orange-50/50 hover:bg-orange-50 border-orange-200' }}">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="p-1.5 bg-orange-600 text-white rounded-lg shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        <div class="truncate">
                            <div class="text-[11px] font-bold text-orange-900 truncate">Fine Dining</div>
                            <div class="text-[10px] text-orange-600 truncate">{{ $todayDinings->count() }} Reservasi Hari Ini</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ms-1">
                        <span class="px-1.5 py-0.5 text-xs font-black rounded-md {{ $todayDinings->count() > 0 ? 'bg-orange-600 text-white' : 'bg-orange-200 text-orange-800' }}">
                            {{ $todayDinings->count() }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-orange-500 transition-transform duration-200 {{ $expandedSummary === 'dining' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>
            </div>

            <!-- DETAIL ACCORDION CONTAINER -->
            @if($expandedSummary === 'checkin')
                <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Check-In Terjadwal Hari Ini ({{ $todayPendingCheckIns->count() }})
                        </span>
                        <button type="button" wire:click="toggleSummary('checkin')" class="text-xs text-blue-700 hover:text-blue-900 font-semibold cursor-pointer">Tutup ✕</button>
                    </div>
                    @if($todayPendingCheckIns->isEmpty())
                        <p class="text-xs text-blue-600 italic py-2">Tidak ada tamu yang menunggu check-in hari ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
                            @foreach($todayPendingCheckIns as $cin)
                                @php
                                    $allocated = $cin->allocatedRooms->first();
                                    $hasRoom = $allocated !== null;
                                @endphp
                                <div class="bg-white p-2.5 rounded-lg border {{ $hasRoom ? 'border-blue-100' : 'border-amber-300 bg-amber-50/40' }} flex items-center justify-between shadow-2xs gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-gray-900 truncate">{{ $cin->customer->name }}</span>
                                            @if(!$hasRoom)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Plot Kamar Dulu</span>
                                            @endif
                                            @if($cin->source === 'website')
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-blue-100 text-blue-800">Web</span>
                                            @endif
                                            @if($cin->include_breakfast)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">🍳 Sarapan</span>
                                            @else
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-medium bg-gray-100 text-gray-600">☕ Room Only</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-500 truncate">
                                            @if($hasRoom)
                                                <strong class="text-emerald-700">Kamar {{ $allocated->room->room_number }}</strong> ({{ $cin->roomType->name }})
                                            @else
                                                <span class="text-amber-800 font-semibold">{{ $cin->roomType->name }}</span> · <span class="text-rose-600 font-bold">Kamar Belum Diplot</span>
                                            @endif
                                            · <span class="text-blue-700 font-semibold">🕒 {{ $cin->check_in_time ?? '14:00' }} WIB</span>
                                        </div>
                                        <div class="text-[10px] text-gray-400">#{{ $cin->reservation_number }}</div>
                                    </div>
                                    @if($hasRoom)
                                        @can('checkin_checkout.manage')
                                        <button wire:click="openCheckIn({{ $cin->id }}, {{ $allocated->room_id }})" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded text-[11px] transition shrink-0 shadow-xs cursor-pointer">
                                            Check-In
                                        </button>
                                        @endcan
                                    @else
                                        @can('room_plot.edit')
                                        <button wire:click="openPlotModal({{ $cin->id }}, true)" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded text-[11px] transition shrink-0 shadow-xs cursor-pointer flex items-center gap-1">
                                            <span>Plot Kamar</span>
                                            <span>&rarr;</span>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif($expandedSummary === 'pmr')
                <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Permintaan Make Up Room (PMR) Aktif ({{ $activePmrs->count() }})
                        </span>
                        <button type="button" wire:click="toggleSummary('pmr')" class="text-xs text-amber-700 hover:text-amber-900 font-semibold cursor-pointer">Tutup ✕</button>
                    </div>
                    @if($activePmrs->isEmpty())
                        <p class="text-xs text-amber-600 italic py-2">Tidak ada permintaan PMR aktif saat ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
                            @foreach($activePmrs as $pmr)
                                <div class="bg-white p-2.5 rounded-lg border border-amber-100 flex items-center justify-between gap-2 shadow-2xs">
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900">Kamar {{ $pmr->room->room_number }}</div>
                                        <div class="text-gray-500 text-[11px] truncate">{{ $pmr->notes }}</div>
                                        <span class="text-[10px] text-amber-700 font-bold uppercase">Prioritas: {{ $pmr->priority }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button wire:click="completePmr({{ $pmr->id }})" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded text-[11px] transition shadow-xs cursor-pointer" title="Tandai PMR Selesai & Kamar Bersih">
                                            ✓ Selesai
                                        </button>
                                        <button wire:click="openCleaningModal({{ $pmr->room->id }})" class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 font-semibold rounded text-[11px] transition cursor-pointer" title="Ubah Status Kebersihan">
                                            Status Kamar
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif($expandedSummary === 'meeting')
                <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Ruang Meeting Hari Ini ({{ $todayMeetings->count() }})
                        </span>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('meeting-rooms.index') }}" wire:navigate class="text-xs text-emerald-700 hover:underline font-semibold">Kelola Meeting →</a>
                            <button type="button" wire:click="toggleSummary('meeting')" class="text-xs text-emerald-700 hover:text-emerald-900 font-semibold cursor-pointer">Tutup ✕</button>
                        </div>
                    </div>
                    @if($todayMeetings->isEmpty())
                        <p class="text-xs text-emerald-600 italic py-2">Tidak ada jadwal ruang meeting untuk hari ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
                            @foreach($todayMeetings as $tm)
                                <div class="bg-white p-2.5 rounded-lg border border-emerald-100 flex items-center justify-between shadow-2xs">
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 truncate">{{ $tm->event_name }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $tm->meetingRoom->name }} · {{ $tm->pax }} Pax</div>
                                        <div class="text-[10px] text-emerald-700 font-semibold">{{ $tm->start_time }} - {{ $tm->end_time }} WIB · {{ $tm->customer->name }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase shrink-0 {{ $tm->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $tm->payment_status === 'paid' ? 'LUNAS' : 'SEBAGIAN' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @elseif($expandedSummary === 'dining')
                <div class="p-3.5 bg-orange-50/70 border border-orange-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-orange-900 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Reservasi Fine Dining Hari Ini ({{ $todayDinings->count() }})
                        </span>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('dining.index') }}" wire:navigate class="text-xs text-orange-700 hover:underline font-semibold">Kelola Dining →</a>
                            <button type="button" wire:click="toggleSummary('dining')" class="text-xs text-orange-700 hover:text-orange-900 font-semibold cursor-pointer">Tutup ✕</button>
                        </div>
                    </div>
                    @if($todayDinings->isEmpty())
                        <p class="text-xs text-orange-600 italic py-2">Tidak ada reservasi dining untuk hari ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
                            @foreach($todayDinings as $td)
                                <div class="bg-white p-2.5 rounded-lg border border-orange-100 flex items-center justify-between shadow-2xs">
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 truncate">{{ $td->customer->name }}</div>
                                        <div class="text-[11px] text-gray-500 truncate">{{ $td->diningPackage ? $td->diningPackage->name : 'Ala Carte' }}</div>
                                        <div class="text-[10px] text-orange-700 font-semibold">{{ $td->time_slot }} WIB · {{ $td->pax }} Orang · Meja: {{ $td->table_number_or_area }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase shrink-0 {{ $td->status === 'seated' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $td->status === 'seated' ? 'DUDUK' : 'BOOKED' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Filter Lantai & Status -->
        <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-2xs flex flex-col md:flex-row gap-3 justify-between items-center">
            <!-- Filter Lantai -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs font-semibold text-gray-500">Lantai:</span>
                @foreach(['all' => 'Semua', 'Lantai 1' => 'Lantai 1 (101-110)', 'Lantai 2' => 'Lantai 2 (201-210)'] as $flKey => $flLbl)
                    <button wire:click="$set('selectedFloor', '{{ $flKey }}')" class="px-2.5 py-1 text-xs rounded-lg font-medium transition cursor-pointer {{ $selectedFloor === $flKey ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $flLbl }}
                    </button>
                @endforeach
            </div>

            <!-- Filter Status -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto flex-wrap">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Status:</span>
                @foreach([
                    'all' => 'Semua',
                    'occupied' => 'Terisi (In-House)',
                    'available' => 'Kosong Siap',
                    'dirty' => 'Kotor / Cleaning',
                    'maintenance' => 'Maintenance'
                ] as $stKey => $stLbl)
                    <button wire:click="$set('selectedStatus', '{{ $stKey }}')" class="px-2.5 py-1 text-xs rounded-lg font-medium whitespace-nowrap transition cursor-pointer {{ $selectedStatus === $stKey ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $stLbl }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Grid 20 Kartu Kamar Fisik (Super Compact 5 Kolom Desktop) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @forelse($roomItems as $item)
                @php
                    $room = $item['room'];
                    $res = $item['activeReservation'];
                    $isHighPriority = $item['isHighPriority'];
                    $isOccupied = $item['isOccupied'];

                    // Penentuan Latar Belakang & Pita Sesuai Status Kamar & Kebersihan
                    $isMaintenance = in_array($room->operational_status, ['maintenance', 'disabled']);

                    if ($isMaintenance) {
                        $cardBg = 'bg-rose-50/70';
                        $cleanlinessBarBg = 'bg-rose-500';
                    } elseif ($isOccupied) {
                        $cardBg = 'bg-sky-50/70';
                        $cleanlinessBarBg = match($room->cleaning_status) {
                            'inspected' => 'bg-emerald-500',
                            'clean' => 'bg-teal-500',
                            'cleaning' => 'bg-blue-500',
                            default => 'bg-amber-500',
                        };
                    } else {
                        // Kamar Kosong: bedakan warna background berdasarkan status kebersihan
                        $cardBg = match($room->cleaning_status) {
                            'inspected' => 'bg-emerald-50/70',
                            'clean' => 'bg-teal-50/70',
                            'cleaning' => 'bg-blue-50/60',
                            default => 'bg-amber-50/70',
                        };
                        $cleanlinessBarBg = match($room->cleaning_status) {
                            'inspected' => 'bg-emerald-500',
                            'clean' => 'bg-teal-500',
                            'cleaning' => 'bg-blue-500',
                            default => 'bg-amber-500',
                        };
                    }

                    $cleanBadgeStyle = match($room->cleaning_status) {
                        'inspected' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                        'clean' => 'bg-teal-100 text-teal-800 border border-teal-200',
                        'cleaning' => 'bg-blue-100 text-blue-800 border border-blue-200',
                        default => 'bg-amber-100 text-amber-800 border border-amber-200',
                    };

                    $cleanBadgeLabel = match($room->cleaning_status) {
                        'inspected' => 'Siap Pakai',
                        'clean' => 'Bersih',
                        'cleaning' => 'Cleaning',
                        default => 'Kotor',
                    };

                    $typeColor = $room->roomType?->color ?? '#059669';
                @endphp

                <div class="relative rounded-xl p-3 transition-all duration-150 flex flex-col justify-between overflow-hidden {{ $cardBg }}"
                     style="border: 3px solid {{ $typeColor }}; box-shadow: 0 4px 16px -2px {{ $typeColor }}40;">
                    
                    <!-- Pita Garis Tebal 6px Status Kebersihan di Bagian Atas Card -->
                    <div class="h-1.5 w-full absolute top-0 left-0 {{ $cleanlinessBarBg }}"></div>

                    <!-- Bagian Atas: Nomor Kamar, Tipe, dan Badge -->
                    <div>
                        <div class="flex items-start justify-between gap-1 pt-0.5">
                            <div class="min-w-0">
                                <span class="text-xl font-black text-gray-900 tracking-tight leading-none block">{{ $room->room_number }}</span>
                                <div class="flex items-center gap-1 mt-1 text-[11px] font-semibold text-gray-600 truncate" title="{{ $room->roomType?->name ?? 'Kamar' }}">
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $typeColor }};"></span>
                                    <span class="truncate">{{ $room->roomType?->name ?? 'Kamar' }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-0.5 shrink-0">
                                <div class="flex items-center gap-0.5">
                                    @if($todaySeason['is_high_season'])
                                        <span class="text-[8px] px-1 py-0.2 rounded font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300" title="High Season">
                                            High
                                        </span>
                                    @endif
                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase tracking-wider {{ $room->operational_status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $room->operational_status }}
                                    </span>
                                </div>
                                <span class="text-[9px] px-1.5 py-0.2 rounded font-semibold {{ $cleanBadgeStyle }}">
                                    {{ $cleanBadgeLabel }}
                                </span>
                            </div>
                        </div>

                        <!-- Prioritas Alert (PRD 8.2) -->
                        @if($isHighPriority)
                            <div class="mt-2 p-1 bg-red-100 border border-red-200 text-red-800 text-[10px] font-bold rounded flex items-center gap-1 animate-pulse">
                                <svg class="w-3 h-3 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span class="truncate">PRIORITAS: Check-in Hari Ini!</span>
                            </div>
                        @endif

                        <!-- Detail Okupansi Tamu Super Compact -->
                        <div class="mt-2 py-1.5 px-2 bg-white/90 backdrop-blur-xs rounded-lg border border-gray-200/60 text-xs shadow-2xs">
                            @if($isOccupied && $res)
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-bold text-gray-900 truncate leading-tight">{{ $res->customer->name }}</span>
                                    @if($res->include_breakfast)
                                        <span class="text-[9px] px-1 py-0.2 rounded font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0" title="Termasuk Sarapan">🍽️ Sarapan</span>
                                    @else
                                        <span class="text-[9px] px-1 py-0.2 rounded font-medium bg-gray-100 text-gray-600 shrink-0" title="Tanpa Sarapan (Room Only)">☕ Room Only</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-600 mt-0.5 truncate">
                                    Out: <span class="font-bold text-gray-800">{{ $res->check_out_date->format('d M') }}</span> ({{ $res->nights_count }} Mlm)
                                </div>
                            @elseif($item['checkInTodayReservation'])
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-amber-900 font-bold text-[11px] truncate leading-tight">
                                        Akan Tiba: {{ $item['checkInTodayReservation']->customer->name }}
                                    </span>
                                    @if($item['checkInTodayReservation']->include_breakfast)
                                        <span class="text-[9px] px-1 py-0.2 rounded font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0" title="Termasuk Sarapan">🍽️ Sarapan</span>
                                    @else
                                        <span class="text-[9px] px-1 py-0.2 rounded font-medium bg-gray-100 text-gray-600 shrink-0" title="Tanpa Sarapan (Room Only)">☕ Room Only</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-600 mt-0.5 truncate flex items-center justify-between">
                                    <span class="text-blue-700 font-semibold">🕒 {{ $item['checkInTodayReservation']->check_in_time ?? '14:00' }}</span>
                                    <span class="text-gray-400">#{{ $item['checkInTodayReservation']->reservation_number }}</span>
                                </div>
                            @else
                                <div class="text-gray-400 italic text-[11px] text-center py-1">Kamar Kosong</div>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Aksi Mikro Compact -->
                    <div class="mt-2.5 pt-2 border-t border-gray-200/70 flex flex-wrap gap-1 justify-end items-center">
                        <!-- Tombol Detil Tamu -->
                        @if($isOccupied && $res)
                            <button wire:click="openDetailModal({{ $res->id }})" class="px-1.5 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-semibold rounded border border-blue-200 transition cursor-pointer" title="Detil Informasi Tamu">
                                Detil
                            </button>
                        @elseif($item['checkInTodayReservation'])
                            <button wire:click="openDetailModal({{ $item['checkInTodayReservation']->id }})" class="px-1.5 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-semibold rounded border border-blue-200 transition cursor-pointer" title="Detil Informasi Tamu">
                                Detil
                            </button>
                        @else
                            <button disabled class="px-1.5 py-0.5 bg-gray-50 text-gray-400 text-[10px] font-normal rounded border border-gray-200 opacity-40 cursor-not-allowed" title="Kamar Kosong">
                                Detil
                            </button>
                        @endif

                        @if($isOccupied && $res)
                            @can('checkin_checkout.manage')
                            <button wire:click="openCheckOut({{ $res->id }})" wire:confirm="Selesaikan checkout tamu {{ $res->customer->name }}?" class="px-2 py-0.5 bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold rounded shadow-2xs transition cursor-pointer" title="Check-Out">
                                Checkout
                            </button>
                            @endcan
                            @can('reservations.manage')
                            <button wire:click="openExtendModal({{ $res->id }})" class="px-1.5 py-0.5 bg-blue-100 hover:bg-blue-200 text-blue-800 text-[10px] font-semibold rounded transition cursor-pointer" title="Extend Stay">
                                Extend
                            </button>
                            <button wire:click="openMoveModal({{ $res->id }}, {{ $room->id }})" class="px-1.5 py-0.5 bg-purple-100 hover:bg-purple-200 text-purple-800 text-[10px] font-semibold rounded transition cursor-pointer" title="Pindah Kamar">
                                Move
                            </button>
                            @endcan
                            @can('maintenance.manage')
                            <button wire:click="openPmrModal({{ $room->id }}, {{ $res->id }})" class="px-1.5 py-0.5 bg-amber-100 hover:bg-amber-200 text-amber-800 text-[10px] font-semibold rounded transition cursor-pointer" title="Permintaan PMR">
                                PMR
                            </button>
                            @endcan
                        @elseif($item['checkInTodayReservation'])
                            @can('checkin_checkout.manage')
                            <button wire:click="openCheckIn({{ $item['checkInTodayReservation']->id }}, {{ $room->id }})" class="px-2.5 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded shadow-2xs transition cursor-pointer">
                                Check-In
                            </button>
                            @endcan
                        @endif

                        <!-- Tombol Kebersihan & Maintenance Selalu Ada -->
                        @can('room_status.edit')
                        <button wire:click="openCleaningModal({{ $room->id }})" class="px-1.5 py-0.5 bg-white hover:bg-gray-100 text-gray-700 text-[10px] font-medium rounded border border-gray-200 transition cursor-pointer" title="Ubah Status Kebersihan">
                            Bersih
                        </button>
                        @endcan
                        @can('maintenance.manage')
                        <button wire:click="openMaintenanceModal({{ $room->id }})" class="px-1.5 py-0.5 bg-white hover:bg-gray-100 text-red-700 text-[10px] font-medium rounded border border-gray-200 transition cursor-pointer" title="Lapor Kerusakan">
                            Rusak
                        </button>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-gray-400">
                    Tidak ada kamar yang sesuai dengan filter.
                </div>
            @endforelse
        </div>

        <!-- Legenda Indikator Status Kamar & Kebersihan -->
        <div class="bg-white/80 p-2.5 rounded-xl border border-gray-200/80 shadow-2xs flex flex-wrap items-center justify-between gap-2.5 text-[11px] text-gray-600">
            <div class="flex items-center gap-1.5 font-bold text-gray-700">
                <span>Indikator Kebersihan:</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-2 rounded-xs bg-emerald-500 shrink-0"></span>
                    <span class="font-semibold text-emerald-800">Siap Pakai</span>
                    <span class="text-[10px] text-gray-400">(Inspected)</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-2 rounded-xs bg-teal-500 shrink-0"></span>
                    <span class="font-semibold text-teal-800">Bersih</span>
                    <span class="text-[10px] text-gray-400">(Menunggu Inspeksi)</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-2 rounded-xs bg-blue-500 shrink-0"></span>
                    <span class="font-semibold text-blue-800">Cleaning</span>
                    <span class="text-[10px] text-gray-400">(Sedang Dibersihkan)</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-2 rounded-xs bg-amber-500 shrink-0"></span>
                    <span class="font-semibold text-amber-800">Kotor</span>
                    <span class="text-[10px] text-gray-400">(Perlu Dibersihkan)</span>
                </span>
            </div>
            <div class="flex items-center gap-3 border-t sm:border-t-0 sm:border-l border-gray-200 pt-1 sm:pt-0 sm:pl-3">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-sky-200 border border-sky-400 shrink-0"></span>
                    <span class="font-medium text-sky-900">Terisi (In-House)</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded bg-rose-200 border border-rose-400 shrink-0"></span>
                    <span class="font-medium text-rose-900">Maintenance</span>
                </span>
            </div>
        </div>

        <!-- Modal Extend Stay -->
        @if($showExtendModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-5 space-y-4">
                    <h3 class="text-base font-bold text-gray-900">Perpanjang Menginap (Extend Stay)</h3>
                    @error('extend') <div class="p-2 bg-red-50 text-red-700 text-xs rounded">{{ $message }}</div> @enderror
                    <form wire:submit="saveExtend" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Checkout Baru</label>
                            <input type="date" wire:model="extend_checkout_date" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500" required>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t">
                            <button type="button" wire:click="$set('showExtendModal', false)" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg">Batal</button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Simpan Extend</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Pindah Kamar (Room Move) -->
        @if($showMoveModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-5 space-y-4">
                    <h3 class="text-base font-bold text-gray-900">Pindah Kamar (Room Move)</h3>
                    @error('move') <div class="p-2 bg-red-50 text-red-700 text-xs rounded">{{ $message }}</div> @enderror
                    <form wire:submit="saveMove" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Kamar Tujuan (Bersih & Tersedia)</label>
                            <select wire:model="target_room_id" class="w-full text-xs border-gray-300 rounded-lg focus:ring-purple-500" required>
                                <option value="">-- Pilih Kamar --</option>
                                @foreach($candidateRooms as $cr)
                                    <option value="{{ $cr->id }}">Kamar {{ $cr->room_number }} ({{ $cr->roomType?->name ?? 'Kamar' }} - {{ $cr->floor }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Pindah Kamar <span class="text-red-500">*</span></label>
                            <textarea wire:model="move_reason" rows="2" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Permintaan tamu / kendala fasilitas..." required></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Penyesuaian Biaya (Rp, Opsional)</label>
                            <input type="number" wire:model="move_price_adjustment" class="w-full text-xs border-gray-300 rounded-lg" placeholder="0">
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t">
                            <button type="button" wire:click="$set('showMoveModal', false)" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg">Batal</button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg">Proses Pindah</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Status Kebersihan -->
        @if($showCleaningModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-5 space-y-4">
                    <h3 class="text-base font-bold text-gray-900">Ubah Status Kebersihan</h3>
                    @error('cleaning') <div class="p-2 bg-red-50 text-red-700 text-xs rounded">{{ $message }}</div> @enderror
                    <form wire:submit="saveCleaning" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Status Baru</label>
                            <select wire:model="new_cleaning_status" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500">
                                <option value="dirty">Kotor (Perlu Dibersihkan)</option>
                                <option value="cleaning">Sedang Dibersihkan</option>
                                <option value="clean">Bersih (Menunggu Inspeksi)</option>
                                <option value="inspected">Diperiksa / Siap Pakai</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t">
                            <button type="button" wire:click="$set('showCleaningModal', false)" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg">Batal</button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Simpan Status</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Request PMR -->
        @if($showPmrModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-5 space-y-4">
                    <h3 class="text-base font-bold text-gray-900">Permintaan PMR (Make Up Room)</h3>
                    @error('pmr') <div class="p-2 bg-red-50 text-red-700 text-xs rounded">{{ $message }}</div> @enderror
                    <form wire:submit="savePmr" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Prioritas</label>
                            <select wire:model="pmr_priority" class="w-full text-xs border-gray-300 rounded-lg">
                                <option value="normal">Normal</option>
                                <option value="high">Tinggi</option>
                                <option value="urgent">Mendesak (Urgent)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan Tamu</label>
                            <textarea wire:model="pmr_notes" rows="2" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Ganti sprei / handuk tambahan..." required></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t">
                            <button type="button" wire:click="$set('showPmrModal', false)" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg">Batal</button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg">Kirim PMR</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Lapor Kerusakan (Maintenance) -->
        @if($showMaintenanceModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-5 space-y-4">
                    <h3 class="text-base font-bold text-gray-900">Lapor Kerusakan (Maintenance)</h3>
                    @error('maintenance') <div class="p-2 bg-red-50 text-red-700 text-xs rounded">{{ $message }}</div> @enderror
                    <form wire:submit="saveMaintenance" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tingkat Urgensi</label>
                            <select wire:model="maintenance_urgency" class="w-full text-xs border-gray-300 rounded-lg">
                                <option value="low">Rendah (Low)</option>
                                <option value="medium">Sedang (Medium)</option>
                                <option value="high">Tinggi (High)</option>
                                <option value="emergency">Darurat (Emergency)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Kerusakan <span class="text-red-500">*</span></label>
                            <textarea wire:model="maintenance_description" rows="3" class="w-full text-xs border-gray-300 rounded-lg" placeholder="AC bocor / kran air patah..." required></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t">
                            <button type="button" wire:click="$set('showMaintenanceModal', false)" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg">Batal</button>
                            <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">Lapor Rusak</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ==================== MODAL DETIL RESERVASI TAMU ==================== -->
        @if($showDetailModal && $detailReservation)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-5 sm:p-6 space-y-4 max-h-[92vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between pb-3 border-b border-gray-200">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-gray-900">Detil Reservasi Tamu</h3>
                                <span class="font-mono text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-bold">#{{ $detailReservation->reservation_number }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">Informasi lengkap tamu dan reservasi kamar</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                                {{ $detailReservation->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $detailReservation->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $detailReservation->status === 'checked_in' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $detailReservation->status === 'checked_out' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $detailReservation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $detailReservation->status === 'expired' ? 'bg-orange-100 text-orange-800' : '' }}">
                                {{ $detailReservation->status }}
                            </span>
                            <button type="button" wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Profil Tamu -->
                    <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/80 space-y-2">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Identitas Tamu</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $detailReservation->customer->name }}</div>
                                <div class="text-xs text-gray-600 mt-0.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                    <span>{{ $detailReservation->customer->identity_type ?? 'KTP' }}: <strong class="text-gray-800">{{ $detailReservation->customer->identity_number ?? '-' }}</strong></span>
                                </div>
                                @if($detailReservation->customer->company)
                                    <div class="text-xs text-gray-500 mt-0.5">Instansi: {{ $detailReservation->customer->company }}</div>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $detailReservation->customer->getWhatsAppUrl("Assalamu'alaikum Bpk/Ibu {$detailReservation->customer->name}, mengonfirmasi reservasi #{$detailReservation->reservation_number} di InSitu Hotel Syariah Garut.") }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        <span>+{{ $detailReservation->customer->phone }}</span>
                                    </a>
                                </div>
                                @if($detailReservation->customer->email)
                                    <div class="text-xs text-gray-500 truncate">Email: {{ $detailReservation->customer->email }}</div>
                                @endif
                                @if($detailReservation->customer->address)
                                    <div class="text-xs text-gray-500 truncate">Alamat: {{ $detailReservation->customer->address }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Menginap & Kamar -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3 bg-white border border-gray-200 rounded-xl space-y-1.5">
                            <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kamar & Tipe</div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $detailReservation->roomType?->color ?? '#059669' }};"></span>
                                <span class="font-bold text-gray-900 text-sm">{{ $detailReservation->roomType?->name ?? 'Tipe Kamar' }}</span>
                            </div>
                            <div class="text-xs">
                                @if($detailReservation->allocatedRooms->isNotEmpty())
                                    <span class="font-black text-emerald-800 text-sm bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        Kamar {{ $detailReservation->allocatedRooms->pluck('room_number')->join(', ') }}
                                    </span>
                                @else
                                    <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs border border-amber-200 font-medium">Belum Diplot Nomor Kamar</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500 pt-1">
                                Kanal: <span class="font-semibold text-gray-700 uppercase">{{ str_replace('_', ' ', $detailReservation->source) }}</span>
                                @if($detailReservation->source === 'ota_manual' && $detailReservation->ota_name)
                                    ({{ $detailReservation->ota_name }})
                                @endif
                            </div>
                            <div class="pt-1 flex items-center gap-1.5">
                                @if($detailReservation->include_breakfast)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">🍳 Termasuk Sarapan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">☕ Tanpa Sarapan (Room Only)</span>
                                @endif
                            </div>
                        </div>

                        <div class="p-3 bg-white border border-gray-200 rounded-xl space-y-1.5">
                            <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Jadwal Menginap & Tamu</div>
                            <div class="text-xs text-gray-900 space-y-0.5">
                                <div>Check-in: <strong class="text-gray-900">{{ $detailReservation->check_in_date->format('d M Y') }}</strong> (Jam: <strong class="text-blue-700">{{ $detailReservation->check_in_time ?? '14:00' }} WIB</strong>)</div>
                                <div>Check-out: <strong class="text-gray-900">{{ $detailReservation->check_out_date->format('d M Y') }}</strong> (12:00 WIB)</div>
                                <div class="text-gray-500">Durasi: <strong class="text-gray-800">{{ $detailReservation->nights_count }} Malam</strong></div>
                            </div>
                            <div class="pt-1 border-t border-gray-100 text-xs font-semibold text-gray-700">
                                Dewasa: {{ $detailReservation->adults }} | Anak: {{ $detailReservation->children ?? 0 }} | Bayi: {{ $detailReservation->infants ?? 0 }}
                            </div>
                        </div>
                    </div>

                    @if($detailReservation->notes)
                        <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900">
                            <strong>Catatan Khusus / Catatan Tamu:</strong> {{ $detailReservation->notes }}
                        </div>
                    @endif

                    <!-- Rincian Biaya & Add-on -->
                    <div class="p-3.5 bg-gray-50/70 border border-gray-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Rincian Biaya & Tagihan</span>
                            <span class="text-xs px-2 py-0.5 rounded font-bold
                                {{ $detailReservation->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($detailReservation->paid_amount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                {{ $detailReservation->payment_status === 'paid' ? 'LUNAS' : ($detailReservation->paid_amount > 0 ? 'SEBAGIAN (DP)' : 'BELUM BAYAR') }}
                            </span>
                        </div>

                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between text-gray-600">
                                <span>Tarif Kamar ({{ $detailReservation->nights_count }} Malam):</span>
                                <span class="font-medium text-gray-900">{{ rupiah($detailReservation->subtotal) }}</span>
                            </div>

                            @if($detailReservation->serviceItems && $detailReservation->serviceItems->isNotEmpty())
                                <div class="pt-1 border-t border-gray-200">
                                    <div class="text-[11px] font-semibold text-gray-500 mb-1">Layanan Tambahan (Add-on):</div>
                                    @foreach($detailReservation->serviceItems as $svc)
                                        <div class="flex justify-between text-gray-600 pl-2 text-[11px]">
                                            <span>• {{ $svc->service_name }} (x{{ $svc->quantity }}):</span>
                                            <span class="font-medium">{{ rupiah($svc->total_price) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-2 border-t border-gray-300 flex justify-between text-sm font-black text-gray-900">
                                <span>Total Tagihan:</span>
                                <span class="text-emerald-700">{{ rupiah($detailReservation->total_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600">
                                <span>Sudah Dibayar:</span>
                                <span class="font-bold text-gray-800">{{ rupiah($detailReservation->paid_amount) }}</span>
                            </div>
                            @if($detailReservation->balance > 0)
                                <div class="flex justify-between text-xs font-bold text-amber-700">
                                    <span>Sisa Tagihan:</span>
                                    <span>{{ rupiah($detailReservation->balance) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Modal Footer Actions -->
                    <div class="pt-3 border-t border-gray-200 flex flex-wrap justify-between items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('invoice.thermal', $detailReservation->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded-lg text-xs font-semibold inline-flex items-center gap-1 transition">
                                🖨️ Struk Kasir
                            </a>
                            <a href="{{ route('invoice.pdf', $detailReservation->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded-lg text-xs font-semibold inline-flex items-center gap-1 transition">
                                📄 Invoice PDF
                            </a>
                        </div>
                        <div>
                            <button type="button" wire:click="closeDetailModal" class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL CEPAT PLOT KAMAR FISIK (QUICK ALLOCATION MODAL) -->
        @if($showPlotModal && $plotReservation)
            @php
                $isArrivalToday = $plotReservation->check_in_date->toDateString() === now()->toDateString();
                $unpaidBalance = max(0, $plotReservation->total_amount - $plotReservation->paid_amount);
            @endphp
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
                 style="animation: fadeIn 0.15s ease-out;"
                 @click.self="$wire.closePlotModal()">
                <div class="bg-white rounded-3xl max-w-xl w-full max-h-[92vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col my-auto">
                    
                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-extrabold text-gray-900 leading-tight">Plot Kamar Fisik</h3>
                                <p class="text-xs text-gray-500">
                                    {{ $plotReservation->customer->name }} &bull; #{{ $plotReservation->reservation_number }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closePlotModal" class="p-2 text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4">
                        @error('plot')
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold">
                                {{ $message }}
                            </div>
                        @enderror

                        <!-- Ringkasan Reservasi -->
                        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-800 text-sm">{{ $plotReservation->roomType->name }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white shadow-xs" style="background-color: {{ $plotReservation->roomType->color ?? '#059669' }};">
                                    {{ $plotReservation->roomType->code }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-gray-600">
                                <div>
                                    <span class="text-gray-400 block text-[10px]">Tamu:</span>
                                    <span class="font-semibold text-gray-900">{{ $plotReservation->customer->name }}</span> ({{ $plotReservation->customer->phone }})
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px]">Sumber & Paket:</span>
                                    <span class="font-semibold text-gray-900">{{ ucfirst($plotReservation->source) }}</span>
                                    <span class="text-[10px] font-bold block {{ $plotReservation->include_breakfast ? 'text-amber-700' : 'text-gray-500' }}">
                                        {{ $plotReservation->include_breakfast ? '🍳 Dgn Sarapan' : '☕ Room Only' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px]">Jadwal Menginap:</span>
                                    <span class="font-semibold text-gray-900">{{ $plotReservation->check_in_date->format('d/m/Y') }} – {{ $plotReservation->check_out_date->format('d/m/Y') }}</span> ({{ $plotReservation->nights_count }} Malam)
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px]">Total Tagihan:</span>
                                    <span class="font-mono font-bold text-emerald-700">Rp {{ number_format($plotReservation->total_amount, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-gray-500 block">({{ $plotReservation->payment_status === 'paid' ? 'LUNAS' : ($plotReservation->paid_amount > 0 ? 'DP Rp ' . number_format($plotReservation->paid_amount, 0, ',', '.') : 'Belum Bayar') }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Kamar Fisik yang Kosong -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-900 uppercase tracking-wide">
                                Pilih Nomor Kamar Fisik yang Tersedia <span class="text-rose-500">*</span>
                            </label>
                            
                            @if(count($plotAvailableRooms) > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-56 overflow-y-auto p-1">
                                    @foreach($plotAvailableRooms as $room)
                                        @php
                                            $isSelected = (int) $plotTargetRoomId === (int) $room->id;
                                            $cleanColor = match($room->cleaning_status) {
                                                'inspected' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'clean' => 'bg-teal-100 text-teal-800 border-teal-200',
                                                'cleaning' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                                            };
                                            $cleanLabel = match($room->cleaning_status) {
                                                'inspected' => 'Siap Pakai',
                                                'clean' => 'Bersih',
                                                'cleaning' => 'Dibersihkan',
                                                default => 'Kotor',
                                            };
                                        @endphp
                                        <button type="button" 
                                                wire:click="$set('plotTargetRoomId', {{ $room->id }})" 
                                                class="p-3 rounded-2xl border text-left transition relative cursor-pointer {{ $isSelected ? 'border-emerald-600 bg-emerald-50/80 ring-2 ring-emerald-500 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50' }}">
                                            @if($isSelected)
                                                <span class="absolute top-2 right-2 w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                                            @endif
                                            <div class="font-mono font-black text-lg text-gray-900">{{ $room->room_number }}</div>
                                            <div class="text-[11px] text-gray-500">Lantai {{ $room->floor }}</div>
                                            <div class="mt-1.5">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $cleanColor }}">
                                                    {{ $cleanLabel }}
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                                @error('plotTargetRoomId')
                                    <span class="text-[11px] text-rose-500 font-semibold block">{{ $message }}</span>
                                @enderror
                            @else
                                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-center space-y-1">
                                    <span class="text-xs font-bold text-rose-800 block">Tidak Ada Kamar Fisik Kosong</span>
                                    <p class="text-[11px] text-rose-600">Semua kamar fisik tipe ini sedang terisi atau memiliki jadwal bentrok pada tanggal tersebut.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Opsi Konfirmasi & Pembayaran -->
                        <div class="p-3.5 bg-blue-50/60 rounded-2xl border border-blue-100 space-y-2.5 text-xs">
                            <span class="font-bold text-blue-900 block text-xs">Opsi Otomatisasi:</span>
                            
                            @if($plotReservation->status === 'pending')
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" wire:model="plotMarkConfirmed" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-gray-700">Ubah status reservasi menjadi <strong>Confirmed</strong> (Bebaskan dari masa tahan 2 jam)</span>
                                </label>
                            @endif

                            @if($unpaidBalance > 0)
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" wire:model="plotMarkPaid" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-gray-700">Tandai pembayaran transfer telah diterima lunas (<strong>Rp {{ number_format($unpaidBalance, 0, ',', '.') }}</strong>)</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-4 sm:p-5 border-t border-gray-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 bg-gray-50/50">
                        <button type="button" wire:click="closePlotModal" class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                            Batal
                        </button>

                        @if(count($plotAvailableRooms) > 0)
                            @if($isArrivalToday)
                                <button type="button" wire:click="savePlotRoom(false)" wire:loading.attr="disabled" class="w-full sm:w-auto px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    Simpan Alokasi Saja
                                </button>
                                <button type="button" wire:click="savePlotRoom(true)" wire:loading.attr="disabled" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span wire:loading.remove>Simpan &amp; Langsung Check-In</span>
                                    <span wire:loading>Memproses...</span>
                                </button>
                            @else
                                <button type="button" wire:click="savePlotRoom(false)" wire:loading.attr="disabled" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span wire:loading.remove>Simpan Alokasi Kamar</span>
                                    <span wire:loading>Memproses...</span>
                                </button>
                            @endif
                        @endif
                    </div>

                </div>
            </div>
        @endif

        <!-- MODAL SETTING ORDER VIA WEB (ALOKASI KAMAR FISIK PENJUALAN ONLINE) -->
        @if($showWebOrderModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
                 style="animation: fadeIn 0.15s ease-out;"
                 @click.self="$wire.closeWebOrderModal()">
                <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[92vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col my-auto">
                    
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 flex items-center justify-center font-bold shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-gray-900 tracking-tight flex items-center gap-2">
                                    Setting Penjualan Kamar via Website
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">Online Allotment</span>
                                </h3>
                                <p class="text-xs text-gray-500">Pilih tanggal dan tentukan kamar fisik mana saja yang dibuka untuk dipesan tamu online.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeWebOrderModal" class="p-2 text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-5">
                        @error('web_order')
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold">
                                {{ $message }}
                            </div>
                        @enderror

                        <!-- Baris Rentang Tanggal -->
                        <div class="p-4 bg-gradient-to-r from-sky-50/70 to-indigo-50/50 rounded-2xl border border-sky-100/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-sky-950 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Pilih Rentang Tanggal Allotment
                                </label>
                                <span class="text-[11px] text-sky-700 font-medium">
                                    @php
                                        try {
                                            $cIn = \Carbon\Carbon::parse($webOrderStartDate);
                                            $cOut = \Carbon\Carbon::parse($webOrderEndDate);
                                            $nDays = max(1, $cIn->diffInDays($cOut));
                                            echo $nDays . ' Malam';
                                        } catch (\Exception $e) {
                                            echo '1 Malam';
                                        }
                                    @endphp
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <span class="block text-[11px] font-semibold text-gray-600 mb-1">Dari Tanggal (Check-In)</span>
                                    <input type="date" wire:model.live="webOrderStartDate" class="w-full text-xs font-bold border-gray-300 rounded-xl shadow-2xs focus:ring-sky-500 focus:border-sky-500 bg-white">
                                </div>
                                <div>
                                    <span class="block text-[11px] font-semibold text-gray-600 mb-1">Sampai Tanggal (Check-Out)</span>
                                    <input type="date" wire:model.live="webOrderEndDate" min="{{ $webOrderStartDate }}" class="w-full text-xs font-bold border-gray-300 rounded-xl shadow-2xs focus:ring-sky-500 focus:border-sky-500 bg-white">
                                </div>
                            </div>
                            <p class="text-[11px] text-sky-800 leading-tight pt-1">
                                💡 Kamar fisik yang diceklis di bawah akan dialokasikan untuk reservasi web pada rentang tanggal tersebut. Kamar yang sudah terpesan otomatis terkunci (disabled).
                            </p>
                        </div>

                        <!-- Daftar Kamar Fisik Per Tipe Kamar -->
                        <div class="space-y-4">
                            @foreach($this->webOrderRoomTypes as $group)
                                @php
                                    $t = $group['type'];
                                    $typeColor = $t->color ?? '#059669';
                                    $isActive = $roomTypeWebStatus[$t->id] ?? true;
                                @endphp
                                <div class="bg-white rounded-2xl border transition-all duration-150 overflow-hidden {{ $isActive ? 'border-gray-200 shadow-2xs' : 'border-gray-200/60 bg-gray-50/50 opacity-75' }}">
                                    <!-- Header Tipe Kamar dengan Toggle Aktif di Web -->
                                    <div class="p-3.5 bg-gray-50/80 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2.5">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $typeColor }};"></span>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-gray-900 text-sm">{{ $t->name }}</span>
                                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded font-bold text-white shadow-2xs" style="background-color: {{ $typeColor }};">{{ $t->code }}</span>
                                                    <span class="text-xs font-bold text-emerald-700 font-mono">{{ rupiah($t->base_price) }}</span>
                                                </div>
                                                <div class="text-[11px] text-gray-500 flex items-center gap-2 mt-0.5">
                                                    <span>{{ $group['selectedCount'] }} dari {{ $group['availableCount'] }} kamar bebas dibuka</span>
                                                    @if(!$isActive)
                                                        <span class="text-rose-600 font-bold">• Disembunyikan dari Web</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opsi Tipe Kamar: Toggle Aktif Web & Tombol Shortcut Centang -->
                                        <div class="flex items-center gap-3">
                                            <!-- Toggle Tipe Kamar Aktif di Web -->
                                            <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                                <input type="checkbox" wire:model.live="roomTypeWebStatus.{{ $t->id }}" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                                <span class="text-xs font-semibold text-gray-700">Tampil di Web</span>
                                            </label>

                                            <!-- Tombol Shortcut Centang Bebas -->
                                            @if($isActive && $group['availableCount'] > 0)
                                                <div class="flex items-center gap-1 border-l border-gray-200 pl-3">
                                                    <button type="button" wire:click="toggleAllRoomsForType({{ $t->id }}, true)" class="px-2 py-1 text-[11px] font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg transition cursor-pointer">
                                                        Pilih Semua
                                                    </button>
                                                    <button type="button" wire:click="toggleAllRoomsForType({{ $t->id }}, false)" class="px-2 py-1 text-[11px] font-semibold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition cursor-pointer">
                                                        Kosongkan
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Grid Kamar Fisik -->
                                    <div class="p-3.5">
                                        @if($group['rooms']->isEmpty())
                                            <p class="text-xs text-gray-400 italic py-2 text-center">Belum ada kamar fisik untuk tipe ini.</p>
                                        @else
                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                                                @foreach($group['rooms'] as $rmItem)
                                                    @php
                                                        $r = $rmItem['room'];
                                                        $canSelect = $rmItem['canSelect'] && $isActive;
                                                        $isSelected = in_array((int) $r->id, array_map('intval', $selectedWebRooms));
                                                        $cleanLabel = match($r->cleaning_status) {
                                                            'inspected' => 'Siap Pakai',
                                                            'clean' => 'Bersih',
                                                            'cleaning' => 'Cleaning',
                                                            default => 'Kotor',
                                                        };
                                                    @endphp

                                                    <label class="relative p-2.5 rounded-xl border transition flex flex-col justify-between gap-1.5 select-none
                                                        {{ !$canSelect 
                                                            ? 'bg-gray-100/80 border-gray-200 text-gray-400 opacity-60 cursor-not-allowed' 
                                                            : ($isSelected 
                                                                ? 'bg-sky-50/70 border-sky-400 ring-2 ring-sky-300 shadow-2xs cursor-pointer' 
                                                                : 'bg-white border-gray-200 hover:border-gray-300 hover:bg-gray-50/50 cursor-pointer') }}">
                                                        
                                                        <div class="flex items-start justify-between gap-1">
                                                            <div class="flex items-center gap-2">
                                                                <input type="checkbox" 
                                                                       value="{{ $r->id }}" 
                                                                       wire:model.live="selectedWebRooms"
                                                                       {{ !$canSelect ? 'disabled' : '' }}
                                                                       class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 {{ !$canSelect ? 'opacity-40 cursor-not-allowed' : '' }}">
                                                                <span class="font-mono font-black text-sm {{ $canSelect ? 'text-gray-900' : 'text-gray-500' }}">#{{ $r->room_number }}</span>
                                                            </div>
                                                            <span class="text-[10px] text-gray-500 font-medium">{{ $r->floor }}</span>
                                                        </div>

                                                        <!-- Status Kamar / Booking Info -->
                                                        <div class="text-[10px]">
                                                            @if($rmItem['isBooked'])
                                                                <span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200" title="Kamar sudah dipesan oleh tamu">
                                                                    🔒 Terpesan {{ $rmItem['bookedReservation'] ? '(' . $rmItem['bookedReservation']->customer->name . ')' : '' }}
                                                                </span>
                                                            @elseif($rmItem['isMaintenance'])
                                                                <span class="inline-flex items-center gap-1 font-semibold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">
                                                                    🛠️ Maintenance
                                                                </span>
                                                            @else
                                                                <span class="text-[10px] text-gray-500">
                                                                    Status: <strong class="text-emerald-700">{{ $cleanLabel }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-4 sm:p-5 border-t border-gray-100 flex items-center justify-between gap-3 bg-gray-50/50">
                        <div class="text-xs text-gray-600">
                            Total <strong class="text-sky-800">{{ count($selectedWebRooms) }} kamar fisik</strong> terpilih untuk penjualan online.
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="closeWebOrderModal" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="button" wire:click="saveWebOrderSettings" wire:loading.attr="disabled" class="px-5 py-2.5 bg-sky-100 hover:bg-sky-200 text-sky-800 border border-sky-300 font-bold text-xs rounded-xl shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                                <span wire:loading.remove>Simpan Pengaturan Web</span>
                                <span wire:loading>Menyimpan...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>
</div>
