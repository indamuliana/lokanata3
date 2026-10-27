<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kalender Reservasi Terpadu</h1>
                <p class="text-sm text-gray-500 mt-1">Timeline visual terintegrasi untuk Kamar Menginap, Ruang Meeting, dan Restoran Syariah</p>
            </div>

            <!-- View Switcher -->
            <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-xl">
                <button wire:click="$set('activeView', 'tape_chart')" class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition {{ $activeView === 'tape_chart' ? 'bg-white text-emerald-800 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                    📊 Timeline Tape Chart Terpadu
                </button>
                <button wire:click="$set('activeView', 'agenda')" class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition {{ $activeView === 'agenda' ? 'bg-white text-emerald-800 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                    📋 Agenda Harian
                </button>
            </div>
        </div>

        @if($activeView === 'tape_chart')
            <!-- Tape Chart Toolbar: Range, Filter & Navigation -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Filter Kategori Layanan di Tape Chart -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0">
                    <span class="text-xs font-semibold text-gray-500 mr-1">Tampilkan:</span>
                    @foreach([
                        'all' => 'Semua Layanan',
                        'rooms' => '🏨 Kamar',
                        'meeting' => '💼 Ruang Meeting',
                        'dining' => '🍽️ Restoran'
                    ] as $rf => $lbl)
                        <button wire:click="$set('resourceFilter', '{{ $rf }}')" class="px-2.5 py-1 text-xs font-bold rounded-lg transition {{ $resourceFilter === $rf ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>

                <!-- Rentang Hari -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500">Rentang:</span>
                    <button wire:click="setDaysCount(7)" class="px-2 py-1 text-xs font-bold rounded-lg transition {{ $daysCount === 7 ? 'bg-emerald-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        7 Hari
                    </button>
                    <button wire:click="setDaysCount(14)" class="px-2 py-1 text-xs font-bold rounded-lg transition {{ $daysCount === 14 ? 'bg-emerald-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        14 Hari
                    </button>
                    <button wire:click="setDaysCount(30)" class="px-2 py-1 text-xs font-bold rounded-lg transition {{ $daysCount === 30 ? 'bg-emerald-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        30 Hari
                    </button>
                </div>

                <!-- Navigasi Periode -->
                <div class="flex items-center gap-2">
                    <button wire:click="changePeriod(-1)" class="px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-bold rounded-lg transition flex items-center gap-1">
                        ‹ Periode Lalu
                    </button>
                    <button wire:click="goToToday" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-lg transition">
                        Hari Ini
                    </button>
                    <button wire:click="changePeriod(1)" class="px-2.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-bold rounded-lg transition flex items-center gap-1">
                        Periode Depan ›
                    </button>
                </div>
            </div>

            <!-- Periode & Legend Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
                <div class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                    <span>📅 Periode:</span>
                    <span class="text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">{{ $currentPeriodLabel }}</span>
                </div>

                <div class="flex items-center gap-3 overflow-x-auto text-xs text-gray-600 flex-wrap">
                    <span class="font-bold text-gray-400">Status:</span>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-emerald-600 inline-block"></span>
                        <span>Kamar Terisi</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-blue-600 inline-block"></span>
                        <span>Kamar Booking</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-purple-600 inline-block"></span>
                        <span>Ruang Meeting</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-amber-600 inline-block"></span>
                        <span>Resto Syariah</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-slate-400 inline-block"></span>
                        <span>Checkout/Selesai</span>
                    </div>
                </div>
            </div>

            <!-- Tape Chart Table Grid -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border-collapse text-left">
                        <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <!-- Header Sticky Resource -->
                                <th class="sticky left-0 z-20 bg-gray-100 py-3 px-3 text-xs font-bold uppercase tracking-wider border-r border-gray-200 w-40 sm:w-48 shadow-xs">
                                    Unit / Resource
                                </th>
                                <!-- Kolom Hari Timeline -->
                                @foreach($timelineDates as $td)
                                    <th class="py-2 px-1 text-center text-xs font-semibold border-r border-gray-200 min-w-[85px] transition {{ $td['is_today'] ? 'bg-emerald-50 text-emerald-900 border-b-2 border-b-emerald-600' : ($td['is_high_season'] ? 'bg-amber-100/60 text-amber-950 border-b border-b-amber-300' : 'text-gray-600') }}" title="{{ $td['season_label'] }}">
                                        <div class="uppercase text-[9px] {{ $td['is_today'] ? 'font-bold text-emerald-700' : ($td['is_high_season'] ? 'text-amber-800 font-bold' : 'text-gray-400') }}">{{ $td['day_name'] }}</div>
                                        <div class="font-bold text-xs {{ $td['is_today'] ? 'text-emerald-800' : ($td['is_high_season'] ? 'text-amber-900' : 'text-gray-800') }}">{{ $td['day_number'] }}</div>
                                        @if($td['is_today'])
                                            <span class="inline-block px-1 py-0.2 bg-emerald-600 text-white rounded text-[8px] font-bold">HARI INI</span>
                                        @elseif($td['season_type'] === 'holiday')
                                            <span class="inline-block px-1.5 py-0.2 bg-amber-600 text-white rounded text-[7.5px] font-bold tracking-tight truncate max-w-[80px]" title="{{ $td['holiday_name'] }}">LIBUR</span>
                                        @elseif($td['is_high_season'])
                                            <span class="inline-block px-1 py-0.2 bg-amber-200 text-amber-900 rounded text-[7.5px] font-bold">WEEKEND</span>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white text-xs">

                            <!-- SECTION 1: KAMAR MENGINAP FISIK -->
                            @if(in_array($resourceFilter, ['all', 'rooms']))
                                <tr class="bg-slate-100">
                                    <td colspan="{{ count($timelineDates) + 1 }}" class="py-1.5 px-3 font-bold text-gray-800 text-[11px] uppercase tracking-wider border-y border-gray-300">
                                        🏨 Kamar Menginap Hotel ({{ $rooms->count() }} Kamar Fisik)
                                    </td>
                                </tr>
                                @foreach($rooms as $room)
                                    @php
                                        $roomAllocs = $allocationsByRoom->get($room->id, collect());
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="sticky left-0 z-10 bg-white py-2 px-3 font-semibold text-gray-900 border-r border-gray-200 whitespace-nowrap shadow-xs">
                                            <div class="flex items-center gap-1.5">
                                                @php
                                                    $cleanDot = match($room->cleaning_status) {
                                                        'inspected' => 'bg-emerald-500',
                                                        'clean' => 'bg-teal-500',
                                                        'cleaning' => 'bg-blue-500',
                                                        default => 'bg-amber-500',
                                                    };
                                                @endphp
                                                <span class="w-2.5 h-2.5 rounded-full {{ $room->operational_status === 'active' ? $cleanDot : 'bg-gray-400' }}" title="{{ ucfirst($room->cleaning_status) }}"></span>
                                                <span class="font-bold text-gray-900 text-sm">#{{ $room->room_number }}</span>
                                            </div>
                                            <div class="text-[10px] text-gray-500 font-normal flex items-center gap-1 mt-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full inline-block" style="background-color: {{ $room->roomType?->color ?? '#059669' }};"></span>
                                                <span>{{ $room->roomType?->name ?? 'Kamar' }} · Lt. {{ $room->floor ?: '1' }}</span>
                                            </div>
                                        </td>

                                        @foreach($timelineDates as $td)
                                            @php
                                                $dStr = $td['date'];
                                                $isMaintenance = $room->operational_status === 'maintenance';
                                                $stayAlloc = $roomAllocs->first(function($a) use ($dStr) {
                                                    return $a->assigned_from->toDateString() <= $dStr && $a->assigned_to->toDateString() > $dStr;
                                                });
                                                $checkoutAlloc = null;
                                                if (!$stayAlloc) {
                                                    $checkoutAlloc = $roomAllocs->first(function($a) use ($dStr) {
                                                        return $a->assigned_to->toDateString() === $dStr;
                                                    });
                                                }
                                            @endphp

                                            <td class="py-1 px-1 border-r border-gray-100 text-center align-middle {{ $td['is_today'] ? 'bg-emerald-50/20' : ($td['is_high_season'] ? 'bg-amber-50/30' : '') }}">
                                                @if($isMaintenance)
                                                    <div class="w-full min-h-[42px] rounded bg-rose-100 border border-rose-200 text-rose-800 text-[10px] font-bold flex items-center justify-center p-1">
                                                        🛠️ Perbaikan
                                                    </div>
                                                @elseif($stayAlloc)
                                                    @php
                                                        $res = $stayAlloc->reservation;
                                                        $isCheckIn = $stayAlloc->assigned_from->toDateString() === $dStr;
                                                        $isLastNight = \Carbon\Carbon::parse($stayAlloc->assigned_to)->subDay()->toDateString() === $dStr;
                                                        $statusColor = match($res->status) {
                                                            'checked_in' => 'bg-emerald-600 hover:bg-emerald-700 text-white',
                                                            'confirmed' => 'bg-blue-600 hover:bg-blue-700 text-white',
                                                            'checked_out' => 'bg-slate-400 hover:bg-slate-500 text-white',
                                                            'pending' => 'bg-amber-500 hover:bg-amber-600 text-white',
                                                            default => 'bg-gray-500 text-white',
                                                        };
                                                    @endphp
                                                    <div wire:click="openDetail({{ $res->id }}, 'room')" class="w-full min-h-[42px] p-1 rounded-md {{ $statusColor }} shadow-xs cursor-pointer text-left transition transform hover:scale-[1.02] flex flex-col justify-between" title="Klik rincian reservasi">
                                                        <div class="font-bold text-[10px] truncate leading-tight">
                                                            {{ $res->customer->name }}
                                                        </div>
                                                        <div class="flex items-center justify-between text-[8px] opacity-90 font-medium mt-0.5">
                                                            <span>#{{ $res->reservation_number }}</span>
                                                            @if($isCheckIn)
                                                                <span class="bg-white/25 px-1 rounded font-bold">CI</span>
                                                            @elseif($isLastNight)
                                                                <span class="bg-white/25 px-1 rounded font-bold">CO</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @elseif($checkoutAlloc)
                                                    @php
                                                        $coRes = $checkoutAlloc->reservation;
                                                    @endphp
                                                    <div wire:click="openDetail({{ $coRes->id }}, 'room')" class="w-full min-h-[42px] p-1 rounded bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 cursor-pointer text-left transition flex flex-col justify-between" title="Tamu checkout hari ini">
                                                        <div class="text-[9px] font-bold truncate">
                                                            Out: {{ $coRes->customer->name }}
                                                        </div>
                                                        <span class="text-[8px] text-slate-400 font-mono">12:00 WIB</span>
                                                    </div>
                                                @else
                                                    <div class="w-full min-h-[42px] flex items-center justify-center text-gray-300 text-[10px] font-medium hover:text-emerald-600 hover:bg-emerald-50/50 rounded transition">
                                                        —
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif

                            <!-- SECTION 2: RUANG MEETING & ACARA -->
                            @if(in_array($resourceFilter, ['all', 'meeting']))
                                <tr class="bg-purple-50/80">
                                    <td colspan="{{ count($timelineDates) + 1 }}" class="py-1.5 px-3 font-bold text-purple-900 text-[11px] uppercase tracking-wider border-y border-purple-200">
                                        💼 Ruang Meeting dan Ballroom ({{ $meetingRooms->count() }} Ruangan)
                                    </td>
                                </tr>
                                @foreach($meetingRooms as $mr)
                                    @php
                                        $roomMeetings = $meetingBookings->get($mr->id, collect());
                                    @endphp
                                    <tr class="hover:bg-purple-50/30 transition">
                                        <td class="sticky left-0 z-10 bg-white py-2 px-3 font-semibold text-gray-900 border-r border-gray-200 whitespace-nowrap shadow-xs">
                                            <div class="font-bold text-purple-900 text-xs">{{ $mr->name }}</div>
                                            <div class="text-[10px] text-gray-500 font-normal">Kapasitas: {{ $mr->capacity }} Pax</div>
                                        </td>

                                        @foreach($timelineDates as $td)
                                            @php
                                                $dStr = $td['date'];
                                                $meetingsOnDate = $roomMeetings->filter(fn($m) => $m->event_date->toDateString() === $dStr);
                                            @endphp
                                            <td class="py-1 px-1 border-r border-gray-100 text-center align-middle {{ $td['is_today'] ? 'bg-emerald-50/20' : '' }}">
                                                @if($meetingsOnDate->isNotEmpty())
                                                    <div class="space-y-1">
                                                        @foreach($meetingsOnDate as $m)
                                                            @php
                                                                $mColor = $m->status === 'completed' ? 'bg-slate-400 hover:bg-slate-500' : 'bg-purple-600 hover:bg-purple-700';
                                                            @endphp
                                                            <div wire:click="openDetail({{ $m->id }}, 'meeting')" class="w-full min-h-[42px] p-1 rounded-md {{ $mColor }} text-white shadow-xs cursor-pointer text-left transition transform hover:scale-[1.02] flex flex-col justify-between" title="Klik rincian meeting: {{ $m->event_name }}">
                                                                <div class="font-bold text-[10px] truncate leading-tight">
                                                                    {{ $m->event_name }}
                                                                </div>
                                                                <div class="text-[8px] opacity-90 truncate">
                                                                    {{ $m->start_time }}-{{ $m->end_time }} · {{ $m->pax }}p
                                                                </div>
                                                                <div class="text-[8px] opacity-80 truncate">
                                                                    {{ $m->customer->name }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="w-full min-h-[42px] flex items-center justify-center text-gray-300 text-[10px] font-medium hover:text-purple-600 hover:bg-purple-50/50 rounded transition">
                                                        —
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif

                            <!-- SECTION 3: RESTORAN & FINE DINING SYARIAH -->
                            @if(in_array($resourceFilter, ['all', 'dining']))
                                <tr class="bg-amber-50/80">
                                    <td colspan="{{ count($timelineDates) + 1 }}" class="py-1.5 px-3 font-bold text-amber-900 text-[11px] uppercase tracking-wider border-y border-amber-200">
                                        🍽️ Restoran dan Area Meja Syariah ({{ count($diningAreas) }} Area)
                                    </td>
                                </tr>
                                @foreach($diningAreas as $area)
                                    @php
                                        $areaDinings = $diningBookings->get($area, collect());
                                    @endphp
                                    <tr class="hover:bg-amber-50/30 transition">
                                        <td class="sticky left-0 z-10 bg-white py-2 px-3 font-semibold text-gray-900 border-r border-gray-200 whitespace-nowrap shadow-xs">
                                            <div class="font-bold text-amber-900 text-xs">{{ $area }}</div>
                                            <div class="text-[10px] text-gray-500 font-normal">Restoran Halal</div>
                                        </td>

                                        @foreach($timelineDates as $td)
                                            @php
                                                $dStr = $td['date'];
                                                $diningsOnDate = $areaDinings->filter(fn($d) => $d->reservation_date->toDateString() === $dStr);
                                            @endphp
                                            <td class="py-1 px-1 border-r border-gray-100 text-center align-middle {{ $td['is_today'] ? 'bg-emerald-50/20' : '' }}">
                                                @if($diningsOnDate->isNotEmpty())
                                                    <div class="space-y-1">
                                                        @foreach($diningsOnDate as $d)
                                                            @php
                                                                $dColor = $d->status === 'completed' ? 'bg-slate-400 hover:bg-slate-500' : 'bg-amber-600 hover:bg-amber-700';
                                                            @endphp
                                                            <div wire:click="openDetail({{ $d->id }}, 'dining')" class="w-full min-h-[42px] p-1 rounded-md {{ $dColor }} text-white shadow-xs cursor-pointer text-left transition transform hover:scale-[1.02] flex flex-col justify-between" title="Klik rincian resto: {{ $d->customer->name }}">
                                                                <div class="font-bold text-[10px] truncate leading-tight">
                                                                    {{ $d->diningPackage ? $d->diningPackage->name : 'Ala Carte' }}
                                                                </div>
                                                                <div class="text-[8px] opacity-90 truncate">
                                                                    {{ $d->time_slot }} · {{ $d->pax }} Pax
                                                                </div>
                                                                <div class="text-[8px] opacity-80 truncate">
                                                                    {{ $d->customer->name }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="w-full min-h-[42px] flex items-center justify-center text-gray-300 text-[10px] font-medium hover:text-amber-600 hover:bg-amber-50/50 rounded transition">
                                                        —
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reservasi Belum Alokasi Kamar Fisik (Jika Ada) -->
            @if(in_array($resourceFilter, ['all', 'rooms']) && $unassignedReservations->isNotEmpty())
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Kamar Belum Diplot Nomor ({{ $unassignedReservations->count() }})
                        </span>
                        <a href="{{ route('reservations.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 underline">
                            Alokasikan di Halaman Reservasi →
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($unassignedReservations as $unres)
                            <div wire:click="openDetail({{ $unres->id }}, 'room')" class="bg-white p-2.5 rounded-lg border border-amber-200 shadow-xs cursor-pointer hover:border-emerald-500 transition">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 text-xs">{{ $unres->customer->name }}</span>
                                    <span class="text-[10px] font-mono text-gray-400">#{{ $unres->reservation_number }}</span>
                                </div>
                                <div class="text-[11px] text-gray-600 mt-0.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full inline-block" style="background-color: {{ $unres->roomType?->color ?? '#059669' }};"></span>
                                    <span>{{ $unres->room_type_name }}</span>
                                </div>
                                <div class="text-[10px] text-gray-500 mt-1">
                                    {{ $unres->check_in_date->format('d M') }} - {{ $unres->check_out_date->format('d M Y') }} ({{ $unres->nights_count }} Malam)
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            <!-- Agenda View: Jadwal Acara & Restoran Harian -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-2">
                        <button wire:click="changeAgendaDate(-1)" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition" title="Hari Sebelumnya">
                            ‹
                        </button>
                        <input type="date" wire:model.live="agendaDate" class="text-xs font-bold text-gray-800">
                        <button wire:click="changeAgendaDate(1)" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition" title="Hari Berikutnya">
                            ›
                        </button>
                        <button wire:click="$set('agendaDate', '{{ now()->toDateString() }}')" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                            Hari Ini
                        </button>
                    </div>

                    <div class="flex items-center gap-2 overflow-x-auto">
                        <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Filter:</span>
                        @foreach([
                            'all' => 'Semua Layanan',
                            'rooms' => '🏨 Kamar',
                            'meeting' => '💼 Ruang Meeting',
                            'dining' => '🍽️ Restoran Syariah'
                        ] as $k => $lbl)
                            <button wire:click="$set('typeFilter', '{{ $k }}')" class="px-3 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $typeFilter === $k ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="p-3 bg-white rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-sm font-bold text-gray-900">Agenda Tanggal: {{ $agendaCurrentDate->isoFormat('dddd, D MMMM Y') }}</span>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">{{ $schedules->count() }} Aktivitas Terjadwal</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($schedules as $item)
                        <div wire:click="openDetail({{ $item['id'] }}, '{{ $item['res_type'] }}')" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-3 hover:shadow-md cursor-pointer transition">
                            <div class="flex items-start justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $item['category'] === 'kamar' ? 'bg-blue-50 text-blue-800 border border-blue-200' : '' }}
                                    {{ $item['category'] === 'meeting' ? 'bg-purple-50 text-purple-800 border border-purple-200' : '' }}
                                    {{ $item['category'] === 'dining' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}">
                                    {{ $item['type'] }}
                                </span>
                                <span class="font-mono text-[11px] font-bold text-gray-400">#{{ $item['ref'] }}</span>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900 text-sm">{{ $item['title'] }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $item['subtitle'] }}</p>
                            </div>

                            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1 text-gray-600 font-medium">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $item['time'] }}</span>
                                </div>
                                <span class="font-bold {{ $item['paid'] >= $item['total'] ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ rupiah($item['total']) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-xl border border-gray-200">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm">Tidak ada jadwal kamar, meeting, atau restoran untuk tanggal ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- MODAL DETAIL RESERVASI (KAMAR, MEETING, ATAU RESTO) -->
        @if($showDetailModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
                    
                    <!-- Detail Kamar -->
                    @if($selectedReservationType === 'room' && $selectedReservation)
                        <div class="flex items-center justify-between pb-3 border-b">
                            <div>
                                <span class="text-xs font-mono font-bold text-gray-400">#{{ $selectedReservation->reservation_number }}</span>
                                <h3 class="text-lg font-bold text-gray-900">Rincian Reservasi Kamar</h3>
                            </div>
                            <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Nama Tamu:</span>
                                    <span class="font-bold text-gray-900">{{ $selectedReservation->customer->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">WhatsApp:</span>
                                    <span class="font-medium text-emerald-700">{{ $selectedReservation->customer->phone }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Sumber:</span>
                                    <span class="font-semibold text-gray-800 uppercase">{{ str_replace('_', ' ', $selectedReservation->source) }}</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Tipe Kamar:</span>
                                    <span class="font-bold text-gray-900 inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full inline-block" style="background-color: {{ $selectedReservation->roomType?->color ?? '#059669' }};"></span>
                                        {{ $selectedReservation->room_type_name }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Nomor Kamar Fisik:</span>
                                    <span class="font-bold text-emerald-800">
                                        {{ $selectedReservation->allocatedRooms->isNotEmpty() ? $selectedReservation->allocatedRooms->map(fn($ar) => $ar->room_number)->join(', ') : 'Belum Diplot' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Periode Menginap:</span>
                                    <span class="font-medium text-gray-900">
                                        {{ $selectedReservation->check_in_date->format('d M Y') }} - {{ $selectedReservation->check_out_date->format('d M Y') }} ({{ $selectedReservation->nights_count }} Malam)
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Status:</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $selectedReservation->status === 'checked_in' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $selectedReservation->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $selectedReservation->status === 'checked_out' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $selectedReservation->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                        {{ $selectedReservation->status }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Total Tagihan:</span>
                                    <span class="font-bold text-emerald-800">{{ rupiah($selectedReservation->total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Telah Terbayar:</span>
                                    <span class="font-bold text-gray-900">{{ rupiah($selectedReservation->paid_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Sisa Tagihan:</span>
                                    <span class="font-bold {{ $selectedReservation->balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                        {{ rupiah($selectedReservation->balance) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t gap-2">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('invoice.thermal', $selectedReservation->id) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Cetak Struk Thermal 80mm">
                                    🖨️ Struk
                                </a>
                                <a href="{{ route('invoice.pdf', $selectedReservation->id) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Unduh Invoice PDF">
                                    📄 PDF
                                </a>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('reservations.index') }}" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg transition">
                                    Buka di Reservasi →
                                </a>
                                <button wire:click="$set('showDetailModal', false)" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg">
                                    Tutup
                                </button>
                            </div>
                        </div>

                    <!-- Detail Ruang Meeting -->
                    @elseif($selectedReservationType === 'meeting' && $selectedMeeting)
                        <div class="flex items-center justify-between pb-3 border-b">
                            <div>
                                <span class="text-xs font-mono font-bold text-purple-600">#{{ $selectedMeeting->reservation_number }}</span>
                                <h3 class="text-lg font-bold text-gray-900">Rincian Booking Ruang Meeting</h3>
                            </div>
                            <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="bg-purple-50/50 p-3 rounded-xl border border-purple-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Nama Acara:</span>
                                    <span class="font-bold text-purple-950">{{ $selectedMeeting->event_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Ruangan:</span>
                                    <span class="font-bold text-purple-800">{{ $selectedMeeting->meetingRoom->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Penyelenggara / Tamu:</span>
                                    <span class="font-semibold text-gray-900">{{ $selectedMeeting->customer->name }} ({{ $selectedMeeting->customer->phone }})</span>
                                </div>
                                @if($selectedMeeting->customer->company)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-semibold">Instansi / Perusahaan:</span>
                                        <span class="font-medium text-gray-700">{{ $selectedMeeting->customer->company }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Tanggal & Waktu:</span>
                                    <span class="font-bold text-gray-900">
                                        {{ $selectedMeeting->event_date->format('d M Y') }} ({{ $selectedMeeting->start_time }} - {{ $selectedMeeting->end_time }} WIB)
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Jumlah Peserta:</span>
                                    <span class="font-semibold text-gray-800">{{ $selectedMeeting->pax }} Orang / Pax</span>
                                </div>
                                @if($selectedMeeting->meetingPackage)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-semibold">Paket Konsumsi:</span>
                                        <span class="font-medium text-purple-700">{{ $selectedMeeting->meetingPackage->name }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Status:</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-100 text-purple-800">
                                        {{ $selectedMeeting->status }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Total Biaya:</span>
                                    <span class="font-bold text-purple-900">{{ rupiah($selectedMeeting->total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Terbayar:</span>
                                    <span class="font-bold text-gray-900">{{ rupiah($selectedMeeting->paid_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Sisa Tagihan:</span>
                                    <span class="font-bold {{ $selectedMeeting->balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                        {{ rupiah($selectedMeeting->balance) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t gap-2">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('meeting.thermal', $selectedMeeting->id) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Cetak Struk Thermal Meeting">
                                    🖨️ Struk
                                </a>
                                <a href="{{ route('meeting.pdf', $selectedMeeting->id) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Unduh Invoice PDF Meeting">
                                    📄 PDF
                                </a>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('meeting-rooms.index') }}" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-800 text-xs font-semibold rounded-lg transition">
                                    Buka di Meeting →
                                </a>
                                <button wire:click="$set('showDetailModal', false)" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg">
                                    Tutup
                                </button>
                            </div>
                        </div>

                    <!-- Detail Restoran Syariah -->
                    @elseif($selectedReservationType === 'dining' && $selectedDining)
                        <div class="flex items-center justify-between pb-3 border-b">
                            <div>
                                <span class="text-xs font-mono font-bold text-amber-600">#{{ $selectedDining->reservation_number }}</span>
                                <h3 class="text-lg font-bold text-gray-900">Rincian Reservasi Restoran</h3>
                            </div>
                            <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="bg-amber-50/50 p-3 rounded-xl border border-amber-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Paket Menu:</span>
                                    <span class="font-bold text-amber-950">{{ $selectedDining->diningPackage ? $selectedDining->diningPackage->name : 'Pesanan Ala Carte' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Area / Meja:</span>
                                    <span class="font-bold text-amber-800">{{ $selectedDining->table_number_or_area }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Nama Pemesan:</span>
                                    <span class="font-semibold text-gray-900">{{ $selectedDining->customer->name }} ({{ $selectedDining->customer->phone }})</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Tanggal & Jam:</span>
                                    <span class="font-bold text-gray-900">
                                        {{ $selectedDining->reservation_date->format('d M Y') }} · Jam {{ $selectedDining->time_slot }} WIB
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Jumlah Tamu:</span>
                                    <span class="font-semibold text-gray-800">{{ $selectedDining->pax }} Orang / Pax</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Status:</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                                        {{ $selectedDining->status }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Total Tagihan:</span>
                                    <span class="font-bold text-amber-900">{{ rupiah($selectedDining->total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Telah Terbayar:</span>
                                    <span class="font-bold text-gray-900">{{ rupiah($selectedDining->paid_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-semibold">Sisa Tagihan:</span>
                                    <span class="font-bold {{ $selectedDining->balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                        {{ rupiah($selectedDining->balance) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t gap-2">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('dining.thermal', $selectedDining->id) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Cetak Struk Thermal Resto">
                                    🖨️ Struk
                                </a>
                                <a href="{{ route('dining.pdf', $selectedDining->id) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Unduh Invoice PDF Resto">
                                    📄 PDF
                                </a>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('dining.index') }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg transition">
                                    Buka di Restoran →
                                </a>
                                <button wire:click="$set('showDetailModal', false)" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        @endif
    </div>
</div>
