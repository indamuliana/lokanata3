<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kasir Shift Front Desk</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola modal laci, penerimaan tunai, pengeluaran shift & rekonsiliasi kas fisik</p>
            </div>

            <div>
                @if(!$activeShift)
                    <button wire:click="openNewShiftModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Buka Shift Baru</span>
                    </button>
                @else
                    <button wire:click="openCloseShiftModal" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Tutup Shift Kasir</span>
                    </button>
                @endif
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

        <!-- Active Shift Real-Time Card -->
        @if($activeShift && $activeSummary)
            <div class="bg-white rounded-2xl border-2 border-emerald-600 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="text-base font-bold text-gray-900">Shift Aktif: {{ $activeShift->shift_name }}</h3>
                        <span class="text-xs text-gray-500">· Dibuka: {{ $activeShift->opened_at->format('H:i, d M Y') }}</span>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold uppercase">
                        BERJALAN
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <span class="text-xs text-gray-500 block">Modal Awal Laci:</span>
                        <span class="text-lg font-bold text-gray-800">{{ rupiah($activeSummary['starting_cash']) }}</span>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                        <span class="text-xs text-emerald-800 block">Total Kas Masuk (+):</span>
                        <span class="text-lg font-bold text-emerald-800">{{ rupiah($activeSummary['total_cash_in']) }}</span>
                        <span class="text-[10px] text-emerald-600 block mt-0.5">{{ $activeSummary['payments_count'] }} transaksi pembayaran</span>
                    </div>

                    <div class="p-3 bg-red-50 rounded-xl border border-red-200">
                        <span class="text-xs text-red-800 block">Biaya / Kas Keluar (-):</span>
                        <span class="text-lg font-bold text-red-800">{{ rupiah($activeSummary['total_cash_out'] + $activeSummary['total_cash_refund']) }}</span>
                        <span class="text-[10px] text-red-600 block mt-0.5">{{ $activeSummary['expenses_count'] }} pengeluaran disetujui</span>
                    </div>

                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                        <span class="text-xs text-blue-800 block font-semibold">Kas Seharusnya di Laci:</span>
                        <span class="text-xl font-extrabold text-blue-900">{{ rupiah($activeSummary['expected_cash']) }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-amber-900 text-sm">Belum Ada Shift Kasir yang Dibuka</h3>
                    <p class="text-xs text-amber-700 mt-0.5">Buka shift kasir terlebih dahulu dengan memasukkan modal awal laci sebelum mencatat transaksi tunai.</p>
                </div>
                <button wire:click="openNewShiftModal" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition">
                    Buka Shift Sekarang
                </button>
            </div>
        @endif

        <!-- Riwayat Shift Kasir -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-gray-900">Riwayat Shift Kasir</h3>
                <span class="text-xs text-gray-400">Pencatatan selisih kas fisik (*cash variance*)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Shift & Petugas</th>
                            <th class="py-3 px-4">Waktu Buka / Tutup</th>
                            <th class="py-3 px-4">Modal Awal</th>
                            <th class="py-3 px-4">Ekspektasi Kas</th>
                            <th class="py-3 px-4">Kas Fisik Aktual</th>
                            <th class="py-3 px-4">Selisih (*Variance*)</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pastShifts as $ps)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900">{{ $ps->shift_name }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $ps->user->name }}</div>
                                </td>
                                <td class="py-3 px-4 text-gray-600">
                                    <div>Buka: {{ $ps->opened_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-[11px] text-gray-400">
                                        Tutup: {{ $ps->closed_at ? $ps->closed_at->format('d/m/Y H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-800">
                                    {{ rupiah($ps->starting_cash) }}
                                </td>
                                <td class="py-3 px-4 font-bold text-gray-900">
                                    {{ rupiah($ps->expected_cash) }}
                                </td>
                                <td class="py-3 px-4 font-bold text-gray-900">
                                    {{ $ps->actual_cash !== null ? rupiah($ps->actual_cash) : '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($ps->status === 'closed')
                                        @if($ps->difference === 0)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                PAS (Rp 0)
                                            </span>
                                        @elseif($ps->difference > 0)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                                LEBIH (+{{ rupiah($ps->difference) }})
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">
                                                KURANG ({{ rupiah($ps->difference) }})
                                            </span>
                                        @endif
                                        @if($ps->difference_reason)
                                            <div class="text-[10px] text-gray-400 mt-0.5 italic">"{{ $ps->difference_reason }}"</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">Berjalan...</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $ps->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $ps->status === 'open' ? 'BUKA' : 'DITUTUP' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    Belum ada catatan shift kasir.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $pastShifts->links() }}
            </div>
        </div>

        <!-- Modal Buka Shift -->
        @if($showOpenModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">Buka Shift Kasir Baru</h3>
                        <button wire:click="$set('showOpenModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    @error('open_shift')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="saveOpenShift" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama / Sesi Shift <span class="text-red-500">*</span></label>
                            <select wire:model="shift_name" class="w-full text-xs border-gray-300 rounded-lg">
                                <option value="Shift 1 (Pagi)">Shift 1 (Pagi - 07:00 s.d. 15:00)</option>
                                <option value="Shift 2 (Siang/Sore)">Shift 2 (Siang - 15:00 s.d. 23:00)</option>
                                <option value="Shift 3 (Malam)">Shift 3 (Malam - 23:00 s.d. 07:00)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Modal Awal Kas Fisik di Laci (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="starting_cash" class="w-full text-sm font-bold border-gray-300 rounded-lg text-emerald-800" required>
                            <span class="text-[10px] text-gray-400 block mt-0.5">Uang kembalian yang tersedia di laci kasir saat pergantian shift.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Buka Shift (Opsional)</label>
                            <input type="text" wire:model="open_notes" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Contoh: Serah terima dari staf shift sebelumnya">
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" wire:click="$set('showOpenModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                                Konfirmasi Buka Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Tutup Shift -->
        @if($showCloseModal && $activeShift)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold text-gray-900">Tutup Shift Kasir (Serah Terima)</h3>
                        <button wire:click="$set('showCloseModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    @error('close_shift')
                        <div class="p-3 bg-red-50 text-red-700 text-xs rounded-lg border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    @if($activeSummary)
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="text-blue-700">Modal Awal Laci:</span>
                                <span class="font-semibold text-blue-900">{{ rupiah($activeSummary['starting_cash']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Total Kas Masuk:</span>
                                <span class="font-semibold text-emerald-700">+{{ rupiah($activeSummary['total_cash_in']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Kas Keluar / Biaya:</span>
                                <span class="font-semibold text-red-700">-{{ rupiah($activeSummary['total_cash_out'] + $activeSummary['total_cash_refund']) }}</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-blue-200 font-bold text-sm">
                                <span class="text-blue-900">Ekspektasi Uang Laci:</span>
                                <span class="text-blue-900">{{ rupiah($activeSummary['expected_cash']) }}</span>
                            </div>
                        </div>
                    @endif

                    <form wire:submit="saveCloseShift" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Hitungan Kas Fisik Aktual di Laci (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.live="actual_cash" class="w-full text-sm font-bold border-gray-300 rounded-lg text-emerald-800" required>
                            @error('actual_cash') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        @if($activeSummary && $actual_cash !== $activeSummary['expected_cash'])
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-amber-900">Terdeteksi Selisih Kas:</span>
                                    <span class="font-bold {{ $actual_cash > $activeSummary['expected_cash'] ? 'text-blue-800' : 'text-red-800' }}">
                                        {{ $actual_cash > $activeSummary['expected_cash'] ? '+' : '' }}{{ rupiah($actual_cash - $activeSummary['expected_cash']) }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">Alasan / Penjelasan Selisih <span class="text-red-500">*</span></label>
                                    <textarea wire:model="difference_reason" rows="2" class="w-full text-xs border-gray-300 rounded-lg" placeholder="Jelaskan penyebab selisih kas..." required></textarea>
                                    @error('difference_reason') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 pt-3 border-t">
                            <button type="button" wire:click="$set('showCloseModal', false)" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm">
                                Konfirmasi Tutup Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
