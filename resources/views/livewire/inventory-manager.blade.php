<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Inventaris dan Persediaan Hotel</h1>
                <p class="text-sm text-gray-500 mt-1">Pengelolaan stok amenities, linen, f&b, pemakaian kamar, stock opname, dan valuasi rata-rata tertimbang.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="openPurchaseModal" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Stok Masuk / Beli</span>
                </button>
                <button wire:click="openUsageModal" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    <span>Catat Pemakaian</span>
                </button>
                <button wire:click="openNewOpnameModal" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Stock Opname</span>
                </button>
                <button wire:click="openNewItemModal" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Tambah Barang</span>
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

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai Persediaan</span>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Peringatan Stok Menipis</span>
                    <p class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-gray-900' }} mt-1">{{ $lowStockCount }} Item</p>
                </div>
                <div class="p-3 {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-gray-50 text-gray-400' }} rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Item Terdaftar</span>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $allItems->count() }} Jenis</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6">
                <button wire:click="$set('activeTab', 'items')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'items' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Daftar Barang (Master Stok)
                </button>
                <button wire:click="$set('activeTab', 'opnames')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'opnames' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Stock Opname & Rekonsiliasi
                </button>
                <button wire:click="$set('activeTab', 'movements')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'movements' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Riwayat Mutasi Stok
                </button>
            </nav>
        </div>

        <!-- TAB 1: ITEMS -->
        @if($activeTab === 'items')
            <!-- Filter & Search -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto">
                    <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Kategori:</span>
                    @foreach(['all' => 'Semua', 'amenities' => 'Amenities', 'linen' => 'Linen', 'fnb' => 'F&B / Resto', 'maintenance' => 'Pemeliharaan'] as $k => $lbl)
                        <button wire:click="$set('categoryFilter', '{{ $k }}')" class="px-2.5 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $categoryFilter === $k ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>

                <div class="w-full md:w-64">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode atau nama barang..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3.5">Kode & Nama Barang</th>
                                <th class="px-6 py-3.5">Kategori</th>
                                <th class="px-6 py-3.5">Sisa Stok</th>
                                <th class="px-6 py-3.5">Min. Stok</th>
                                <th class="px-6 py-3.5">HPP Rata-rata</th>
                                <th class="px-6 py-3.5">Total Nilai</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr class="hover:bg-gray-50/75 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                                        <div class="text-xs font-mono text-gray-500">{{ $item->code }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                                            {{ $item->category === 'amenities' ? 'bg-blue-50 text-blue-700' : '' }}
                                            {{ $item->category === 'linen' ? 'bg-purple-50 text-purple-700' : '' }}
                                            {{ $item->category === 'fnb' ? 'bg-amber-50 text-amber-700' : '' }}
                                            {{ $item->category === 'maintenance' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            {{ ucfirst($item->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 font-bold {{ $item->isLowStock() ? 'text-rose-600' : 'text-gray-900' }}">
                                            <span>{{ $item->current_stock }}</span>
                                            <span class="text-xs font-normal text-gray-500">{{ $item->unit }}</span>
                                            @if($item->isLowStock())
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-700 rounded">Menipis</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        {{ $item->minimum_stock }} {{ $item->unit }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-700">
                                        Rp {{ number_format($item->average_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono font-semibold text-gray-900">
                                        Rp {{ number_format($item->total_value, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button wire:click="openPurchaseModal({{ $item->id }})" title="Beli Stok" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            </button>
                                            <button wire:click="openUsageModal({{ $item->id }})" title="Pakai Stok" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                            </button>
                                            <button wire:click="openWasteModal({{ $item->id }})" title="Hapus Rusak" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Belum ada barang terdaftar. Klik tombol "Tambah Barang" untuk memulai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($items->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- TAB 2: STOCK OPNAMES -->
        @if($activeTab === 'opnames')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3.5">No. Opname</th>
                                <th class="px-6 py-3.5">Tanggal</th>
                                <th class="px-6 py-3.5">Petugas</th>
                                <th class="px-6 py-3.5">Jumlah Item</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5">Persetujuan</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($opnames as $opname)
                                <tr class="hover:bg-gray-50/75 transition">
                                    <td class="px-6 py-4 font-mono font-bold text-gray-900">{{ $opname->opname_number }}</td>
                                    <td class="px-6 py-4 text-xs">{{ $opname->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-xs">{{ $opname->creator->name }}</td>
                                    <td class="px-6 py-4 text-xs font-semibold">{{ $opname->items->count() }} item</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                            {{ $opname->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                            {{ $opname->status === 'draft' ? 'bg-amber-50 text-amber-700' : '' }}
                                            {{ $opname->status === 'rejected' ? 'bg-rose-50 text-rose-700' : '' }}">
                                            {{ ucfirst($opname->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        @if($opname->approved_by)
                                            <div>{{ $opname->approver?->name }}</div>
                                            <div class="text-[10px]">{{ $opname->approved_at?->format('d/m/Y H:i') }}</div>
                                        @else
                                            <span class="text-gray-400 italic">Menunggu Approval Manager</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($opname->status === 'draft' && (auth()->user()->hasRole('Manager') || auth()->user()->hasRole('admin')))
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button wire:click="approveOpname({{ $opname->id }})" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded shadow-sm">
                                                    Setujui
                                                </button>
                                                <button wire:click="rejectOpname({{ $opname->id }})" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded shadow-sm">
                                                    Tolak
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Belum ada rekonsiliasi stock opname.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($opnames->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $opnames->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- TAB 3: MOVEMENTS -->
        @if($activeTab === 'movements')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3.5">Waktu</th>
                                <th class="px-6 py-3.5">Barang</th>
                                <th class="px-6 py-3.5">Jenis Mutasi</th>
                                <th class="px-6 py-3.5">Jumlah</th>
                                <th class="px-6 py-3.5">Biaya Satuan</th>
                                <th class="px-6 py-3.5">Total Nilai</th>
                                <th class="px-6 py-3.5">Catatan / Vendor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($movements as $m)
                                <tr class="hover:bg-gray-50/75 transition">
                                    <td class="px-6 py-4 text-xs font-mono text-gray-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $m->item->name }}</div>
                                        <div class="text-xs font-mono text-gray-500">{{ $m->item->code }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                            {{ $m->type === 'purchase' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                            {{ $m->type === 'usage' ? 'bg-blue-50 text-blue-700' : '' }}
                                            {{ $m->type === 'adjustment' ? 'bg-amber-50 text-amber-700' : '' }}
                                            {{ $m->type === 'waste' ? 'bg-rose-50 text-rose-700' : '' }}">
                                            {{ ucfirst($m->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">
                                        {{ $m->quantity }} {{ $m->item->unit }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono">
                                        Rp {{ number_format($m->unit_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono font-bold text-gray-900">
                                        Rp {{ number_format($m->total_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        {{ $m->vendor?->name ?? $m->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Belum ada riwayat mutasi stok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $movements->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- MODAL 1: NEW ITEM -->
        @if($showItemModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Tambah Master Barang Baru</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Kode Barang</label>
                            <input type="text" wire:model="item_code" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            @error('item_code') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Nama Barang</label>
                            <input type="text" wire:model="item_name" placeholder="Misal: Sabun Hotel Herbal 30ml" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            @error('item_name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Kategori</label>
                                <select wire:model="item_category" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                    <option value="amenities">Amenities</option>
                                    <option value="linen">Linen</option>
                                    <option value="fnb">F&B / Resto</option>
                                    <option value="maintenance">Pemeliharaan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Satuan (Unit)</label>
                                <input type="text" wire:model="item_unit" placeholder="pcs, pack, kg" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Batas Minimum Stok (Peringatan)</label>
                            <input type="number" wire:model="item_minimum_stock" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showItemModal', false)" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="button" wire:click="saveItem" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700">Simpan Barang</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL 2: PURCHASE (STOCK IN) -->
        @if($showPurchaseModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Penerimaan Stok / Pembelian</h3>
                    @error('purchase') <div class="p-2 bg-rose-50 text-rose-700 text-xs rounded">{{ $message }}</div> @enderror
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Pilih Barang</label>
                            <select wire:model="purchase_item_id" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                @foreach($allItems as $itm)
                                    <option value="{{ $itm->id }}">{{ $itm->name }} (Sisa: {{ $itm->current_stock }} {{ $itm->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Jumlah Masuk</label>
                                <input type="number" wire:model="purchase_qty" min="1" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Harga Satuan (Rp)</label>
                                <input type="number" wire:model="purchase_unit_cost" min="0" step="500" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Vendor / Pemasok</label>
                                <select wire:model="purchase_vendor_id" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                    <option value="">-- Tanpa Vendor --</option>
                                    @foreach($vendors as $vnd)
                                        <option value="{{ $vnd->id }}">{{ $vnd->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Sumber Dana</label>
                                <select wire:model="purchase_payment_source" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                    <option value="cash">Kas Front Desk</option>
                                    <option value="bank">Bank Operasional</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Catatan Pembelian</label>
                            <input type="text" wire:model="purchase_notes" placeholder="No. Faktur atau keterangan" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg text-xs font-mono text-gray-700 flex justify-between items-center">
                            <span>Total Pembayaran:</span>
                            <span class="font-bold text-sm text-emerald-600">Rp {{ number_format($purchase_qty * $purchase_unit_cost, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showPurchaseModal', false)" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="button" wire:click="savePurchase" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700">Simpan Stok Masuk</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL 3: USAGE (STOCK OUT) -->
        @if($showUsageModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Catat Pemakaian Operasional</h3>
                    @error('usage') <div class="p-2 bg-rose-50 text-rose-700 text-xs rounded">{{ $message }}</div> @enderror
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Pilih Barang</label>
                            <select wire:model="usage_item_id" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                @foreach($allItems as $itm)
                                    <option value="{{ $itm->id }}">{{ $itm->name }} (Sisa: {{ $itm->current_stock }} {{ $itm->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Jumlah Dipakai</label>
                            <input type="number" wire:model="usage_qty" min="1" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Keterangan / Keperluan</label>
                            <input type="text" wire:model="usage_notes" placeholder="Misal: Replenish Kamar 101 - 105" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showUsageModal', false)" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="button" wire:click="saveUsage" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">Catat Pemakaian</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL 4: WASTE (DAMAGED / EXPIRED) -->
        @if($showWasteModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Penghapusan Stok Rusak / Kedaluwarsa</h3>
                    @error('waste') <div class="p-2 bg-rose-50 text-rose-700 text-xs rounded">{{ $message }}</div> @enderror
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Pilih Barang</label>
                            <select wire:model="waste_item_id" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                @foreach($allItems as $itm)
                                    <option value="{{ $itm->id }}">{{ $itm->name }} (Sisa: {{ $itm->current_stock }} {{ $itm->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Jumlah Rusak / Hilang</label>
                            <input type="number" wire:model="waste_qty" min="1" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Alasan Penghapusan</label>
                            <input type="text" wire:model="waste_reason" placeholder="Misal: Kemasan bocor saat penyimpanan" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showWasteModal', false)" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="button" wire:click="saveWaste" class="px-4 py-2 bg-rose-600 text-white text-sm font-semibold rounded-lg hover:bg-rose-700">Catat Penghapusan</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL 5: STOCK OPNAME FORM -->
        @if($showOpnameModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl space-y-4 max-h-[90vh] flex flex-col">
                    <h3 class="text-lg font-bold text-gray-900">Formulir Rekonsiliasi Stock Opname Fisik</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Tanggal Opname</label>
                            <input type="date" wire:model="opname_date" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Catatan Dokumen</label>
                            <input type="text" wire:model="opname_notes" placeholder="Opname akhir bulan..." class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-left text-xs text-gray-600">
                            <thead class="bg-gray-50 sticky top-0 uppercase font-semibold text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2.5">Barang</th>
                                    <th class="px-4 py-2.5 text-center">Stok Sistem</th>
                                    <th class="px-4 py-2.5 text-center">Hitungan Fisik</th>
                                    <th class="px-4 py-2.5 text-center">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($allItems as $itm)
                                    @php
                                        $phys = (int) ($opname_counts[$itm->id] ?? $itm->current_stock);
                                        $diff = $phys - $itm->current_stock;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2.5 font-medium text-gray-900">
                                            {{ $itm->name }}
                                            <span class="text-gray-400 font-normal">({{ $itm->unit }})</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-bold text-gray-700">{{ $itm->current_stock }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <input type="number" wire:model.live="opname_counts.{{ $itm->id }}" class="w-20 text-center text-xs py-1 rounded border-gray-300">
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-mono font-bold {{ $diff < 0 ? 'text-rose-600' : ($diff > 0 ? 'text-emerald-600' : 'text-gray-400') }}">
                                            {{ $diff > 0 ? "+{$diff}" : $diff }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showOpnameModal', false)" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="button" wire:click="saveOpname" class="px-4 py-2 bg-amber-600 text-white text-sm font-semibold rounded-lg hover:bg-amber-700">Simpan Draft Opname</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
