<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Penutupan Harian (Night Audit)</h1>
                <p class="text-sm text-gray-500 mt-1">Checklist harian, pengakuan pendapatan menginap (akrual) & pembukuan penutupan tanggal</p>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-600">Tanggal Audit:</label>
                <input type="date" wire:model.live="auditDate" class="text-xs font-bold border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
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

        <!-- Pre-Audit Checklist Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span>📋 Checklist Penutupan Hari:</span>
                    <span class="text-emerald-700">{{ \Carbon\Carbon::parse($auditDate)->isoFormat('dddd, D MMMM Y') }}</span>
                </h3>

                @if($checklist['already_audited'])
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold flex items-center gap-1">
                        ✓ SUDAH DIAUDIT
                    </span>
                @else
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold flex items-center gap-1">
                        ⏳ BELUM DIAUDIT
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <!-- 1. Shift Kasir -->
                <div class="p-4 rounded-xl border {{ $checklist['open_shifts_count'] > 0 ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-200' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-800">1. Status Shift Kasir</span>
                        @if($checklist['open_shifts_count'] > 0)
                            <span class="text-amber-700 font-bold">⚠️ {{ $checklist['open_shifts_count'] }} Terbuka</span>
                        @else
                            <span class="text-emerald-700 font-bold">✓ Bersih</span>
                        @endif
                    </div>
                    <p class="text-gray-500 mt-1">Semua laci kasir shift sebaiknya telah dihitung dan ditutup sebelum audit malam.</p>
                </div>

                <!-- 2. Kamar Menginap Terisi -->
                <div class="p-4 rounded-xl border bg-blue-50 border-blue-200">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-blue-900">2. Kamar Menginap Aktif</span>
                        <span class="font-extrabold text-blue-900 text-sm">{{ $checklist['occupied_rooms_count'] }} Kamar</span>
                    </div>
                    <p class="text-blue-700 mt-1">Pendapatan 1 malam inap akan otomatis diakui ke akun Pendapatan Kamar & PB1.</p>
                </div>

                <!-- 3. Estimasi Pendapatan Akrual -->
                <div class="p-4 rounded-xl border bg-gray-50 border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-800">3. Est. Pendapatan Kamar</span>
                        <span class="font-bold text-emerald-700 text-sm">{{ rupiah($checklist['estimated_room_revenue']) }}</span>
                    </div>
                    <p class="text-gray-500 mt-1">Nilai sewa kamar harian yang akan dibukukan ke jurnal akuntansi berpasangan.</p>
                </div>
            </div>

            <!-- In-House Guests Preview -->
            @if($checklist['in_house_reservations']->isNotEmpty())
                <div class="space-y-2 pt-2">
                    <span class="text-xs font-bold text-gray-700 block">Daftar Kamar In-House yang Akan Diakui Pendapatannya:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        @foreach($checklist['in_house_reservations'] as $ih)
                            <div class="p-2.5 bg-gray-50 rounded-lg border border-gray-200 flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-gray-900">{{ $ih->customer->name }}</span>
                                    <span class="text-gray-500">({{ $ih->roomType->name }})</span>
                                    <span class="text-[10px] text-gray-400 block">Reservasi #{{ $ih->reservation_number }}</span>
                                </div>
                                <span class="font-bold text-emerald-700">
                                    {{ rupiah((int) round($ih->subtotal / max(1, $ih->nights_count))) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Action Audit -->
            @if(!$checklist['already_audited'])
                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="w-full sm:w-auto">
                        <input type="text" wire:model="notes" placeholder="Catatan audit malam (opsional)..." class="text-xs border-gray-300 rounded-lg w-full sm:w-80">
                    </div>

                    <button wire:click="executeAudit" wire:confirm="Yakin ingin menjalankan Night Audit untuk tanggal {{ $auditDate }}? Tindakan ini akan membukukan jurnal pengakuan pendapatan kamar." class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Jalankan Night Audit Tanggal Ini</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Past Audits Table -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-bold text-sm text-gray-900">Riwayat Penutupan Harian (Night Audit)</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Tanggal Audit</th>
                            <th class="py-3 px-4">Petugas Auditor</th>
                            <th class="py-3 px-4">Waktu Eksekusi</th>
                            <th class="py-3 px-4">Kamar Terisi</th>
                            <th class="py-3 px-4">Pendapatan Kamar</th>
                            <th class="py-3 px-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pastAudits as $pa)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 px-4 font-bold text-gray-900">
                                    {{ $pa->audit_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    {{ $pa->auditor->name }}
                                </td>
                                <td class="py-3 px-4 text-gray-500">
                                    {{ $pa->performed_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-blue-800">
                                    {{ $pa->rooms_occupied_count }} Kamar
                                </td>
                                <td class="py-3 px-4 font-bold text-emerald-700">
                                    {{ rupiah($pa->total_room_revenue) }}
                                </td>
                                <td class="py-3 px-4 text-gray-500">
                                    {{ $pa->notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400">
                                    Belum ada riwayat penutupan harian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $pastAudits->links() }}
            </div>
        </div>
    </div>
</div>
