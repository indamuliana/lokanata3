<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Kop Surat Landscape untuk Cetak Print Laporan -->
        @php
            $kopLandscape = \App\Models\HotelSetting::get('letterhead_landscape_path');
            $hotelName = \App\Models\HotelSetting::get('hotel_name', 'InSitu Hotel Syariah Garut');
            $hotelAddress = \App\Models\HotelSetting::get('hotel_address', 'Jl. Situ Indah No. 1, Garut, Jawa Barat');
        @endphp
        @if($kopLandscape && file_exists(public_path('storage/' . $kopLandscape)))
            <div class="hidden print:block text-center mb-4 border-b-2 border-emerald-700 pb-2">
                <img src="{{ asset('storage/' . $kopLandscape) }}" class="w-full max-h-28 object-contain">
            </div>
        @else
            <div class="hidden print:block text-center mb-4 border-b-2 border-emerald-700 pb-2">
                <h2 class="text-xl font-bold uppercase tracking-wider text-emerald-800">{{ $hotelName }}</h2>
                <p class="text-xs text-gray-600">{{ $hotelAddress }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan dan Analitik Manajemen Hotel</h1>
                <p class="text-sm text-gray-500 mt-1">Laporan kinerja operasional (Okupansi, ADR, RevPAR) dan laporan keuangan akrual resmi InSitu Hotel Syariah Garut.</p>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>

        <!-- Filter & Period Controls -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto">
                <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">Rentang Cepat:</span>
                <button wire:click="setPeriod('today')" class="px-3 py-1.5 text-xs rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Hari Ini</button>
                <button wire:click="setPeriod('this_month')" class="px-3 py-1.5 text-xs rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Bulan Ini</button>
                <button wire:click="setPeriod('this_year')" class="px-3 py-1.5 text-xs rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Tahun Ini</button>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-500">Dari:</span>
                    <input type="date" wire:model.live="start_date" class="text-xs py-1.5 rounded-lg border-gray-300">
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-gray-500">Sampai:</span>
                    <input type="date" wire:model.live="end_date" class="text-xs py-1.5 rounded-lg border-gray-300">
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6">
                <button wire:click="$set('activeTab', 'operational')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'operational' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Kinerja Operasional (Okupansi & RevPAR)
                </button>
                <button wire:click="$set('activeTab', 'income_statement')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'income_statement' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Laba Rugi (Income Statement)
                </button>
                <button wire:click="$set('activeTab', 'trial_balance')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'trial_balance' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Neraca Saldo (Trial Balance)
                </button>
                <button wire:click="$set('activeTab', 'balance_sheet')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'balance_sheet' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Neraca Keuangan (Balance Sheet)
                </button>
                <button wire:click="$set('activeTab', 'history')" class="pb-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'history' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Riwayat Pesanan Terpadu (Kamar, Meeting & Dining)
                </button>
            </nav>
        </div>

        <!-- TAB 1: OPERATIONAL -->
        @if($activeTab === 'operational')
            <div class="space-y-6">
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tingkat Okupansi</span>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $operational['occupancy_rate'] }}%</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $operational['occupied_room_nights'] }} dari {{ $operational['total_available_room_nights'] }} room-nights</p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Average Daily Rate (ADR)</span>
                        <p class="text-3xl font-extrabold text-blue-600 mt-2">Rp {{ number_format($operational['adr'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Rata-rata pendapatan per malam kamar terjual</p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">RevPAR</span>
                        <p class="text-3xl font-extrabold text-indigo-600 mt-2">Rp {{ number_format($operational['rev_par'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Pendapatan per kamar tersedia (20 kamar)</p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pendapatan Kamar</span>
                        <p class="text-3xl font-extrabold text-gray-900 mt-2">Rp {{ number_format($operational['total_room_revenue'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Periode {{ $operational['days_in_period'] }} hari</p>
                    </div>
                </div>

                <!-- Channel Distribution -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Sebaran Kanal Pemesanan Tamu (Booking Sources)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach([
                            'walk_in' => ['name' => 'Walk-in (Langsung)', 'color' => 'bg-emerald-500'],
                            'ota' => ['name' => 'Online Travel Agent (OTA)', 'color' => 'bg-blue-500'],
                            'whatsapp' => ['name' => 'WhatsApp Manual', 'color' => 'bg-green-500'],
                            'website' => ['name' => 'Website Resmi Hotel', 'color' => 'bg-purple-500'],
                        ] as $key => $meta)
                            @php
                                $cnt = $operational['channels'][$key] ?? 0;
                                $pct = $operational['total_bookings'] > 0 ? round(($cnt / $operational['total_bookings']) * 100, 1) : 0;
                            @endphp
                            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-600">{{ $meta['name'] }}</span>
                                    <span class="text-xs font-bold text-gray-900">{{ $cnt }} booking</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $meta['color'] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-500">{{ $pct }}% dari total</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 2: INCOME STATEMENT -->
        @if($activeTab === 'income_statement')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="flex justify-between items-center pb-4 border-b">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Laporan Laba Rugi Operasional</h2>
                        <p class="text-xs text-gray-500">Periode: {{ Carbon\Carbon::parse($start_date)->format('d M Y') }} s/d {{ Carbon\Carbon::parse($end_date)->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-gray-500 block">Laba Bersih Periode Ini</span>
                        <span class="text-2xl font-bold {{ $incomeStatement['net_profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            Rp {{ number_format($incomeStatement['net_profit'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Revenues -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b-2 border-emerald-500">
                            <h3 class="text-sm font-bold text-gray-900 uppercase">Pendapatan (Revenues)</h3>
                            <span class="font-bold text-sm text-emerald-600 font-mono">Rp {{ number_format($incomeStatement['total_revenue'], 0, ',', '.') }}</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            @forelse($incomeStatement['revenues'] as $rev)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-gray-600">{{ $rev['code'] }} - {{ $rev['name'] }}</span>
                                    <span class="font-mono font-medium text-gray-900">Rp {{ number_format($rev['amount'], 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="text-xs text-gray-400 italic">Belum ada pendapatan dibukukan dalam periode ini.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Expenses -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b-2 border-rose-500">
                            <h3 class="text-sm font-bold text-gray-900 uppercase">Beban Usaha (Expenses)</h3>
                            <span class="font-bold text-sm text-rose-600 font-mono">Rp {{ number_format($incomeStatement['total_expense'], 0, ',', '.') }}</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            @forelse($incomeStatement['expenses'] as $exp)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-gray-600">{{ $exp['code'] }} - {{ $exp['name'] }}</span>
                                    <span class="font-mono font-medium text-gray-900">Rp {{ number_format($exp['amount'], 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="text-xs text-gray-400 italic">Belum ada beban operasional dibukukan dalam periode ini.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 3: TRIAL BALANCE -->
        @if($activeTab === 'trial_balance')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Neraca Saldo (Trial Balance) per {{ Carbon\Carbon::parse($asOfDate ?? $end_date)->format('d F Y') }}</h3>
                        <p class="text-xs text-gray-500">Keseimbangan saldo seluruh akun buku besar aktif.</p>
                    </div>
                    <div>
                        @if($trialBalance['is_balanced'])
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full flex items-center gap-1">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Seimbang (Balanced)
                            </span>
                        @else
                            <span class="px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">
                                Tidak Seimbang (Periksa Jurnal)
                            </span>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-100 text-xs uppercase font-semibold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3">Kode Akun</th>
                                <th class="px-6 py-3">Nama Akun</th>
                                <th class="px-6 py-3">Kategori</th>
                                <th class="px-6 py-3 text-right">Debit (Rp)</th>
                                <th class="px-6 py-3 text-right">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($trialBalance['lines'] as $line)
                                <tr class="hover:bg-gray-50/75">
                                    <td class="px-6 py-3 font-mono text-xs font-semibold text-gray-900">{{ $line['code'] }}</td>
                                    <td class="px-6 py-3 text-gray-800 font-medium">{{ $line['name'] }}</td>
                                    <td class="px-6 py-3 text-xs uppercase text-gray-500">{{ $line['type'] }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-xs {{ $line['debit_balance'] > 0 ? 'text-gray-900 font-semibold' : 'text-gray-400' }}">
                                        {{ $line['debit_balance'] > 0 ? number_format($line['debit_balance'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-mono text-xs {{ $line['credit_balance'] > 0 ? 'text-gray-900 font-semibold' : 'text-gray-400' }}">
                                        {{ $line['credit_balance'] > 0 ? number_format($line['credit_balance'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-bold border-t-2 border-gray-300">
                            <tr>
                                <td colspan="3" class="px-6 py-3.5 text-right uppercase text-xs text-gray-700">Total Neraca Saldo:</td>
                                <td class="px-6 py-3.5 text-right font-mono text-sm text-gray-900">
                                    Rp {{ number_format($trialBalance['total_debit'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3.5 text-right font-mono text-sm text-gray-900">
                                    Rp {{ number_format($trialBalance['total_credit'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 4: BALANCE SHEET -->
        @if($activeTab === 'balance_sheet')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="flex justify-between items-center pb-4 border-b">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Neraca Keuangan (Balance Sheet)</h2>
                        <p class="text-xs text-gray-500">Per Tanggal: {{ Carbon\Carbon::parse($end_date)->format('d F Y') }}</p>
                    </div>
                    <div>
                        @if($balanceSheet['is_balanced'])
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">
                                Aset = Kewajiban + Ekuitas
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Aktiva / Assets -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b-2 border-blue-500">
                            <h3 class="text-sm font-bold text-gray-900 uppercase">Aktiva (Assets)</h3>
                            <span class="font-bold text-sm text-blue-600 font-mono">Rp {{ number_format($balanceSheet['total_assets'], 0, ',', '.') }}</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            @foreach($balanceSheet['assets'] as $a)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-gray-600">{{ $a['code'] }} - {{ $a['name'] }}</span>
                                    <span class="font-mono font-medium text-gray-900">Rp {{ number_format($a['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pasiva (Liabilities + Equities) -->
                    <div class="space-y-6">
                        <!-- Liabilities -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b-2 border-amber-500">
                                <h3 class="text-sm font-bold text-gray-900 uppercase">Kewajiban (Liabilities)</h3>
                                <span class="font-bold text-sm text-amber-600 font-mono">Rp {{ number_format($balanceSheet['total_liabilities'], 0, ',', '.') }}</span>
                            </div>
                            <div class="space-y-2 text-sm">
                                @forelse($balanceSheet['liabilities'] as $liab)
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-600">{{ $liab['code'] }} - {{ $liab['name'] }}</span>
                                        <span class="font-mono font-medium text-gray-900">Rp {{ number_format($liab['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <div class="text-xs text-gray-400 italic">Tidak ada saldo kewajiban saat ini.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Equities -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b-2 border-purple-500">
                                <h3 class="text-sm font-bold text-gray-900 uppercase">Ekuitas (Equity)</h3>
                                <span class="font-bold text-sm text-purple-600 font-mono">Rp {{ number_format($balanceSheet['total_equities'], 0, ',', '.') }}</span>
                            </div>
                            <div class="space-y-2 text-sm">
                                @foreach($balanceSheet['equities'] as $eq)
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-gray-600">{{ $eq['code'] }} - {{ $eq['name'] }}</span>
                                        <span class="font-mono font-medium text-gray-900">Rp {{ number_format($eq['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-lg flex justify-between items-center border border-gray-200">
                            <span class="text-xs font-bold uppercase text-gray-700">Total Pasiva (Kewajiban + Ekuitas):</span>
                            <span class="font-bold font-mono text-sm text-gray-900">Rp {{ number_format($balanceSheet['total_liabilities_and_equity'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 5: RIWAYAT PESANAN TERPADU & PENJUALAN -->
        @if($activeTab === 'history')
            <div class="space-y-6">
                <!-- Ringkasan Omzet Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Omzet Kamar Hotel</span>
                        <p class="text-2xl font-black text-emerald-600 mt-2">{{ rupiah($roomRevenue) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Periode terpilih</p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Omzet Ruang Meeting</span>
                        <p class="text-2xl font-black text-amber-600 mt-2">{{ rupiah($meetingRevenue) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Periode terpilih</p>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Omzet Fine Dining</span>
                        <p class="text-2xl font-black text-teal-600 mt-2">{{ rupiah($diningRevenue) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Periode terpilih</p>
                    </div>

                    <div class="bg-emerald-900 text-white p-5 rounded-xl shadow-md">
                        <span class="text-xs font-semibold text-emerald-300 uppercase tracking-wider">Grand Total Omzet</span>
                        <p class="text-2xl font-black text-white mt-2">{{ rupiah($grandTotalRevenue) }}</p>
                        <p class="text-xs text-emerald-200 mt-1">Kamar + Meeting + Dining</p>
                    </div>
                </div>

                <!-- Filter Unit & Action Ekspor Bar -->
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <div class="w-full sm:w-48">
                            <select wire:model.live="historyUnitFilter" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="all">🏢 Semua Unit Layanan</option>
                                <option value="room">🛏️ Kamar Hotel Saja</option>
                                <option value="meeting">🏢 Ruang Meeting Saja</option>
                                <option value="dining">🍽️ Fine Dining Saja</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-64">
                            <input type="text" wire:model.live.debounce.300ms="historySearch" placeholder="Cari kode booking, nama tamu..." class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <!-- Tombol Ekspor Excel & PDF -->
                    <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                        <a href="{{ route('reports.export.csv', ['unit' => $historyUnitFilter, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs inline-flex items-center gap-2 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Unduh Excel (CSV)</span>
                        </a>

                        <a href="{{ route('reports.export.pdf', ['unit' => $historyUnitFilter, 'start_date' => $start_date, 'end_date' => $end_date]) }}" target="_blank" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold shadow-xs inline-flex items-center gap-2 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span>Unduh PDF Resmi</span>
                        </a>
                    </div>
                </div>

                <!-- Tabel Preview Transaksi Riwayat Terpadu -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Daftar Transaksi Pesanan Terakhir</span>
                        <span class="text-xs text-gray-500">Menampilkan hingga 60 transaksi per tinjauan</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                            <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th class="py-3 px-4">Unit</th>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">No. Transaksi</th>
                                    <th class="py-3 px-4">Tamu / Pelanggan</th>
                                    <th class="py-3 px-4">Rincian</th>
                                    <th class="py-3 px-4">Total</th>
                                    <th class="py-3 px-4">Terbayar</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($historyItems as $item)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if($item['unit_code'] === 'ROOM')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Kamar</span>
                                            @elseif($item['unit_code'] === 'MEETING')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Meeting</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Dining</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">{{ $item['date'] }}</td>
                                        <td class="py-3 px-4 font-mono font-bold text-gray-900">{{ $item['code'] }}</td>
                                        <td class="py-3 px-4 font-medium text-gray-900">{{ $item['customer'] }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ $item['detail'] }}</td>
                                        <td class="py-3 px-4 font-bold text-gray-900">{{ rupiah($item['total']) }}</td>
                                        <td class="py-3 px-4 text-emerald-700 font-semibold">{{ rupiah($item['paid']) }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                                {{ in_array($item['status'], ['checked_out', 'completed']) ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ in_array($item['status'], ['confirmed', 'checked_in', 'seated']) ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ in_array($item['status'], ['cancelled', 'expired']) ? 'bg-red-100 text-red-800' : '' }}
                                                {{ in_array($item['status'], ['pending']) ? 'bg-amber-100 text-amber-800' : '' }}">
                                                {{ str_replace('_', ' ', $item['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-gray-400">
                                            Tidak ada data transaksi pada rentang periode dan filter ini.
                                        </td>
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
