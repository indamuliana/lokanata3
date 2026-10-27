<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Ruang Meeting</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola reservasi ruang rapat, ballroom, paket konsumsi & invoice acara</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button wire:click="openSettingsModal" class="px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-xs sm:text-sm font-semibold rounded-lg shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Kelola Tarif & Paket Ruangan</span>
                </button>
                <button wire:click="openCreateModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Booking Ruang Meeting</span>
                </button>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('message') }}
            </div>
        @endif

        <!-- Tab Switcher Utama: Acara Aktif & Mendatang vs Riwayat Acara Selesai -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 pb-3">
            <div class="flex items-center gap-2">
                <button wire:click="$set('viewTab', 'active')" class="px-4 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2 {{ $viewTab === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>🏢 Acara Aktif & Mendatang</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $viewTab === 'active' ? 'bg-emerald-700 text-white' : 'bg-gray-200 text-gray-700' }} font-mono">{{ $activeCount }}</span>
                </button>
                <button wire:click="$set('viewTab', 'history')" class="px-4 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2 {{ $viewTab === 'history' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>📁 Riwayat Acara Selesai</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $viewTab === 'history' ? 'bg-indigo-700 text-white' : 'bg-gray-200 text-gray-700' }} font-mono">{{ $historyCount }}</span>
                </button>
            </div>

            @if($viewTab === 'history')
                <!-- Tombol Cepat Ekspor Excel & PDF untuk Riwayat Ruang Meeting -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.export.csv', ['unit' => 'meeting', 'history_only' => 1]) }}" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-semibold shadow-2xs inline-flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Ekspor Excel</span>
                    </a>
                    <a href="{{ route('reports.export.pdf', ['unit' => 'meeting', 'history_only' => 1]) }}" target="_blank" class="px-3.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-800 border border-red-300 rounded-lg text-xs font-semibold shadow-2xs inline-flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Ekspor PDF</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari acara, no. booking, instansi..." class="text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-64">
                <input type="date" wire:model.live="dateFilter" class="text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Status:</span>
                @foreach([
                    'all' => 'Semua',
                    'confirmed' => 'Terkonfirmasi',
                    'in_progress' => 'Sedang Berlangsung',
                    'completed' => 'Selesai',
                    'cancelled' => 'Batal'
                ] as $val => $label)
                    <button wire:click="$set('statusFilter', '{{ $val }}')" class="px-2.5 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $statusFilter === $val ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Table Meeting Reservations -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">No. Booking</th>
                            <th class="py-3 px-4">Nama Acara & Pemesan</th>
                            <th class="py-3 px-4">Ruangan</th>
                            <th class="py-3 px-4">Waktu Pelaksanaan</th>
                            <th class="py-3 px-4">Peserta</th>
                            <th class="py-3 px-4">Total & Bayar</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reservations as $res)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-gray-900">
                                    {{ $res->reservation_number }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900">{{ $res->event_name }}</div>
                                    <div class="text-[11px] text-gray-500">
                                        {{ $res->customer->name }}
                                        @if($res->customer->company)
                                            · <span class="font-medium text-emerald-800">{{ $res->customer->company }}</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-gray-400">+{{ $res->customer->phone }}</div>
                                    @if($res->facility_notes)
                                        <div class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-100 font-medium" title="Fasilitas: {{ $res->facility_notes }}">
                                            <span>🛠️</span>
                                            <span>{{ \Illuminate\Support\Str::limit($res->facility_notes, 30) }}</span>
                                        </div>
                                    @endif
                                    @if($res->items->count() > 0)
                                        <div class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-amber-50 text-amber-800 border border-amber-200 font-medium">
                                            <span>📦</span>
                                            <span>{{ $res->items->count() }} Layanan Tambahan</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-gray-800">{{ $res->meetingRoom->name }}</span>
                                    <div class="text-[10px] text-gray-500">
                                        {{ ucfirst(str_replace('_', ' ', $res->pricing_type)) }}
                                        @if($res->meetingPackage)
                                            · {{ $res->meetingPackage->name }}
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-900">{{ $res->event_date->format('d M Y') }}</div>
                                    <div class="text-[11px] text-emerald-700 font-semibold">{{ $res->start_time }} - {{ $res->end_time }} WIB</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-bold text-[11px]">
                                        {{ $res->pax }} Orang
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-emerald-700">{{ rupiah($res->total_amount) }}</div>
                                    <div class="text-[11px] {{ $res->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ ucfirst(str_replace('_', ' ', $res->payment_status)) }} (Lunas: {{ rupiah($res->paid_amount) }})
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $res->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $res->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $res->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $res->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- Rincian & Tambah Layanan Susulan -->
                                        <button wire:click="openDetailModal({{ $res->id }})" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded text-[11px] font-semibold transition" title="Lihat Rincian & Tambah Layanan">
                                            🔍 Rincian
                                        </button>

                                        @if($res->status !== 'cancelled')
                                            <button wire:click="openPaymentModal({{ $res->id }})" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-[11px] font-semibold transition" title="Catat Pembayaran">
                                                💵 Bayar
                                            </button>
                                        @endif

                                        <!-- Cetak Struk 80mm -->
                                        <a href="{{ route('meeting.thermal', $res->id) }}" target="_blank" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-[11px] font-semibold transition inline-flex items-center gap-1" title="Cetak Struk Thermal 80mm">
                                            🖨️ Struk
                                        </a>

                                        <!-- Cetak PDF -->
                                        <a href="{{ route('meeting.pdf', $res->id) }}" target="_blank" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-[11px] font-semibold transition inline-flex items-center gap-1" title="Unduh Invoice PDF">
                                            📄 PDF
                                        </a>

                                        @if($res->status !== 'cancelled')
                                            <button wire:click="cancelReservation({{ $res->id }})" wire:confirm="Batalkan pesanan meeting ini?" class="text-[11px] text-red-600 hover:text-red-800 font-semibold ml-1">
                                                Batalkan
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">
                                    Belum ada data reservasi ruang meeting yang sesuai.
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

        <!-- Modal Booking Ruang Meeting -->
        @if($showCreateModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-lg font-bold text-gray-900">Booking Ruang Meeting & Acara</h3>
                        <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    @error('general')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Availability Alert Banner -->
                    @if(!$isAvailable)
                        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-800 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Jadwal Bentrok! Ruangan ini sudah terisi untuk rentang jam yang dipilih pada tanggal tersebut.</span>
                        </div>
                    @else
                        <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Jadwal Tersedia & Siap Dipesan</span>
                        </div>
                    @endif

                    <form wire:submit="saveReservation" class="space-y-4">
                        <!-- Acara & Ruangan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Acara / Kegiatan <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="event_name" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: Raker Internal BAZNAS" required>
                                @error('event_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Ruangan <span class="text-red-500">*</span></label>
                                <select wire:model.live="meeting_room_id" class="w-full text-xs border-gray-300 rounded-lg" required>
                                    @foreach($meetingRooms as $mr)
                                        <option value="{{ $mr->id }}">{{ $mr->name }} (Maks {{ $mr->capacity }} Pax)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tanggal & Jam -->
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Acara <span class="text-red-500">*</span></label>
                                <input type="date" wire:model.live="event_date" class="w-full text-xs border-gray-300 rounded-lg" required>
                                @if(!empty($breakdown['is_high_season']))
                                    <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        🔥 High Season ({{ $breakdown['season_label'] ?? 'Akhir Pekan' }})
                                    </span>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                                <input type="time" wire:model.live="start_time" class="w-full text-xs border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                                <input type="time" wire:model.live="end_time" class="w-full text-xs border-gray-300 rounded-lg" required>
                            </div>
                        </div>

                        <!-- Peserta & Skema Harga -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Peserta (Pax) <span class="text-red-500">*</span></label>
                                <input type="number" wire:model.live.debounce.400ms="pax" min="1" class="w-full text-xs border-gray-300 rounded-lg" required>
                                @error('pax') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Skema Penetapan Harga <span class="text-red-500">*</span></label>
                                <select wire:model.live="pricing_type" class="w-full text-xs border-gray-300 rounded-lg" required>
                                    <option value="all_in">Paket Sewa All-In Flat</option>
                                    <option value="per_pax">Tarif per Orang (Per Pax)</option>
                                    <option value="hourly">Tarif per Jam (Hourly)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pilihan Paket jika Per Pax -->
                        @if($pricing_type === 'per_pax')
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Paket Konsumsi / Meeting</label>
                                <select wire:model.live="meeting_package_id" class="w-full text-xs border-gray-300 rounded-lg">
                                    <option value="">-- Tarif Standar (Rp 75.000/pax) --</option>
                                    @foreach($meetingPackages as $pkg)
                                        <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ rupiah($pkg->price_per_pax) }}/pax)</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Informasi Pemesan -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Kontak / PIC <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="customer_name" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Nama PIC..." required>
                                @error('customer_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="customer_phone" class="w-full text-xs border-gray-300 rounded-lg" placeholder="0812xxxx" required>
                                @error('customer_phone') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Instansi / Perusahaan</label>
                                <input type="text" wire:model="customer_company" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: PT Garut Makmur">
                            </div>
                        </div>

                        <!-- Keterangan Setup Fasilitas Ruangan -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">
                                🛠️ Keterangan Kebutuhan Fasilitas & Tata Letak Ruang
                            </label>
                            <textarea wire:model="facility_notes" rows="2" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: Perlu meja saja tanpa kursi (atau formasi U-Shape), minta 5 titik terminal colokan listrik per meja, AC dinyalakan 30 menit sebelum acara..."></textarea>
                        </div>

                        <!-- Layanan Tambahan Meeting Room -->
                        <div class="border border-gray-200 rounded-xl p-3.5 bg-gray-50/70 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>📦</span> Layanan Tambahan Meeting (Opsional)
                                    </h4>
                                    <p class="text-[11px] text-gray-500">Pilih dari katalog master atau tambah layanan manual/kustom. Harga satuan bisa Rp 0.</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" wire:click="addServiceRowFromMaster" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-2xs flex items-center gap-1 transition" title="Tambah item dari katalog master layanan">
                                        <span>📦</span> + Dari Master
                                    </button>
                                    <button type="button" wire:click="addServiceRowManual" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-2xs flex items-center gap-1 transition" title="Tambah item manual / kustom baru">
                                        <span>✏️</span> + Input Manual
                                    </button>
                                </div>
                            </div>

                            @if(empty($service_items))
                                <div class="text-center py-3 text-xs text-gray-400 border border-dashed border-gray-200 rounded-lg bg-white">
                                    Belum ada layanan tambahan dipilih. Klik <strong>+ Dari Master</strong> untuk pilih dari katalog atau <strong>+ Input Manual</strong> untuk layanan kustom.
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($service_items as $idx => $item)
                                        <div class="grid grid-cols-12 gap-2 items-center bg-white p-2.5 rounded-lg border border-gray-200 text-xs shadow-2xs" wire:key="service-item-{{ $idx }}">
                                            @if(($item['type'] ?? 'master') === 'manual')
                                                <!-- MODE MANUAL -->
                                                <div class="col-span-12 sm:col-span-5 space-y-1">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">✏️ Manual / Kustom</span>
                                                        <span class="text-[10px] text-gray-400">Tidak tersimpan di master</span>
                                                    </div>
                                                    <input type="text" wire:model.blur="service_items.{{ $idx }}.name" wire:change="updateServiceItem({{ $idx }})" class="w-full text-xs border-indigo-200 focus:border-indigo-500 rounded-lg py-1 px-2 font-medium text-gray-800" placeholder="Ketik nama layanan kustom (misal: Cetak Banner 3x1, Sewa Sound)..." required>
                                                </div>
                                            @else
                                                <!-- MODE MASTER -->
                                                <div class="col-span-12 sm:col-span-5 space-y-1">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">📦 Katalog Master</span>
                                                    </div>
                                                    <select wire:change="selectMasterService({{ $idx }}, $event.target.value)" class="w-full text-xs border-emerald-300 rounded-lg py-1 px-2 bg-emerald-50/40">
                                                        <option value="">-- Pilih dari Master Layanan --</option>
                                                        @foreach($availableServices as $asvc)
                                                            <option value="{{ $asvc->id }}" @selected(($item['meeting_service_id'] ?? null) == $asvc->id)>
                                                                {{ $asvc->name }} ({{ rupiah($asvc->default_price) }}/{{ $asvc->unit }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            <!-- Qty -->
                                            <div class="col-span-4 sm:col-span-2">
                                                <label class="block text-[10px] text-gray-500 font-semibold mb-0.5">Qty</label>
                                                <input type="number" wire:model.blur="service_items.{{ $idx }}.quantity" wire:change="updateServiceItem({{ $idx }})" min="1" class="w-full text-xs border-gray-300 rounded-lg py-1 px-2 text-center" required>
                                            </div>

                                            <!-- Harga Satuan -->
                                            <div class="col-span-4 sm:col-span-2">
                                                <label class="block text-[10px] text-gray-500 font-semibold mb-0.5">Tarif Satuan (Rp)</label>
                                                <input type="number" wire:model.blur="service_items.{{ $idx }}.unit_price" wire:change="updateServiceItem({{ $idx }})" min="0" class="w-full text-xs border-gray-300 rounded-lg py-1 px-2" placeholder="0">
                                            </div>

                                            <!-- Subtotal & Hapus -->
                                            <div class="col-span-4 sm:col-span-3 flex items-center justify-between gap-1 pt-3 sm:pt-0">
                                                <div class="text-right flex-1">
                                                    <div class="text-[10px] text-gray-400">Subtotal</div>
                                                    <div class="font-bold text-emerald-800">{{ rupiah(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)) }}</div>
                                                </div>
                                                <button type="button" wire:click="removeServiceRow({{ $idx }})" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded" title="Hapus baris">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Catatan Tambahan -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan Reservasi (Opsional)</label>
                            <input type="text" wire:model="notes" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Catatan internal kasir / faktur...">
                        </div>

                        <!-- Breakdown Preview -->
                        @if(!empty($breakdown))
                            <div class="bg-gray-50 rounded-xl p-3 border border-gray-200 text-xs space-y-1">
                                <div class="flex justify-between text-gray-600">
                                    <span>Durasi Waktu:</span>
                                    <span class="font-bold text-gray-800">{{ $breakdown['duration_hours'] }} Jam</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal Sewa/Paket:</span>
                                    <span class="font-semibold text-gray-800">{{ rupiah($breakdown['subtotal']) }}</span>
                                </div>
                                @if(!empty($breakdown['additional_charges']) && $breakdown['additional_charges'] > 0)
                                    <div class="flex justify-between text-emerald-700 font-semibold">
                                        <span>Layanan Tambahan (Flat):</span>
                                        <span>+ {{ rupiah($breakdown['additional_charges']) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-gray-600">
                                    <span>Pajak (10%):</span>
                                    <span>{{ rupiah($breakdown['tax_amount']) }}</span>
                                </div>
                                <div class="flex justify-between pt-1 border-t border-gray-200 text-sm font-bold text-gray-900">
                                    <span>Total Tagihan:</span>
                                    <span class="text-emerald-700 text-base">{{ rupiah($breakdown['total_amount']) }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Uang Muka DP -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Uang Muka / DP Langsung (Rp)</label>
                                <input type="number" wire:model="initial_paid_amount" class="w-full text-xs border-gray-300 rounded-lg" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Bayar DP</label>
                                <select wire:model="payment_method" class="w-full text-xs border-gray-300 rounded-lg">
                                    <option value="cash">Tunai / Cash</option>
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="qris_manual">QRIS Statis</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" @if(!$isAvailable) disabled @endif class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm disabled:opacity-50">
                                Simpan Reservasi Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Catat Pembayaran Meeting -->
        @if($showPaymentModal && $selectedReservation)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">💵 Catat Pembayaran Meeting</h3>
                        <button wire:click="$set('showPaymentModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Acara:</span>
                            <span class="font-bold text-gray-800">{{ $selectedReservation->event_name }}</span>
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
                            <span class="text-red-600">{{ rupiah($selectedReservation->balance) }}</span>
                        </div>
                    </div>

                    @error('payment')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="savePayment" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Pembayaran (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="payment_amount" class="w-full text-sm font-bold border-gray-300 rounded-lg text-emerald-800" required>
                            @error('payment_amount') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pembayaran</label>
                            <select wire:model="payment_method" class="w-full text-xs border-gray-300 rounded-lg">
                                <option value="cash">Tunai / Cash</option>
                                <option value="bank_transfer">Transfer Bank (BCA / Mandiri / BSI)</option>
                                <option value="qris_manual">QRIS Statis</option>
                                <option value="card_manual">Kartu Debit / EDC</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" wire:click="$set('showPaymentModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        <!-- Modal Kelola Tarif & Paket Ruang Meeting -->
        @if($showSettingsModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full p-6 space-y-5 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Kelola Standar Tarif & Paket Ruang Meeting</h3>
                            <p class="text-xs text-gray-500">Atur harga sewa ruangan (per jam/halfday/fullday) dan paket konsumsi per pax</p>
                        </div>
                        <button wire:click="$set('showSettingsModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    @if(session()->has('settings_message'))
                        <div class="p-3 bg-emerald-50 text-emerald-800 text-xs rounded-lg border border-emerald-200 font-medium">
                            {{ session('settings_message') }}
                        </div>
                    @endif

                    <!-- Tab Switcher -->
                    <div class="flex gap-2 border-b border-gray-200 pb-2 flex-wrap">
                        <button type="button" wire:click="$set('settingsTab', 'rooms')" class="px-3.5 py-2 text-xs font-bold rounded-lg transition {{ $settingsTab === 'rooms' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            🏢 Ruang Meeting & Tarif
                        </button>
                        <button type="button" wire:click="$set('settingsTab', 'packages')" class="px-3.5 py-2 text-xs font-bold rounded-lg transition {{ $settingsTab === 'packages' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            🍱 Paket Konsumsi & Coffee Break
                        </button>
                        <button type="button" wire:click="$set('settingsTab', 'services')" class="px-3.5 py-2 text-xs font-bold rounded-lg transition {{ $settingsTab === 'services' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            📦 Master Layanan Tambahan Meeting
                        </button>
                    </div>

                    <!-- Tab 1: Rooms -->
                    @if($settingsTab === 'rooms')
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Form Tambah / Edit Ruangan -->
                            <div class="lg:col-span-5 bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    {{ $editing_room_id ? '✏️ Edit Tarif Ruangan' : '➕ Tambah Ruangan Baru' }}
                                </h4>

                                <form wire:submit="saveRoom" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Ruang Meeting <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="room_name" class="w-full text-xs" placeholder="Contoh: Ruang Rapat VIP" required>
                                        @error('room_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kapasitas Maksimal (Pax) <span class="text-red-500">*</span></label>
                                        <input type="number" wire:model="room_capacity" class="w-full text-xs" min="1" required>
                                        @error('room_capacity') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Per Jam (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="room_hourly_rate" class="w-full text-xs font-semibold text-emerald-800" min="0" required>
                                            @error('room_hourly_rate') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Halfday 4 Jam (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="room_halfday_rate" class="w-full text-xs font-semibold text-emerald-800" min="0" required>
                                            @error('room_halfday_rate') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Fullday 8 Jam (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="room_fullday_rate" class="w-full text-xs font-semibold text-emerald-800" min="0" required>
                                            @error('room_fullday_rate') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="p-2.5 bg-amber-50/60 rounded-lg border border-amber-200/80 space-y-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="is_room_high_season_same" class="rounded text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-xs font-semibold text-gray-800">Tarif High Season sama dengan tarif normal</span>
                                        </label>

                                        @if(!$is_room_high_season_same)
                                            <div class="grid grid-cols-3 gap-2 pt-1 border-t border-amber-200">
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-amber-900 mb-0.5">High: Per Jam (Rp)</label>
                                                    <input type="number" wire:model="room_high_hourly_rate" class="w-full text-xs font-bold text-amber-900 bg-white" min="0">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-amber-900 mb-0.5">High: 4 Jam (Rp)</label>
                                                    <input type="number" wire:model="room_high_halfday_rate" class="w-full text-xs font-bold text-amber-900 bg-white" min="0">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-semibold text-amber-900 mb-0.5">High: 8 Jam (Rp)</label>
                                                    <input type="number" wire:model="room_high_fullday_rate" class="w-full text-xs font-bold text-amber-900 bg-white" min="0">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Fasilitas (Pisahkan dengan koma)</label>
                                        <input type="text" wire:model="room_facilities" class="w-full text-xs" placeholder="Projector HD, Mic Wireless, AC, Wi-Fi">
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="room_is_active" wire:model="room_is_active">
                                        <label for="room_is_active" class="text-xs text-gray-700 font-semibold mb-0 cursor-pointer">Ruangan Aktif & Tersedia untuk Booking</label>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2 border-t">
                                        @if($editing_room_id)
                                            <button type="button" wire:click="resetRoomForm" class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800">
                                                Batal Edit
                                            </button>
                                        @endif
                                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                            {{ $editing_room_id ? 'Simpan Tarif' : 'Tambah Ruangan' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Daftar Ruangan -->
                            <div class="lg:col-span-7 space-y-3">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Daftar Ruang Meeting & Tarif Standar</h4>
                                    <div class="flex items-center gap-1 bg-gray-200/70 p-0.5 rounded-lg text-xs">
                                        <button type="button" wire:click="$set('roomsFilter', 'active')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $roomsFilter === 'active' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            Aktif ({{ $activeRoomsCount }})
                                        </button>
                                        <button type="button" wire:click="$set('roomsFilter', 'trashed')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $roomsFilter === 'trashed' ? 'bg-white text-rose-800 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            🗑️ Arsip ({{ $trashedRoomsCount }})
                                        </button>
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                        <thead class="bg-gray-50 text-gray-600 font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">Nama Ruang</th>
                                                <th class="py-2.5 px-3">Tarif Sewa Standar</th>
                                                <th class="py-2.5 px-3">Status</th>
                                                <th class="py-2.5 px-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse($allMeetingRooms as $mr)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2.5 px-3">
                                                        <div class="font-bold text-gray-900">{{ $mr->name }}</div>
                                                        <div class="text-[11px] text-gray-500">Maks. {{ $mr->capacity }} Pax</div>
                                                        <div class="text-[10px] text-gray-400 mt-0.5">{{ is_array($mr->facilities) ? implode(', ', $mr->facilities) : '' }}</div>
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        <div class="text-emerald-700 font-semibold">1 Jam: {{ rupiah($mr->hourly_rate) }}</div>
                                                        <div class="text-gray-600 text-[11px]">4 Jam: {{ rupiah($mr->halfday_rate) }}</div>
                                                        <div class="text-gray-600 text-[11px]">8 Jam: {{ rupiah($mr->fullday_rate) }}</div>
                                                        @if($mr->high_hourly_rate && ($mr->high_hourly_rate != $mr->hourly_rate || $mr->high_halfday_rate != $mr->halfday_rate))
                                                            <div class="mt-1 text-[10px] text-amber-800 font-semibold border-t border-amber-200/50 pt-0.5">
                                                                High: {{ rupiah($mr->high_hourly_rate) }} / {{ rupiah($mr->high_halfday_rate) }} / {{ rupiah($mr->high_fullday_rate) }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        @if($mr->trashed())
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">
                                                                Terhapus
                                                            </span>
                                                        @else
                                                            <button wire:click="toggleRoomStatus({{ $mr->id }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ $mr->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                                {{ $mr->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                            </button>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                        @if($mr->trashed())
                                                            <button wire:click="restoreRoom({{ $mr->id }})" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition" title="Pulihkan kembali ruangan ini">
                                                                ♻️ Pulihkan
                                                            </button>
                                                        @else
                                                            <div class="flex items-center justify-end gap-1.5">
                                                                <button wire:click="editRoom({{ $mr->id }})" class="px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition">
                                                                    Edit
                                                                </button>
                                                                <button wire:click="deleteRoom({{ $mr->id }})" wire:confirm="Yakin ingin menghapus ruang meeting '{{ $mr->name }}'? (Data akan diarsipkan/soft delete)" class="px-2 py-1 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition" title="Hapus ruang meeting">
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-400">
                                                        {{ $roomsFilter === 'trashed' ? 'Tidak ada ruang meeting di arsip sampah.' : 'Belum ada ruang meeting terdaftar.' }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @elseif($settingsTab === 'packages')
                        <!-- Tab 2: Packages -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Form Tambah / Edit Paket Meeting -->
                            <div class="lg:col-span-5 bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    {{ $editing_pkg_id ? '✏️ Edit Paket Meeting' : '➕ Tambah Paket Konsumsi Baru' }}
                                </h4>

                                <form wire:submit="saveMeetingPackage" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Paket Konsumsi <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="pkg_name" class="w-full text-xs" placeholder="Contoh: Paket Coffee Break + Snack" required>
                                        @error('pkg_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Harga / Pax (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="pkg_price_per_pax" class="w-full text-xs font-bold text-emerald-800" min="0" required>
                                            @error('pkg_price_per_pax') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Min. Pax <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="pkg_min_pax" class="w-full text-xs" min="1" required>
                                            @error('pkg_min_pax') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="p-2.5 bg-amber-50/60 rounded-lg border border-amber-200/80 space-y-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="is_pkg_high_season_same" class="rounded text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-xs font-semibold text-gray-800">Tarif High Season sama dengan tarif normal</span>
                                        </label>

                                        @if(!$is_pkg_high_season_same)
                                            <div class="pt-1 border-t border-amber-200">
                                                <label class="block text-[10px] font-semibold text-amber-900 mb-0.5">High Season: Harga / Pax (Rp) <span class="text-red-500">*</span></label>
                                                <input type="number" wire:model="pkg_high_price_per_pax" class="w-full text-xs font-bold text-amber-900 bg-white" min="0">
                                                @error('pkg_high_price_per_pax') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi & Fasilitas Konsumsi</label>
                                        <textarea wire:model="pkg_description" rows="3" class="w-full text-xs" placeholder="Rincian makanan, snack, coffee break, durasi..."></textarea>
                                        @error('pkg_description') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="pkg_is_active" wire:model="pkg_is_active">
                                        <label for="pkg_is_active" class="text-xs text-gray-700 font-semibold mb-0 cursor-pointer">Paket Aktif & Ditampilkan di Pilihan Booking</label>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2 border-t">
                                        @if($editing_pkg_id)
                                            <button type="button" wire:click="resetMeetingPackageForm" class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800">
                                                Batal Edit
                                            </button>
                                        @endif
                                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                            {{ $editing_pkg_id ? 'Simpan Paket' : 'Tambah Paket' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Daftar Paket Meeting -->
                            <div class="lg:col-span-7 space-y-3">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Daftar Paket Konsumsi Terdaftar</h4>
                                    <div class="flex items-center gap-1 bg-gray-200/70 p-0.5 rounded-lg text-xs">
                                        <button type="button" wire:click="$set('packagesFilter', 'active')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $packagesFilter === 'active' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            Aktif ({{ $activePackagesCount }})
                                        </button>
                                        <button type="button" wire:click="$set('packagesFilter', 'trashed')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $packagesFilter === 'trashed' ? 'bg-white text-rose-800 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            🗑️ Arsip ({{ $trashedPackagesCount }})
                                        </button>
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                        <thead class="bg-gray-50 text-gray-600 font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">Nama Paket & Fasilitas</th>
                                                <th class="py-2.5 px-3">Tarif / Pax</th>
                                                <th class="py-2.5 px-3">Status</th>
                                                <th class="py-2.5 px-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse($allMeetingPackages as $mp)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2.5 px-3">
                                                        <div class="font-bold text-gray-900">{{ $mp->name }}</div>
                                                        <div class="text-[11px] text-gray-500 line-clamp-2 max-w-xs">{{ $mp->description ?: 'Tidak ada deskripsi' }}</div>
                                                        <div class="text-[10px] text-gray-400 mt-0.5">Min: {{ $mp->min_pax }} Pax</div>
                                                    </td>
                                                    <td class="py-2.5 px-3 font-semibold text-emerald-700 whitespace-nowrap">
                                                        {{ rupiah($mp->price_per_pax) }}
                                                        @if($mp->high_price_per_pax && $mp->high_price_per_pax != $mp->price_per_pax)
                                                            <div class="text-[10px] text-amber-800 font-bold">
                                                                High: {{ rupiah($mp->high_price_per_pax) }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        @if($mp->trashed())
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">
                                                                Terhapus
                                                            </span>
                                                        @else
                                                            <button wire:click="toggleMeetingPackageStatus({{ $mp->id }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ $mp->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                                {{ $mp->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                            </button>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                        @if($mp->trashed())
                                                            <button wire:click="restoreMeetingPackage({{ $mp->id }})" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition" title="Pulihkan kembali paket meeting ini">
                                                                ♻️ Pulihkan
                                                            </button>
                                                        @else
                                                            <div class="flex items-center justify-end gap-1.5">
                                                                <button wire:click="editMeetingPackage({{ $mp->id }})" class="px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition">
                                                                    Edit
                                                                </button>
                                                                <button wire:click="deleteMeetingPackage({{ $mp->id }})" wire:confirm="Yakin ingin menghapus paket meeting '{{ $mp->name }}'? (Data akan diarsipkan/soft delete)" class="px-2 py-1 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition" title="Hapus paket meeting">
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-400">
                                                        {{ $packagesFilter === 'trashed' ? 'Tidak ada paket konsumsi di arsip sampah.' : 'Belum ada paket konsumsi tersimpan.' }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @elseif($settingsTab === 'services')
                        <!-- Tab 3: Master Additional Services -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Form Tambah / Edit Layanan Tambahan -->
                            <div class="lg:col-span-5 bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    {{ $editing_svc_id ? '✏️ Edit Layanan Tambahan' : '➕ Tambah Layanan Tambahan Baru' }}
                                </h4>

                                <form wire:submit="saveService" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Layanan Tambahan <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="svc_name" class="w-full text-xs" placeholder="Contoh: Cetak Banner 3x1 Meter" required>
                                        @error('svc_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tarif Standar (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="svc_price" class="w-full text-xs font-bold text-emerald-800" min="0" required>
                                            @error('svc_price') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Satuan / Unit <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="svc_unit" class="w-full text-xs" placeholder="lembar, porsi, set, pcs" required>
                                            @error('svc_unit') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="svc_is_active" wire:model="svc_is_active">
                                        <label for="svc_is_active" class="text-xs text-gray-700 font-semibold mb-0 cursor-pointer">Layanan Aktif & Tampil di Pilihan Booking</label>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2 border-t">
                                        @if($editing_svc_id)
                                            <button type="button" wire:click="resetServiceForm" class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800">
                                                Batal Edit
                                            </button>
                                        @endif
                                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                            {{ $editing_svc_id ? 'Simpan Perubahan' : 'Tambah Layanan' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Daftar Layanan Tambahan Master -->
                            <div class="lg:col-span-7 space-y-3">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Daftar Layanan Tambahan Terdaftar</h4>
                                    <div class="flex items-center gap-1 bg-gray-200/70 p-0.5 rounded-lg text-xs">
                                        <button type="button" wire:click="$set('servicesFilter', 'active')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $servicesFilter === 'active' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            Aktif ({{ $activeServicesCount }})
                                        </button>
                                        <button type="button" wire:click="$set('servicesFilter', 'trashed')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $servicesFilter === 'trashed' ? 'bg-white text-rose-800 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            🗑️ Arsip ({{ $trashedServicesCount }})
                                        </button>
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                        <thead class="bg-gray-50 text-gray-600 font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">Nama Layanan Tambahan</th>
                                                <th class="py-2.5 px-3">Tarif Standar</th>
                                                <th class="py-2.5 px-3">Status</th>
                                                <th class="py-2.5 px-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse($allMeetingServices as $ms)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2.5 px-3">
                                                        <div class="font-bold text-gray-900">{{ $ms->name }}</div>
                                                        <div class="text-[10px] text-gray-400">Satuan: {{ $ms->unit }}</div>
                                                    </td>
                                                    <td class="py-2.5 px-3 font-semibold text-emerald-700 whitespace-nowrap">
                                                        {{ rupiah($ms->default_price) }} / {{ $ms->unit }}
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        @if($ms->trashed())
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">
                                                                Terhapus
                                                            </span>
                                                        @else
                                                            <button wire:click="toggleServiceStatus({{ $ms->id }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ $ms->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                                {{ $ms->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                            </button>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                        @if($ms->trashed())
                                                            <button wire:click="restoreService({{ $ms->id }})" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition" title="Pulihkan kembali layanan ini">
                                                                ♻️ Pulihkan
                                                            </button>
                                                        @else
                                                            <div class="flex items-center justify-end gap-1.5">
                                                                <button wire:click="editService({{ $ms->id }})" class="px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition">
                                                                    Edit
                                                                </button>
                                                                <button wire:click="deleteService({{ $ms->id }})" wire:confirm="Yakin ingin menghapus layanan '{{ $ms->name }}'? (Data akan diarsipkan/soft delete)" class="px-2 py-1 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition" title="Hapus layanan">
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-400">
                                                        {{ $servicesFilter === 'trashed' ? 'Tidak ada layanan tambahan di arsip sampah.' : 'Belum ada layanan tambahan tersimpan.' }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end pt-3 border-t">
                        <button type="button" wire:click="$set('showSettingsModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Rincian Reservasi & Tambah Layanan Susulan -->
        @if($showDetailModal && $detailReservation)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <div class="text-[11px] font-mono font-bold text-emerald-700 uppercase tracking-wider">{{ $detailReservation->reservation_number }}</div>
                            <h3 class="text-base font-bold text-gray-900">{{ $detailReservation->event_name }}</h3>
                        </div>
                        <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    @if(session()->has('detail_message'))
                        <div class="p-3 bg-emerald-50 text-emerald-800 text-xs rounded-lg border border-emerald-200 font-medium">
                            {{ session('detail_message') }}
                        </div>
                    @endif

                    @error('post_service')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Info Ringkas Acara & Ruangan -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                        <div>
                            <div class="text-gray-400 text-[10px]">Kontak / PIC:</div>
                            <div class="font-bold text-gray-800">{{ $detailReservation->customer->name }}</div>
                            <div class="text-gray-500 text-[11px]">{{ $detailReservation->customer->phone }}</div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-[10px]">Ruang & Jadwal:</div>
                            <div class="font-semibold text-emerald-800">{{ $detailReservation->meetingRoom->name }}</div>
                            <div class="text-gray-600">{{ $detailReservation->event_date->format('d M Y') }} ({{ $detailReservation->start_time }} - {{ $detailReservation->end_time }})</div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-[10px]">Peserta & Paket:</div>
                            <div class="font-semibold text-gray-800">{{ $detailReservation->pax }} Pax</div>
                            <div class="text-gray-500 text-[11px]">{{ $detailReservation->meetingPackage?->name ?: 'Paket Standar' }}</div>
                        </div>
                    </div>

                    <!-- Keterangan Setup Fasilitas Ruang -->
                    <div class="p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl text-xs space-y-1">
                        <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                            <span>🛠️</span>
                            <span>Kebutuhan Fasilitas & Tata Letak Ruangan</span>
                        </div>
                        @if($detailReservation->facility_notes)
                            <div class="text-indigo-950 font-medium whitespace-pre-line">{{ $detailReservation->facility_notes }}</div>
                        @else
                            <div class="text-indigo-400 italic">Tidak ada catatan instruksi fasilitas khusus.</div>
                        @endif
                    </div>

                    <!-- Daftar Item Layanan Tambahan -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center gap-1.5">
                            <span>📦</span>
                            <span>Daftar Layanan Tambahan Meeting ({{ $detailReservation->items->count() }})</span>
                        </h4>

                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                <thead class="bg-gray-50 text-gray-600 font-semibold">
                                    <tr>
                                        <th class="py-2 px-3">Nama Layanan</th>
                                        <th class="py-2 px-3 text-center">Qty</th>
                                        <th class="py-2 px-3 text-right">Harga Satuan</th>
                                        <th class="py-2 px-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($detailReservation->items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-3 font-medium text-gray-900">{{ $item->name }}</td>
                                            <td class="py-2 px-3 text-center">{{ $item->quantity }}</td>
                                            <td class="py-2 px-3 text-right text-gray-600">{{ rupiah($item->unit_price) }}</td>
                                            <td class="py-2 px-3 text-right font-bold text-emerald-800">{{ rupiah($item->subtotal) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-4 text-center text-gray-400 text-xs">
                                                Belum ada layanan tambahan pada reservasi ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Form Tambah Layanan Susulan (Jika status belum dibatalkan) -->
                    @if($detailReservation->status !== 'cancelled')
                        <div class="bg-amber-50/60 border border-amber-200 rounded-xl p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>➕</span> Tambah Layanan Susulan Saat Acara Berlangsung
                                </h4>
                                <div class="flex items-center gap-1 bg-amber-100/60 p-0.5 rounded-lg text-xs">
                                    <button type="button" wire:click="setPostServiceMode('master')" class="px-2.5 py-0.5 font-bold rounded-md transition {{ $post_service_mode === 'master' ? 'bg-amber-600 text-white shadow-2xs' : 'text-amber-900 hover:bg-amber-200/50' }}">
                                        📦 Dari Master
                                    </button>
                                    <button type="button" wire:click="setPostServiceMode('manual')" class="px-2.5 py-0.5 font-bold rounded-md transition {{ $post_service_mode === 'manual' ? 'bg-amber-600 text-white shadow-2xs' : 'text-amber-900 hover:bg-amber-200/50' }}">
                                        ✏️ Input Manual
                                    </button>
                                </div>
                            </div>

                            <form wire:submit="addPostBookingItem" class="space-y-2">
                                <div class="grid grid-cols-12 gap-2 items-end">
                                    <div class="col-span-12 sm:col-span-5 space-y-1">
                                        @if($post_service_mode === 'master')
                                            <label class="block text-[10px] text-amber-800 font-semibold mb-0.5">Pilih dari Master</label>
                                            <select wire:change="onPostServiceSelected($event.target.value)" class="w-full text-xs border-amber-300 rounded-lg py-1 px-2 bg-white">
                                                <option value="">-- Pilih dari Master --</option>
                                                @foreach($availableServices as $asvc)
                                                    <option value="{{ $asvc->id }}" @selected($post_service_id == $asvc->id)>
                                                        {{ $asvc->name }} ({{ rupiah($asvc->default_price) }}/{{ $asvc->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <label class="block text-[10px] text-amber-800 font-semibold mb-0.5">Nama Layanan Kustom / Manual</label>
                                            <input type="text" wire:model="post_item_name" class="w-full text-xs border-amber-300 rounded-lg py-1 px-2 bg-white font-medium text-gray-800" placeholder="Ketik nama layanan kustom..." required>
                                        @endif
                                    </div>
                                    <div class="col-span-4 sm:col-span-2">
                                        <label class="block text-[10px] text-amber-800 font-semibold mb-0.5">Qty</label>
                                        <input type="number" wire:model="post_item_qty" min="1" class="w-full text-xs border-amber-300 rounded-lg py-1 px-2 text-center" required>
                                    </div>
                                    <div class="col-span-4 sm:col-span-2">
                                        <label class="block text-[10px] text-amber-800 font-semibold mb-0.5">Tarif (Rp)</label>
                                        <input type="number" wire:model="post_item_price" min="0" class="w-full text-xs border-amber-300 rounded-lg py-1 px-2" placeholder="0">
                                    </div>
                                    <div class="col-span-4 sm:col-span-3">
                                        <button type="submit" class="w-full py-1.5 px-3 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-2xs transition">
                                            + Tambahkan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Rincian Keuangan & Tagihan Akhir -->
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal Sewa / Paket:</span>
                            <span class="font-semibold text-gray-800">{{ rupiah($detailReservation->subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Total Layanan Tambahan (Flat):</span>
                            <span>+ {{ rupiah($detailReservation->additional_charges) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Pajak (10%):</span>
                            <span>{{ rupiah($detailReservation->tax_amount) }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-gray-200 text-sm font-bold text-gray-900">
                            <span>Total Tagihan:</span>
                            <span class="text-emerald-700 text-base">{{ rupiah($detailReservation->total_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Telah Dibayar:</span>
                            <span class="font-bold text-emerald-700">{{ rupiah($detailReservation->paid_amount) }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-dashed border-gray-200 font-bold">
                            <span class="text-gray-700">Sisa Tagihan (Balance):</span>
                            <span class="{{ $detailReservation->balance > 0 ? 'text-red-600' : 'text-emerald-600' }} text-sm">{{ rupiah($detailReservation->balance) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('meeting.thermal', $detailReservation->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold border inline-flex items-center gap-1">
                                🖨️ Cetak Struk
                            </a>
                            <a href="{{ route('meeting.pdf', $detailReservation->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold border inline-flex items-center gap-1">
                                📄 Unduh Invoice PDF
                            </a>
                        </div>
                        <button type="button" wire:click="$set('showDetailModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
