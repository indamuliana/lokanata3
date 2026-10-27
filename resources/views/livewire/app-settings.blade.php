<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengaturan Aplikasi & Hotel</h1>
                <p class="text-sm text-gray-500 mt-1">Konfigurasi identitas hotel, nomor WhatsApp, multi-rekening bank, tarif pajak, logo, kop surat, dan kalender High Season</p>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-medium flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm font-medium flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tab Navigasi Pengaturan -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-1.5 flex flex-wrap gap-1">
            <button wire:click="$set('activeTab', 'identity')" class="px-4 py-2.5 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'identity' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>🏢</span>
                <span>Identitas & Kontak</span>
            </button>
            <button wire:click="$set('activeTab', 'bank')" class="px-4 py-2.5 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'bank' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>💳</span>
                <span>Rekening Bank Resmi ({{ count($bank_accounts) }})</span>
            </button>
            <button wire:click="$set('activeTab', 'tax')" class="px-4 py-2.5 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'tax' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>📊</span>
                <span>Tarif Pajak & Layanan</span>
            </button>
            <button wire:click="$set('activeTab', 'branding')" class="px-4 py-2.5 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'branding' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>🖼️</span>
                <span>Logo & Kop Surat</span>
            </button>
            <button wire:click="$set('activeTab', 'season')" class="px-4 py-2.5 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'season' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>📅</span>
                <span>Kalender Libur & Musim ({{ count($holidays) }})</span>
            </button>
        </div>

        <!-- TAB 1: IDENTITAS & KONTAK HOTEL -->
        @if($activeTab === 'identity')
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="border-b border-gray-100 pb-3">
                    <h2 class="text-base font-bold text-gray-900">Identitas Properti & Kontak Resmi</h2>
                    <p class="text-xs text-gray-500">Informasi ini akan tertera pada seluruh surat, invoice, dan komunikasi WhatsApp resmi.</p>
                </div>

                <form wire:submit="saveIdentity" class="space-y-4 max-w-3xl">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Resmi Hotel <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="hotel_name" class="w-full text-sm font-semibold border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: InSitu Hotel Syariah Garut" required>
                            @error('hotel_name') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Slogan / Tagline Hotel</label>
                            <input type="text" wire:model="hotel_tagline" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: Hotel Syariah Nyaman dan Berkah di Garut">
                            @error('hotel_tagline') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Lengkap Hotel <span class="text-red-500">*</span></label>
                            <textarea wire:model="hotel_address" rows="2" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Alamat lengkap properti..." required></textarea>
                            @error('hotel_address') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Telepon Front Desk <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="hotel_phone" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: 0262-123456" required>
                            @error('hotel_phone') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp Resmi (Awali dengan 62) <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="hotel_whatsapp" class="w-full text-sm font-semibold text-emerald-800 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: 628123456789" required>
                            <span class="text-[10px] text-gray-400">Digunakan untuk tombol Click-to-Chat otomatis bagi staf dan tamu.</span>
                            @error('hotel_whatsapp') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email Resmi Hotel <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="hotel_email" class="w-full text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: info@insituhotel.id" required>
                            @error('hotel_email') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                            Simpan Identitas & Kontak
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- TAB 2: REKENING BANK RESMI -->
        @if($activeTab === 'bank')
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="border-b border-gray-100 pb-3">
                    <h2 class="text-base font-bold text-gray-900">Rekening Transfer Resmi Hotel</h2>
                    <p class="text-xs text-gray-500">Daftar rekening bank resmi yang akan dicantumkan pada Invoice Kamar, Kwitansi Meeting, dan Dining.</p>
                </div>

                <!-- Form Tambah Rekening -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">➕ Tambah Rekening Baru</h3>
                    <form wire:submit="addBankAccount" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Nama Bank / Metode <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="new_bank_name" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: Bank Syariah Indonesia (BSI)" required>
                            @error('new_bank_name') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Nomor Rekening <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="new_account_number" class="w-full text-xs font-mono font-bold border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: 7123456789" required>
                            @error('new_account_number') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Atas Nama (Pemilik) <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="new_account_holder" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: PT Insitu Hotel Syariah" required>
                            @error('new_account_holder') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                Tambahkan Rekening
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Rekening Aktif -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 font-semibold uppercase tracking-wider text-[10px]">
                                <th class="py-2.5 px-3">Bank / Metode</th>
                                <th class="py-2.5 px-3">Nomor Rekening</th>
                                <th class="py-2.5 px-3">Atas Nama</th>
                                <th class="py-2.5 px-3 text-center">Status Tampil</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($bank_accounts as $index => $acc)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-2.5 px-3 font-bold text-gray-900">{{ $acc['bank_name'] }}</td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-emerald-800">{{ $acc['account_number'] }}</td>
                                    <td class="py-2.5 px-3 text-gray-700">{{ $acc['account_holder'] }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <button type="button" wire:click="toggleBankStatus({{ $index }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ ($acc['is_active'] ?? true) ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-200 text-gray-600 hover:bg-gray-300' }}">
                                            {{ ($acc['is_active'] ?? true) ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <button type="button" wire:click="removeBankAccount({{ $index }})" wire:confirm="Hapus rekening {{ $acc['bank_name'] }} - {{ $acc['account_number'] }}?" class="text-red-500 hover:text-red-700 font-bold text-xs">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        Belum ada rekening transfer yang didaftarkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 3: TARIF PAJAK & LAYANAN -->
        @if($activeTab === 'tax')
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="border-b border-gray-100 pb-3">
                    <h2 class="text-base font-bold text-gray-900">Tarif Pajak (PB1) & Biaya Layanan</h2>
                    <p class="text-xs text-gray-500">Persentase ini menjadi acuan kalkulasi pajak standar untuk setiap reservasi kamar, sewa meeting, dan pesanan dining baru.</p>
                </div>

                <form wire:submit="saveTaxSettings" class="space-y-4 max-w-xl">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Pajak Daerah / PB1 (%) <span class="text-red-500">*</span></label>
                            <div class="relative rounded-lg shadow-sm">
                                <input type="number" wire:model="tax_percentage" min="0" max="100" class="w-full text-sm font-bold text-emerald-800 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 pr-10" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500 font-bold text-sm">%</div>
                            </div>
                            <span class="text-[11px] text-gray-400 mt-1 block">Tarif standar umumnya adalah 10% untuk Pajak Hotel & Restoran.</span>
                            @error('tax_percentage') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Biaya Layanan / Service Charge (%)</label>
                            <div class="relative rounded-lg shadow-sm">
                                <input type="number" wire:model="service_charge_percentage" min="0" max="100" class="w-full text-sm font-bold border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 pr-10">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500 font-bold text-sm">%</div>
                            </div>
                            <span class="text-[11px] text-gray-400 mt-1 block">Isi 0 jika tidak ada pungutan service charge tambahan.</span>
                            @error('service_charge_percentage') <span class="text-[11px] text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                            Simpan Pengaturan Pajak
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- TAB 4: LOGO & KOP SURAT BERKAS -->
        @if($activeTab === 'branding')
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-6">
                <div class="border-b border-gray-100 pb-3">
                    <h2 class="text-base font-bold text-gray-900">Logo & Kop Surat Resmi Properti</h2>
                    <p class="text-xs text-gray-500">Unggah berkas gambar kop surat resmi (format JPG / PNG) yang akan otomatis muncul di kwitansi, invoice, serta laporan.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. Logo Utama Hotel -->
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col justify-between space-y-4 bg-gray-50/50">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-bold text-gray-900 uppercase">1. Logo Utama Hotel</h3>
                                <span class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-800 font-semibold rounded">PNG / JPG</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mb-3">Tampil pada bilah navigasi website, header dokumen, dan aplikasi publik.</p>

                            <div class="h-32 bg-white rounded-lg border border-dashed border-gray-300 flex items-center justify-center overflow-hidden p-2">
                                @if($logo_file)
                                    <img src="{{ $logo_file->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                                @elseif($hotel_logo_path)
                                    <img src="{{ asset('storage/' . $hotel_logo_path) }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <span class="text-xs text-gray-400">Belum ada logo terunggah</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <input type="file" wire:model="logo_file" accept="image/png,image/jpeg,image/svg+xml" class="text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                                @if($logo_file)
                                    <button type="button" wire:click="uploadLogo" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                        Simpan
                                    </button>
                                @endif
                            </div>
                            @if($hotel_logo_path)
                                <button type="button" wire:click="removeLogo" wire:confirm="Hapus logo hotel?" class="text-[11px] text-red-600 hover:underline">
                                    Hapus Logo
                                </button>
                            @endif
                            @error('logo_file') <span class="text-[10px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- 2. Kop Surat Portrait (A4 Tegak) -->
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col justify-between space-y-4 bg-gray-50/50">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-bold text-gray-900 uppercase">2. Kop Surat Portrait (A4)</h3>
                                <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-800 font-semibold rounded">Kwitansi & Invoice PDF</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mb-3">Header atas untuk Invoice Kamar, Kwitansi Meeting & Dining format tegak.</p>

                            <div class="h-32 bg-white rounded-lg border border-dashed border-gray-300 flex items-center justify-center overflow-hidden p-2">
                                @if($letterhead_portrait_file)
                                    <img src="{{ $letterhead_portrait_file->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                                @elseif($letterhead_portrait_path)
                                    <img src="{{ asset('storage/' . $letterhead_portrait_path) }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <span class="text-xs text-gray-400">Belum ada kop surat portrait</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <input type="file" wire:model="letterhead_portrait_file" accept="image/png,image/jpeg" class="text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                                @if($letterhead_portrait_file)
                                    <button type="button" wire:click="uploadLetterheadPortrait" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                        Simpan
                                    </button>
                                @endif
                            </div>
                            @if($letterhead_portrait_path)
                                <button type="button" wire:click="removeLetterheadPortrait" wire:confirm="Hapus kop portrait?" class="text-[11px] text-red-600 hover:underline">
                                    Hapus Kop Portrait
                                </button>
                            @endif
                            @error('letterhead_portrait_file') <span class="text-[10px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- 3. Kop Surat Landscape (A4 Mendatar) -->
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col justify-between space-y-4 bg-gray-50/50">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-bold text-gray-900 uppercase">3. Kop Surat Landscape (A4)</h3>
                                <span class="text-[10px] px-2 py-0.5 bg-purple-100 text-purple-800 font-semibold rounded">Laporan & Audit PDF</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mb-3">Header atas untuk Cetak Laporan Keuangan, Jurnal, dan Ringkasan Okupansi.</p>

                            <div class="h-32 bg-white rounded-lg border border-dashed border-gray-300 flex items-center justify-center overflow-hidden p-2">
                                @if($letterhead_landscape_file)
                                    <img src="{{ $letterhead_landscape_file->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                                @elseif($letterhead_landscape_path)
                                    <img src="{{ asset('storage/' . $letterhead_landscape_path) }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <span class="text-xs text-gray-400">Belum ada kop surat landscape</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <input type="file" wire:model="letterhead_landscape_file" accept="image/png,image/jpeg" class="text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                                @if($letterhead_landscape_file)
                                    <button type="button" wire:click="uploadLetterheadLandscape" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                        Simpan
                                    </button>
                                @endif
                            </div>
                            @if($letterhead_landscape_path)
                                <button type="button" wire:click="removeLetterheadLandscape" wire:confirm="Hapus kop landscape?" class="text-[11px] text-red-600 hover:underline">
                                    Hapus Kop Landscape
                                </button>
                            @endif
                            @error('letterhead_landscape_file') <span class="text-[10px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- 4. Kop Surat Struk Thermal 80mm -->
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col justify-between space-y-4 bg-gray-50/50">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-bold text-gray-900 uppercase">4. Kop Struk Thermal (80mm)</h3>
                                <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-800 font-semibold rounded">POS Printer Kasir</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mb-3">Header monokrom/hitam-putih untuk struk kasir thermal 80mm.</p>

                            <div class="h-32 bg-white rounded-lg border border-dashed border-gray-300 flex items-center justify-center overflow-hidden p-2">
                                @if($letterhead_thermal_file)
                                    <img src="{{ $letterhead_thermal_file->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                                @elseif($letterhead_thermal_path)
                                    <img src="{{ asset('storage/' . $letterhead_thermal_path) }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <span class="text-xs text-gray-400">Belum ada kop struk thermal</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <input type="file" wire:model="letterhead_thermal_file" accept="image/png,image/jpeg" class="text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                                @if($letterhead_thermal_file)
                                    <button type="button" wire:click="uploadLetterheadThermal" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm">
                                        Simpan
                                    </button>
                                @endif
                            </div>
                            @if($letterhead_thermal_path)
                                <button type="button" wire:click="removeLetterheadThermal" wire:confirm="Hapus kop thermal 80mm?" class="text-[11px] text-red-600 hover:underline">
                                    Hapus Kop Thermal
                                </button>
                            @endif
                            @error('letterhead_thermal_file') <span class="text-[10px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 5: KALENDER LIBUR & HIGH SEASON -->
        @if($activeTab === 'season')
            <div class="space-y-6">
                <!-- Info Banner -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-2xl">💡</span>
                    <div class="text-xs text-amber-900 leading-relaxed">
                        <div class="font-bold text-sm text-amber-950 mb-0.5">Aturan Otomatis Musim & Tarif Hotel</div>
                        <ul class="list-disc pl-4 space-y-1">
                            <li><strong>Akhir Pekan (Sabtu & Minggu)</strong> secara sistematis langsung dikategorikan sebagai <strong>High Season</strong> tanpa perlu diinput manual.</li>
                            <li>Daftar tanggal di bawah digunakan untuk menandai <strong>Hari Libur Nasional, Cuti Bersama, atau Libur Sekolah</strong> sebagai High Season tambahan.</li>
                            <li>Semua modul (Kamar Hotel, Ruang Meeting, dan Fine Dining Syariah) akan otomatis mengenali tanggal-tanggal ini.</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Kolom Kiri: Form Input & Import Box -->
                    <div class="space-y-6">
                        <!-- Form Tambah Tanggal -->
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                            <div class="border-b border-gray-100 pb-2">
                                <h3 class="text-sm font-bold text-gray-900">➕ Tambah Tanggal Libur</h3>
                                <p class="text-[11px] text-gray-500">Tandai tanggal tertentu sebagai High Season.</p>
                            </div>

                            <form wire:submit="addHoliday" class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="holiday_date" class="w-full text-xs font-semibold border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" required>
                                    @error('holiday_date') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Selesai (Opsional)</label>
                                    <input type="date" wire:model="holiday_end_date" class="w-full text-xs font-semibold border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Kosongkan jika hanya 1 hari">
                                    <span class="text-[10px] text-gray-400">Isi jika libur beberapa hari berurutan.</span>
                                    @error('holiday_end_date') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Hari Libur / Keterangan <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="holiday_name" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: Idul Fitri 1447 H" required>
                                    @error('holiday_name') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                                    <textarea wire:model="holiday_notes" rows="2" class="w-full text-xs border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="Catatan internal..."></textarea>
                                </div>

                                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                    Simpan Tanggal Libur
                                </button>
                            </form>
                        </div>

                        <!-- Import Box -->
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                            <div class="border-b border-gray-100 pb-2">
                                <h3 class="text-sm font-bold text-gray-900">📥 Import dari CSV / Excel</h3>
                                <p class="text-[11px] text-gray-500">Unggah daftar libur massal dalam format CSV (bisa dibuka/disimpan di Excel).</p>
                            </div>

                            <div class="flex items-center justify-between pb-1">
                                <button type="button" wire:click="downloadTemplate" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1.5 underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh Template CSV</span>
                                </button>
                                <span class="text-[10px] text-gray-400">template_high_season.csv</span>
                            </div>

                            <form wire:submit="importHolidays" class="space-y-3 pt-2 border-t border-gray-100">
                                <div>
                                    <input type="file" wire:model="holiday_import_file" accept=".csv,text/csv,text/plain" class="w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                    @error('holiday_import_file') <span class="text-[10px] text-red-600 block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <button type="submit" class="w-full py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-2" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="importHolidays">📤 Upload & Proses Import</span>
                                    <span wire:loading wire:target="importHolidays">Memproses Import...</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Daftar Tanggal Libur -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">Daftar Tanggal Libur Terdaftar</h3>
                                    <p class="text-[11px] text-gray-500">Total: {{ count($holidays) }} tanggal aktif/terdaftar</p>
                                </div>

                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <select wire:model.live="season_year" class="text-xs border-gray-300 rounded-lg focus:ring-emerald-500">
                                        <option value="all">Semua Tahun</option>
                                        @foreach(range(date('Y') - 1, date('Y') + 2) as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model.live.debounce.300ms="season_search" placeholder="Cari libur..." class="text-xs border-gray-300 rounded-lg focus:ring-emerald-500 w-36 sm:w-44">
                                </div>
                            </div>

                            @if($holidays->isEmpty())
                                <div class="text-center py-12 text-gray-400 text-xs">
                                    <span class="text-3xl block mb-2">🏖️</span>
                                    Belum ada tanggal libur yang didaftarkan untuk filter ini.<br>
                                    Weekend (Sabtu & Minggu) tetap otomatis aktif sebagai High Season.
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead>
                                            <tr class="border-b border-gray-200 text-gray-500 font-semibold uppercase tracking-wider text-[10px]">
                                                <th class="py-2.5 px-3">Tanggal</th>
                                                <th class="py-2.5 px-3">Hari</th>
                                                <th class="py-2.5 px-3">Nama Libur</th>
                                                <th class="py-2.5 px-3">Catatan</th>
                                                <th class="py-2.5 px-3 text-center">Status</th>
                                                <th class="py-2.5 px-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($holidays as $h)
                                                <tr class="hover:bg-gray-50 transition {{ !$h->is_active ? 'opacity-60 bg-gray-50/50' : '' }}">
                                                    <td class="py-2.5 px-3 font-bold text-gray-900 whitespace-nowrap">
                                                        {{ $h->date->format('d M Y') }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-gray-600 whitespace-nowrap">
                                                        {{ $h->date->locale('id')->isoFormat('dddd') }}
                                                    </td>
                                                    <td class="py-2.5 px-3 font-semibold text-emerald-800">
                                                        {{ $h->name }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-gray-500 text-[11px]">
                                                        {{ $h->notes ?: '-' }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center">
                                                        <button type="button" wire:click="toggleHolidayStatus({{ $h->id }})" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ $h->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-200 text-gray-600 hover:bg-gray-300' }}">
                                                            {{ $h->is_active ? 'Aktif' : 'Nonaktif' }}
                                                        </button>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right">
                                                        <button type="button" wire:click="deleteHoliday({{ $h->id }})" wire:confirm="Hapus tanggal libur {{ $h->date->format('d/m/Y') }} ({{ $h->name }})?" class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition" title="Hapus">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
