<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Aset Tetap & Penyusutan</h1>
                <p class="text-sm text-gray-500 mt-1">Pencatatan aset kamar, peralatan operasional, nilai buku, dan jadwal depresiasi garis lurus otomatis.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="openDepreciationModal" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Jalankan Penyusutan Bulanan</span>
                </button>
                <button wire:click="openRegisterModal" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Daftarkan Aset Baru</span>
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
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai Perolehan</span>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalAssetCost, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Akumulasi Penyusutan</span>
                    <p class="text-2xl font-bold text-rose-600 mt-1">Rp {{ number_format($totalAccumDepreciation, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Buku Bersih (Net)</span>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalBookValue, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Kategori:</span>
                @foreach(['all' => 'Semua', 'electronic' => 'Elektronik', 'furniture' => 'Furnitur', 'building' => 'Bangunan', 'vehicle' => 'Kendaraan', 'other' => 'Lain-lain'] as $k => $lbl)
                    <button wire:click="$set('categoryFilter', '{{ $k }}')" class="px-2.5 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $categoryFilter === $k ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>

            <div class="w-full md:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau kode aset..." class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <!-- Assets Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3.5">Kode & Nama Aset</th>
                            <th class="px-6 py-3.5">Kategori / Lokasi</th>
                            <th class="px-6 py-3.5">Tgl Beli / Umur</th>
                            <th class="px-6 py-3.5">Harga Perolehan</th>
                            <th class="px-6 py-3.5">Akum. Penyusutan</th>
                            <th class="px-6 py-3.5">Nilai Buku</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50/75 transition">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $asset->name }}</div>
                                    <div class="text-xs font-mono text-gray-500">{{ $asset->code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-medium text-gray-800">{{ ucfirst($asset->category) }}</div>
                                    <div class="text-xs text-gray-500">{{ $asset->room ? 'Kamar ' . $asset->room->room_number : 'Umum / Publik' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div>{{ $asset->purchase_date->format('d/m/Y') }}</div>
                                    <div class="text-gray-400">{{ $asset->useful_life_years }} Tahun</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono font-semibold text-gray-900">
                                    Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-rose-600">
                                    Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono font-bold text-emerald-600">
                                    Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                        {{ $asset->status === 'active' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                        {{ $asset->status === 'maintenance' ? 'bg-amber-50 text-amber-700' : '' }}
                                        {{ $asset->status === 'disposed' ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="viewHistory({{ $asset->id }})" class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded transition">
                                        Riwayat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Belum ada aset tetap terdaftar. Klik "Daftarkan Aset Baru" untuk mencatat aset.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assets->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $assets->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: REGISTER ASSET -->
        @if($showRegisterModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Daftarkan Aset Tetap Baru</h3>
                    @error('register_asset') <div class="p-2 bg-rose-50 text-rose-700 text-xs rounded">{{ $message }}</div> @enderror
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Kode Aset</label>
                                <input type="text" wire:model="asset_code" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                @error('asset_code') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Kategori</label>
                                <select wire:model="asset_category" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                    <option value="electronic">Elektronik</option>
                                    <option value="furniture">Furnitur</option>
                                    <option value="building">Bangunan</option>
                                    <option value="vehicle">Kendaraan</option>
                                    <option value="other">Lain-lain</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Nama Aset</label>
                            <input type="text" wire:model="asset_name" placeholder="Misal: AC Daikin 1 PK Kamar 101" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            @error('asset_name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Penempatan Kamar (Opsional)</label>
                                <select wire:model="asset_room_id" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                    <option value="">-- Area Umum / Tanpa Kamar --</option>
                                    @foreach($rooms as $rm)
                                        <option value="{{ $rm->id }}">Kamar {{ $rm->room_number }} ({{ $rm->roomType->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Tanggal Perolehan</label>
                                <input type="date" wire:model="asset_purchase_date" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Harga Beli (Rp)</label>
                                <input type="number" wire:model="asset_purchase_cost" min="1000" step="50000" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Masa Manfaat (Tahun)</label>
                                <input type="number" wire:model="asset_useful_life" min="1" max="50" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Nilai Residu (Rp)</label>
                                <input type="number" wire:model="asset_salvage_value" min="0" step="10000" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Sumber Pembayaran</label>
                                <select wire:model="asset_payment_source" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                    <option value="bank">Bank Operasional</option>
                                    <option value="cash">Kas Front Desk</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Catatan Tambahan</label>
                                <input type="text" wire:model="asset_notes" placeholder="No seri / garansi..." class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showRegisterModal', false)" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="button" wire:click="saveAsset" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700">Simpan & Jurnal Aset</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL: RUN MONTHLY DEPRECIATION -->
        @if($showDepreciationModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Jalankan Penyusutan Bulanan</h3>
                    <p class="text-xs text-gray-500">
                        Sistem akan menghitung beban depresiasi metode garis lurus untuk semua aset aktif dan membukukan jurnal otomatis:
                        <br><span class="font-mono text-indigo-700">Debit 5160 (Beban Penyusutan) / Kredit 1220 (Akum. Penyusutan)</span>.
                    </p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700">Pilih Bulan Periode</label>
                        <input type="date" wire:model="depreciation_period" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" wire:click="$set('showDepreciationModal', false)" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="button" wire:click="runDepreciation" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">Proses Penyusutan</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL: ASSET DEPRECIATION HISTORY -->
        @if($showHistoryModal && $selectedAsset)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl space-y-4 max-h-[90vh] flex flex-col">
                    <div class="flex justify-between items-start pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $selectedAsset->name }}</h3>
                            <p class="text-xs font-mono text-gray-500">{{ $selectedAsset->code }} | Perolehan: Rp {{ number_format($selectedAsset->purchase_cost, 0, ',', '.') }}</p>
                        </div>
                        <button wire:click="$set('showHistoryModal', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-left text-xs text-gray-600">
                            <thead class="bg-gray-50 uppercase font-semibold text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2.5">Periode Bulan</th>
                                    <th class="px-4 py-2.5">Beban Susut</th>
                                    <th class="px-4 py-2.5">Akumulasi Setelahnya</th>
                                    <th class="px-4 py-2.5">No. Jurnal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($selectedAsset->depreciations as $dep)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium">{{ $dep->period_date->format('M Y') }}</td>
                                        <td class="px-4 py-2.5 font-mono text-rose-600 font-semibold">Rp {{ number_format($dep->depreciation_amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2.5 font-mono">Rp {{ number_format($dep->accumulated_after, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2.5 font-mono text-indigo-600">{{ $dep->journalEntry?->entry_number ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada riwayat penyusutan tercatat untuk aset ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
