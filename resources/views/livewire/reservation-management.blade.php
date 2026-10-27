<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Reservasi</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola reservasi Walk-In, OTA Manual, Website, dan WhatsApp</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5">
                @can('reservations.manage')
                <!-- Tombol Master Layanan Tambahan -->
                <button wire:click="openMasterAddonModal" class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5" title="Kelola Katalog Master Layanan Ekstra">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Master Layanan Tambahan</span>
                </button>

                <!-- Tombol Manual Expiry -->
                <button wire:click="triggerManualReleaseExpired" wire:loading.attr="disabled" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5" title="Periksa dan batalkan booking website pending yang lewat batas 2 jam">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Lepas Booking Kedaluwarsa</span>
                </button>

                <!-- Tombol Input Reservasi Baru -->
                <button wire:click="openCreateModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Input Reservasi Baru</span>
                </button>
                @endcan
            </div>
        </div>

        @if(session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        <!-- Tab Switcher Utama: Aktif & Mendatang vs Riwayat / Selesai -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 pb-3">
            <div class="flex items-center gap-2">
                <button wire:click="$set('viewTab', 'active')" class="px-4 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2 {{ $viewTab === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>🛎️ Tamu Aktif & Mendatang</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $viewTab === 'active' ? 'bg-emerald-700 text-white' : 'bg-gray-200 text-gray-700' }} font-mono">{{ $activeCount }}</span>
                </button>
                <button wire:click="$set('viewTab', 'history')" class="px-4 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2 {{ $viewTab === 'history' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>📁 Riwayat Selesai / Checkout</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $viewTab === 'history' ? 'bg-indigo-700 text-white' : 'bg-gray-200 text-gray-700' }} font-mono">{{ $historyCount }}</span>
                </button>
            </div>

            @if($viewTab === 'history')
                <!-- Tombol Cepat Ekspor Excel & PDF untuk Riwayat Kamar -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.export.csv', ['unit' => 'room', 'history_only' => 1]) }}" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-semibold shadow-2xs inline-flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Ekspor Excel</span>
                    </a>
                    <a href="{{ route('reports.export.pdf', ['unit' => 'room', 'history_only' => 1]) }}" target="_blank" class="px-3.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-800 border border-red-300 rounded-lg text-xs font-semibold shadow-2xs inline-flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Ekspor PDF</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Filter & Search Bar Terpadu -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-3">
            <!-- Baris 1: Search, Filter Periode, Filter Kanal, Filter Pembayaran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor, nama, WA..." class="w-full text-xs pl-8 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div>
                    <select wire:model.live="periodFilter" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="all">📅 Semua Periode</option>
                        <option value="today">📅 Hari Ini</option>
                        <option value="this_week">📅 Minggu Ini</option>
                        <option value="this_month">📅 Bulan Ini</option>
                    </select>
                </div>

                <div>
                    <select wire:model.live="channelFilter" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="all">🌐 Semua Kanal</option>
                        <option value="walk_in">Walk-In (Front Desk)</option>
                        <option value="ota_manual">OTA Manual</option>
                        <option value="whatsapp_manual">WhatsApp Manual</option>
                        <option value="website">Website Booking</option>
                    </select>
                </div>

                <div>
                    <select wire:model.live="paymentFilter" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="all">💳 Semua Status Bayar</option>
                        <option value="paid">Lunas (Paid)</option>
                        <option value="partially_paid">Sebagian (DP)</option>
                        <option value="unpaid">Belum Bayar (Unpaid)</option>
                    </select>
                </div>
            </div>

            <!-- Baris 2: Filter Status Pill Tabs -->
            <div class="flex items-center gap-1.5 pt-2 border-t border-gray-100 overflow-x-auto">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap mr-1">Status:</span>
                @foreach([
                    'all' => 'Semua',
                    'confirmed' => 'Terkonfirmasi',
                    'pending' => 'Pending (Website)',
                    'checked_in' => 'In-House',
                    'checked_out' => 'Checkout',
                    'cancelled' => 'Batal',
                    'expired' => 'Kedaluwarsa'
                ] as $val => $label)
                    <button wire:click="$set('statusFilter', '{{ $val }}')" class="px-2.5 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $statusFilter === $val ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Floating Action Bar Pilihan Baris -->
        @if(count($selectedRows) > 0)
            <div class="bg-emerald-900 text-white px-4 py-2.5 rounded-xl shadow-lg flex items-center justify-between animate-fade-in text-xs font-medium">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span><strong>{{ count($selectedRows) }}</strong> reservasi terpilih</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="clearSelectedRows" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 text-white rounded-lg transition cursor-pointer">
                        Batalkan Pilihan
                    </button>
                </div>
            </div>
        @endif

        <!-- Table Reservasi -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-3 w-14">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>No.</span>
                                </div>
                            </th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Tamu</th>
                            <th class="py-3 px-4">Kanal</th>
                            <th class="py-3 px-4">Tipe Kamar</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $todayStr = now()->toDateString();
                        @endphp
                        @forelse($reservations as $res)
                            @php
                                $isTodayActive = $res->check_in_date->toDateString() <= $todayStr 
                                                 && $res->check_out_date->toDateString() >= $todayStr 
                                                 && !in_array($res->status, ['cancelled', 'expired']);
                            @endphp
                            <tr class="transition {{ $isTodayActive ? 'bg-emerald-50/70 border-l-4 border-l-emerald-500 font-medium' : 'hover:bg-gray-50/70' }}">
                                <!-- Checkbox & No. -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" wire:model.live="selectedRows" value="{{ $res->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="font-mono text-gray-500 font-semibold">{{ $reservations->firstItem() + $loop->index }}</span>
                                    </div>
                                </td>

                                <!-- Tanggal -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-gray-900">{{ $res->check_in_date->format('d M Y') }} - {{ $res->check_out_date->format('d M Y') }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-xs text-gray-600 flex-wrap">
                                        <span class="font-medium text-gray-700">{{ $res->nights_count }} Malam</span>
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 bg-blue-50 text-blue-700 border border-blue-200 rounded font-semibold text-[10px]" title="Estimasi Jam Check-In">
                                            🕒 {{ $res->check_in_time ?? '14:00' }} WIB
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono mt-0.5">#{{ $res->reservation_number }}</div>
                                </td>

                                <!-- Tamu -->
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ $res->customer->name }}</div>
                                    <div class="text-[11px] text-gray-600 mt-0.5 font-medium">
                                        Dewasa: {{ $res->adults }} | Anak: {{ $res->children ?? 0 }} | Bayi: {{ $res->infants ?? 0 }}
                                    </div>
                                    @if($res->customer->identity_type && $res->customer->identity_number)
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ $res->customer->identity_type }}: {{ $res->customer->identity_number }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Kanal -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                            {{ $res->source === 'walk_in' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                            {{ $res->source === 'ota_manual' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                            {{ $res->source === 'website' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                            {{ $res->source === 'whatsapp_manual' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}">
                                            {{ str_replace('_', ' ', $res->source) }}
                                        </span>
                                        @if($res->source === 'ota_manual' && $res->ota_name)
                                            <span class="text-[10px] text-gray-500 font-medium">({{ $res->ota_name }})</span>
                                        @endif
                                    </div>
                                    <div class="font-bold text-gray-900 text-xs mt-1">{{ rupiah($res->total_amount) }}</div>
                                    <div class="mt-0.5">
                                        <span class="text-[10px] px-1.5 py-0.2 rounded font-bold
                                            {{ $res->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($res->paid_amount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $res->payment_status === 'paid' ? 'Lunas' : ($res->paid_amount > 0 ? 'Sebagian (DP: ' . rupiah($res->paid_amount) . ')' : 'Belum Bayar') }}
                                        </span>
                                    </div>
                                    @if($res->source === 'ota_manual' && $res->ota_name)
                                        <button wire:click="toggleOtaSettlement({{ $res->id }})" class="mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold transition block {{ $res->ota_settlement_status === 'settled' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' }}" title="Status Pencairan Dana OTA">
                                            {{ $res->ota_settlement_status === 'settled' ? '✓ Dana Cair' : '⏳ Dana Pending' }}
                                        </button>
                                    @endif
                                </td>

                                <!-- Tipe Kamar -->
                                <td class="py-3 px-4">
                                    <div>
                                        @if($res->allocatedRooms->isNotEmpty())
                                            <span class="text-sm font-black text-gray-900 block leading-tight">
                                                Kamar {{ $res->allocatedRooms->map(fn($ar) => $ar->room_number)->join(', ') }}
                                            </span>
                                        @else
                                            <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 inline-block">
                                                Belum Diplot
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 text-xs text-gray-600">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $res->roomType?->color ?? '#059669' }};"></span>
                                        <span class="font-medium truncate">{{ $res->room_type_name }}</span>
                                    </div>
                                </td>

                                <!-- Status & WhatsApp -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            {{ $res->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $res->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $res->status === 'checked_in' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $res->status === 'checked_out' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $res->status === 'expired' ? 'bg-orange-100 text-orange-800' : '' }}">
                                            {{ $res->status }}
                                        </span>

                                        <!-- Cukup icon WhatsApp saja -->
                                        <a href="{{ $res->customer->getWhatsAppUrl("Assalamu'alaikum Bpk/Ibu {$res->customer->name}, mengonfirmasi reservasi #{$res->reservation_number} di InSitu Hotel Syariah Garut.") }}" target="_blank" class="p-1 rounded-full text-white bg-emerald-500 hover:bg-emerald-600 shadow-2xs transition inline-flex items-center justify-center shrink-0" title="Chat WhatsApp Tamu (+{{ $res->customer->phone }})">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        </a>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- Tombol Detil -->
                                        <button wire:click="openDetailModal({{ $res->id }})" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded text-[11px] font-semibold transition cursor-pointer" title="Detil Informasi Tamu & Reservasi">
                                            Detil
                                        </button>

                                        <!-- Tombol Check-In / Check-Out Cepat -->
                                        @can('checkin_checkout.manage')
                                            @if(in_array($res->status, ['confirmed', 'pending']))
                                                <button wire:click="checkIn({{ $res->id }})" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold shadow-2xs transition inline-flex items-center gap-1 cursor-pointer" title="Check-In Tamu">
                                                    <span>🛎️ Check-In</span>
                                                </button>
                                            @elseif($res->status === 'checked_in')
                                                <button wire:click="checkOut({{ $res->id }})" wire:confirm="Konfirmasi check-out untuk tamu {{ $res->customer->name }}? Kamar fisik akan otomatis ditandai kotor (dirty)." class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[11px] font-bold shadow-2xs transition inline-flex items-center gap-1 cursor-pointer" title="Check-Out Tamu">
                                                    <span>🚪 Check-Out</span>
                                                </button>
                                            @endif
                                        @endcan

                                        <!-- Tombol Invoice Dropdown (Struk & PDF) -->
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" @click.outside="open = false" type="button" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-[11px] font-semibold transition inline-flex items-center gap-1 cursor-pointer">
                                                <span>Invoice</span>
                                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" x-cloak class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-30 py-1 text-left">
                                                <a href="{{ route('invoice.thermal', $res->id) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100 transition">
                                                    <span>🖨️ Struk Thermal 80mm</span>
                                                </a>
                                                <a href="{{ route('invoice.pdf', $res->id) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100 transition">
                                                    <span>📄 Unduh Invoice PDF</span>
                                                </a>
                                            </div>
                                        </div>

                                        @can('reservation_payment.manage')
                                            @if($res->status !== 'cancelled')
                                                <button wire:click="openPaymentModal({{ $res->id }})" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-[11px] font-semibold transition" title="Catat Pembayaran">
                                                    💵 Bayar
                                                </button>
                                                
                                                <button wire:click="openAddonModal({{ $res->id }})" class="px-2 py-1 bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 rounded text-[11px] font-semibold transition" title="Tambah Layanan / Add-on">
                                                    + Layanan
                                                </button>
                                            @endif
                                        @endcan

                                        @can('reservations.manage')
                                            <button wire:click="openEditModal({{ $res->id }})" class="px-2 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded text-[11px] font-semibold transition" title="Edit Reservasi">
                                                ✏️ Edit
                                            </button>
                                        @endcan

                                        @can('reservation_payment.manage')
                                            @if($res->paid_amount > 0 && in_array($res->status, ['cancelled', 'pending', 'confirmed']))
                                                <button wire:click="openPaymentModal({{ $res->id }}, true)" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded text-[11px] font-semibold transition" title="Refund">
                                                    ↩️ Refund
                                                </button>
                                            @endif
                                        @endcan

                                        @can('reservations.manage')
                                            @if(!in_array($res->status, ['cancelled', 'checked_out']))
                                                <button wire:click="cancelReservation({{ $res->id }})" wire:confirm="Yakin ingin membatalkan reservasi ini?" class="text-[11px] text-red-600 hover:text-red-800 font-semibold ml-1 cursor-pointer">
                                                    Batalkan
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    Belum ada data reservasi yang sesuai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $reservations->links() }}
            </div>
        </div>

        <!-- ==================== MODAL TAMBAH RESERVASI BARU (LEBAR & RESPOCSIF) ==================== -->
        @if($showCreateModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full p-6 space-y-5 max-h-[92vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Input Reservasi Baru</h3>
                            <p class="text-xs text-gray-500">Isi formulir lengkap pemesanan kamar hotel</p>
                        </div>
                        <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">✕</button>
                    </div>

                    @error('general')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveReservation">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            
                            <!-- KOLOM KIRI (7 Kolom): Sumber, OTA, Identitas, Tamu, Addons -->
                            <div class="lg:col-span-7 space-y-4">
                                <!-- Sumber Reservasi -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Sumber Pemesanan</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        @foreach([
                                            'walk_in' => '🚶 Walk-In',
                                            'ota_manual' => '🌐 OTA Manual',
                                            'whatsapp_manual' => '💬 WhatsApp',
                                            'website' => '💻 Website'
                                        ] as $k => $lbl)
                                        <label class="border rounded-lg p-2 text-center text-xs font-semibold cursor-pointer transition {{ $source === $k ? 'border-emerald-600 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                                            <input type="radio" wire:model.live="source" value="{{ $k }}" class="sr-only">
                                            {{ $lbl }}
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Detail Khusus OTA Manual -->
                                @if($source === 'ota_manual')
                                    <div class="p-3.5 bg-blue-50/60 border border-blue-200 rounded-xl space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-blue-900">Detail Portal OTA (Online Travel Agent)</span>
                                            <span class="text-[10px] text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full font-medium">Bebas Pajak Internal</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Portal OTA</label>
                                                <select wire:model="ota_name" class="w-full text-xs border-gray-300 rounded-lg">
                                                    <option value="Traveloka">Traveloka</option>
                                                    <option value="Booking.com">Booking.com</option>
                                                    <option value="Agoda">Agoda</option>
                                                    <option value="Tiket.com">Tiket.com</option>
                                                    <option value="Trip.com">Trip.com</option>
                                                    <option value="Lainnya">Lainnya</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Kode Booking OTA</label>
                                                <input type="text" wire:model="ota_reference_number" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: TRV-882319">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2.5">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Harga Bruto (Rp)</label>
                                                <input type="number" wire:model.live.debounce.300ms="ota_gross_amount" class="w-full text-xs border-gray-300 rounded-lg" placeholder="0">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Komisi OTA (Rp)</label>
                                                <input type="number" wire:model.live.debounce.300ms="ota_commission" class="w-full text-xs border-gray-300 rounded-lg" placeholder="0">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Net Diharapkan (Rp)</label>
                                                <input type="number" wire:model="ota_net_expected" readonly class="w-full text-xs border-gray-300 rounded-lg bg-gray-100 font-bold text-emerald-800">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Identitas Tamu Lengkap -->
                                <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200 space-y-3">
                                    <span class="text-xs font-bold text-gray-800 block">Data Identitas Tamu</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Tamu <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="customer_name" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Nama lengkap tamu..." required>
                                            @error('customer_name') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">No. WhatsApp / HP <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="customer_phone" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="0812xxxx atau 62812xxxx" required>
                                            @error('customer_phone') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tanda Pengenal</label>
                                            <select wire:model="customer_identity_type" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                                <option value="KTP">KTP</option>
                                                <option value="SIM">SIM</option>
                                                <option value="Paspor">Paspor</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nomor Tanda Pengenal</label>
                                            <input type="text" wire:model="customer_identity_number" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Nomor NIK KTP / No SIM / No Paspor">
                                        </div>
                                    </div>
                                </div>

                                <!-- Jumlah Tamu Kompak dengan Ikon -->
                                <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                                    <label class="block text-xs font-bold text-gray-800 mb-2">Jumlah Tamu yang Menginap</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 text-center">
                                            <label class="text-[11px] font-semibold text-gray-600 flex items-center justify-center gap-1 mb-1">
                                                <span>🧑</span> <span>Dewasa</span>
                                            </label>
                                            <input type="number" wire:model.live="adults" min="1" max="10" class="w-full text-center text-xs font-bold border-gray-300 rounded-md">
                                        </div>
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 text-center">
                                            <label class="text-[11px] font-semibold text-gray-600 flex items-center justify-center gap-1 mb-1">
                                                <span>🧒</span> <span>Anak</span>
                                            </label>
                                            <input type="number" wire:model.live="children" min="0" max="10" class="w-full text-center text-xs font-bold border-gray-300 rounded-md">
                                        </div>
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 text-center">
                                            <label class="text-[11px] font-semibold text-gray-600 flex items-center justify-center gap-1 mb-1">
                                                <span>👶</span> <span>Bayi</span>
                                            </label>
                                            <input type="number" wire:model.live="infants" min="0" max="5" class="w-full text-center text-xs font-bold border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tanggal Menginap & Pilihan Kamar -->
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tanggal Check-In <span class="text-red-500">*</span></label>
                                            <input type="date" wire:model.live="check_in_date" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5 flex items-center justify-between">
                                                <span>Jam Check-In (Est)</span>
                                                <span class="text-[9px] text-gray-400 font-normal">Catatan FO</span>
                                            </label>
                                            <select wire:model="check_in_time" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                                <optgroup label="Pagi / Siang (Early Check-In)">
                                                    <option value="08:00">08:00 WIB</option>
                                                    <option value="09:00">09:00 WIB</option>
                                                    <option value="10:00">10:00 WIB</option>
                                                    <option value="11:00">11:00 WIB</option>
                                                    <option value="12:00">12:00 WIB</option>
                                                    <option value="13:00">13:00 WIB</option>
                                                </optgroup>
                                                <optgroup label="Standar Check-In">
                                                    <option value="14:00" selected>14:00 WIB (Standar)</option>
                                                    <option value="14:30">14:30 WIB</option>
                                                    <option value="15:00">15:00 WIB</option>
                                                    <option value="15:30">15:30 WIB</option>
                                                    <option value="16:00">16:00 WIB</option>
                                                    <option value="16:30">16:30 WIB</option>
                                                    <option value="17:00">17:00 WIB</option>
                                                    <option value="17:30">17:30 WIB</option>
                                                </optgroup>
                                                <optgroup label="Malam (Late Arrival)">
                                                    <option value="18:00">18:00 WIB</option>
                                                    <option value="18:30">18:30 WIB</option>
                                                    <option value="19:00">19:00 WIB</option>
                                                    <option value="19:30">19:30 WIB</option>
                                                    <option value="20:00">20:00 WIB</option>
                                                    <option value="20:30">20:30 WIB</option>
                                                    <option value="21:00">21:00 WIB</option>
                                                    <option value="21:30">21:30 WIB</option>
                                                    <option value="22:00">22:00 WIB (Late)</option>
                                                    <option value="22:30">22:30 WIB (Late)</option>
                                                    <option value="23:00">23:00 WIB (Late)</option>
                                                    <option value="23:30">23:30 WIB (Late)</option>
                                                    <option value="00:00">00:00 WIB (Tengah Malam)</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tanggal Check-Out <span class="text-red-500">*</span></label>
                                            <input type="date" wire:model.live="check_out_date" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" required>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tipe Kamar <span class="text-red-500">*</span></label>
                                            <select wire:model.live="room_type_id" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" required>
                                                @foreach($roomTypes as $t)
                                                    <option value="{{ $t->id }}">{{ $t->name }} (Standar: {{ rupiah($t->base_price) }})</option>
                                                @endforeach
                                            </select>
                                            <div class="text-[10px] text-gray-500 mt-1">
                                                Tersedia: <span class="font-bold text-emerald-700">{{ $availableCount }} Kamar</span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Alokasi Kamar Fisik (Opsional)</label>
                                            <select wire:model="room_id" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                                <option value="">-- Tetapkan Nanti Saat Check-In --</option>
                                                @foreach($availableRooms as $ar)
                                                    <option value="{{ $ar->id }}">Kamar {{ $ar->room_number }} ({{ $ar->floor ?? 'Lt.' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Layanan Ekstra (Add-on) Multi Dinamis -->
                                <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200 space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div>
                                            <label class="text-xs font-bold text-gray-800">Layanan Ekstra (Add-on)</label>
                                            <p class="text-[10px] text-gray-500">Bisa menambahkan lebih dari 1 layanan</p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" wire:click="addBookingAddonFromMaster" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-[11px] font-semibold transition flex items-center gap-1">
                                                <span>📦 + Dari Master</span>
                                            </button>
                                            <button type="button" wire:click="addBookingAddonManual" class="px-2.5 py-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded text-[11px] font-semibold transition flex items-center gap-1">
                                                <span>✏️ + Manual</span>
                                            </button>
                                        </div>
                                    </div>

                                    @if(empty($booking_addon_items))
                                        <div class="text-center py-3 bg-white rounded-lg border border-dashed border-gray-300 text-[11px] text-gray-400">
                                            Belum ada layanan ekstra dipilih. Klik tombol di atas untuk menambahkan.
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            @foreach($booking_addon_items as $idx => $addon)
                                                <div class="bg-white p-2.5 rounded-lg border border-gray-200 grid grid-cols-12 gap-2 items-center">
                                                    @if(isset($addon['additional_service_id']) && $addon['additional_service_id'])
                                                        <div class="col-span-12 sm:col-span-5">
                                                            <select wire:change="selectMasterAddon({{ $idx }}, $event.target.value)" class="w-full text-xs border-gray-300 rounded-md">
                                                                @foreach($additionalServices as $asvc)
                                                                    <option value="{{ $asvc->id }}" {{ $addon['additional_service_id'] == $asvc->id ? 'selected' : '' }}>
                                                                        {{ $asvc->name }} ({{ rupiah($asvc->default_price) }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @else
                                                        <div class="col-span-12 sm:col-span-5">
                                                            <input type="text" wire:model.live.debounce.300ms="booking_addon_items.{{ $idx }}.name" placeholder="Nama layanan manual..." class="w-full text-xs border-gray-300 rounded-md" required>
                                                        </div>
                                                    @endif

                                                    <div class="col-span-3 sm:col-span-2">
                                                        <input type="number" wire:model.live="booking_addon_items.{{ $idx }}.quantity" min="1" placeholder="Qty" class="w-full text-center text-xs border-gray-300 rounded-md">
                                                    </div>

                                                    <div class="col-span-5 sm:col-span-3">
                                                        <input type="number" wire:model.live.debounce.300ms="booking_addon_items.{{ $idx }}.unit_price" placeholder="Harga Satuan" class="w-full text-xs border-gray-300 rounded-md">
                                                    </div>

                                                    <div class="col-span-3 sm:col-span-1 text-right text-xs font-semibold text-gray-700">
                                                        {{ rupiah(($addon['quantity'] ?? 1) * ($addon['unit_price'] ?? 0)) }}
                                                    </div>

                                                    <div class="col-span-1 text-right">
                                                        <button type="button" wire:click="removeBookingAddon({{ $idx }})" class="text-red-500 hover:text-red-700 p-1" title="Hapus baris">
                                                            ✕
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <div class="text-right text-xs font-semibold text-gray-600 pt-1">
                                                Total Add-on: <span class="font-bold text-gray-900">{{ rupiah($booking_addons_total) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- KOLOM KANAN (5 Kolom): Tarif Edit, Pajak, Diskon, Rincian, DP, Submit -->
                            <div class="lg:col-span-5 space-y-4 bg-gray-50/70 p-4 rounded-xl border border-gray-200 flex flex-col justify-between">
                                <div class="space-y-4">
                                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-200 pb-2">
                                        Tarif & Rincian Pembayaran
                                    </h4>

                                    <!-- Tarif Kamar per Malam (Bisa Diedit Fleksibel) -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                                            Tarif Kamar per Malam (Rp)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-gray-500 font-bold">Rp</span>
                                            <input type="number" wire:model.live.debounce.300ms="custom_night_rate" class="w-full pl-9 text-xs font-bold text-gray-800 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Tarif malam...">
                                        </div>
                                        <p class="text-[10px] text-gray-500 mt-0.5">Dapat disesuaikan jika ada negosiasi / promo internal.</p>
                                    </div>

                                    <!-- Checkbox Kena Pajak Daerah (10%) -->
                                    <div class="p-2.5 bg-white rounded-lg border border-gray-200">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="apply_tax" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                            <div>
                                                <span class="text-xs font-bold text-gray-800 block">Kena Pajak Daerah (10% PB1)</span>
                                                <span class="text-[10px] text-gray-500 block">Hilangkan centang jika pemesanan bebas pajak (0%).</span>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Diskon & Promo -->
                                    <div class="grid grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Kode Promo</label>
                                            <input type="text" wire:model.live="promo_code" class="w-full text-xs border-gray-300 rounded-lg uppercase" placeholder="SYARIAH10">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Diskon Manual (Rp)</label>
                                            <input type="number" wire:model.live="manual_discount" class="w-full text-xs border-gray-300 rounded-lg" placeholder="0">
                                        </div>
                                    </div>

                                    <!-- Rincian Biaya Preview Real-Time -->
                                    @if(!empty($breakdown))
                                        <div class="bg-white rounded-xl p-3.5 border border-gray-200 text-xs space-y-2 shadow-sm">
                                            <div class="flex justify-between text-gray-600">
                                                <span>Durasi Menginap:</span>
                                                <span class="font-bold text-gray-900">{{ $breakdown['nights'] }} Malam</span>
                                            </div>
                                            <div class="flex justify-between text-gray-600">
                                                <span>Subtotal Kamar:</span>
                                                <span class="font-semibold text-gray-800">{{ rupiah($breakdown['subtotal']) }}</span>
                                            </div>
                                            @if($booking_addons_total > 0)
                                                <div class="flex justify-between text-blue-700">
                                                    <span>Layanan Ekstra (Add-on):</span>
                                                    <span class="font-semibold">{{ rupiah($booking_addons_total) }}</span>
                                                </div>
                                            @endif
                                            @if(($breakdown['discount_amount'] ?? 0) > 0)
                                                <div class="flex justify-between text-emerald-700 font-medium">
                                                    <span>Potongan Diskon:</span>
                                                    <span>-{{ rupiah($breakdown['discount_amount']) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between text-gray-600">
                                                <span>Pajak Daerah ({{ $apply_tax && $source !== 'ota_manual' ? '10%' : 'Bebas 0%' }}):</span>
                                                <span>{{ rupiah($breakdown['tax_amount'] ?? 0) }}</span>
                                            </div>
                                            <div class="flex justify-between pt-2.5 border-t border-gray-200 text-sm font-bold text-gray-900 items-baseline">
                                                <span>Total Tagihan:</span>
                                                <span class="text-emerald-700 text-lg font-black">{{ rupiah($breakdown['total_amount'] ?? 0) }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Uang Muka / DP Langsung -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Uang Muka / Pembayaran Langsung (Rp)</label>
                                        <input type="number" wire:model="initial_paid_amount" class="w-full text-xs font-bold text-emerald-800 border-gray-300 rounded-lg" placeholder="0">
                                    </div>

                                    <!-- Catatan Reservasi -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                                        <textarea wire:model="notes" rows="2" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Permintaan khusus tamu, e.g. non-smoking room..."></textarea>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-4">
                                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                                        Simpan Reservasi
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ==================== MODAL EDIT PESANAN / RESERVASI ==================== -->
        @if($showEditModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full p-6 space-y-5 max-h-[92vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">✏️ Koreksi / Edit Pesanan Reservasi</h3>
                            <p class="text-xs text-gray-500">Perbaiki kesalahan ketik nama, kontak, identitas, kamar, tanggal, atau tarif</p>
                        </div>
                        <button wire:click="$set('showEditModal', false)" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">✕</button>
                    </div>

                    @error('edit_general')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveEditedReservation">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            
                            <!-- KOLOM KIRI (7 Kolom): Sumber, OTA, Identitas, Tamu, Tanggal -->
                            <div class="lg:col-span-7 space-y-4">
                                <!-- Sumber Reservasi -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Sumber Pemesanan</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        @foreach([
                                            'walk_in' => '🚶 Walk-In',
                                            'ota_manual' => '🌐 OTA Manual',
                                            'whatsapp_manual' => '💬 WhatsApp',
                                            'website' => '💻 Website'
                                        ] as $k => $lbl)
                                        <label class="border rounded-lg p-2 text-center text-xs font-semibold cursor-pointer transition {{ $edit_source === $k ? 'border-purple-600 bg-purple-50 text-purple-800 ring-2 ring-purple-500/20' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                                            <input type="radio" wire:model.live="edit_source" value="{{ $k }}" class="sr-only">
                                            {{ $lbl }}
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Detail Khusus OTA Manual jika dipilih -->
                                @if($edit_source === 'ota_manual')
                                    <div class="p-3.5 bg-blue-50/60 border border-blue-200 rounded-xl space-y-3">
                                        <span class="text-xs font-bold text-blue-900 block">Detail Portal OTA:</span>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama OTA</label>
                                                <select wire:model="edit_ota_name" class="w-full text-xs border-gray-300 rounded-lg">
                                                    <option value="Traveloka">Traveloka</option>
                                                    <option value="Booking.com">Booking.com</option>
                                                    <option value="Agoda">Agoda</option>
                                                    <option value="Tiket.com">Tiket.com</option>
                                                    <option value="Trip.com">Trip.com</option>
                                                    <option value="Lainnya">Lainnya</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Kode Booking OTA</label>
                                                <input type="text" wire:model="edit_ota_reference_number" class="w-full text-xs border-gray-300 rounded-lg">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2.5">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Harga Bruto (Rp)</label>
                                                <input type="number" wire:model.live.debounce.300ms="edit_ota_gross_amount" class="w-full text-xs border-gray-300 rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Komisi OTA (Rp)</label>
                                                <input type="number" wire:model.live.debounce.300ms="edit_ota_commission" class="w-full text-xs border-gray-300 rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Net Diharapkan (Rp)</label>
                                                <input type="number" wire:model="edit_ota_net_expected" readonly class="w-full text-xs border-gray-300 rounded-lg bg-gray-100 font-bold text-emerald-800">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Identitas Tamu -->
                                <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200 space-y-3">
                                    <span class="text-xs font-bold text-gray-800 block">Data Identitas Tamu</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Tamu <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="edit_customer_name" class="w-full text-xs border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500" required>
                                            @error('edit_customer_name') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">No. WhatsApp <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="edit_customer_phone" class="w-full text-xs border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500" required>
                                            @error('edit_customer_phone') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tanda Pengenal</label>
                                            <select wire:model="edit_customer_identity_type" class="w-full text-xs border-gray-300 rounded-lg">
                                                <option value="KTP">KTP</option>
                                                <option value="SIM">SIM</option>
                                                <option value="Paspor">Paspor</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nomor Tanda Pengenal</label>
                                            <input type="text" wire:model="edit_customer_identity_number" class="w-full text-xs border-gray-300 rounded-lg" placeholder="NIK / No SIM / No Paspor">
                                        </div>
                                    </div>
                                </div>

                                <!-- Jumlah Tamu Ikonik -->
                                <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                                    <label class="block text-xs font-bold text-gray-800 mb-2">Jumlah Tamu yang Menginap</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 text-center">
                                            <label class="text-[11px] font-semibold text-gray-600 flex items-center justify-center gap-1 mb-1">
                                                <span>🧑</span> <span>Dewasa</span>
                                            </label>
                                            <input type="number" wire:model.live="edit_adults" min="1" max="10" class="w-full text-center text-xs font-bold border-gray-300 rounded-md">
                                        </div>
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 text-center">
                                            <label class="text-[11px] font-semibold text-gray-600 flex items-center justify-center gap-1 mb-1">
                                                <span>🧒</span> <span>Anak</span>
                                            </label>
                                            <input type="number" wire:model.live="edit_children" min="0" max="10" class="w-full text-center text-xs font-bold border-gray-300 rounded-md">
                                        </div>
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 text-center">
                                            <label class="text-[11px] font-semibold text-gray-600 flex items-center justify-center gap-1 mb-1">
                                                <span>👶</span> <span>Bayi</span>
                                            </label>
                                            <input type="number" wire:model.live="edit_infants" min="0" max="5" class="w-full text-center text-xs font-bold border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>

                                <!-- Periode & Kamar -->
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tanggal Check-In <span class="text-red-500">*</span></label>
                                            <input type="date" wire:model.live="edit_check_in_date" class="w-full text-xs border-gray-300 rounded-lg" required>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5 flex items-center justify-between">
                                                <span>Jam Check-In (Est)</span>
                                                <span class="text-[9px] text-gray-400 font-normal">Catatan FO</span>
                                            </label>
                                            <select wire:model="edit_check_in_time" class="w-full text-xs border-gray-300 rounded-lg">
                                                <optgroup label="Pagi / Siang (Early Check-In)">
                                                    <option value="08:00">08:00 WIB</option>
                                                    <option value="09:00">09:00 WIB</option>
                                                    <option value="10:00">10:00 WIB</option>
                                                    <option value="11:00">11:00 WIB</option>
                                                    <option value="12:00">12:00 WIB</option>
                                                    <option value="13:00">13:00 WIB</option>
                                                </optgroup>
                                                <optgroup label="Standar Check-In">
                                                    <option value="14:00">14:00 WIB (Standar)</option>
                                                    <option value="14:30">14:30 WIB</option>
                                                    <option value="15:00">15:00 WIB</option>
                                                    <option value="15:30">15:30 WIB</option>
                                                    <option value="16:00">16:00 WIB</option>
                                                    <option value="16:30">16:30 WIB</option>
                                                    <option value="17:00">17:00 WIB</option>
                                                    <option value="17:30">17:30 WIB</option>
                                                </optgroup>
                                                <optgroup label="Malam (Late Arrival)">
                                                    <option value="18:00">18:00 WIB</option>
                                                    <option value="18:30">18:30 WIB</option>
                                                    <option value="19:00">19:00 WIB</option>
                                                    <option value="19:30">19:30 WIB</option>
                                                    <option value="20:00">20:00 WIB</option>
                                                    <option value="20:30">20:30 WIB</option>
                                                    <option value="21:00">21:00 WIB</option>
                                                    <option value="21:30">21:30 WIB</option>
                                                    <option value="22:00">22:00 WIB (Late)</option>
                                                    <option value="22:30">22:30 WIB (Late)</option>
                                                    <option value="23:00">23:00 WIB (Late)</option>
                                                    <option value="23:30">23:30 WIB (Late)</option>
                                                    <option value="00:00">00:00 WIB (Tengah Malam)</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tanggal Check-Out <span class="text-red-500">*</span></label>
                                            <input type="date" wire:model.live="edit_check_out_date" class="w-full text-xs border-gray-300 rounded-lg" required>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tipe Kamar <span class="text-red-500">*</span></label>
                                            <select wire:model.live="edit_room_type_id" class="w-full text-xs border-gray-300 rounded-lg" required>
                                                @foreach($roomTypes as $t)
                                                    <option value="{{ $t->id }}">{{ $t->name }} (Standar: {{ rupiah($t->base_price) }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Kamar Fisik (Opsional)</label>
                                            <select wire:model="edit_room_id" class="w-full text-xs border-gray-300 rounded-lg">
                                                <option value="">-- Tanpa Alokasi Nomor Kamar --</option>
                                                @foreach($edit_availableRooms as $ar)
                                                    <option value="{{ $ar->id }}">Kamar {{ $ar->room_number }} ({{ $ar->floor ?? 'Lt.' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KOLOM KANAN (5 Kolom): Tarif Edit, Pajak, Diskon, Rincian, Simpan -->
                            <div class="lg:col-span-5 space-y-4 bg-gray-50/70 p-4 rounded-xl border border-gray-200 flex flex-col justify-between">
                                <div class="space-y-4">
                                    <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-200 pb-2">
                                        Perhitungan & Tarif
                                    </h4>

                                    <!-- Tarif Kamar per Malam -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tarif Kamar per Malam (Rp)</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-gray-500 font-bold">Rp</span>
                                            <input type="number" wire:model.live.debounce.300ms="edit_custom_night_rate" class="w-full pl-9 text-xs font-bold text-gray-800 border-gray-300 rounded-lg">
                                        </div>
                                    </div>

                                    <!-- Checkbox Kena Pajak Daerah (10%) -->
                                    <div class="p-2.5 bg-white rounded-lg border border-gray-200">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="edit_apply_tax" class="rounded text-purple-600 focus:ring-purple-500 h-4 w-4">
                                            <div>
                                                <span class="text-xs font-bold text-gray-800 block">Kena Pajak Daerah (10% PB1)</span>
                                                <span class="text-[10px] text-gray-500 block">Hilangkan centang jika bebas pajak (0%).</span>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Diskon Manual -->
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Diskon Manual (Rp)</label>
                                        <input type="number" wire:model.live="edit_manual_discount" class="w-full text-xs border-gray-300 rounded-lg" placeholder="0">
                                    </div>

                                    <!-- Rincian Biaya Preview Real-Time -->
                                    @if(!empty($edit_breakdown))
                                        <div class="bg-white rounded-xl p-3.5 border border-gray-200 text-xs space-y-2 shadow-sm">
                                            <div class="flex justify-between text-gray-600">
                                                <span>Durasi Menginap:</span>
                                                <span class="font-bold text-gray-900">{{ $edit_breakdown['nights'] }} Malam</span>
                                            </div>
                                            <div class="flex justify-between text-gray-600">
                                                <span>Subtotal Kamar:</span>
                                                <span class="font-semibold text-gray-800">{{ rupiah($edit_breakdown['subtotal']) }}</span>
                                            </div>
                                            @if(($edit_breakdown['additional_charges'] ?? 0) > 0)
                                                <div class="flex justify-between text-blue-700">
                                                    <span>Layanan Tambahan Terkait:</span>
                                                    <span class="font-semibold">{{ rupiah($edit_breakdown['additional_charges']) }}</span>
                                                </div>
                                            @endif
                                            @if(($edit_breakdown['discount_amount'] ?? 0) > 0)
                                                <div class="flex justify-between text-emerald-700 font-medium">
                                                    <span>Potongan Diskon:</span>
                                                    <span>-{{ rupiah($edit_breakdown['discount_amount']) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between text-gray-600">
                                                <span>Pajak Daerah ({{ $edit_apply_tax && $edit_source !== 'ota_manual' ? '10%' : 'Bebas 0%' }}):</span>
                                                <span>{{ rupiah($edit_breakdown['tax_amount'] ?? 0) }}</span>
                                            </div>
                                            <div class="flex justify-between pt-2.5 border-t border-gray-200 text-sm font-bold text-gray-900 items-baseline">
                                                <span>Total Tagihan Baru:</span>
                                                <span class="text-purple-700 text-lg font-black">{{ rupiah($edit_breakdown['total_amount'] ?? 0) }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Catatan -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Reservasi</label>
                                        <textarea wire:model="edit_notes" rows="2" class="w-full text-xs border-gray-300 rounded-lg"></textarea>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-4">
                                    <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow-sm transition">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ==================== MODAL MASTER LAYANAN TAMBAHAN (ADDON) ==================== -->
        @if($showMasterAddonModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full p-6 space-y-5 max-h-[92vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">⚙️ Master Layanan Tambahan (Add-on)</h3>
                            <p class="text-xs text-gray-500">Kelola daftar layanan ekstra hotel (Extra Bed, Breakfast, Shuttle, Laundry, dsb)</p>
                        </div>
                        <button wire:click="$set('showMasterAddonModal', false)" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">✕</button>
                    </div>

                    @if(session()->has('addon_modal_message'))
                        <div class="p-3 bg-emerald-50 text-emerald-800 text-xs rounded-lg border border-emerald-200">
                            {{ session('addon_modal_message') }}
                        </div>
                    @endif

                    <!-- Form Tambah / Edit Master Addon -->
                    <form wire:submit="saveMasterAddon" class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-800">
                                {{ $master_addon_id ? '✏️ Edit Layanan #' . $master_addon_id : '➕ Tambah Layanan Baru' }}
                            </span>
                            @if($master_addon_id)
                                <button type="button" wire:click="resetMasterAddonForm" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                    Batal Edit
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Layanan <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="master_addon_name" placeholder="Contoh: Extra Bed / Sarapan Pagi" class="w-full text-xs border-gray-300 rounded-lg" required>
                                @error('master_addon_name') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Kategori</label>
                                <select wire:model="master_addon_category" class="w-full text-xs border-gray-300 rounded-lg">
                                    <option value="amenities">Fasilitas / Amenities (Extra Bed, Pillow, dll)</option>
                                    <option value="fnb">Makanan & Minuman (F&B / Breakfast)</option>
                                    <option value="laundry">Laundry / Cuci</option>
                                    <option value="late_checkout">Late Check-out / Durasi</option>
                                    <option value="transportation">Transportasi / Shuttle</option>
                                    <option value="others">Lain-lain</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tarif Standar (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="master_addon_price" min="0" placeholder="0" class="w-full text-xs border-gray-300 rounded-lg font-bold" required>
                                @error('master_addon_price') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Satuan <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="master_addon_unit" placeholder="pcs, porsi, kg, jam, trip" class="w-full text-xs border-gray-300 rounded-lg" required>
                                @error('master_addon_unit') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex items-center pt-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="master_addon_active" class="rounded text-emerald-600 h-4 w-4">
                                    <span class="text-xs font-semibold text-gray-700">Aktif Digunakan</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                {{ $master_addon_id ? 'Perbarui Layanan' : 'Simpan ke Master' }}
                            </button>
                        </div>
                    </form>

                    <!-- Navigasi Tab Aktif vs Arsip -->
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                        <div class="flex gap-2">
                            <button wire:click="$set('master_addon_tab', 'active')" class="px-3 py-1 text-xs font-bold rounded-lg transition {{ $master_addon_tab === 'active' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                Layanan Aktif ({{ $activeAddonCount }})
                            </button>
                            <button wire:click="$set('master_addon_tab', 'trash')" class="px-3 py-1 text-xs font-bold rounded-lg transition {{ $master_addon_tab === 'trash' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                🗑️ Arsip / Dihapus ({{ $trashedAddonCount }})
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Master Addon -->
                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                            <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Nama Layanan</th>
                                    <th class="py-2.5 px-3">Kategori</th>
                                    <th class="py-2.5 px-3">Tarif Standar</th>
                                    <th class="py-2.5 px-3">Satuan</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($masterAddonList as $addon)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-2.5 px-3 font-semibold text-gray-900">
                                            {{ $addon->name }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="text-[10px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-medium">
                                                {{ ucfirst($addon->category ?? 'amenities') }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 font-bold text-emerald-700">
                                            {{ rupiah($addon->default_price) }}
                                        </td>
                                        <td class="py-2.5 px-3 text-gray-600">
                                            / {{ $addon->unit }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            @if(!$addon->trashed())
                                                <button wire:click="toggleMasterAddonStatus({{ $addon->id }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold transition {{ $addon->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                                                    {{ $addon->is_active ? '✓ Aktif' : 'Off' }}
                                                </button>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    Diarsipkan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if(!$addon->trashed())
                                                    <button wire:click="editMasterAddon({{ $addon->id }})" class="px-2 py-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded text-[10px] font-semibold transition">
                                                        Edit
                                                    </button>
                                                    <button wire:click="deleteMasterAddon({{ $addon->id }})" wire:confirm="Pindahkan layanan ini ke arsip?" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded text-[10px] font-semibold transition">
                                                        Hapus
                                                    </button>
                                                @else
                                                    <button wire:click="restoreMasterAddon({{ $addon->id }})" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded text-[10px] font-semibold transition">
                                                        Pulihkan
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-gray-400">
                                            {{ $master_addon_tab === 'trash' ? 'Tidak ada layanan di arsip.' : 'Belum ada data layanan master.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" wire:click="$set('showMasterAddonModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Catat Pembayaran / Refund -->
        @if($showPaymentModal && $selectedReservation)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">
                            {{ $is_refund_mode ? '↩️ Proses Refund / Pengembalian Dana' : '💵 Catat Pembayaran / Kwitansi' }}
                        </h3>
                        <button wire:click="$set('showPaymentModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <!-- Ringkasan Reservasi -->
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Reservasi:</span>
                            <span class="font-bold text-gray-800">#{{ $selectedReservation->reservation_number }} ({{ $selectedReservation->customer->name }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Tagihan:</span>
                            <span class="font-semibold text-gray-800">{{ rupiah($selectedReservation->total_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Telah Dibayar:</span>
                            <span class="font-bold text-emerald-700">{{ rupiah($selectedReservation->paid_amount) }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-gray-200 font-bold">
                            <span class="text-gray-700">Sisa Tagihan:</span>
                            <span class="{{ $selectedReservation->balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">{{ rupiah($selectedReservation->balance) }}</span>
                        </div>
                    </div>

                    @error('payment')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="savePayment" class="space-y-4">
                        @if(!$is_refund_mode)
                            <!-- Tombol Cepat Nominal -->
                            <div class="flex gap-2">
                                <button type="button" wire:click="setQuickPayment('full')" class="flex-1 py-1.5 px-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-xs font-semibold transition">
                                    Bayar Lunas ({{ rupiah($selectedReservation->balance) }})
                                </button>
                                <button type="button" wire:click="setQuickPayment('half')" class="flex-1 py-1.5 px-2 bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-300 rounded text-xs font-semibold transition">
                                    DP 50%
                                </button>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Pembayaran (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="payment_amount" class="w-full text-sm font-bold border-gray-300 rounded-lg text-emerald-800 focus:ring-emerald-500 focus:border-emerald-500" required>
                                @error('payment_amount') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pembayaran (Verifikasi Manual)</label>
                                <select wire:model="payment_method" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="cash">Tunai / Cash (Front Desk)</option>
                                    <option value="bank_transfer">Transfer Bank (BCA / Mandiri / BSI)</option>
                                    <option value="qris_manual">QRIS Statis (Verifikasi Manual Staff)</option>
                                    <option value="card_manual">Kartu Debit / Kredit (Mesin EDC)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Bank / Rekening</label>
                                    <input type="text" wire:model="bank_account" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: BSI / BCA">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">No. Bukti / Ref / Slip</label>
                                    <input type="text" wire:model="payment_reference" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: TRF-90218">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                                <input type="text" wire:model="payment_notes" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: Pelunasan saat check-in">
                            </div>
                        @else
                            <!-- Mode Refund -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Refund (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" wire:model="payment_amount" class="w-full text-sm font-bold border-gray-300 rounded-lg text-amber-800" max="{{ $selectedReservation->paid_amount }}" required>
                                <div class="text-[11px] text-gray-500 mt-1">Maksimal pengembalian: {{ rupiah($selectedReservation->paid_amount) }}</div>
                                @error('payment_amount') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Pengembalian Dana <span class="text-red-500">*</span></label>
                                <textarea wire:model="refund_reason" rows="2" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: Pembatalan karena kendala darurat tamu..." required></textarea>
                                @error('refund_reason') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" wire:click="$set('showPaymentModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white {{ $is_refund_mode ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} rounded-lg shadow-sm">
                                {{ $is_refund_mode ? 'Konfirmasi Refund' : 'Simpan Pembayaran' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Tambah Layanan Add-on untuk Reservasi Tertentu -->
        @if($showAddonModal && $selectedReservation)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">➕ Tambah Layanan Ekstra (Add-on)</h3>
                        <button wire:click="$set('showAddonModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <div class="text-xs text-gray-600">
                        Menambahkan tagihan layanan untuk reservasi <strong class="text-gray-800">#{{ $selectedReservation->reservation_number }}</strong> ({{ $selectedReservation->customer->name }}).
                    </div>

                    @if($selectedReservation->serviceItems->isNotEmpty())
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                            <div class="font-bold text-gray-700 mb-1.5">Layanan Tambahan Sebelumnya:</div>
                            <ul class="space-y-1">
                                @foreach($selectedReservation->serviceItems as $it)
                                    <li class="flex justify-between text-gray-600">
                                        <span>• {{ $it->item_name }} ({{ $it->quantity }}x)</span>
                                        <span class="font-medium text-gray-800">{{ rupiah($it->subtotal) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @error('addon')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveAddon" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih dari Katalog Cepat</label>
                            <select wire:model.live="addon_service_id" class="w-full text-xs border-gray-300 rounded-lg">
                                <option value="">-- Layanan Kustom / Ketik Manual --</option>
                                @foreach($additionalServices as $asvc)
                                    <option value="{{ $asvc->id }}">{{ $asvc->name }} - {{ rupiah($asvc->default_price) }} / {{ $asvc->unit }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Layanan / Barang <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="addon_item_name" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: Extra Bed / Laundry Express" required>
                            @error('addon_item_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Kuantitas (Qty) <span class="text-red-500">*</span></label>
                                <input type="number" wire:model.live="addon_quantity" min="1" class="w-full text-xs border-gray-300 rounded-lg" required>
                                @error('addon_quantity') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" wire:model.live="addon_unit_price" class="w-full text-xs border-gray-300 rounded-lg" required>
                                @error('addon_unit_price') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Subtotal Preview -->
                        <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-lg flex justify-between items-center text-xs">
                            <span class="font-medium text-blue-900">Total Tambahan (+Pajak 10%):</span>
                            <span class="font-bold text-blue-900 text-sm">{{ rupiah($addon_quantity * $addon_unit_price * 1.1) }}</span>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" wire:click="$set('showAddonModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">
                                Tambahkan Layanan
                            </button>
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
                            <button type="button" wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer">
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
                                            <span>• {{ $svc->item_name }} (x{{ $svc->quantity }}):</span>
                                            <span class="font-medium">{{ rupiah($svc->subtotal) }}</span>
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
                            <button type="button" wire:click="closeDetailModal" class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <!-- ==================== MODAL PILIH KAMAR & QUICK CHECK-IN ==================== -->
        @if($showQuickCheckInModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">🛎️ Alokasi Kamar & Check-In</h3>
                            <p class="text-xs text-gray-500">Tentukan nomor kamar fisik yang bersih untuk tamu</p>
                        </div>
                        <button wire:click="closeQuickCheckInModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">✕</button>
                    </div>

                    @error('quick_check_in')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    @if(empty($quickCheckInAvailableRooms))
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-1">
                            <p class="font-bold">⚠️ Tidak Ada Kamar Bersih yang Tersedia</p>
                            <p>Semua kamar fisik untuk tipe ini sedang terisi atau belum dibersihkan. Silakan bersihkan kamar terlebih dahulu atau lakukan upgrade tipe kamar.</p>
                        </div>
                        <div class="flex justify-end pt-3 border-t">
                            <button type="button" wire:click="closeQuickCheckInModal" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Tutup
                            </button>
                        </div>
                    @else
                        <form wire:submit="confirmQuickCheckIn" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pilih Kamar Fisik yang Siap Pakai (Clean/Inspected):</label>
                                <select wire:model="quickCheckInRoomId" class="w-full text-xs font-bold border-gray-300 rounded-lg text-emerald-800 focus:ring-emerald-500 focus:border-emerald-500" required>
                                    @foreach($quickCheckInAvailableRooms as $r)
                                        <option value="{{ $r->id }}">
                                            Kamar {{ $r->room_number }} ({{ $r->floor ?? 'Lantai 1' }}) — Status: {{ ucfirst($r->cleaning_status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('quickCheckInRoomId') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-3 border-t">
                                <button type="button" wire:click="closeQuickCheckInModal" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                                    Konfirmasi & Check-In
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
