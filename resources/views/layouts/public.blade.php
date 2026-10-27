<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>InSitu Hotel Syariah Garut - Hunian Nyaman & Berkah di Kaki Gunung Papandayan</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 bg-gray-50 antialiased selection:bg-emerald-600 selection:text-white" x-data="{ mobileNav: false }">
    @php
        $navMenus = \App\Models\WebMenu::active()->orderBy('sort_order')->get();
        $isHomePage = request()->routeIs('home');
        $hotelPhone = \App\Models\HotelSetting::get('hotel_phone', '0262-123456');
        $hotelWa = \App\Models\HotelSetting::get('hotel_whatsapp', '6281120070000');
        $hotelAddress = \App\Models\HotelSetting::get('hotel_address', 'Jl. Raya Cipanas No. 18, Tarogong Kaler, Kabupaten Garut, Jawa Barat 44151');
    @endphp

    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="p-2 bg-emerald-600 text-white rounded-lg font-bold text-lg leading-none tracking-tight">IS</span>
                <div>
                    <span class="font-extrabold text-gray-900 text-lg tracking-tight block">InSitu Hotel</span>
                    <span class="text-[10px] uppercase font-semibold text-emerald-600 tracking-wider block -mt-1">Syariah Garut</span>
                </div>
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium text-gray-600">
                @if($navMenus->isNotEmpty())
                    @foreach($navMenus as $menu)
                        @php
                            $targetUrl = str_starts_with($menu->url, '#') ? ($isHomePage ? $menu->url : url('/' . $menu->url)) : $menu->url;
                        @endphp
                        @if(str_contains(strtolower($menu->label), 'pesan') || str_contains(strtolower($menu->url), 'booking'))
                            <a href="{{ $targetUrl }}" target="{{ $menu->target }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition">
                                {{ $menu->label }}
                            </a>
                        @else
                            <a href="{{ $targetUrl }}" target="{{ $menu->target }}" class="hover:text-emerald-600 transition">
                                {{ $menu->label }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <!-- Fallback default menus -->
                    <a href="{{ $isHomePage ? '#kamar' : url('/#kamar') }}" class="hover:text-emerald-600 transition">Pilihan Kamar</a>
                    <a href="{{ $isHomePage ? '#fasilitas' : url('/#fasilitas') }}" class="hover:text-emerald-600 transition">Fasilitas</a>
                    <a href="{{ $isHomePage ? '#galeri' : url('/#galeri') }}" class="hover:text-emerald-600 transition">Galeri</a>
                    <a href="{{ $isHomePage ? '#ulasan' : url('/#ulasan') }}" class="hover:text-emerald-600 transition">Ulasan Tamu</a>
                    <a href="{{ $isHomePage ? '#kegiatan' : url('/#kegiatan') }}" class="hover:text-emerald-600 transition">Momen Medsos</a>
                    <a href="{{ route('news.index') }}" class="hover:text-emerald-600 transition">Berita & Promo</a>
                    <a href="{{ $isHomePage ? '#lokasi' : url('/#lokasi') }}" class="hover:text-emerald-600 transition">Lokasi</a>
                    <a href="{{ $isHomePage ? '#booking' : url('/#booking') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition">
                        Pesan Kamar Sekarang
                    </a>
                @endif

                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-gray-700 transition">
                    Portal Karyawan
                </a>
            </nav>

            <!-- Mobile Hamburger Button -->
            <div class="flex md:hidden items-center gap-2">
                <a href="{{ $isHomePage ? '#booking' : url('/#booking') }}" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg">
                    Pesan
                </a>
                <button type="button" @click="mobileNav = !mobileNav" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileNav, 'inline-flex': !mobileNav }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !mobileNav, 'inline-flex': mobileNav }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Nav Menu Drawer -->
        <div x-show="mobileNav" x-cloak class="md:hidden bg-white border-b border-gray-200 px-4 pt-2 pb-4 space-y-2 shadow-lg">
            @if($navMenus->isNotEmpty())
                @foreach($navMenus as $menu)
                    @php
                        $targetUrl = str_starts_with($menu->url, '#') ? ($isHomePage ? $menu->url : url('/' . $menu->url)) : $menu->url;
                    @endphp
                    <a href="{{ $targetUrl }}" target="{{ $menu->target }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                        {{ $menu->label }}
                    </a>
                @endforeach
            @else
                <a href="{{ $isHomePage ? '#kamar' : url('/#kamar') }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50">Pilihan Kamar</a>
                <a href="{{ $isHomePage ? '#fasilitas' : url('/#fasilitas') }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50">Fasilitas</a>
                <a href="{{ $isHomePage ? '#galeri' : url('/#galeri') }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50">Galeri</a>
                <a href="{{ $isHomePage ? '#ulasan' : url('/#ulasan') }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50">Ulasan Tamu</a>
                <a href="{{ $isHomePage ? '#kegiatan' : url('/#kegiatan') }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50">Momen Medsos</a>
                <a href="{{ route('news.index') }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50">Berita & Promo</a>
                <a href="{{ $isHomePage ? '#lokasi' : url('/#lokasi') }}" @click="mobileNav = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-emerald-50">Lokasi</a>
            @endif
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                <a href="{{ route('login') }}" class="text-gray-500 hover:text-emerald-600 font-medium">Portal Karyawan</a>
                <span class="text-gray-400">Garut, Jawa Barat</span>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-2 text-white font-bold text-lg mb-3">
                    <span class="p-1.5 bg-emerald-600 text-white rounded font-bold text-sm">IS</span>
                    <span>InSitu Hotel Syariah Garut</span>
                </div>
                <p class="text-xs leading-relaxed">
                    Akomodasi syariah ramah keluarga berstandar bintang 3 di Garut. Mengedepankan ketenangan ibadah, kebersihan prima, dan keramahan Sunda penuh berkah.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Alamat & Kontak</h4>
                <p class="text-xs leading-relaxed">
                    {{ $hotelAddress }}
                </p>
                <p class="text-xs mt-2 text-emerald-400 font-medium">
                    WhatsApp: {{ $hotelWa }} (Front Desk 24 Jam)
                </p>
                <p class="text-xs mt-1 text-gray-400">
                    Telepon: {{ $hotelPhone }}
                </p>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Layanan Kami</h4>
                <ul class="text-xs space-y-1.5">
                    <li>• 20 Kamar Nyaman Berkonsep Syariah</li>
                    <li>• Ruang Meeting Cimanuk & Papandayan</li>
                    <li>• Restoran Fine Dining Liwet Garut</li>
                    <li>• Musholla & Arah Kiblat Terverifikasi</li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-8 border-t border-gray-800 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} InSitu Hotel Syariah Garut. SikolaNata3 PMS. Semua hak dilindungi undang-undang.
        </div>
    </footer>
</body>
</html>
