<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Restoran dan Fine Dining Syariah</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola reservasi meja, liwet kastrol, paket prasmanan, panitia guru & pesanan khusus halal</p>
            </div>
            
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                <button wire:click="openMasterModal('packages')" class="px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-xs sm:text-sm font-semibold rounded-lg shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>⚙️ Kelola Master Menu & Layanan</span>
                </button>
                <button wire:click="openCreateModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>➕ Reservasi Meja / Paket Resto</span>
                </button>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('message') }}
            </div>
        @endif

        <!-- Tab Switcher Utama: Reservasi Aktif & Mendatang vs Riwayat Selesai -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 pb-3">
            <div class="flex items-center gap-2">
                <button wire:click="$set('viewTab', 'active')" class="px-4 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2 {{ $viewTab === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>🍽️ Reservasi Aktif & Mendatang</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $viewTab === 'active' ? 'bg-emerald-700 text-white' : 'bg-gray-200 text-gray-700' }} font-mono">{{ $activeCount }}</span>
                </button>
                <button wire:click="$set('viewTab', 'history')" class="px-4 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2 {{ $viewTab === 'history' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span>📁 Riwayat Dining Selesai</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $viewTab === 'history' ? 'bg-indigo-700 text-white' : 'bg-gray-200 text-gray-700' }} font-mono">{{ $historyCount }}</span>
                </button>
            </div>

            @if($viewTab === 'history')
                <!-- Tombol Cepat Ekspor Excel & PDF untuk Riwayat Dining -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.export.csv', ['unit' => 'dining', 'history_only' => 1]) }}" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-semibold shadow-2xs inline-flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Ekspor Excel</span>
                    </a>
                    <a href="{{ route('reports.export.pdf', ['unit' => 'dining', 'history_only' => 1]) }}" target="_blank" class="px-3.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-800 border border-red-300 rounded-lg text-xs font-semibold shadow-2xs inline-flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Ekspor PDF</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pemesan, rombongan, PIC, no. booking..." class="text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-72">
                <input type="date" wire:model.live="dateFilter" class="text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Status:</span>
                @foreach([
                    'all' => 'Semua',
                    'confirmed' => 'Terkonfirmasi',
                    'seated' => 'Tamu Hadir / Duduk',
                    'completed' => 'Selesai',
                    'cancelled' => 'Batal'
                ] as $val => $label)
                    <button wire:click="$set('statusFilter', '{{ $val }}')" class="px-2.5 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $statusFilter === $val ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Table Dining Reservations -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">No. Booking</th>
                            <th class="py-3 px-4">Rombongan & Tamu</th>
                            <th class="py-3 px-4">Paket Menu & Meja</th>
                            <th class="py-3 px-4">Jadwal Santap</th>
                            <th class="py-3 px-4">Pax</th>
                            <th class="py-3 px-4">Total & Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reservations as $res)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 px-4 align-top whitespace-nowrap">
                                    <div class="font-mono font-bold text-gray-900">{{ $res->reservation_number }}</div>

                                    @if($res->is_wikrama_teachers)
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ✨ Panitia Guru Wikrama
                                            </span>
                                        </div>
                                    @endif

                                    <div class="mt-1">
                                        @if(!empty($res->activity_link))
                                            <a href="{{ $res->activity_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition cursor-pointer" title="Buka Link Kegiatan / Rundown (Tab Baru)">
                                                <span>🔗 Link Kegiatan</span>
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @else
                                            <button type="button" disabled class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed opacity-60" title="Tidak ada link kegiatan">
                                                <span>🔗 Link Kegiatan</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 align-top">
                                    @php
                                        $custPhone = preg_replace('/[^0-9]/', '', $res->customer->phone ?? '');
                                        if (str_starts_with($custPhone, '0')) {
                                            $custPhone = '62' . substr($custPhone, 1);
                                        }
                                        $waText = urlencode("Assalamualaikum Bpk/Ibu {$res->customer->name}, konfirmasi reservasi resto #{$res->reservation_number} di Restoran InSitu Hotel Syariah Garut untuk {$res->pax} orang pada {$res->reservation_date->format('d/m/Y')} pukul {$res->time_slot} WIB.");
                                        $waUrl = !empty($custPhone) ? "https://wa.me/{$custPhone}?text={$waText}" : null;
                                    @endphp

                                    @if($res->group_name)
                                        <div class="font-bold text-gray-900 flex items-center gap-1">
                                            <span>👥 {{ $res->group_name }}</span>
                                        </div>
                                    @endif
                                    <div class="{{ $res->group_name ? 'text-[11px] text-gray-600 font-medium' : 'font-bold text-gray-900' }} flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $res->customer->name }}</span>
                                        <span class="text-gray-400 font-normal">({{ $res->customer->phone }})</span>
                                        @if($waUrl)
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-300 transition" title="Chat WhatsApp Pemesan">
                                                <span>💬 WA</span>
                                            </a>
                                        @endif
                                    </div>
                                    @if($res->pic_name)
                                        @php
                                            $picPhone = preg_replace('/[^0-9]/', '', $res->pic_phone ?? '');
                                            if (str_starts_with($picPhone, '0')) {
                                                $picPhone = '62' . substr($picPhone, 1);
                                            }
                                            $picWaText = urlencode("Assalamualaikum Bpk/Ibu {$res->pic_name} (PIC {$res->group_name}), konfirmasi reservasi resto #{$res->reservation_number} untuk {$res->pax} orang pada {$res->reservation_date->format('d/m/Y')} pukul {$res->time_slot} WIB.");
                                            $picWaUrl = !empty($picPhone) ? "https://wa.me/{$picPhone}?text={$picWaText}" : null;
                                        @endphp
                                        <div class="text-[10px] text-gray-500 flex items-center gap-1.5 mt-0.5 flex-wrap">
                                            <span>PIC: <strong class="text-gray-700">{{ $res->pic_name }}</strong> {{ $res->pic_phone ? '('.$res->pic_phone.')' : '' }}</span>
                                            @if($picWaUrl)
                                                <a href="{{ $picWaUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-0.5 px-1 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition" title="Chat WhatsApp PIC">
                                                    <span>💬 WA PIC</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 align-top">
                                    <div class="font-semibold text-gray-800">
                                        @if($res->diningPackage)
                                            {{ $res->diningPackage->name }}
                                        @elseif($res->custom_menu_name)
                                            <span class="text-amber-800">✏️ {{ $res->custom_menu_name }}</span>
                                        @else
                                            Pesanan Ala Carte
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-emerald-800 font-medium">📍 {{ $res->table_number_or_area }}</div>
                                    @if($res->items->count() > 0)
                                        <div class="text-[10px] text-indigo-700 font-semibold mt-0.5">
                                            + {{ $res->items->count() }} Layanan Tambahan ({{ rupiah($res->additional_charges) }})
                                        </div>
                                    @endif
                                    @if($res->special_requests)
                                        <div class="text-[10px] text-gray-500 italic mt-0.5 whitespace-pre-line">"{!! nl2br(e($res->special_requests)) !!}"</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 align-top whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $res->reservation_date->format('d M Y') }}</div>
                                    <div class="text-[11px] text-emerald-700 font-semibold">{{ $res->time_slot }} WIB</div>
                                </td>
                                <td class="py-3 px-4 align-top whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[11px]">
                                        {{ $res->pax }} Orang
                                    </span>
                                </td>
                                <td class="py-3 px-4 align-top whitespace-nowrap space-y-1">
                                    <div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                            {{ $res->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $res->status === 'seated' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $res->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $res->status }}
                                        </span>
                                    </div>
                                    <div class="font-bold text-emerald-700 text-xs">{{ rupiah($res->total_amount) }}</div>
                                    <div class="text-[11px] {{ $res->payment_status === 'paid' ? 'text-emerald-600 font-semibold' : 'text-amber-600' }}">
                                        {{ ucfirst(str_replace('_', ' ', $res->payment_status)) }} (Lunas: {{ rupiah($res->paid_amount) }})
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $res->apply_tax ? 'Termasuk Pajak 10%' : 'Bebas Pajak' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right align-top whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <!-- Tombol Detil Pesanan -->
                                        <button wire:click="openDetailModal({{ $res->id }})" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded text-[11px] font-semibold transition inline-flex items-center gap-1" title="Lihat Detil Lengkap Pesanan">
                                            <span>🔍 Detil</span>
                                        </button>

                                        <!-- Edit Pesanan -->
                                        <button wire:click="openEditModal({{ $res->id }})" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded text-[11px] font-semibold transition" title="Edit Pesanan">
                                            ✏️ Edit
                                        </button>

                                        @if($res->status === 'confirmed')
                                            <button wire:click="updateStatus({{ $res->id }}, 'seated')" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded text-[11px] font-semibold transition" title="Tamu Hadir di Meja">
                                                🪑 Hadir
                                            </button>
                                        @elseif($res->status === 'seated')
                                            <button wire:click="updateStatus({{ $res->id }}, 'completed')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-[11px] font-semibold transition" title="Selesai Makan">
                                                ✅ Selesai
                                            </button>
                                        @endif

                                        @if($res->status !== 'cancelled' && $res->balance > 0)
                                            <button wire:click="openPaymentModal({{ $res->id }})" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-[11px] font-semibold transition" title="Catat Pembayaran">
                                                💵 Bayar
                                            </button>
                                        @endif

                                        <!-- Cetak Struk 80mm -->
                                        <a href="{{ route('dining.thermal', $res->id) }}" target="_blank" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-[11px] font-semibold transition inline-flex items-center gap-1" title="Cetak Struk Thermal 80mm">
                                            🖨️ Struk
                                        </a>

                                        <!-- Cetak PDF -->
                                        <a href="{{ route('dining.pdf', $res->id) }}" target="_blank" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-[11px] font-semibold transition inline-flex items-center gap-1" title="Unduh Bukti Reservasi PDF">
                                            📄 PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    Belum ada data reservasi fine dining yang sesuai.
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

        <!-- ========================================== -->
        <!-- MODAL RESERVASI BARU RESTO (WIDE & RESPONSIVE) -->
        <!-- ========================================== -->
        @if($showCreateModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-5 sm:p-6 space-y-5 max-h-[92vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Reservasi Baru Restoran & Fine Dining Syariah</h3>
                            <p class="text-xs text-gray-500">Lengkapi data rombongan, menu hidangan, layanan tambahan, dan kalkulasi tagihan</p>
                        </div>
                        <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                    </div>

                    @error('general')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveReservation" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- KOLOM KIRI: DATA PEMESAN & ROMBONGAN -->
                            <div class="space-y-4 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
                                <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>👥 Data Tamu, Rombongan & Jadwal</span>
                                </h4>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Rombongan / Instansi <span class="text-gray-400 font-normal">(opsional)</span></label>
                                    <input type="text" wire:model="group_name" class="w-full text-xs rounded-lg border-gray-300" placeholder="Contoh: Rombongan Disdik Jabar / Keluarga Besar Bpk H. Ahmad">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pemesan <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="customer_name" class="w-full text-xs rounded-lg border-gray-300" placeholder="Nama..." required>
                                        @error('customer_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="customer_phone" class="w-full text-xs rounded-lg border-gray-300" placeholder="0812xxxx" required>
                                        @error('customer_phone') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama PIC Lapangan <span class="text-gray-400 font-normal">(opsional)</span></label>
                                        <input type="text" wire:model="pic_name" class="w-full text-xs rounded-lg border-gray-300" placeholder="Nama PIC...">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kontak PIC <span class="text-gray-400 font-normal">(opsional)</span></label>
                                        <input type="text" wire:model="pic_phone" class="w-full text-xs rounded-lg border-gray-300" placeholder="08xxxxxxxx">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Link Kegiatan / Rundown <span class="text-gray-400 font-normal">(opsional)</span></label>
                                    <input type="url" wire:model="activity_link" class="w-full text-xs rounded-lg border-gray-300" placeholder="https://drive.google.com/... atau tautan rundown acara">
                                    @error('activity_link') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Request Panitia Guru Wikrama -->
                                <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-lg">
                                    <label class="flex items-center gap-2 cursor-pointer mb-0">
                                        <input type="checkbox" wire:model="is_wikrama_teachers" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                        <div>
                                            <span class="text-xs font-bold text-emerald-900">Request Panitia Guru Wikrama ?</span>
                                            <p class="text-[11px] text-emerald-700">Tandai jika acara/reservasi ini dikelola atau dihadiri oleh kepanitiaan guru SMK Wikrama 1 Garut.</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Jadwal & Area -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                        <input type="date" wire:model.live="reservation_date" class="w-full text-xs rounded-lg border-gray-300" required>
                                        @if(!empty($breakdown['is_high_season']))
                                            <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                🔥 High Season ({{ $breakdown['season_label'] ?? 'Akhir Pekan' }})
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Datang <span class="text-red-500">*</span></label>
                                        <input type="time" wire:model="time_slot" class="w-full text-xs rounded-lg border-gray-300" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Tamu <span class="text-red-500">*</span></label>
                                        <input type="number" wire:model.live.debounce.400ms="pax" min="1" class="w-full text-xs rounded-lg border-gray-300" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Area / Meja Pilihan</label>
                                    <input type="text" wire:model="table_number_or_area" class="w-full text-xs rounded-lg border-gray-300" placeholder="Contoh: Saung Lesehan 1 / Gazebo Utama / Meja VIP">
                                </div>
                            </div>

                            <!-- KOLOM KANAN: PAKET MENU, LAYANAN TAMBAHAN & PEMBAYARAN -->
                            <div class="space-y-4">
                                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200 space-y-3">
                                    <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center justify-between">
                                        <span>🍽️ Pilihan Paket Menu Spesial</span>
                                    </h4>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Paket Menu</label>
                                        <select wire:model.live="dining_package_id" class="w-full text-xs rounded-lg border-gray-300">
                                            <option value="">-- ✏️ Paket Kustom / Ketik Manual Sendiri --</option>
                                            @foreach($diningPackages as $dp)
                                                <option value="{{ $dp->id }}">{{ $dp->name }} ({{ rupiah($dp->price_per_pax) }}/pax, Min {{ $dp->min_pax }} Pax)</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if(empty($dining_package_id))
                                        <!-- Form Input Menu Manual -->
                                        <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-lg space-y-2">
                                            <div class="text-[11px] font-bold text-amber-900">✏️ Rincian Menu Kustom / Ala Carte</div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Menu Kustom</label>
                                                <input type="text" wire:model="custom_menu_name" class="w-full text-xs rounded-lg border-gray-300" placeholder="Contoh: Liwet Kastrol Bebek Ungkep Spesial">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tarif per Pax (Rp)</label>
                                                <input type="number" wire:model.live.debounce.400ms="custom_price_per_pax" min="0" class="w-full text-xs rounded-lg border-gray-300 font-bold text-emerald-800" placeholder="65000">
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Pajak Toggle -->
                                    <div class="flex items-center justify-between pt-1">
                                        <label class="flex items-center gap-2 cursor-pointer mb-0">
                                            <input type="checkbox" wire:model.live="apply_tax" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                            <span class="text-xs font-semibold text-gray-800">Kenakan Pajak Restoran (PB1 10%)</span>
                                        </label>
                                        <span class="text-[11px] {{ $apply_tax ? 'text-emerald-700 font-bold' : 'text-gray-400' }}">
                                            {{ $apply_tax ? 'Pajak Aktif' : 'Bebas Pajak (0%)' }}
                                        </span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Khusus / Permintaan Syariah <span class="text-gray-400 font-normal">(bisa multi-baris / Enter)</span></label>
                                        <textarea wire:model="special_requests" rows="3" class="w-full text-xs rounded-lg border-gray-300" placeholder="Penyekat ikhwan-akhwat&#10;Kurangi garam&#10;Sambal dipisah"></textarea>
                                    </div>
                                </div>

                                <!-- LAYANAN TAMBAHAN (MULTI-ADDONS) -->
                                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200 space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">🛎️ Layanan Tambahan (Add-on)</h4>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" wire:click="addAddonFromMaster" class="px-2.5 py-1 bg-white hover:bg-gray-100 border border-indigo-200 text-indigo-700 rounded-md text-[11px] font-semibold transition">
                                                📦 + Dari Master
                                            </button>
                                            <button type="button" wire:click="addAddonManual" class="px-2.5 py-1 bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 rounded-md text-[11px] font-semibold transition">
                                                ✏️ + Manual
                                            </button>
                                        </div>
                                    </div>

                                    @if(count($booking_addon_items) > 0)
                                        <div class="space-y-2">
                                            @foreach($booking_addon_items as $index => $item)
                                                <div class="p-2.5 bg-white rounded-lg border border-gray-200 space-y-2 shadow-2xs">
                                                    <div class="flex items-center gap-2">
                                                        @if(isset($item['dining_service_id']) && $item['dining_service_id'] !== null)
                                                            <div class="flex-1">
                                                                <select wire:change="selectMasterAddon({{ $index }}, $event.target.value)" class="w-full text-xs rounded-lg border-gray-300 py-1">
                                                                    @foreach($masterServices as $ms)
                                                                        <option value="{{ $ms->id }}" {{ $item['dining_service_id'] == $ms->id ? 'selected' : '' }}>
                                                                            {{ $ms->name }} ({{ rupiah($ms->default_price) }}/{{ $ms->unit }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @else
                                                            <div class="flex-1">
                                                                <input type="text" wire:model.live.debounce.300ms="booking_addon_items.{{ $index }}.name" class="w-full text-xs rounded-lg border-gray-300 py-1" placeholder="Nama layanan kustom (e.g. Liwet Kastrol Extra)...">
                                                            </div>
                                                        @endif
                                                        <button type="button" wire:click="removeAddon({{ $index }})" class="text-rose-500 hover:text-rose-700 p-1 text-xs" title="Hapus item">
                                                            🗑️
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-3 gap-2 items-center">
                                                        <div>
                                                            <span class="text-[10px] text-gray-500 block">Qty:</span>
                                                            <input type="number" wire:model.live.debounce.400ms="booking_addon_items.{{ $index }}.quantity" min="1" class="w-full text-xs rounded-lg border-gray-300 py-1">
                                                        </div>
                                                        <div>
                                                            <span class="text-[10px] text-gray-500 block">Harga Satuan (Rp):</span>
                                                            <input type="number" wire:model.live.debounce.400ms="booking_addon_items.{{ $index }}.unit_price" min="0" class="w-full text-xs rounded-lg border-gray-300 py-1 font-semibold">
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-[10px] text-gray-500 block">Subtotal:</span>
                                                            <span class="text-xs font-bold text-gray-800">
                                                                {{ rupiah(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-3 text-center text-gray-400 bg-white rounded-lg border border-dashed border-gray-200 text-xs">
                                            Belum ada layanan tambahan. Klik tombol di atas untuk menambah menu/layanan ekstra.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- KALKULASI BREAKDOWN & PEMBAYARAN DP -->
                        <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-200 space-y-3">
                            <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider">💰 Ringkasan Biaya & Uang Muka (DP)</h4>
                            
                            @if(!empty($breakdown))
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-3 rounded-lg border border-emerald-100 text-xs">
                                    <div>
                                        <span class="text-gray-500 text-[11px] block">Tarif Makanan ({{ $pax }} Pax):</span>
                                        <span class="font-bold text-gray-800">{{ rupiah($breakdown['subtotal']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-[11px] block">Layanan Tambahan:</span>
                                        <span class="font-bold text-indigo-700">{{ rupiah($breakdown['additional_charges']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-[11px] block">Pajak Resto ({{ $apply_tax ? '10%' : '0%' }}):</span>
                                        <span class="font-bold text-gray-800">{{ rupiah($breakdown['tax_amount']) }}</span>
                                    </div>
                                    <div class="border-l border-emerald-100 pl-3">
                                        <span class="text-emerald-800 font-bold text-[11px] block">Total Tagihan:</span>
                                        <span class="text-base font-extrabold text-emerald-700">{{ rupiah($breakdown['total_amount']) }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Uang Muka / DP (Rp) <span class="text-gray-400 font-normal">(opsional)</span></label>
                                    <input type="number" wire:model="initial_paid_amount" class="w-full text-xs rounded-lg border-gray-300 font-bold text-emerald-800" placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Bayar DP</label>
                                    <select wire:model="payment_method" class="w-full text-xs rounded-lg border-gray-300">
                                        <option value="cash">Tunai / Kasir</option>
                                        <option value="bank_transfer">Transfer Bank (BCA / Mandiri / BSI)</option>
                                        <option value="qris_manual">QRIS Statis</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                                Simpan Reservasi Resto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- MODAL EDIT PESANAN RESTO (CORRECT TYPOS)  -->
        <!-- ========================================== -->
        @if($showEditModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-5 sm:p-6 space-y-5 max-h-[92vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">✏️ Edit Pesanan Resto</h3>
                            <p class="text-xs text-gray-500">Perbaiki kesalahan ketik, rombongan, jumlah tamu, paket menu, pajak, dan rincian layanan</p>
                        </div>
                        <button wire:click="$set('showEditModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                    </div>

                    @error('edit_general')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveEditedReservation" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- KIRI: EDIT DATA TAMU & JADWAL -->
                            <div class="space-y-4 bg-gray-50/70 p-4 rounded-xl border border-gray-200">
                                <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider">👥 Data Rombongan & Jadwal</h4>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Rombongan / Instansi</label>
                                    <input type="text" wire:model="edit_group_name" class="w-full text-xs rounded-lg border-gray-300">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pemesan <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="edit_customer_name" class="w-full text-xs rounded-lg border-gray-300" required>
                                        @error('edit_customer_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="edit_customer_phone" class="w-full text-xs rounded-lg border-gray-300" required>
                                        @error('edit_customer_phone') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama PIC</label>
                                        <input type="text" wire:model="edit_pic_name" class="w-full text-xs rounded-lg border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kontak PIC</label>
                                        <input type="text" wire:model="edit_pic_phone" class="w-full text-xs rounded-lg border-gray-300">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Link Kegiatan / Rundown</label>
                                    <input type="url" wire:model="edit_activity_link" class="w-full text-xs rounded-lg border-gray-300">
                                    @error('edit_activity_link') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Wikrama Teacher checkbox -->
                                <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-lg">
                                    <label class="flex items-center gap-2 cursor-pointer mb-0">
                                        <input type="checkbox" wire:model="edit_is_wikrama_teachers" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                        <div>
                                            <span class="text-xs font-bold text-emerald-900">Request Panitia Guru Wikrama ?</span>
                                            <p class="text-[11px] text-emerald-700">Tandai jika reservasi ini untuk kepanitiaan guru Wikrama.</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Jadwal & Area -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                        <input type="date" wire:model.live="edit_reservation_date" class="w-full text-xs rounded-lg border-gray-300" required>
                                        @if(!empty($edit_breakdown['is_high_season']))
                                            <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                🔥 High Season ({{ $edit_breakdown['season_label'] ?? 'Akhir Pekan' }})
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Datang <span class="text-red-500">*</span></label>
                                        <input type="time" wire:model="edit_time_slot" class="w-full text-xs rounded-lg border-gray-300" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pax <span class="text-red-500">*</span></label>
                                        <input type="number" wire:model.live.debounce.400ms="edit_pax" min="1" class="w-full text-xs rounded-lg border-gray-300" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Area / Meja Pilihan</label>
                                    <input type="text" wire:model="edit_table_number_or_area" class="w-full text-xs rounded-lg border-gray-300">
                                </div>
                            </div>

                            <!-- KANAN: EDIT MENU, LAYANAN & PAJAK -->
                            <div class="space-y-4">
                                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200 space-y-3">
                                    <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider">🍽️ Menu & Pajak</h4>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Paket Menu</label>
                                        <select wire:model.live="edit_dining_package_id" class="w-full text-xs rounded-lg border-gray-300">
                                            <option value="">-- ✏️ Paket Kustom / Ketik Manual Sendiri --</option>
                                            @foreach($diningPackages as $dp)
                                                <option value="{{ $dp->id }}">{{ $dp->name }} ({{ rupiah($dp->price_per_pax) }}/pax)</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if(empty($edit_dining_package_id))
                                        <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-lg space-y-2">
                                            <div class="text-[11px] font-bold text-amber-900">✏️ Menu Kustom</div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Menu Kustom</label>
                                                <input type="text" wire:model="edit_custom_menu_name" class="w-full text-xs rounded-lg border-gray-300">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tarif per Pax (Rp)</label>
                                                <input type="number" wire:model.live.debounce.400ms="edit_custom_price_per_pax" min="0" class="w-full text-xs rounded-lg border-gray-300 font-bold text-emerald-800">
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Pajak Toggle -->
                                    <div class="flex items-center justify-between pt-1">
                                        <label class="flex items-center gap-2 cursor-pointer mb-0">
                                            <input type="checkbox" wire:model.live="edit_apply_tax" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                                            <span class="text-xs font-semibold text-gray-800">Kenakan Pajak Restoran (PB1 10%)</span>
                                        </label>
                                        <span class="text-[11px] {{ $edit_apply_tax ? 'text-emerald-700 font-bold' : 'text-gray-400' }}">
                                            {{ $edit_apply_tax ? 'Pajak Aktif' : 'Bebas Pajak' }}
                                        </span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Permintaan Khusus / Syariah <span class="text-gray-400 font-normal">(bisa multi-baris / Enter)</span></label>
                                        <textarea wire:model="edit_special_requests" rows="3" class="w-full text-xs rounded-lg border-gray-300" placeholder="Penyekat ikhwan-akhwat&#10;Kurangi garam&#10;Sambal dipisah"></textarea>
                                    </div>
                                </div>

                                <!-- EDIT ADDON ITEMS -->
                                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200 space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">🛎️ Layanan Tambahan (Add-on)</h4>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" wire:click="addEditAddonFromMaster" class="px-2.5 py-1 bg-white hover:bg-gray-100 border border-indigo-200 text-indigo-700 rounded-md text-[11px] font-semibold transition">
                                                📦 + Dari Master
                                            </button>
                                            <button type="button" wire:click="addEditAddonManual" class="px-2.5 py-1 bg-white hover:bg-gray-100 border border-gray-300 text-gray-700 rounded-md text-[11px] font-semibold transition">
                                                ✏️ + Manual
                                            </button>
                                        </div>
                                    </div>

                                    @if(count($edit_addon_items) > 0)
                                        <div class="space-y-2">
                                            @foreach($edit_addon_items as $idx => $item)
                                                <div class="p-2.5 bg-white rounded-lg border border-gray-200 space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        @if(isset($item['dining_service_id']) && $item['dining_service_id'] !== null)
                                                            <div class="flex-1">
                                                                <select wire:change="selectEditMasterAddon({{ $idx }}, $event.target.value)" class="w-full text-xs rounded-lg border-gray-300 py-1">
                                                                    @foreach($masterServices as $ms)
                                                                        <option value="{{ $ms->id }}" {{ $item['dining_service_id'] == $ms->id ? 'selected' : '' }}>
                                                                            {{ $ms->name }} ({{ rupiah($ms->default_price) }}/{{ $ms->unit }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @else
                                                            <div class="flex-1">
                                                                <input type="text" wire:model.live.debounce.300ms="edit_addon_items.{{ $idx }}.name" class="w-full text-xs rounded-lg border-gray-300 py-1" placeholder="Nama layanan kustom...">
                                                            </div>
                                                        @endif
                                                        <button type="button" wire:click="removeEditAddon({{ $idx }})" class="text-rose-500 hover:text-rose-700 p-1 text-xs">
                                                            🗑️
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-3 gap-2 items-center">
                                                        <div>
                                                            <span class="text-[10px] text-gray-500 block">Qty:</span>
                                                            <input type="number" wire:model.live.debounce.400ms="edit_addon_items.{{ $idx }}.quantity" min="1" class="w-full text-xs rounded-lg border-gray-300 py-1">
                                                        </div>
                                                        <div>
                                                            <span class="text-[10px] text-gray-500 block">Harga Satuan (Rp):</span>
                                                            <input type="number" wire:model.live.debounce.400ms="edit_addon_items.{{ $idx }}.unit_price" min="0" class="w-full text-xs rounded-lg border-gray-300 py-1 font-semibold">
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-[10px] text-gray-500 block">Subtotal:</span>
                                                            <span class="text-xs font-bold text-gray-800">
                                                                {{ rupiah(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- EDIT BREAKDOWN PREVIEW -->
                        @if(!empty($edit_breakdown))
                            <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200 space-y-2">
                                <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider">💰 Rekalkulasi Tagihan Setelah Perubahan</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-3 rounded-lg border border-amber-100 text-xs">
                                    <div>
                                        <span class="text-gray-500 text-[11px] block">Tarif Makanan:</span>
                                        <span class="font-bold text-gray-800">{{ rupiah($edit_breakdown['subtotal']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-[11px] block">Layanan Tambahan:</span>
                                        <span class="font-bold text-indigo-700">{{ rupiah($edit_breakdown['additional_charges']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-[11px] block">Pajak Resto:</span>
                                        <span class="font-bold text-gray-800">{{ rupiah($edit_breakdown['tax_amount']) }}</span>
                                    </div>
                                    <div class="border-l border-amber-100 pl-3">
                                        <span class="text-amber-900 font-bold text-[11px] block">Total Baru:</span>
                                        <span class="text-base font-extrabold text-emerald-700">{{ rupiah($edit_breakdown['total_amount']) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-end gap-3 pt-3 border-t">
                            <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm">
                                Simpan Perubahan Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- MODAL MASTER SETTINGS (PAKET & LAYANAN)   -->
        <!-- ========================================== -->
        @if($showMasterModal || $showPackageModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full p-5 sm:p-6 space-y-5 max-h-[92vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">⚙️ Master Pengaturan Resto Fine Dining</h3>
                            <p class="text-xs text-gray-500">Kelola katalog Paket Menu Spesial (dengan foto) & Master Layanan Tambahan Restoran</p>
                        </div>
                        <button wire:click="closeMasterModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                    </div>

                    <!-- TAB SELECTOR -->
                    <div class="flex items-center gap-3 border-b border-gray-200 pb-2">
                        <button type="button" wire:click="$set('masterTab', 'packages')" class="px-4 py-2 text-xs sm:text-sm font-bold rounded-lg transition {{ $masterTab === 'packages' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            🍽️ Paket Menu Spesial Dining ({{ $activePackagesCount }})
                        </button>
                        <button type="button" wire:click="$set('masterTab', 'services')" class="px-4 py-2 text-xs sm:text-sm font-bold rounded-lg transition {{ $masterTab === 'services' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            🛎️ Master Layanan Tambahan ({{ $activeServicesCount }})
                        </button>
                    </div>

                    <!-- ================= TAB 1: PAKET MENU ================= -->
                    @if($masterTab === 'packages')
                        @if(session()->has('package_message'))
                            <div class="p-3 bg-emerald-50 text-emerald-800 text-xs rounded-lg border border-emerald-200 font-medium">
                                {{ session('package_message') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Form Paket Menu -->
                            <div class="lg:col-span-5 bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    {{ $editing_package_id ? '✏️ Edit Paket Menu' : '➕ Tambah Paket Baru' }}
                                </h4>

                                <form wire:submit="savePackage" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Paket Menu <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="package_name" class="w-full text-xs rounded-lg border-gray-300" placeholder="Contoh: Paket Liwet Kastrol Komplit" required>
                                        @error('package_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Harga / Pax (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="package_price_per_pax" class="w-full text-xs rounded-lg border-gray-300 font-bold text-emerald-800" min="0" required>
                                            @error('package_price_per_pax') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Min. Pax <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="package_min_pax" class="w-full text-xs rounded-lg border-gray-300" min="1" required>
                                            @error('package_min_pax') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="p-2.5 bg-amber-50/60 rounded-lg border border-amber-200/80 space-y-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="is_package_high_season_same" class="rounded text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-xs font-semibold text-gray-800">Tarif High Season sama dengan tarif normal</span>
                                        </label>

                                        @if(!$is_package_high_season_same)
                                            <div class="pt-1 border-t border-amber-200">
                                                <label class="block text-[10px] font-semibold text-amber-900 mb-0.5">High Season: Harga / Pax (Rp) <span class="text-red-500">*</span></label>
                                                <input type="number" wire:model="package_high_price_per_pax" class="w-full text-xs font-bold text-amber-900 bg-white rounded-lg border-amber-300" min="0">
                                                @error('package_high_price_per_pax') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Rincian Menu & Hidangan</label>
                                        <textarea wire:model="package_menu_details" rows="3" class="w-full text-xs rounded-lg border-gray-300" placeholder="Rincian hidangan makanan, lauk, sambal, minuman..."></textarea>
                                        @error('package_menu_details') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Upload Foto Paket Menu -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Foto / Gambar Paket Menu <span class="text-gray-400 font-normal">(opsional)</span></label>
                                        <input type="file" wire:model="package_photo" accept="image/*" class="w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                        @error('package_photo') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror

                                        <div wire:loading wire:target="package_photo" class="text-[11px] text-emerald-600 mt-1">
                                            Mengunggah pratinjau gambar...
                                        </div>

                                        @if($package_photo)
                                            <div class="mt-2">
                                                <span class="text-[10px] text-gray-500 block mb-1">Pratinjau Foto Baru:</span>
                                                <img src="{{ $package_photo->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-emerald-200 object-cover">
                                            </div>
                                        @elseif($existing_image_path)
                                            <div class="mt-2">
                                                <span class="text-[10px] text-gray-500 block mb-1">Foto Tersimpan:</span>
                                                <img src="{{ asset('storage/' . $existing_image_path) }}" class="h-24 w-auto rounded-lg border border-gray-200 object-cover">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="package_is_active" wire:model="package_is_active" class="rounded text-emerald-600">
                                        <label for="package_is_active" class="text-xs text-gray-700 font-semibold mb-0 cursor-pointer">Paket Aktif & Ditampilkan di Pilihan Reservasi</label>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2 border-t">
                                        @if($editing_package_id)
                                            <button type="button" wire:click="resetPackageForm" class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800">
                                                Batal Edit
                                            </button>
                                        @endif
                                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                            {{ $editing_package_id ? 'Simpan Perubahan' : 'Tambah Paket' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Daftar Paket Menu -->
                            <div class="lg:col-span-7 space-y-3">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Daftar Paket Terdaftar</h4>
                                    <div class="flex items-center gap-1 bg-gray-200/70 p-0.5 rounded-lg text-xs">
                                        <button type="button" wire:click="$set('packageFilter', 'active')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $packageFilter === 'active' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            Aktif ({{ $activePackagesCount }})
                                        </button>
                                        <button type="button" wire:click="$set('packageFilter', 'trashed')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $packageFilter === 'trashed' ? 'bg-white text-rose-800 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            🗑️ Arsip ({{ $trashedPackagesCount }})
                                        </button>
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                        <thead class="bg-gray-50 text-gray-600 font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">Foto & Paket</th>
                                                <th class="py-2.5 px-3">Tarif / Pax</th>
                                                <th class="py-2.5 px-3">Status</th>
                                                <th class="py-2.5 px-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse($allPackages as $pkg)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2.5 px-3">
                                                        <div class="flex items-center gap-2.5">
                                                            @if($pkg->image_path && file_exists(public_path('storage/' . $pkg->image_path)))
                                                                <img src="{{ asset('storage/' . $pkg->image_path) }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200 shrink-0">
                                                            @else
                                                                <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-base shrink-0">
                                                                    🍱
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div class="font-bold text-gray-900">{{ $pkg->name }}</div>
                                                                <div class="text-[11px] text-gray-500 line-clamp-1 max-w-xs">{{ $pkg->menu_details ?: 'Tidak ada rincian' }}</div>
                                                                <div class="text-[10px] text-gray-400">Min: {{ $pkg->min_pax }} Pax</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-2.5 px-3 font-semibold text-emerald-700 whitespace-nowrap">
                                                        {{ rupiah($pkg->price_per_pax) }}
                                                        @if($pkg->high_price_per_pax && $pkg->high_price_per_pax != $pkg->price_per_pax)
                                                            <div class="text-[10px] text-amber-800 font-bold">
                                                                High: {{ rupiah($pkg->high_price_per_pax) }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        @if($pkg->trashed())
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">
                                                                Terhapus
                                                            </span>
                                                        @else
                                                            <button wire:click="togglePackageStatus({{ $pkg->id }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ $pkg->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                                {{ $pkg->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                            </button>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                        @if($pkg->trashed())
                                                            <button wire:click="restorePackage({{ $pkg->id }})" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition">
                                                                ♻️ Pulihkan
                                                            </button>
                                                        @else
                                                            <div class="flex items-center justify-end gap-1.5">
                                                                <button wire:click="editPackage({{ $pkg->id }})" class="px-2 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition">
                                                                    Edit
                                                                </button>
                                                                <button wire:click="deletePackage({{ $pkg->id }})" wire:confirm="Yakin ingin menghapus paket '{{ $pkg->name }}'? (Data akan diarsipkan/soft delete)" class="px-2 py-1 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition">
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-400">
                                                        {{ $packageFilter === 'trashed' ? 'Tidak ada paket menu di arsip sampah.' : 'Belum ada paket menu tersimpan.' }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- ================= TAB 2: MASTER LAYANAN TAMBAHAN ================= -->
                    @if($masterTab === 'services')
                        @if(session()->has('service_message'))
                            <div class="p-3 bg-indigo-50 text-indigo-800 text-xs rounded-lg border border-indigo-200 font-medium">
                                {{ session('service_message') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Form Master Layanan -->
                            <div class="lg:col-span-5 bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                                <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">
                                    {{ $editing_service_id ? '✏️ Edit Layanan Tambahan' : '➕ Tambah Layanan Tambahan' }}
                                </h4>

                                <form wire:submit="saveService" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Layanan Tambahan <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="service_name" class="w-full text-xs rounded-lg border-gray-300" placeholder="Contoh: Liwet Kastrol Jumbo, Grill BBQ Set..." required>
                                        @error('service_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model="service_default_price" class="w-full text-xs rounded-lg border-gray-300 font-bold text-emerald-800" min="0" required>
                                            @error('service_default_price') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="service_unit" class="w-full text-xs rounded-lg border-gray-300" placeholder="porsi / paket / set / pcs" required>
                                            @error('service_unit') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="service_is_active" wire:model="service_is_active" class="rounded text-indigo-600">
                                        <label for="service_is_active" class="text-xs text-gray-700 font-semibold mb-0 cursor-pointer">Layanan Aktif & Muncul di Pilihan Reservasi</label>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2 border-t">
                                        @if($editing_service_id)
                                            <button type="button" wire:click="resetServiceForm" class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800">
                                                Batal Edit
                                            </button>
                                        @endif
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                            {{ $editing_service_id ? 'Simpan Perubahan' : 'Tambah Layanan' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Daftar Layanan Tambahan -->
                            <div class="lg:col-span-7 space-y-3">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Daftar Layanan Terdaftar</h4>
                                    <div class="flex items-center gap-1 bg-gray-200/70 p-0.5 rounded-lg text-xs">
                                        <button type="button" wire:click="$set('serviceFilter', 'active')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $serviceFilter === 'active' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            Aktif ({{ $activeServicesCount }})
                                        </button>
                                        <button type="button" wire:click="$set('serviceFilter', 'trashed')" class="px-2.5 py-0.5 font-semibold rounded-md transition {{ $serviceFilter === 'trashed' ? 'bg-white text-rose-800 shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                                            🗑️ Arsip ({{ $trashedServicesCount }})
                                        </button>
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                        <thead class="bg-gray-50 text-gray-600 font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">Nama Layanan</th>
                                                <th class="py-2.5 px-3">Tarif Satuan</th>
                                                <th class="py-2.5 px-3">Status</th>
                                                <th class="py-2.5 px-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse($allServices as $svc)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2.5 px-3 font-semibold text-gray-800">
                                                        {{ $svc->name }}
                                                    </td>
                                                    <td class="py-2.5 px-3 font-bold text-emerald-700 whitespace-nowrap">
                                                        {{ rupiah($svc->default_price) }} <span class="text-[10px] text-gray-500 font-normal">/ {{ $svc->unit }}</span>
                                                    </td>
                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                        @if($svc->trashed())
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">
                                                                Terhapus
                                                            </span>
                                                        @else
                                                            <button wire:click="toggleServiceStatus({{ $svc->id }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ $svc->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                                {{ $svc->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                            </button>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                        @if($svc->trashed())
                                                            <button wire:click="restoreService({{ $svc->id }})" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition">
                                                                ♻️ Pulihkan
                                                            </button>
                                                        @else
                                                            <div class="flex items-center justify-end gap-1.5">
                                                                <button wire:click="editService({{ $svc->id }})" class="px-2 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg transition">
                                                                    Edit
                                                                </button>
                                                                <button wire:click="deleteService({{ $svc->id }})" wire:confirm="Yakin ingin menghapus layanan '{{ $svc->name }}'? (Data akan diarsipkan/soft delete)" class="px-2 py-1 text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg transition">
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-400">
                                                        {{ $serviceFilter === 'trashed' ? 'Tidak ada layanan di arsip sampah.' : 'Belum ada master layanan tambahan tersimpan.' }}
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
                        <button type="button" wire:click="closeMasterModal" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- MODAL CATAT PEMBAYARAN RESTO              -->
        <!-- ========================================== -->
        @if($showPaymentModal && $selectedReservation)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">💵 Catat Pembayaran Resto</h3>
                        <button wire:click="$set('showPaymentModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pemesan:</span>
                            <span class="font-bold text-gray-800">{{ $selectedReservation->customer->name }}</span>
                        </div>
                        @if($selectedReservation->group_name)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Rombongan:</span>
                                <span class="font-bold text-emerald-800">👥 {{ $selectedReservation->group_name }}</span>
                            </div>
                        @endif
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
                                <option value="cash">Tunai / Cash Kasir</option>
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

        <!-- ========================================== -->
        <!-- MODAL DETIL LENGKAP RESERVASI RESTO       -->
        <!-- ========================================== -->
        @if($showDetailModal && $detailReservation)
            @php
                $detailCustPhone = preg_replace('/[^0-9]/', '', $detailReservation->customer->phone ?? '');
                if (str_starts_with($detailCustPhone, '0')) {
                    $detailCustPhone = '62' . substr($detailCustPhone, 1);
                }
                $detailWaText = urlencode("Assalamualaikum Bpk/Ibu {$detailReservation->customer->name}, konfirmasi reservasi resto #{$detailReservation->reservation_number} di Restoran InSitu Hotel Syariah Garut untuk {$detailReservation->pax} orang pada {$detailReservation->reservation_date->format('d/m/Y')} pukul {$detailReservation->time_slot} WIB.");
                $detailWaUrl = !empty($detailCustPhone) ? "https://wa.me/{$detailCustPhone}?text={$detailWaText}" : null;

                $detailPicPhone = preg_replace('/[^0-9]/', '', $detailReservation->pic_phone ?? '');
                if (str_starts_with($detailPicPhone, '0')) {
                    $detailPicPhone = '62' . substr($detailPicPhone, 1);
                }
                $detailPicWaText = urlencode("Assalamualaikum Bpk/Ibu {$detailReservation->pic_name} (PIC {$detailReservation->group_name}), konfirmasi reservasi resto #{$detailReservation->reservation_number} untuk {$detailReservation->pax} orang pada {$detailReservation->reservation_date->format('d/m/Y')} pukul {$detailReservation->time_slot} WIB.");
                $detailPicWaUrl = !empty($detailPicPhone) ? "https://wa.me/{$detailPicPhone}?text={$detailPicWaText}" : null;
            @endphp

            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-5 sm:p-6 space-y-5 max-h-[92vh] overflow-y-auto">
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <span>🔍 Detil Reservasi Resto</span>
                                    <span class="font-mono text-emerald-700">#{{ $detailReservation->reservation_number }}</span>
                                </h3>
                                <p class="text-xs text-gray-500">Informasi lengkap pemesanan meja, hidangan, layanan tambahan & status pembayaran</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                {{ $detailReservation->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $detailReservation->status === 'seated' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $detailReservation->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $detailReservation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $detailReservation->status }}
                            </span>
                        </div>
                        <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- KOLOM KIRI: DATA PEMESAN & JADWAL -->
                        <div class="space-y-4">
                            <!-- Card Tamu & Rombongan -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2.5 text-xs">
                                <h4 class="font-bold text-gray-800 uppercase tracking-wider text-[11px] border-b pb-1.5 flex items-center gap-1.5">
                                    <span>👥 Data Rombongan & Pemesan</span>
                                </h4>

                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-gray-500">Rombongan:</span>
                                    <span class="col-span-2 font-bold text-gray-900">{{ $detailReservation->group_name ?: '-' }}</span>
                                </div>

                                <div class="grid grid-cols-3 gap-1 items-center">
                                    <span class="text-gray-500">Nama Pemesan:</span>
                                    <div class="col-span-2 flex items-center justify-between gap-1">
                                        <span class="font-bold text-gray-900">{{ $detailReservation->customer->name }}</span>
                                        @if($detailWaUrl)
                                            <a href="{{ $detailWaUrl }}" target="_blank" rel="noopener noreferrer" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition inline-flex items-center gap-1" title="Chat WhatsApp Pemesan">
                                                <span>💬 Chat WA</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-gray-500">WhatsApp:</span>
                                    <span class="col-span-2 font-semibold text-gray-800">+{{ $detailReservation->customer->phone }}</span>
                                </div>

                                <div class="grid grid-cols-3 gap-1 items-center">
                                    <span class="text-gray-500">PIC Lapangan:</span>
                                    <div class="col-span-2 flex items-center justify-between gap-1">
                                        <span class="font-semibold text-gray-800">{{ $detailReservation->pic_name ?: '-' }}</span>
                                        @if($detailPicWaUrl)
                                            <a href="{{ $detailPicWaUrl }}" target="_blank" rel="noopener noreferrer" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition inline-flex items-center gap-1" title="Chat WhatsApp PIC">
                                                <span>💬 Chat WA PIC</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if($detailReservation->pic_phone)
                                    <div class="grid grid-cols-3 gap-1">
                                        <span class="text-gray-500">Kontak PIC:</span>
                                        <span class="col-span-2 font-semibold text-gray-800">{{ $detailReservation->pic_phone }}</span>
                                    </div>
                                @endif

                                @if($detailReservation->is_wikrama_teachers)
                                    <div class="pt-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ✨ Request Panitia Guru Wikrama
                                        </span>
                                    </div>
                                @endif

                                <!-- Link Kegiatan Button -->
                                <div class="pt-2 border-t border-gray-200 flex items-center justify-between gap-2">
                                    <span class="text-gray-500">Link Kegiatan:</span>
                                    @if(!empty($detailReservation->activity_link))
                                        <a href="{{ $detailReservation->activity_link }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition inline-flex items-center gap-1.5" title="Buka Link Kegiatan di Tab Baru">
                                            <span>🔗 Buka Link Kegiatan / Rundown</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    @else
                                        <button type="button" disabled class="px-3 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed opacity-60" title="Tidak ada link kegiatan yang dilampirkan">
                                            <span>🔗 Link Kegiatan (Kosong)</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Jadwal & Lokasi -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2 text-xs">
                                <h4 class="font-bold text-gray-800 uppercase tracking-wider text-[11px] border-b pb-1.5 flex items-center gap-1.5">
                                    <span>📍 Jadwal & Lokasi Santap</span>
                                </h4>

                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-gray-500">Tanggal Acara:</span>
                                    <span class="col-span-2 font-bold text-gray-900">{{ $detailReservation->reservation_date->format('l, d F Y') }}</span>
                                </div>

                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-gray-500">Jam Santap:</span>
                                    <span class="col-span-2 font-bold text-emerald-700">{{ $detailReservation->time_slot }} WIB</span>
                                </div>

                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-gray-500">Area / Meja:</span>
                                    <span class="col-span-2 font-bold text-gray-900">{{ $detailReservation->table_number_or_area }}</span>
                                </div>

                                <div class="grid grid-cols-3 gap-1">
                                    <span class="text-gray-500">Jumlah Tamu:</span>
                                    <span class="col-span-2 font-bold text-amber-800">{{ $detailReservation->pax }} Orang (Pax)</span>
                                </div>

                                <div class="pt-2 border-t border-gray-200">
                                    <span class="text-gray-500 block mb-1">Catatan Khusus / Permintaan Syariah:</span>
                                    <div class="p-2.5 bg-white rounded-lg border border-gray-200 text-gray-800 whitespace-pre-line text-xs font-medium">
                                        {!! nl2br(e($detailReservation->special_requests ?: 'Tidak ada catatan khusus.')) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: PAKET MENU, LAYANAN TAMBAHAN & BIAYA -->
                        <div class="space-y-4">
                            <!-- Card Paket Menu -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2 text-xs">
                                <h4 class="font-bold text-gray-800 uppercase tracking-wider text-[11px] border-b pb-1.5 flex items-center gap-1.5">
                                    <span>🍽️ Paket Menu Spesial</span>
                                </h4>

                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-900 text-sm">
                                        @if($detailReservation->diningPackage)
                                            {{ $detailReservation->diningPackage->name }}
                                        @elseif($detailReservation->custom_menu_name)
                                            <span class="text-amber-800">✏️ {{ $detailReservation->custom_menu_name }}</span>
                                        @else
                                            Pesanan Ala Carte
                                        @endif
                                    </span>
                                    <span class="font-bold text-emerald-700 text-xs">{{ rupiah($detailReservation->price_per_pax) }} / pax</span>
                                </div>

                                <div class="flex justify-between text-gray-600 pt-1">
                                    <span>Subtotal Makanan ({{ $detailReservation->pax }} Pax):</span>
                                    <span class="font-bold text-gray-800">{{ rupiah($detailReservation->subtotal) }}</span>
                                </div>

                                @if($detailReservation->diningPackage && $detailReservation->diningPackage->menu_details)
                                    <div class="mt-2 p-2 bg-white rounded-lg border border-gray-200 text-[11px] text-gray-600">
                                        <strong class="text-gray-800 block mb-0.5">Rincian Hidangan:</strong>
                                        {{ $detailReservation->diningPackage->menu_details }}
                                    </div>
                                @endif
                            </div>

                            <!-- Card Layanan Tambahan (Add-on) -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2 text-xs">
                                <h4 class="font-bold text-indigo-900 uppercase tracking-wider text-[11px] border-b pb-1.5 flex items-center justify-between">
                                    <span>🛎️ Layanan Tambahan (Add-on)</span>
                                    <span class="text-[10px] font-bold text-indigo-700">{{ $detailReservation->items->count() }} Item</span>
                                </h4>

                                @if($detailReservation->items->count() > 0)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                                        <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                                            <thead class="bg-gray-50 text-[10px] text-gray-500 font-semibold uppercase">
                                                <tr>
                                                    <th class="py-1.5 px-2.5">Layanan</th>
                                                    <th class="py-1.5 px-2.5 text-center">Qty</th>
                                                    <th class="py-1.5 px-2.5 text-right">Tarif</th>
                                                    <th class="py-1.5 px-2.5 text-right">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($detailReservation->items as $it)
                                                    <tr>
                                                        <td class="py-1.5 px-2.5 font-medium text-gray-900">{{ $it->name }}</td>
                                                        <td class="py-1.5 px-2.5 text-center">{{ $it->quantity }}</td>
                                                        <td class="py-1.5 px-2.5 text-right">{{ rupiah($it->unit_price) }}</td>
                                                        <td class="py-1.5 px-2.5 text-right font-semibold text-gray-800">{{ rupiah($it->subtotal) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-2.5 text-center text-gray-400 bg-white rounded-lg border border-dashed text-xs">
                                        Tidak ada layanan tambahan untuk pesanan ini.
                                    </div>
                                @endif
                            </div>

                            <!-- Card Rincian Tagihan & Status Bayar -->
                            <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-200 space-y-2 text-xs">
                                <h4 class="font-bold text-emerald-900 uppercase tracking-wider text-[11px] border-b border-emerald-200 pb-1.5">
                                    💰 Rincian Tagihan & Pelunasan
                                </h4>

                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal Makanan:</span>
                                    <span class="font-semibold text-gray-800">{{ rupiah($detailReservation->subtotal) }}</span>
                                </div>

                                @if($detailReservation->additional_charges > 0)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Total Layanan Tambahan:</span>
                                        <span class="font-semibold text-indigo-700">{{ rupiah($detailReservation->additional_charges) }}</span>
                                    </div>
                                @endif

                                @if($detailReservation->discount_amount > 0)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Diskon Potongan:</span>
                                        <span class="font-semibold text-rose-600">-{{ rupiah($detailReservation->discount_amount) }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between text-gray-600">
                                    <span>Pajak Restoran (PB1):</span>
                                    <span class="font-semibold text-gray-800">
                                        {{ $detailReservation->apply_tax ? rupiah($detailReservation->tax_amount).' (10%)' : 'Bebas Pajak (0%)' }}
                                    </span>
                                </div>

                                <div class="flex justify-between pt-1.5 border-t border-emerald-200 font-bold text-sm text-gray-900">
                                    <span>Total Tagihan:</span>
                                    <span class="text-emerald-700 text-base">{{ rupiah($detailReservation->total_amount) }}</span>
                                </div>

                                <div class="flex justify-between pt-1 text-gray-700">
                                    <span>Telah Dibayar:</span>
                                    <span class="font-bold text-emerald-700">{{ rupiah($detailReservation->paid_amount) }}</span>
                                </div>

                                <div class="flex justify-between font-bold">
                                    <span>Sisa Tagihan:</span>
                                    <span class="{{ $detailReservation->balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                        {{ rupiah($detailReservation->balance) }}
                                        <span class="text-[10px] font-normal">({{ ucfirst(str_replace('_', ' ', $detailReservation->payment_status)) }})</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIWAYAT PEMBAYARAN JIKA ADA -->
                    @if($detailReservation->payments->count() > 0)
                        <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 space-y-2 text-xs">
                            <h4 class="font-bold text-gray-800 uppercase tracking-wider text-[11px]">
                                📜 Riwayat Transaksi Pembayaran
                            </h4>
                            <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                                <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                                    <thead class="bg-gray-50 text-[10px] text-gray-500 font-semibold uppercase">
                                        <tr>
                                            <th class="py-1.5 px-3">No. Kuitansi</th>
                                            <th class="py-1.5 px-3">Waktu Bayar</th>
                                            <th class="py-1.5 px-3">Metode</th>
                                            <th class="py-1.5 px-3 text-right">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($detailReservation->payments as $pay)
                                            <tr>
                                                <td class="py-1.5 px-3 font-mono font-bold text-gray-900">{{ $pay->payment_number }}</td>
                                                <td class="py-1.5 px-3 text-gray-600">{{ $pay->created_at->format('d/m/Y H:i') }} WIB</td>
                                                <td class="py-1.5 px-3 font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $pay->payment_method)) }}</td>
                                                <td class="py-1.5 px-3 text-right font-bold text-emerald-700">{{ rupiah($pay->amount) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- FOOTER ACTIONS -->
                    <div class="flex items-center justify-between flex-wrap gap-2 pt-3 border-t border-gray-200">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($detailWaUrl)
                                <a href="{{ $detailWaUrl }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 shadow-xs">
                                    <span>💬 Chat WhatsApp Pemesan</span>
                                </a>
                            @endif

                            @if($detailPicWaUrl)
                                <a href="{{ $detailPicWaUrl }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5">
                                    <span>💬 Chat WhatsApp PIC</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Edit Pesanan -->
                            <button type="button" wire:click="$set('showDetailModal', false); openEditModal({{ $detailReservation->id }})" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                <span>✏️ Edit Pesanan</span>
                            </button>

                            <!-- Struk 80mm -->
                            <a href="{{ route('dining.thermal', $detailReservation->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                <span>🖨️ Struk 80mm</span>
                            </a>

                            <!-- Cetak PDF -->
                            <a href="{{ route('dining.pdf', $detailReservation->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                <span>📄 Cetak PDF</span>
                            </a>

                            <!-- Tutup -->
                            <button type="button" wire:click="$set('showDetailModal', false)" class="px-4 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
