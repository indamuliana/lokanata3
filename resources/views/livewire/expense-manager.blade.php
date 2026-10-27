<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengeluaran Operasional Hotel</h1>
                <p class="text-sm text-gray-500 mt-1">Pencatatan kas keluar operasional & persetujuan Manager untuk biaya di atas Rp 500.000</p>
            </div>

            <button wire:click="openCreateModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Pengeluaran Baru</span>
            </button>
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

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Kategori:</span>
                @foreach([
                    'all' => 'Semua',
                    'supplies' => 'Perlengkapan',
                    'utilities' => 'Utilitas',
                    'maintenance' => 'Pemeliharaan',
                    'fnb' => 'Dapur / Resto',
                    'transport' => 'Transport',
                    'other' => 'Lain-lain'
                ] as $k => $lbl)
                    <button wire:click="$set('categoryFilter', '{{ $k }}')" class="px-2.5 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $categoryFilter === $k ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Status:</span>
                @foreach([
                    'all' => 'Semua',
                    'approved' => 'Disetujui',
                    'pending_approval' => 'Pending Approval',
                    'rejected' => 'Ditolak'
                ] as $sk => $slbl)
                    <button wire:click="$set('statusFilter', '{{ $sk }}')" class="px-2.5 py-1 text-xs rounded-full font-medium whitespace-nowrap transition {{ $statusFilter === $sk ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $slbl }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Table Expenses -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">No. Bukti & Tanggal</th>
                            <th class="py-3 px-4">Deskripsi</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Nominal</th>
                            <th class="py-3 px-4">Metode</th>
                            <th class="py-3 px-4">Pemohon & Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($expenses as $exp)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 px-4">
                                    <div class="font-mono font-bold text-gray-900">{{ $exp->expense_number }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $exp->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-900 max-w-xs">
                                    {{ $exp->description }}
                                    @if($exp->rejection_reason)
                                        <div class="text-[10px] text-red-600 italic mt-0.5">Alasan tolak: {{ $exp->rejection_reason }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">
                                        {{ $exp->category }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-red-700 text-sm">
                                    {{ rupiah($exp->amount) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $exp->payment_method === 'cash' ? 'bg-amber-50 text-amber-800' : 'bg-blue-50 text-blue-800' }}">
                                        {{ $exp->payment_method === 'cash' ? 'KAS LACI' : 'TRANSFER' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $exp->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $exp->status === 'pending_approval' ? 'bg-amber-100 text-amber-800 animate-pulse' : '' }}
                                        {{ $exp->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $exp->status === 'approved' ? 'DISETUJUI' : ($exp->status === 'pending_approval' ? 'PENDING APPROVAL' : 'DITOLAK') }}
                                    </span>
                                    <div class="text-[10px] text-gray-400 mt-0.5">Oleh: {{ $exp->requester->name }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($exp->status === 'pending_approval' && $isManager)
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button wire:click="approve({{ $exp->id }})" wire:confirm="Setujui pengeluaran ini?" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-semibold transition">
                                                ✓ Setujui
                                            </button>
                                            <button wire:click="openRejectModal({{ $exp->id }})" class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded text-[11px] font-semibold transition">
                                                ✕ Tolak
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    Belum ada data pengeluaran operasional.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $expenses->links() }}
            </div>
        </div>

        <!-- Modal Catat Pengeluaran Baru -->
        @if($showCreateModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">Catat Pengeluaran Operasional</h3>
                        <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    @error('expense')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveExpense" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori Pengeluaran <span class="text-red-500">*</span></label>
                            <select wire:model="category" class="w-full text-xs border-gray-300 rounded-lg">
                                <option value="supplies">Perlengkapan & Amenities Kamar</option>
                                <option value="utilities">Utilitas (Listrik, Air, Internet)</option>
                                <option value="maintenance">Pemeliharaan & Kerusakan</option>
                                <option value="fnb">Dapur & Konsumsi Resto</option>
                                <option value="transport">Transportasi / Shuttle</option>
                                <option value="other">Operasional Lain-lain</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.live="amount" class="w-full text-sm font-bold border-gray-300 rounded-lg text-red-700" required>
                            @error('amount') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                            @if($amount > 500000)
                                <div class="mt-1.5 p-2 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-[11px] font-medium">
                                    ⚠️ Nominal > Rp 500.000 membutuhkan persetujuan (approval) Manager sebelum diakui kas keluar.
                                </div>
                            @elseif($amount > 0)
                                <div class="mt-1.5 p-2 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-[11px] font-medium">
                                    ✓ Nominal &le; Rp 500.000 otomatis disetujui & langsung memotong kas laci/bank.
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Sumber Dana Kas <span class="text-red-500">*</span></label>
                            <select wire:model="payment_method" class="w-full text-xs border-gray-300 rounded-lg">
                                <option value="cash">Kas Fisik Laci Front Desk</option>
                                <option value="bank_transfer">Transfer Bank Operasional</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Keperluan <span class="text-red-500">*</span></label>
                            <textarea wire:model="description" rows="2" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: Beli air galon & sabun cuci tangan resepsionis..." required></textarea>
                            @error('description') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                                Simpan Pengeluaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Tolak Pengeluaran -->
        @if($showRejectModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">Tolak Pengeluaran</h3>
                        <button wire:click="$set('showRejectModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    @error('reject')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveReject" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea wire:model="rejection_reason" rows="3" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Tuliskan alasan penolakan..." required></textarea>
                            @error('rejection_reason') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" wire:click="$set('showRejectModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm">
                                Konfirmasi Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
