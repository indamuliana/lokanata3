<div>
    <!-- Hero Section with Auto Crossfade Slider -->
    <section class="relative bg-gradient-to-br from-emerald-950 via-teal-950 to-gray-950 text-white py-20 lg:py-28 overflow-hidden"
        x-data="{
            activeSlide: 0,
            slidesCount: {{ count($sliderImages) }},
            timer: null,
            init() {
                if (this.slidesCount > 1) {
                    this.timer = setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
                    }, 5000);
                }
            },
            goTo(idx) {
                this.activeSlide = idx;
                if (this.timer) { clearInterval(this.timer); this.init(); }
            }
        }"
    >
        <!-- Background Images with Crossfade -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            @foreach($sliderImages as $idx => $sImg)
                <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    x-show="activeSlide === {{ $idx }}"
                    x-transition:enter="transition-opacity ease-out duration-1000"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-1000"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <img src="{{ $sImg['url'] }}" alt="{{ $sImg['title'] ?? 'InSitu Hotel' }}" class="w-full h-full object-cover opacity-25 filter blur-2xs scale-105 transition-transform duration-7000 ease-out">
                </div>
            @endforeach
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950/95 via-gray-900/80 to-emerald-950/70"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-800/80 text-emerald-200 border border-emerald-700/50 backdrop-blur">
                {{ $heroTagline }}
            </span>
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight max-w-4xl mx-auto leading-tight">
                {{ $heroTitle }}
            </h1>
            <p class="text-base sm:text-lg text-emerald-100 max-w-2xl mx-auto leading-relaxed">
                {{ $heroSubtitle }}
            </p>
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button type="button" wire:click="searchRooms" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-900/50 transition cursor-pointer">
                    Pesan Kamar Langsung
                </button>
                <a href="#kamar" class="w-full sm:w-auto px-8 py-3.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-sm rounded-xl border border-white/20 transition">
                    Lihat Pilihan Kamar
                </a>
                <a href="#galeri" class="w-full sm:w-auto px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-sm rounded-xl border border-white/20 transition">
                    Galeri Foto
                </a>
            </div>

            <!-- Dots Indicator -->
            @if(count($sliderImages) > 1)
                <div class="pt-4 flex items-center justify-center gap-2">
                    @foreach($sliderImages as $idx => $sImg)
                        <button type="button" @click="goTo({{ $idx }})" class="h-1.5 rounded-full transition-all duration-300"
                            :class="activeSlide === {{ $idx }} ? 'w-6 bg-emerald-400' : 'w-1.5 bg-white/30 hover:bg-white/60'"
                            title="Slide {{ $idx + 1 }}">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- QUICK BOOKING BAR (FLOATING CAPSULE SINGLE-ROW) -->
    <div class="relative z-30 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 lg:-mt-10 mb-12" x-data="{ openRoomsPopover: false, openDatePopover: false, openPromoPopover: false }">
        <div class="bg-white rounded-2xl lg:rounded-full shadow-2xl border border-gray-200/90 p-1.5 lg:p-2 text-gray-800">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                
                <!-- 1. Tanggal Menginap -->
                <div class="flex-1 px-4 py-2 hover:bg-emerald-50/40 rounded-xl lg:rounded-l-full transition cursor-pointer relative" @click="openDatePopover = !openDatePopover; openRoomsPopover = false; openPromoPopover = false">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal Menginap</span>
                            <span class="block text-xs lg:text-sm font-extrabold text-gray-900 truncate">
                                {{ $this->formattedStayDates }}
                            </span>
                        </div>
                    </div>

                    <!-- Date Selection Dropdown / Popover -->
                    <div x-show="openDatePopover" @click.away="openDatePopover = false" @click.stop x-cloak class="absolute left-0 top-full mt-2 w-full sm:w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 p-4 z-50 space-y-3 cursor-default">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Check-In</label>
                            <input type="date" wire:model.live="check_in_date" min="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            @error('check_in_date') <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Check-Out</label>
                            <input type="date" wire:model.live="check_out_date" min="{{ \Carbon\Carbon::parse($check_in_date)->addDay()->toDateString() }}" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            @error('check_out_date') <span class="text-[11px] text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="pt-2 border-t flex items-center justify-between text-xs text-gray-500">
                            <span>Durasi Menginap:</span>
                            <span class="font-bold text-emerald-700">{{ $this->nightsCount }} Malam</span>
                        </div>
                        <div class="pt-2 border-t text-right">
                            <button type="button" @click="openDatePopover = false" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg cursor-pointer">Selesai</button>
                        </div>
                    </div>
                </div>

                <!-- 2. Kamar & Tamu Selector -->
                <div class="px-4 py-2 hover:bg-emerald-50/40 lg:min-w-[210px] transition cursor-pointer relative" @click="openRoomsPopover = !openRoomsPopover; openDatePopover = false; openPromoPopover = false">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kamar &amp; Tamu</span>
                            <span class="block text-xs lg:text-sm font-extrabold text-gray-900 truncate">
                                {{ $rooms_count }} Kamar, {{ $adults + $children }} Tamu
                            </span>
                        </div>
                    </div>

                    <!-- Interactive Counter Popover -->
                    <div x-show="openRoomsPopover" @click.away="openRoomsPopover = false" @click.stop x-cloak class="absolute right-0 top-full mt-2 w-full sm:w-72 bg-white rounded-2xl shadow-2xl border border-gray-200 p-4 z-50 space-y-4 cursor-default">
                        <!-- Kamar Counter -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h5 class="text-xs font-bold text-gray-900">Kamar</h5>
                                <span class="text-[10px] text-gray-400">Jumlah unit</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click.stop="decrementRooms" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center font-bold text-sm">-</button>
                                <span class="w-6 text-center font-bold text-xs text-gray-800">{{ $rooms_count }}</span>
                                <button type="button" wire:click.stop="incrementRooms" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center font-bold text-sm">+</button>
                            </div>
                        </div>

                        <!-- Dewasa Counter -->
                        <div class="flex items-center justify-between border-t pt-3">
                            <div>
                                <h5 class="text-xs font-bold text-gray-900">Dewasa</h5>
                                <span class="text-[10px] text-gray-400">Usia 13+ thn</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click.stop="decrementAdults" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center font-bold text-sm">-</button>
                                <span class="w-6 text-center font-bold text-xs text-gray-800">{{ $adults }}</span>
                                <button type="button" wire:click.stop="incrementAdults" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center font-bold text-sm">+</button>
                            </div>
                        </div>

                        <!-- Anak Counter -->
                        <div class="flex items-center justify-between border-t pt-3">
                            <div>
                                <h5 class="text-xs font-bold text-gray-900">Anak</h5>
                                <span class="text-[10px] text-gray-400">Usia 0-12 thn</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click.stop="decrementChildren" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center font-bold text-sm">-</button>
                                <span class="w-6 text-center font-bold text-xs text-gray-800">{{ $children }}</span>
                                <button type="button" wire:click.stop="incrementChildren" class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 flex items-center justify-center font-bold text-sm">+</button>
                            </div>
                        </div>

                        <div class="pt-2 border-t text-right">
                            <button type="button" @click="openRoomsPopover = false" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg">Selesai</button>
                        </div>
                    </div>
                </div>

                <!-- 3. Voucher / Promo Code -->
                <div class="px-4 py-2 hover:bg-emerald-50/40 lg:min-w-[170px] transition cursor-pointer relative" @click="openPromoPopover = !openPromoPopover; openDatePopover = false; openRoomsPopover = false">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <div class="min-w-0 flex-1">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kode Voucher</span>
                            @if($promo_code)
                                <span class="inline-flex items-center gap-1 text-xs lg:text-sm font-extrabold text-emerald-700 truncate">
                                    <span>{{ $promo_code }}</span>
                                    <button type="button" wire:click.stop="clearPromoCode" class="text-rose-500 hover:text-rose-700 text-xs ml-1" title="Hapus Promo">✕</button>
                                </span>
                            @else
                                <span class="block text-xs lg:text-sm font-bold text-gray-500 hover:text-emerald-700 transition">
                                    Tambah Promo
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Promo Code Dropdown / Popover -->
                    <div x-show="openPromoPopover" @click.away="openPromoPopover = false" @click.stop x-cloak class="absolute right-0 top-full mt-2 w-full sm:w-72 bg-white rounded-2xl shadow-2xl border border-gray-200 p-4 z-50 space-y-3 cursor-default">
                        <label class="block text-xs font-semibold text-gray-700">Masukkan Kode Promo / Voucher</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model.live="promo_code" placeholder="Contoh: PROMOINSI" class="flex-1 text-xs rounded-lg border-gray-300 uppercase focus:border-emerald-500 focus:ring-emerald-500">
                            <button type="button" @click="openPromoPopover = false" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700">Terapkan</button>
                        </div>
                    </div>
                </div>

                <!-- 4. Tombol Aksi: Cari Kamar -->
                <div class="p-1 lg:pl-2 shrink-0">
                    <button type="button" wire:click="searchRooms" wire:loading.attr="disabled" class="w-full lg:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs lg:text-sm rounded-xl lg:rounded-full shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center gap-2 cursor-pointer">
                        <svg wire:loading.remove wire:target="searchRooms" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <svg wire:loading wire:target="searchRooms" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span wire:loading.remove wire:target="searchRooms">Cari Kamar</span>
                        <span wire:loading wire:target="searchRooms">Mengecek...</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Syariah Values Section -->
    <section id="fasilitas" class="py-12 bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900">Bebas Miras & Non-Halal</h4>
                        <p class="text-xs text-gray-500 mt-1">Lingkungan bebas alkohol, rokok di area umum, dan makanan 100% halal bersertifikat.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900">Perlengkapan Ibadah</h4>
                        <p class="text-xs text-gray-500 mt-1">Penunjuk arah kiblat akurat, sajadah bersih, dan Al-Qur'an tersedia di setiap kamar.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900">Ramah Keluarga & Mahram</h4>
                        <p class="text-xs text-gray-500 mt-1">Mengutamakan privasi dan kenyamanan tamu keluarga serta pasangan sah.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900">Lokasi Dekat Cipanas</h4>
                        <p class="text-xs text-gray-500 mt-1">Hanya 5 menit ke pemandian air panas alami Cipanas dan sentra oleh-oleh Garut.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Showcase Section -->
    <section id="kamar" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Pilihan Tipe Kamar Terbaik</h2>
                <p class="text-sm text-gray-500 mt-2">Setiap kamar didesain dengan sirkulasi udara optimal, tempat tidur ortopedik bermutu tinggi, dan kamar mandi modern bersih.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($roomTypes as $type)
                    @php
                        $coverPhoto = $type->primary_photo;
                        $photosList = $type->all_photos;
                        $photosCount = count($photosList);
                        $typeColor = $type->color ?? '#059669';
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 group">
                        <div>
                            <!-- Cover Photo & Gallery Trigger -->
                            <div class="relative aspect-[16/10] bg-gray-100 overflow-hidden cursor-pointer" wire:click="openGallery({{ $type->id }})">
                                @if($coverPhoto)
                                    <img src="{{ $coverPhoto }}" alt="{{ $type->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs font-medium">
                                        Foto Kamar Belum Tersedia
                                    </div>
                                @endif

                                <!-- Badge Kode & Warna Tipe -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg text-white shadow-md inline-flex items-center gap-1.5" style="background-color: {{ $typeColor }};">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        {{ $type->code }}
                                    </span>
                                </div>

                                <!-- Button Lihat Foto -->
                                @if($photosCount > 0)
                                    <button type="button" wire:click.stop="openGallery({{ $type->id }})" class="absolute bottom-3 right-3 bg-black/75 hover:bg-black text-white text-xs font-semibold px-2.5 py-1 rounded-lg backdrop-blur-xs flex items-center gap-1.5 shadow transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $photosCount }} Foto</span>
                                    </button>
                                @endif
                            </div>

                            @php
                                $availCount = $this->getAvailableCountForType($type);
                                $typeEstimate = $this->getPricingForType($type);
                            @endphp
                            <div class="p-5 space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">
                                        Kapasitas {{ $type->standard_capacity }} Org
                                    </span>
                                    @if($availCount > 0)
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1.5 shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Tersedia {{ $availCount }} Kamar
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-rose-100 text-rose-800 flex items-center gap-1.5 shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Penuh / Tidak Tersedia Online
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-lg font-bold text-gray-900 leading-snug">{{ $type->name }}</h3>
                                <p class="text-xs text-gray-500 line-clamp-2">{{ $type->description ?? 'Hunian nyaman dengan sirkulasi udara optimal dan fasilitas syariah.' }}</p>
                                
                                <!-- Custom Facilities with Emojis / Icons -->
                                <ul class="text-xs text-gray-600 space-y-1.5 pt-3 border-t border-gray-100">
                                    @php $structuredFacilities = $type->structured_facilities; @endphp
                                    @forelse(array_slice($structuredFacilities, 0, 4) as $fac)
                                        <li class="flex items-center gap-2">
                                            <span class="text-sm shrink-0">{{ $fac['icon'] }}</span>
                                            <span class="truncate">{{ $fac['text'] }}</span>
                                        </li>
                                    @empty
                                        <li class="text-xs text-gray-400 italic">Fasilitas lengkap syariah</li>
                                    @endforelse
                                    @if(count($structuredFacilities) > 4)
                                        <li class="text-[11px] text-gray-400 font-medium">
                                            + {{ count($structuredFacilities) - 4 }} fasilitas lainnya
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <div class="p-5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-gray-500 block">Estimasi ({{ $typeEstimate['nights'] ?? 1 }} malam)</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-base font-extrabold text-emerald-800 font-mono">{{ rupiah($typeEstimate['total_amount'] ?? $type->base_price) }}</span>
                                    @if(($typeEstimate['discount_amount'] ?? 0) > 0)
                                        <span class="text-[10px] text-gray-400 line-through font-mono">{{ rupiah($typeEstimate['subtotal']) }}</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 block">{{ rupiah($type->base_price) }}/malam</span>
                            </div>
                            @if($availCount > 0)
                                <button type="button" wire:click="openQuickBookingModal({{ $type->id }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                    Pilih Kamar
                                </button>
                            @else
                                <button type="button" disabled class="px-3.5 py-2 bg-gray-200 text-gray-500 text-xs font-bold rounded-xl cursor-not-allowed">
                                    Penuh / Tidak Tersedia Online
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SECTION GALERI FOTO BANYAK GAMBAR -->
    <section id="galeri" class="py-16 bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center max-w-2xl mx-auto">
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Galeri Hotel</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">Suasana Asri & Fasilitas InSitu Hotel</h2>
                <p class="text-sm text-gray-500 mt-1">Potret sudut ketenangan, kebersihan prima, dan keramahan pelayanan syariah kami di Garut.</p>
            </div>

            @if($webGalleries->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($webGalleries as $gal)
                        <div class="group relative aspect-[4/3] rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition bg-gray-200 cursor-pointer" wire:click="openWebLightbox('{{ $gal->image_url }}', '{{ addslashes($gal->title) }}', '{{ addslashes($gal->caption ?? '') }}')">
                            <img src="{{ $gal->image_url }}" alt="{{ $gal->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition p-4 flex flex-col justify-end text-white">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">{{ $gal->category }}</span>
                                <h4 class="font-bold text-xs leading-snug line-clamp-1">{{ $gal->title }}</h4>
                                @if($gal->caption)
                                    <p class="text-[10px] text-gray-200 line-clamp-1 mt-0.5">{{ $gal->caption }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 gap-4">
                    @php
                        $defaultOtaPhotos = [
                            ['url' => asset('storage/web-galleries/ota-pemandangan-1.jpg'), 'title' => 'Panorama Gunung Guntur & Alam Garut', 'category' => 'Pemandangan', 'caption' => 'Suasana sejuk teras hijau dengan latar megah Gunung Guntur'],
                            ['url' => asset('storage/web-galleries/ota-resto-6.jpg'), 'title' => 'Restoran & Ruang Jamuan Liwet Garut', 'category' => 'Resto & Event', 'caption' => 'Area makan luas bersuasana hangat dan nyaman untuk keluarga'],
                            ['url' => asset('storage/web-galleries/ota-kamar-3.jpg'), 'title' => 'Kamar Tidur Utama Bersih & Nyaman', 'category' => 'Kamar', 'caption' => 'Tempat tidur ortopedik standar hotel berbintang dengan pencahayaan hangat'],
                            ['url' => asset('storage/web-galleries/ota-kamar-4.jpg'), 'title' => 'Kamar Twin Bed Modern', 'category' => 'Kamar', 'caption' => 'Penataan rapi dan lantai kayu hangat karya teaching factory SMK Wikrama'],
                            ['url' => asset('storage/web-galleries/ota-kamar_mandi-5.jpg'), 'title' => 'Kamar Mandi & Amenitas Lengkap', 'category' => 'Fasilitas', 'caption' => 'Fasilitas kebersihan prima berating 9.8/10 di Agoda'],
                            ['url' => asset('storage/web-galleries/ota-pemandangan-2.jpg'), 'title' => 'Fasilitas Minibar & Coffee Maker', 'category' => 'Fasilitas', 'caption' => 'Peralatan lengkap dan lemari penyimpanan di setiap kamar'],
                        ];
                    @endphp
                    @foreach($defaultOtaPhotos as $gal)
                        <div class="group relative aspect-[4/3] rounded-2xl overflow-hidden shadow-xs hover:shadow-lg transition bg-gray-200 cursor-pointer" wire:click="openWebLightbox('{{ $gal['url'] }}', '{{ addslashes($gal['title']) }}', '{{ addslashes($gal['caption']) }}')">
                            <img src="{{ $gal['url'] }}" alt="{{ $gal['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition p-4 flex flex-col justify-end text-white">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">{{ $gal['category'] }}</span>
                                <h4 class="font-bold text-xs leading-snug line-clamp-1">{{ $gal['title'] }}</h4>
                                <p class="text-[10px] text-gray-200 line-clamp-1 mt-0.5">{{ $gal['caption'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- SECTION TESTIMONI TAMU OTENTIK (SINGLE-ROW RUNNING MARQUEE) -->
    <section id="ulasan" class="py-16 bg-white border-t border-gray-200 overflow-hidden">
        <style>
            @@keyframes marquee-ltr {
                0% {
                    transform: translateX(-50%);
                }
                100% {
                    transform: translateX(0%);
                }
            }
            .marquee-track-ltr {
                display: flex !important;
                width: max-content !important;
                animation: marquee-ltr 48s linear infinite !important;
            }
            .marquee-track-ltr:hover {
                animation-play-state: paused !important;
            }
            .testimonial-card-item {
                width: 568px !important;
                min-width: 568px !important;
                max-width: 568px !important;
                flex: 0 0 568px !important;
                box-sizing: border-box !important;
            }
            @media (prefers-reduced-motion: reduce) {
                .marquee-track-ltr {
                    animation: none !important;
                }
            }
        </style>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold">
                        <span>⭐</span>
                        <span>Ulasan Tamu Terverifikasi OTA</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">Pengalaman Nyata Menginap di InSitu Hotel</h2>
                    <p class="text-sm text-gray-500 mt-1">Ulasan otentik dari Booking.com, Tiket.com, dan Agoda yang merasakan langsung keramahan syariah kami.</p>
                </div>

                <!-- Minimalist 1-Line OTA Score Badges -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs font-bold shadow-sm">
                        <span class="w-4 h-4 rounded text-white flex items-center justify-center text-[9px] font-black shrink-0" style="background-color: #003580;">B</span>
                        <span>Booking.com <strong class="text-sm font-mono font-extrabold">8.7</strong><span class="text-[10px] text-blue-600 font-normal">/10</span></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-900 text-xs font-bold shadow-sm">
                        <span class="w-4 h-4 rounded text-white flex items-center justify-center text-[9px] font-black shrink-0" style="background-color: #0064D2;">t</span>
                        <span>Tiket.com <strong class="text-sm font-mono font-extrabold">4.6</strong><span class="text-[10px] text-sky-600 font-normal">/5</span></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-xs font-bold shadow-sm">
                        <span class="w-4 h-4 rounded text-white flex items-center justify-center text-[9px] font-black shrink-0" style="background-color: #5392F9;">ag</span>
                        <span>Agoda <strong class="text-sm font-mono font-extrabold">8.4</strong><span class="text-[10px] text-indigo-600 font-normal">/10</span></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-sm">
                        <span>✨</span>
                        <span>Kebersihan <strong class="font-mono font-bold">9.8</strong></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Single-Row Running Testimonials (Dari Kiri ke Kanan) -->
        <div class="relative w-full mt-6 py-2 overflow-hidden">
            <!-- Left & Right Gradient Shadows for Smooth Fade -->
            <div class="pointer-events-none absolute inset-y-0 left-0 w-12 sm:w-24 bg-gradient-to-r from-white via-white/80 to-transparent z-10"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 w-12 sm:w-24 bg-gradient-to-l from-white via-white/80 to-transparent z-10"></div>

            @php
                $marqueeReviews = array_merge($curatedReviews, $curatedReviews);
            @endphp

            <div class="marquee-track-ltr flex items-stretch gap-4 px-4 py-2">
                @foreach($marqueeReviews as $review)
                    @php
                        $badgeStyle = match($review['platform']) {
                            'Booking.com' => 'background-color: #003580; color: #ffffff;',
                            'Tiket.com' => 'background-color: #0064D2; color: #ffffff;',
                            'Agoda' => 'background-color: #5392F9; color: #ffffff;',
                            default => 'background-color: #047857; color: #ffffff;'
                        };
                    @endphp
                    <div class="testimonial-card-item shrink-0 bg-gray-50/90 hover:bg-white rounded-2xl p-5 sm:p-6 border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col justify-between select-none"
                         style="width: 568px; min-width: 568px; max-width: 568px; flex: 0 0 568px;">
                        
                        <!-- Top Meta: Platform & Rating -->
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold tracking-wide" style="{{ $badgeStyle }}">
                                    {{ $review['platform'] }}
                                </span>
                                <div class="flex items-center gap-1 text-amber-400 text-xs">
                                    <span>⭐⭐⭐⭐⭐</span>
                                    <span class="text-xs font-mono font-bold text-gray-700 ml-1">{{ $review['rating'] }}/{{ $review['rating_scale'] }}</span>
                                </div>
                            </div>

                            <!-- Review Title & Quote -->
                            <div>
                                <h4 class="font-bold text-xs sm:text-sm text-gray-900 leading-snug line-clamp-1">"{{ $review['title'] }}"</h4>
                                <p class="text-xs text-gray-600 mt-1.5 leading-relaxed italic line-clamp-3">
                                    "{{ $review['review_quote'] }}"
                                </p>
                            </div>
                        </div>

                        <!-- Reviewer Profile -->
                        <div class="pt-3 mt-3 border-t border-gray-200/80 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-emerald-600/10 text-emerald-800 font-extrabold flex items-center justify-center text-xs shrink-0">
                                    {{ substr($review['reviewer_name'], 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h5 class="font-bold text-gray-900 text-xs truncate">{{ $review['reviewer_name'] }}</h5>
                                    <span class="text-[10px] text-gray-400 block truncate">{{ $review['origin'] }} &bull; {{ $review['guest_type'] }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-gray-400 font-mono shrink-0 ml-2">{{ date('d M Y', strtotime($review['date'])) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center text-[11px] text-gray-400 flex items-center justify-center gap-1.5 pt-2">
            <span>💡</span>
            <span>Arahkan kursor atau tahan kartu untuk menjeda testimoni</span>
        </div>
    </section>

    <!-- SECTION MOMEN & KEGIATAN INSTAGRAM (@insituhotelsyariahgarut) -->
    <section id="kegiatan" class="py-16 bg-gradient-to-b from-gray-50 to-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-pink-100 text-pink-800 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        <span>Sosial Media & Kegiatan</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">Momen Seru di InSitu Hotel Garut</h2>
                    <p class="text-sm text-gray-500 mt-1">Ikuti kegiatan terkini, suasana gathering, dan promo eksklusif di Instagram kami.</p>
                </div>
                <a href="https://www.instagram.com/insituhotelsyariahgarut/" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-700 hover:to-purple-700 text-white font-bold text-xs shadow-md hover:shadow-lg transition">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    <span>Follow @insituhotelsyariahgarut</span>
                </a>
            </div>

            <!-- Instagram 3 Cards Showcase -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Gala Dinner & Gathering -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <div class="aspect-[16/10] bg-gray-100 overflow-hidden relative">
                            <img src="{{ asset('storage/web-galleries/ota-resto-6.jpg') }}" alt="Gala Dinner & Intimate Gathering" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-black/60 text-white backdrop-blur-xs flex items-center gap-1">
                                <span>🎉</span>
                                <span>Event & Gathering</span>
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <span class="text-[11px] font-bold text-pink-600 block">Sewa Full 1 Hotel (Privat)</span>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Gala Dinner & Acara Syukuran Keluarga</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Nikmati kehangatan berkumpul bersama kolega dan keluarga besar dengan jamuan liwet khas Garut, sound system lengkap, dan privasi penuh seluruh lantai hotel.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="https://www.instagram.com/insituhotelsyariahgarut/" target="_blank" class="text-xs font-bold text-pink-600 hover:text-pink-700 inline-flex items-center gap-1">
                            <span>Lihat di Instagram</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Card 2: Family Staycation & Panorama -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <div class="aspect-[16/10] bg-gray-100 overflow-hidden relative">
                            <img src="{{ asset('storage/web-galleries/ota-pemandangan-1.jpg') }}" alt="Family Staycation View Gunung Guntur" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-black/60 text-white backdrop-blur-xs flex items-center gap-1">
                                <span>🌿</span>
                                <span>Panorama Alam</span>
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <span class="text-[11px] font-bold text-emerald-600 block">Sejuk & Ramah Anak</span>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Pagi Berkabut Menatap Gunung Guntur</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Suasana sejuk bebas polusi dari balkon kamar. Tempat terbaik menyegarkan pikiran dan beristirahat nyaman setelah berendam di air panas alami Cipanas.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="https://www.instagram.com/insituhotelsyariahgarut/" target="_blank" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
                            <span>Lihat di Instagram</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Teaching Factory & Hospitality -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:-translate-y-1.5 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <div class="aspect-[16/10] bg-gray-100 overflow-hidden relative">
                            <img src="{{ asset('storage/web-galleries/ota-kamar-3.jpg') }}" alt="Teaching Factory SMK Wikrama 1 Garut" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-black/60 text-white backdrop-blur-xs flex items-center gap-1">
                                <span>🎓</span>
                                <span>Teaching Factory</span>
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <span class="text-[11px] font-bold text-indigo-600 block">Karya Generasi Muda Berprestasi</span>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Hospitality Ramah Siswa SMK Wikrama 1 Garut</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Dikelola secara profesional berkolaborasi dengan siswa beasiswa perhotelan SMK Wikrama 1 Garut yang terkenal sopan, ramah, dan sigap membantu setiap kebutuhan tamu.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="https://www.instagram.com/insituhotelsyariahgarut/" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                            <span>Lihat di Instagram</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION POST BERITA & PROMO TERBARU -->
    <section id="berita" class="py-16 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Kabar & Promo</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">Artikel Terkini & Penawaran Spesial</h2>
                    <p class="text-sm text-gray-500 mt-1">Dapatkan informasi diskon musiman serta panduan wisata keluarga di Garut.</p>
                </div>
                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 transition">
                    <span>Lihat Semua Berita & Promo</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            @if($latestPosts->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($latestPosts as $post)
                        <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                            <div>
                                <a href="{{ route('news.detail', $post->slug) }}" class="block aspect-[16/10] bg-gray-100 overflow-hidden relative">
                                    @if($post->featured_image_url)
                                        <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                        </div>
                                    @endif
                                    <div class="absolute top-3 left-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-700/90 text-white backdrop-blur-xs">
                                            {{ $post->category }}
                                        </span>
                                    </div>
                                </a>

                                <div class="p-5 space-y-2">
                                    <span class="text-[10px] text-gray-400 block">{{ $post->published_at ? $post->published_at->format('d M Y') : '' }}</span>
                                    <h3 class="text-sm font-bold text-gray-900 group-hover:text-emerald-700 transition leading-snug line-clamp-2">
                                        <a href="{{ route('news.detail', $post->slug) }}">
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                        {{ $post->summary ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 90) }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-5 pt-0">
                                <a href="{{ route('news.detail', $post->slug) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
                                    <span>Baca Artikel</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center bg-white rounded-2xl border border-gray-200 text-xs text-gray-500">
                    Belum ada postingan artikel atau promo terkini. Kunjungi kembali dalam waktu dekat!
                </div>
            @endif
        </div>
    </section>

    <!-- Booking Form Section -->
    <section id="booking" class="py-16 bg-white border-t border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center">
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Booking Engine Resmi</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">Formulir Pemesanan Kamar Online</h2>
                <p class="text-sm text-gray-500 mt-1">Pemesanan langsung mendapat jaminan kamar dengan masa tahan 2 jam tanpa kartu kredit.</p>
            </div>

            @if(session()->has('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Confirmation Card -->
            @if($confirmedReservation)
                <div class="bg-emerald-50/50 border-2 border-emerald-500 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-emerald-500 text-white rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Alhamdulillah, Pemesanan Berhasil Dibuat!</h3>
                            <p class="text-xs text-gray-600 mt-0.5">Kode Reservasi Anda: <span class="font-mono font-bold text-emerald-700 text-sm">{{ $confirmedReservation->reservation_number }}</span></p>
                        </div>
                    </div>

                    <!-- Expiry Hold Warning -->
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Masa Tahan Kamar: 2 Jam (Berlaku hingga {{ $confirmedReservation->expires_at?->format('H:i') ?? '-' }} WIB)
                        </p>
                        <p>Silakan lakukan transfer uang muka / pelunasan ke rekening hotel dan konfirmasikan ke WhatsApp Front Desk kami sebelum masa tahan habis agar reservasi tidak kedaluwarsa otomatis.</p>
                    </div>

                    <!-- Summary Table -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200 text-xs space-y-2">
                        <div class="flex justify-between py-1 border-b">
                            <span class="text-gray-500">Nama Pemesan:</span>
                            <span class="font-semibold text-gray-900">{{ $confirmedReservation->customer->name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b">
                            <span class="text-gray-500">Tipe Kamar:</span>
                            <span class="font-semibold text-gray-900">{{ $confirmedReservation->roomType->name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b">
                            <span class="text-gray-500">Periode Menginap:</span>
                            <span class="font-semibold text-gray-900">{{ $confirmedReservation->check_in_date->format('d/m/Y') }} s/d {{ $confirmedReservation->check_out_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b">
                            <span class="text-gray-500">Total Tagihan (Termasuk Pajak PB1):</span>
                            <span class="font-bold font-mono text-emerald-600 text-sm">Rp {{ number_format($confirmedReservation->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    @php
                        $bankAccounts = \App\Models\HotelSetting::get('bank_accounts', []);
                    @endphp
                    @if(!empty($bankAccounts))
                        <div class="bg-white rounded-xl p-4 border border-gray-200 space-y-2">
                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wide">Rekening Transfer Resmi Hotel:</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                @foreach($bankAccounts as $bank)
                                    @if($bank['is_active'] ?? true)
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                                            <span class="font-bold text-emerald-700 block">{{ $bank['bank_name'] }}</span>
                                            <span class="font-mono text-sm font-bold block mt-1 select-all">{{ $bank['account_number'] }}</span>
                                            <span class="text-gray-500 text-[11px]">a.n. {{ $bank['account_holder'] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @php
                        $hotelWa = \App\Models\HotelSetting::get('hotel_whatsapp', '6281120070000');
                        $cleanWa = preg_replace('/[^0-9]/', '', $hotelWa);
                        $waText = urlencode("Assalamu'alaikum Front Desk InSitu Hotel Syariah Garut, saya sudah memesan kamar melalui website dengan kode *{$confirmedReservation->reservation_number}* atas nama *{$confirmedReservation->customer->name}*. Mohon informasi konfirmasi pembayaran saya. Terima kasih.");
                    @endphp

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <a href="https://wa.me/{{ $cleanWa }}?text={{ $waText }}" target="_blank" class="flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            <span>Konfirmasi Bukti Transfer via WhatsApp</span>
                        </a>

                        <button wire:click="resetBooking" class="py-3 px-6 bg-white hover:bg-gray-100 text-gray-700 font-semibold text-xs rounded-xl border border-gray-300 transition cursor-pointer">
                            Pesan Kamar Lainnya
                        </button>
                    </div>
                </div>

            @elseif(!$isWebsiteBookingEnabled)
                <!-- Booking Disabled Notice -->
                <div class="p-8 bg-gray-50 border border-gray-200 rounded-2xl text-center space-y-3">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Pemesanan Online Website Sedang Ditutup Sementara</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto">
                        Manajemen sedang melakukan pemeliharaan ketersediaan sistem. Untuk pemesanan cepat kamar atau ruang meeting, silakan hubungi Front Desk WhatsApp kami.
                    </p>
                    <div class="pt-2">
                        @php
                            $hotelWa = \App\Models\HotelSetting::get('hotel_whatsapp', '6281120070000');
                            $cleanWa = preg_replace('/[^0-9]/', '', $hotelWa);
                        @endphp
                        <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                            <span>Hubungi Front Desk WhatsApp</span>
                        </a>
                    </div>
                </div>

            @else
                <!-- Active Booking Form -->
                <form wire:submit="submitBooking" class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                    <!-- Step 1: Dates & Room Selection -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Tanggal Check-in</label>
                            <input type="date" wire:model.live="check_in_date" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            @error('check_in_date') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Tanggal Check-out</label>
                            <input type="date" wire:model.live="check_out_date" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            @error('check_out_date') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Pilihan Tipe Kamar</label>
                            <select wire:model.live="room_type_id" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                @foreach($roomTypes as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }} (Rp {{ number_format($rt->base_price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Room Availability & Live Pricing Preview -->
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $availableCount > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                            <span class="text-xs font-semibold text-gray-800">
                                Status Ketersediaan: <span class="font-bold text-emerald-800">{{ $availableCount }} Kamar Tersedia</span>
                            </span>
                        </div>

                        @if($pricing)
                            <div class="text-right">
                                <span class="text-xs text-gray-500 block">Estimasi Total Tagihan ({{ $pricing['nights'] }} Malam):</span>
                                <span class="text-lg font-bold font-mono text-emerald-700">Rp {{ number_format($pricing['total_amount'], 0, ',', '.') }}</span>
                                <span class="text-[10px] text-gray-400 block">(Termasuk Pajak Daerah 10%)</span>
                            </div>
                        @endif
                    </div>

                    <!-- Step 2: Guest Details -->
                    <div class="border-t border-gray-100 pt-5 space-y-4">
                        <h4 class="text-xs font-bold text-gray-900 uppercase">Data Tamu Utama:</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Nama Lengkap</label>
                                <input type="text" wire:model="guest_name" placeholder="Sesuai KTP" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                @error('guest_name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Nomor WhatsApp Aktif</label>
                                <input type="tel" wire:model="guest_phone" placeholder="Contoh: 08123456789" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                                @error('guest_phone') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">NIK KTP (Opsional)</label>
                                <input type="text" wire:model="guest_nik" placeholder="16 digit NIK" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Kode Promo (Opsional)</label>
                                <input type="text" wire:model.live.debounce.500ms="promo_code" placeholder="Contoh: PROMOINSI" class="mt-1 w-full text-sm rounded-lg border-gray-300 uppercase">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Jumlah Tamu Dewasa</label>
                                <input type="number" wire:model="adults" min="1" max="10" class="mt-1 w-full text-sm rounded-lg border-gray-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Permintaan Khusus (Opsional)</label>
                            <input type="text" wire:model="guest_notes" placeholder="Misal: Mohon kamar lantai 1, sajadah tambahan..." class="mt-1 w-full text-sm rounded-lg border-gray-300">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Kamar akan ditahan 2 jam menunggu pembayaran.</span>
                        <button type="submit" @if($availableCount < 1) disabled @endif class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 text-white font-bold text-sm rounded-xl shadow-md transition">
                            Konfirmasi Booking Sekarang
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <!-- SECTION LOKASI & WISATA -->
    <section id="lokasi" class="py-16 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center max-w-2xl mx-auto">
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Akses & Lokasi</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2">Strategis di Jantung Wisata Cipanas</h2>
                <p class="text-sm text-gray-500 mt-1">Akses mudah dari gerbang tol Garut, stasiun kereta api, dan objek wisata favorit Jawa Barat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-5 rounded-2xl border border-gray-200 space-y-2">
                    <div class="text-emerald-600 font-bold text-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Pemandian Air Panas Cipanas</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">Hanya 5 menit berkendara menuju sumber mata air panas alami belerang berkasiat untuk kesehatan.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200 space-y-2">
                    <div class="text-emerald-600 font-bold text-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Sentra Kuliner & Dodol Garut</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">Dekat pusat oleh-oleh khas Sukaregang (kerajinan kulit) dan pusat dodol ternama di Tarogong.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200 space-y-2">
                    <div class="text-emerald-600 font-bold text-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Jalur Kawah Papandayan</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">Titik singgah ternyaman bagi keluarga dan pendaki yang hendak menuju kawasan Gunung Papandayan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- LIGHTBOX PHOTO GALLERY MODAL (ROOMS) -->
    @if($showGalleryModal && $galleryRoomType)
        @php
            $galleryPhotos = $galleryRoomType->all_photos;
            $totalCount = count($galleryPhotos);
            $currentPhoto = $galleryPhotos[$activePhotoIndex] ?? ($galleryPhotos[0] ?? null);
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/90 flex flex-col justify-between p-4 sm:p-6 backdrop-blur-sm animate-fade-in">
            <!-- Modal Header -->
            <div class="flex items-center justify-between text-white max-w-5xl mx-auto w-full pb-3 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $galleryRoomType->color ?? '#059669' }};"></span>
                    <h3 class="text-lg font-bold">{{ $galleryRoomType->name }}</h3>
                    <span class="text-xs text-gray-400 font-mono">({{ $galleryRoomType->code }})</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs font-semibold bg-white/10 px-3 py-1 rounded-full text-gray-200">
                        Foto {{ $activePhotoIndex + 1 }} dari {{ $totalCount }}
                    </span>
                    <button type="button" wire:click="closeGallery" class="p-1.5 rounded-full hover:bg-white/20 text-gray-300 hover:text-white transition" title="Tutup Galeri">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Main Photo Display with Navigation Arrows -->
            <div class="relative flex-1 flex items-center justify-center max-w-5xl mx-auto w-full my-4">
                @if($totalCount > 1)
                    <button type="button" wire:click="prevPhoto" class="absolute left-2 sm:left-4 z-10 p-3 rounded-full bg-black/50 hover:bg-black/80 text-white transition backdrop-blur-xs shadow-lg" title="Foto Sebelumnya">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                @endif

                <div class="max-h-[65vh] max-w-full rounded-xl overflow-hidden shadow-2xl bg-black/40 flex items-center justify-center">
                    @if($currentPhoto)
                        <img src="{{ $currentPhoto }}" alt="{{ $galleryRoomType->name }}" class="max-h-[65vh] w-auto max-w-full object-contain rounded-xl select-none">
                    @endif
                </div>

                @if($totalCount > 1)
                    <button type="button" wire:click="nextPhoto" class="absolute right-2 sm:right-4 z-10 p-3 rounded-full bg-black/50 hover:bg-black/80 text-white transition backdrop-blur-xs shadow-lg" title="Foto Berikutnya">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endif
            </div>

            <!-- Bottom Thumbnail Strip & Facilities Preview -->
            <div class="max-w-5xl mx-auto w-full pt-3 border-t border-white/10 space-y-3">
                <!-- Thumbnail Strip -->
                <div class="flex items-center justify-center gap-2 overflow-x-auto py-1">
                    @foreach($galleryPhotos as $idx => $thumb)
                        <button type="button" wire:click="$set('activePhotoIndex', {{ $idx }})" class="relative w-16 h-12 rounded-lg overflow-hidden shrink-0 border-2 transition {{ $activePhotoIndex === $idx ? 'border-emerald-500 scale-105 shadow-md' : 'border-transparent opacity-60 hover:opacity-100' }}">
                            <img src="{{ $thumb }}" alt="Thumbnail {{ $idx + 1 }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>

                <!-- Facilities Quick List -->
                <div class="flex flex-wrap items-center justify-center gap-2 text-xs text-gray-300">
                    @foreach($galleryRoomType->structured_facilities as $fac)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-gray-200">
                            <span>{{ $fac['icon'] }}</span>
                            <span>{{ $fac['text'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- LIGHTBOX WEB GALLERY MODAL -->
    @if($showWebLightbox)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/90 flex flex-col justify-between p-4 sm:p-6 backdrop-blur-sm animate-fade-in" @click.self="$wire.closeWebLightbox()">
            <div class="flex items-center justify-between text-white max-w-5xl mx-auto w-full pb-3 border-b border-white/10">
                <div>
                    <h3 class="text-base font-bold">{{ $webLightboxTitle }}</h3>
                    @if($webLightboxCaption)
                        <p class="text-xs text-gray-300">{{ $webLightboxCaption }}</p>
                    @endif
                </div>
                <button type="button" wire:click="closeWebLightbox" class="p-1.5 rounded-full hover:bg-white/20 text-gray-300 hover:text-white transition" title="Tutup">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="relative flex-1 flex items-center justify-center max-w-5xl mx-auto w-full my-4">
                <div class="max-h-[75vh] max-w-full rounded-xl overflow-hidden shadow-2xl bg-black/40 flex items-center justify-center">
                    <img src="{{ $webLightboxImage }}" alt="{{ $webLightboxTitle }}" class="max-h-[75vh] w-auto max-w-full object-contain rounded-xl select-none">
                </div>
            </div>

            <div class="text-center text-xs text-gray-400 py-2">
                InSitu Hotel Syariah Garut &bull; Galeri Publik
            </div>
        </div>
    @endif

    <!-- MODAL HASIL PENCARIAN & PEMESANAN INSTAN (UNIFIED MULTI-STEP MODAL) -->
    @if($showSearchResultsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" 
             style="animation: fadeIn 0.2s ease-out;"
             @click.self="$wire.closeSearchResultsModal()">
            <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[92vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col my-auto">
                
                <!-- Modal Header -->
                <div class="px-5 py-4 sm:px-6 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-20">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                            @if($modalStep === 1)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            @elseif($modalStep === 2)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-gray-900 leading-tight">
                                @if($modalStep === 1)
                                    Pilihan Kamar Tersedia
                                @elseif($modalStep === 2)
                                    Konfirmasi Pemesanan Kamar
                                @else
                                    Alhamdulillah, Pemesanan Berhasil!
                                @endif
                            </h3>
                            <p class="text-xs text-gray-500">
                                @if($modalStep === 1)
                                    {{ $this->formattedStayDates }} &bull; {{ $rooms_count }} Kamar, {{ $adults + $children }} Tamu
                                @elseif($modalStep === 2)
                                    {{ $this->selectedRoomType?->name }} &bull; {{ $this->formattedStayDates }}
                                @else
                                    Kode Reservasi: <span class="font-mono font-bold text-emerald-800">{{ $confirmedReservation?->reservation_number }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeSearchResultsModal" class="p-2 text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100 transition cursor-pointer" title="Tutup">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-5">
                    @if(session()->has('error'))
                        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- LANGKAH 1: DAFTAR KAMAR TERSEDIA -->
                    @if($modalStep === 1)
                        @php
                            $availList = $this->availableRoomTypesList;
                        @endphp

                        @if(count($availList) > 0)
                            <div class="space-y-3.5">
                                <div class="flex items-center justify-between text-xs text-gray-500 pb-1">
                                    <span>Pilihan <strong class="text-emerald-700 font-bold">{{ count($availList) }} tipe kamar</strong> untuk periode menginap Anda:</span>
                                    <span class="text-[11px] text-gray-400 hidden sm:inline">*Harga termasuk pajak &amp; sarapan</span>
                                </div>

                                @foreach($availList as $item)
                                    @php
                                        $roomType = $item['type'];
                                        $p = $item['pricing'];
                                        $count = $item['available_count'];
                                        $totalStay = $p['total_amount'] ?? ($roomType->base_price * max(1, $this->nightsCount));
                                        $subtotal = $p['subtotal'] ?? ($roomType->base_price * max(1, $this->nightsCount));
                                    @endphp
                                    <div class="bg-gray-50/80 hover:bg-emerald-50/20 border border-gray-200/90 rounded-2xl p-4 sm:p-5 transition flex flex-col sm:flex-row gap-4 items-stretch sm:items-center justify-between">
                                        <!-- Info Kiri Kamar -->
                                        <div class="flex gap-3.5 items-start sm:items-center">
                                            @if($roomType->primary_photo)
                                                <img src="{{ $roomType->primary_photo }}" alt="{{ $roomType->name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover border border-gray-200 shrink-0">
                                            @else
                                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-gray-200 flex items-center justify-center text-gray-400 shrink-0 font-bold text-xs">
                                                    Foto Kamar
                                                </div>
                                            @endif
                                            <div class="space-y-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white shadow-xs" style="background-color: {{ $roomType->color ?? '#059669' }};">
                                                        {{ $roomType->code }}
                                                    </span>
                                                    <h4 class="font-extrabold text-sm sm:text-base text-gray-900 truncate">{{ $roomType->name }}</h4>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-gray-600 pt-0.5">
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200">
                                                        👥 {{ $roomType->max_occupancy ?? 2 }} Dewasa
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200">
                                                        🛏️ {{ $roomType->bed_type ?? 'King / Twin' }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200">
                                                        📶 WiFi Cepat
                                                    </span>
                                                </div>
                                                <div class="pt-1">
                                                    @if($count > 0)
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                                            ● Sisa {{ $count }} Unit
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                                            ● Penuh / Tidak Tersedia Online
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Harga & Tombol Pilih -->
                                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-200 shrink-0 gap-2 sm:gap-1.5">
                                            <div class="text-left sm:text-right">
                                                <span class="text-[10px] text-gray-400 block font-medium">Estimasi {{ $this->nightsCount }} Malam:</span>
                                                <div class="flex items-baseline gap-1.5 sm:justify-end">
                                                    <span class="text-base sm:text-lg font-black text-emerald-700 font-mono">{{ rupiah($totalStay) }}</span>
                                                    @if(($p['discount_amount'] ?? 0) > 0)
                                                        <span class="text-[10px] text-gray-400 line-through font-mono">{{ rupiah($subtotal) }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] text-gray-500 block">{{ rupiah($roomType->base_price) }} / malam</span>
                                            </div>
                                            @if($count > 0)
                                                <button type="button" wire:click="selectRoomForBooking({{ $roomType->id }})" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs hover:shadow-md transition flex items-center gap-1.5 cursor-pointer">
                                                    <span>Pilih Kamar Ini</span>
                                                    <span>&rarr;</span>
                                                </button>
                                            @else
                                                <button type="button" disabled class="px-4 py-2.5 bg-gray-200 text-gray-500 font-bold text-xs rounded-xl cursor-not-allowed">
                                                    Penuh / Tidak Tersedia Online
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- Empty State: Seluruh Kamar Penuh -->
                            <div class="py-8 px-4 text-center space-y-4 max-w-md mx-auto">
                                <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 mx-auto flex items-center justify-center">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-gray-900">Semua Kamar Penuh di Tanggal Ini</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Seluruh kamar telah terpesan untuk periode <strong class="text-gray-700">{{ $this->formattedStayDates }}</strong>. Anda dapat mencoba mengganti tanggal menginap atau menghubungi Front Desk untuk alternatif ketersediaan.
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-2 pt-2 justify-center">
                                    <button type="button" wire:click="closeSearchResultsModal" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition cursor-pointer">
                                        Ganti Tanggal Menginap
                                    </button>
                                    @php
                                        $hotelWa = \App\Models\HotelSetting::get('hotel_whatsapp', '6281120070000');
                                        $cleanWa = preg_replace('/[^0-9]/', '', $hotelWa);
                                        $waMsgFull = urlencode("Halo Front Desk InSitu Hotel Garut, saya ingin menanyakan ketersediaan kamar untuk tanggal {$this->formattedStayDates}.");
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanWa }}?text={{ $waMsgFull }}" target="_blank" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                                        <span>Chat WhatsApp Front Desk</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </div>
                        @endif

                    <!-- LANGKAH 2: FORM BIODATA & RINCIAN BIAYA -->
                    @elseif($modalStep === 2 && $this->selectedRoomType)
                        @php
                            $modalRoom = $this->selectedRoomType;
                            $modalPricing = $this->selectedRoomPricing;
                        @endphp

                        <!-- Ringkasan Kamar Terpilih -->
                        <div class="bg-gray-50 rounded-2xl p-3.5 sm:p-4 border border-gray-200 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($modalRoom->primary_photo)
                                    <img src="{{ $modalRoom->primary_photo }}" alt="{{ $modalRoom->name }}" class="w-16 h-16 rounded-xl object-cover border border-gray-200 shrink-0">
                                @endif
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white shadow-xs" style="background-color: {{ $modalRoom->color ?? '#059669' }};">{{ $modalRoom->code }}</span>
                                        <h4 class="font-extrabold text-sm sm:text-base text-gray-900 truncate">{{ $modalRoom->name }}</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $this->formattedStayDates }} &bull; {{ $adults }} Dewasa, {{ $children }} Anak
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="backToRoomSelection" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-white border border-emerald-200 hover:bg-emerald-50 px-3 py-1.5 rounded-lg transition shrink-0 cursor-pointer">
                                &larr; Ganti Kamar
                            </button>
                        </div>

                        <!-- Rincian Biaya Transparan -->
                        @if($modalPricing)
                            <div class="bg-white rounded-2xl p-4 border border-gray-200 text-xs space-y-2">
                                <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                                    <h5 class="font-bold text-gray-800 text-xs uppercase tracking-wide">Rincian Pembayaran Transparan</h5>
                                    <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded">Resmi InSitu Hotel</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Tarif Kamar ({{ $modalPricing['nights'] }} malam):</span>
                                    <span class="font-mono">{{ rupiah($modalPricing['subtotal']) }}</span>
                                </div>
                                @if(($modalPricing['discount_amount'] ?? 0) > 0)
                                    <div class="flex justify-between text-emerald-600 font-semibold">
                                        <span>Potongan Diskon Promo ({{ $promo_code }}):</span>
                                        <span class="font-mono">-{{ rupiah($modalPricing['discount_amount']) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-gray-600">
                                    <span>Pajak Daerah PB1 (10%):</span>
                                    <span class="font-mono">{{ rupiah($modalPricing['tax_amount']) }}</span>
                                </div>
                                <div class="flex justify-between items-baseline pt-2 border-t font-bold text-sm text-gray-900">
                                    <span>Total Pembayaran:</span>
                                    <span class="font-mono text-emerald-700 text-base sm:text-lg font-black">{{ rupiah($modalPricing['total_amount']) }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Formulir Biodata Tamu -->
                        <div class="space-y-4 pt-1">
                            <h5 class="font-bold text-gray-800 text-xs uppercase tracking-wide">Data Diri Pemesan</h5>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="guest_name" placeholder="Nama sesuai KTP" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('guest_name') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp / HP <span class="text-rose-500">*</span></label>
                                    <input type="tel" wire:model="guest_phone" placeholder="Contoh: 08123456789" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('guest_phone') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email (Opsional)</label>
                                    <input type="email" wire:model="guest_email" placeholder="nama@email.com" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('guest_email') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">NIK KTP (Opsional)</label>
                                    <input type="text" wire:model="guest_nik" placeholder="16 digit NIK" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('guest_nik') <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                                <textarea wire:model="guest_notes" rows="2" placeholder="Permintaan khusus (misal: lantai bebas rokok, check-in lebih awal, dll)" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>

                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-[11px] flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pemesanan otomatis ditahan selama 2 jam tanpa perlu kartu kredit. Pembayaran dapat ditransfer via rekening bank resmi hotel.</span>
                            </div>
                        </div>

                        <!-- Footer Actions Langkah 2 -->
                        <div class="pt-3 border-t flex flex-col sm:flex-row items-center justify-between gap-2.5">
                            <button type="button" wire:click="backToRoomSelection" class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                                &larr; Kembali ke Pilihan Kamar
                            </button>
                            <button type="button" wire:click="submitBooking" wire:loading.attr="disabled" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                                <span wire:loading.remove>Konfirmasi Pemesanan Sekarang</span>
                                <span wire:loading class="flex items-center gap-1.5">
                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Memproses...
                                </span>
                            </button>
                        </div>

                    <!-- LANGKAH 3: KARTU SUKSES PEMESANAN -->
                    @elseif($modalStep === 3 && $confirmedReservation)
                        <div class="bg-emerald-50 border-2 border-emerald-500 rounded-2xl p-5 sm:p-6 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-emerald-500 text-white rounded-full shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-base sm:text-lg font-extrabold text-gray-900">Alhamdulillah, Pemesanan Berhasil!</h4>
                                    <p class="text-xs text-gray-600">Kode Reservasi: <span class="font-mono font-black text-emerald-800 text-sm sm:text-base">{{ $confirmedReservation->reservation_number }}</span></p>
                                </div>
                            </div>

                            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs space-y-1">
                                <p class="font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Masa Tahan: 2 Jam (s/d {{ $confirmedReservation->expires_at?->format('H:i') ?? '-' }} WIB)
                                </p>
                                <p>Silakan konfirmasikan pembayaran ke Front Desk via WhatsApp agar kamar tidak terlepas otomatis oleh sistem.</p>
                            </div>

                            <div class="bg-white rounded-xl p-4 border border-gray-200 text-xs space-y-2">
                                <div class="flex justify-between py-1 border-b">
                                    <span class="text-gray-500">Pemesan:</span>
                                    <span class="font-semibold text-gray-900">{{ $confirmedReservation->customer->name }}</span>
                                </div>
                                <div class="flex justify-between py-1 border-b">
                                    <span class="text-gray-500">Tipe Kamar:</span>
                                    <span class="font-semibold text-gray-900">{{ $confirmedReservation->roomType->name }}</span>
                                </div>
                                <div class="flex justify-between py-1 border-b">
                                    <span class="text-gray-500">Jadwal Menginap:</span>
                                    <span class="font-semibold text-gray-900">{{ $confirmedReservation->check_in_date->format('d/m/Y') }} - {{ $confirmedReservation->check_out_date->format('d/m/Y') }} ({{ $confirmedReservation->nights_count }} Malam)</span>
                                </div>
                                <div class="flex justify-between py-1 border-b">
                                    <span class="text-gray-500">Total Tagihan (Termasuk PB1):</span>
                                    <span class="font-bold font-mono text-emerald-700 text-sm sm:text-base">Rp {{ number_format($confirmedReservation->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Info Rekening Bank Resmi -->
                            @php
                                $bankAccounts = \App\Models\HotelSetting::get('bank_accounts', []);
                            @endphp
                            @if(!empty($bankAccounts))
                                <div class="bg-white rounded-xl p-3.5 border border-gray-200 text-xs space-y-2">
                                    <span class="block font-bold text-gray-800 text-[11px] uppercase tracking-wider">Rekening Resmi Pembayaran:</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($bankAccounts as $bank)
                                            @if($bank['is_active'] ?? true)
                                                <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100 flex flex-col">
                                                    <span class="text-[10px] text-gray-400 font-semibold">{{ $bank['bank_name'] }}</span>
                                                    <span class="font-mono font-bold text-gray-900 text-sm select-all">{{ $bank['account_number'] }}</span>
                                                    <span class="text-[10px] text-gray-500">a.n. {{ $bank['account_holder'] }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="pt-2 flex flex-col sm:flex-row gap-2">
                                @php
                                    $hotelWa = \App\Models\HotelSetting::get('hotel_whatsapp', '6281120070000');
                                    $cleanWa = preg_replace('/[^0-9]/', '', $hotelWa);
                                    $waMsg = urlencode("Halo InSitu Hotel Garut, saya telah membuat reservasi online dengan kode: {$confirmedReservation->reservation_number} atas nama {$confirmedReservation->customer->name}. Mohon info instruksi pembayaran.");
                                @endphp
                                <a href="https://wa.me/{{ $cleanWa }}?text={{ $waMsg }}" target="_blank" class="flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-md text-center flex items-center justify-center gap-2">
                                    <span>Konfirmasi ke WhatsApp Front Desk</span>
                                    <span>&rarr;</span>
                                </a>
                                <button type="button" wire:click="resetBooking" class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition cursor-pointer">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @endif

    <!-- FLOATING WHATSAPP ACTION BUTTON & GREETING CARD (FAB) -->
    <div x-data="{ openWaCard: false }" class="fixed bottom-6 right-6 z-40 select-none">
        <!-- Pop-up Greeting Card -->
        <div x-show="openWaCard" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             @click.away="openWaCard = false"
             x-cloak
             class="mb-3 w-80 sm:w-88 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-xs flex items-center justify-center font-bold text-base border border-white/30">
                            IS
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-400 border-2 border-white"></span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm leading-tight">Front Desk InSitu Hotel</h4>
                        <span class="text-[11px] text-emerald-100 block">Online &bull; Tanggap Cepat 24 Jam</span>
                    </div>
                </div>
                <button type="button" @click="openWaCard = false" class="p-1 rounded-full hover:bg-white/20 text-white/80 hover:text-white transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Card Body / Chat Bubble -->
            <div class="p-4 bg-gray-50/70 space-y-3">
                <div class="p-3 bg-white rounded-2xl rounded-tl-xs shadow-2xs border border-gray-100 text-xs text-gray-700 leading-relaxed">
                    <p class="font-medium">Sampurasun! 🙏</p>
                    <p class="mt-1">Ada yang bisa kami bantu seputar reservasi kamar, ketersediaan tanggal, atau sewa ruang meeting di InSitu Hotel Garut?</p>
                </div>
                <span class="text-[10px] text-gray-400 block text-right">InSitu Hotel Syariah Garut</span>
            </div>

            <!-- Card Action Footer -->
            <div class="p-3 bg-white border-t border-gray-100">
                @php
                    $officialWa = \App\Models\HotelSetting::get('hotel_whatsapp', '6281120070000');
                    $cleanWa = preg_replace('/[^0-9]/', '', $officialWa);
                @endphp
                <a href="https://wa.me/{{ $cleanWa }}?text={{ urlencode('Assalamu\'alaikum Front Desk InSitu Hotel Garut, saya ingin bertanya seputar reservasi kamar.') }}" 
                   target="_blank" 
                   class="w-full py-2.5 px-4 bg-[#25D366] hover:bg-[#20ba5a] active:bg-[#1da851] text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    <span>Mulai Chat WhatsApp (+{{ $cleanWa }})</span>
                </a>
            </div>
        </div>

        <!-- Floating Button Trigger -->
        <button type="button" 
                @click="openWaCard = !openWaCard"
                class="relative group w-14 h-14 rounded-full bg-[#25D366] hover:bg-[#20ba5a] text-white shadow-xl hover:shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer"
                title="Hubungi Kami via WhatsApp">
            <!-- Pulsing ring effect -->
            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
            </span>

            <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
        </button>
    </div>
</div>
