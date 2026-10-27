<div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Header Page & Tabs -->
    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-emerald-100 text-emerald-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Administrasi Website</h1>
                    <p class="text-xs text-gray-500">Kelola artikel berita, banyak foto galeri, menu navigasi, dan konten landing page secara mandiri.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Buka Landing Page</span>
            </a>
            <a href="{{ route('news.index') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-xl border border-emerald-200 transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <span>Katalog Berita</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 bg-white rounded-xl shadow-xs px-4">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            <button wire:click="$set('activeTab', 'posts')" class="cursor-pointer py-4 px-1 border-b-2 font-semibold text-xs whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'posts' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <span>Post Berita & Promo</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full {{ $activeTab === 'posts' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ \App\Models\WebPost::count() }}</span>
            </button>

            <button wire:click="$set('activeTab', 'galleries')" class="cursor-pointer py-4 px-1 border-b-2 font-semibold text-xs whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'galleries' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Galeri Banyak Gambar</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full {{ $activeTab === 'galleries' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ \App\Models\WebGallery::count() }}</span>
            </button>

            <button wire:click="$set('activeTab', 'menus')" class="cursor-pointer py-4 px-1 border-b-2 font-semibold text-xs whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'menus' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span>Menu Navigasi Web</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full {{ $activeTab === 'menus' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ \App\Models\WebMenu::count() }}</span>
            </button>

            <button wire:click="$set('activeTab', 'settings')" class="cursor-pointer py-4 px-1 border-b-2 font-semibold text-xs whitespace-nowrap transition flex items-center gap-2 {{ $activeTab === 'settings' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Teks Hero & Kontak</span>
            </button>
        </nav>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: POST BERITA & PROMO -->
    <!-- ============================================================== -->
    @if($activeTab === 'posts')
        <div class="space-y-4">
            @if(session()->has('post_success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between">
                    <span>{{ session('post_success') }}</span>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            <!-- Post Toolbar -->
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <input type="text" wire:model.live.debounce.300ms="postSearch" placeholder="Cari judul berita..." class="w-full text-xs rounded-lg border-gray-300 pl-8 pr-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <select wire:model.live="postCategoryFilter" class="text-xs rounded-lg border-gray-300 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Kategori</option>
                        <option value="Berita">Berita</option>
                        <option value="Promo">Promo & Diskon</option>
                        <option value="Kegiatan">Kegiatan Hotel</option>
                        <option value="Wisata Garut">Wisata Sekitar</option>
                    </select>
                </div>
                <button wire:click="openCreatePostModal" class="w-full sm:w-auto cursor-pointer inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tulis Post Baru</span>
                </button>
            </div>

            <!-- Posts Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold text-[11px]">
                                <th class="py-3 px-4">Artikel / Berita</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Dibaca</th>
                                <th class="py-3 px-4">Tanggal Rilis</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($posts as $post)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-14 h-10 rounded-lg bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                                                @if($post->featured_image_url)
                                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('news.detail', $post->slug) }}" target="_blank" class="font-bold text-gray-900 hover:text-emerald-600 transition block">
                                                    {{ $post->title }}
                                                </a>
                                                <span class="text-[10px] text-gray-400 font-mono">/berita/{{ $post->slug }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">
                                            {{ $post->category }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button wire:click="togglePostStatus({{ $post->id }})" class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-amber-100 text-amber-800 hover:bg-amber-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $post->status === 'published' ? 'bg-emerald-600' : 'bg-amber-600' }}"></span>
                                            <span>{{ ucfirst($post->status) }}</span>
                                        </button>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-gray-600">
                                        {{ number_format($post->views_count) }}x
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $post->published_at ? $post->published_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button wire:click="editPost({{ $post->id }})" class="text-emerald-600 hover:text-emerald-800 font-semibold cursor-pointer">
                                            Edit
                                        </button>
                                        <button wire:click="deletePost({{ $post->id }})" wire:confirm="Hapus postingan ini secara permanen?" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        Belum ada postingan berita atau artikel. Klik "Tulis Post Baru" di atas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($posts->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $posts->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- TAB 2: GALERI BANYAK GAMBAR -->
    <!-- ============================================================== -->
    @if($activeTab === 'galleries')
        <div class="space-y-4">
            @if(session()->has('gallery_success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between">
                    <span>{{ session('gallery_success') }}</span>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">Filter Kategori:</span>
                    <select wire:model.live="galleryCategoryFilter" class="text-xs rounded-lg border-gray-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Foto Galeri</option>
                        <option value="gallery">Galeri Suasana Hotel</option>
                        <option value="slider">Banner Slider Utama</option>
                        <option value="facility">Fasilitas & Layanan</option>
                    </select>
                </div>
                <button wire:click="openCreateGalleryModal" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Upload Banyak Foto</span>
                </button>
            </div>

            <!-- Galleries Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @forelse($galleries as $gallery)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden flex flex-col justify-between group hover:shadow-md transition">
                        <div class="relative aspect-video bg-gray-100 overflow-hidden">
                            <img src="{{ $gallery->image_url }}" alt="{{ $gallery->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute top-2 left-2 flex gap-1">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-black/60 text-white backdrop-blur-xs">
                                    {{ $gallery->category }}
                                </span>
                            </div>
                            <div class="absolute top-2 right-2">
                                <button wire:click="toggleGalleryStatus({{ $gallery->id }})" title="Klik untuk toggle aktif/nonaktif" class="cursor-pointer px-2 py-0.5 rounded-full text-[9px] font-bold transition {{ $gallery->is_active ? 'bg-emerald-600 text-white' : 'bg-gray-600 text-gray-200' }}">
                                    {{ $gallery->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </div>
                        </div>
                        <div class="p-3 space-y-1">
                            <h4 class="font-bold text-gray-900 text-xs truncate" title="{{ $gallery->title }}">{{ $gallery->title }}</h4>
                            <p class="text-[10px] text-gray-500 truncate">{{ $gallery->caption ?: 'Tanpa keterangan' }}</p>
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-[11px]">
                                <span class="text-gray-400 font-mono text-[10px]">Urutan #{{ $gallery->sort_order }}</span>
                                <div class="flex items-center gap-2">
                                    <button wire:click="editGallery({{ $gallery->id }})" class="text-emerald-600 hover:text-emerald-800 font-semibold cursor-pointer">Edit</button>
                                    <button wire:click="deleteGallery({{ $gallery->id }})" wire:confirm="Hapus foto ini?" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center bg-white rounded-xl border border-gray-200">
                        <p class="text-xs text-gray-400">Belum ada foto galeri yang diunggah. Klik "Upload Banyak Foto" untuk menambahkan koleksi foto hotel.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- TAB 3: MENU NAVIGASI DINAMIS -->
    <!-- ============================================================== -->
    @if($activeTab === 'menus')
        <div class="space-y-4">
            @if(session()->has('menu_success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between">
                    <span>{{ session('menu_success') }}</span>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-sm text-gray-800">Daftar Link Navigasi Navbar Landing Page</h3>
                    <p class="text-[11px] text-gray-500">Anda dapat menghubungkan label navbar ke anchor internal (misal <code class="bg-gray-100 px-1 py-0.5 rounded text-emerald-700">#kamar</code>), link rute berita (<code class="bg-gray-100 px-1 py-0.5 rounded text-emerald-700">/berita</code>), atau halaman statis yang Anda buat manual via kode.</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($menus->isEmpty())
                        <button wire:click="seedDefaultMenus" class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-semibold rounded-lg transition">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Pasang Menu Standar</span>
                        </button>
                    @endif
                    <button wire:click="openCreateMenuModal" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Menu Navigasi</span>
                    </button>
                </div>
            </div>

            <!-- Menus Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold text-[11px]">
                            <th class="py-3 px-4 w-20 text-center">Urutan</th>
                            <th class="py-3 px-4">Label Menu</th>
                            <th class="py-3 px-4">Tautan URL / Halaman</th>
                            <th class="py-3 px-4 text-center">Target</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($menus as $menu)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-center font-bold font-mono text-gray-700">
                                    {{ $menu->sort_order }}
                                </td>
                                <td class="py-3 px-4 font-bold text-gray-900">
                                    {{ $menu->label }}
                                </td>
                                <td class="py-3 px-4 font-mono text-emerald-700">
                                    <a href="{{ $menu->url }}" target="{{ $menu->target }}" class="hover:underline flex items-center gap-1">
                                        <span>{{ $menu->url }}</span>
                                        @if($menu->target === '_blank')
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        @endif
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-500">
                                    <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px]">{{ $menu->target }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button wire:click="toggleMenuStatus({{ $menu->id }})" class="cursor-pointer px-2.5 py-1 rounded-full text-[10px] font-bold transition {{ $menu->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <button wire:click="editMenu({{ $menu->id }})" class="text-emerald-600 hover:text-emerald-800 font-semibold cursor-pointer">
                                        Edit
                                    </button>
                                    <button wire:click="deleteMenu({{ $menu->id }})" wire:confirm="Hapus menu ini?" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400">
                                    Belum ada menu navigasi khusus. Klik tombol "Pasang Menu Standar" di atas untuk memasang tautan default.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- TAB 4: TEKS HERO & KONTAK -->
    <!-- ============================================================== -->
    @if($activeTab === 'settings')
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-6 space-y-6">
            @if(session()->has('settings_success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between">
                    <span>{{ session('settings_success') }}</span>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            <div>
                <h3 class="text-base font-bold text-gray-900">Konten Hero Banner & Informasi Kontak Landing Page</h3>
                <p class="text-xs text-gray-500">Teks ini langsung muncul pada bagian atas dan footer website publik InSitu Hotel.</p>
            </div>

            <form wire:submit="saveSettings" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Badge Tagline Atas</label>
                        <input type="text" wire:model="hero_tagline" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Utama Hero (Headline)</label>
                        <input type="text" wire:model="hero_title" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Sub-judul / Paragraf Deskripsi Hero</label>
                        <textarea wire:model="hero_subtitle" rows="3" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp Layanan Tamu</label>
                        <input type="text" wire:model="web_whatsapp" placeholder="Contoh: 628122334455" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Telepon Kantor (Front Desk)</label>
                        <input type="text" wire:model="web_phone" placeholder="Contoh: 0262-123456" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Lengkap Hotel</label>
                        <textarea wire:model="web_address" rows="2" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Link Tautan Google Maps</label>
                        <input type="text" wire:model="web_maps_url" placeholder="https://maps.google.com/..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="cursor-pointer px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                        Simpan Perubahan Teks
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL POST BERITA -->
    <!-- ============================================================== -->
    @if($showPostModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-2xl relative max-h-[90vh] overflow-y-auto" @click.outside="$wire.set('showPostModal', false)">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-lg text-gray-900">
                        {{ $editingPostId ? 'Edit Post Berita / Artikel' : 'Tulis Post Berita Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showPostModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer text-xl font-bold">&times;</button>
                </div>

                <form wire:submit="savePost" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Artikel *</label>
                        <input type="text" wire:model.live.debounce.400ms="post_title" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                        @error('post_title') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">URL Slug *</label>
                            <input type="text" wire:model="post_slug" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                            @error('post_slug') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori *</label>
                            <select wire:model="post_category" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="Berita">Berita Hotel</option>
                                <option value="Promo">Promo & Diskon</option>
                                <option value="Kegiatan">Kegiatan Tamu</option>
                                <option value="Wisata Garut">Wisata Sekitar</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Gambar Utama (Featured Image)</label>
                        <input type="file" wire:model="post_featured_image" accept="image/*" class="w-full text-xs border border-gray-300 rounded-lg p-2">
                        @if($post_featured_image)
                            <div class="mt-2 text-[11px] text-emerald-600 font-semibold">Preview siap diunggah.</div>
                        @elseif($current_featured_image)
                            <div class="mt-2 text-[10px] text-gray-500">Gambar tersimpan: <span class="font-mono">{{ basename($current_featured_image) }}</span></div>
                        @endif
                        @error('post_featured_image') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Ringkasan Singkat (Excerpt)</label>
                        <textarea wire:model="post_summary" rows="2" placeholder="1-2 kalimat ringkasan yang muncul di kartu artikel..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Isi Artikel Lengkap *</label>
                        <textarea wire:model="post_content" rows="6" placeholder="Tuliskan berita, ketentuan promo, atau artikel kegiatan..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required></textarea>
                        @error('post_content') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Status Publikasi</label>
                            <select wire:model="post_status" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="published">Tayangkan Sekarang (Published)</option>
                                <option value="draft">Simpan Draft</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Jadwal Tanggal Terbit</label>
                            <input type="datetime-local" wire:model="post_published_at" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" wire:click="$set('showPostModal', false)" class="cursor-pointer px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">Batal</button>
                        <button type="submit" class="cursor-pointer px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl">Simpan Postingan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL GALERI FOTO (BANYAK GAMBAR) -->
    <!-- ============================================================== -->
    @if($showGalleryModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative" @click.outside="$wire.set('showGalleryModal', false)">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-lg text-gray-900">
                        {{ $editingGalleryId ? 'Edit Data Foto' : 'Unggah Foto Galeri' }}
                    </h3>
                    <button type="button" wire:click="$set('showGalleryModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer text-xl font-bold">&times;</button>
                </div>

                <form wire:submit="saveGallery" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Judul / Nama Foto *</label>
                        <input type="text" wire:model="gallery_title" placeholder="Misal: Kolam Renang Air Hangat" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                        @error('gallery_title') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori Tampilan *</label>
                        <select wire:model="gallery_category" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="gallery">Galeri Suasana Hotel</option>
                            <option value="slider">Banner Slider Utama</option>
                            <option value="facility">Fasilitas Hotel</option>
                        </select>
                    </div>

                    @if(!$editingGalleryId)
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih File Foto (Bisa Banyak Sekaligus) *</label>
                            <input type="file" wire:model="gallery_photos" multiple accept="image/*" class="w-full text-xs border border-gray-300 rounded-lg p-2" required>
                            <p class="text-[10px] text-gray-500 mt-1">Dapat memilih lebih dari 1 file gambar sekaligus (maks 4MB/foto).</p>
                            @if(!empty($gallery_photos))
                                <div class="mt-2 text-xs font-bold text-emerald-700 bg-emerald-50 p-2 rounded-lg">
                                    {{ count($gallery_photos) }} foto terpilih dan siap diunggah.
                                </div>
                            @endif
                            @error('gallery_photos') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Ganti Foto (Opsional)</label>
                            <input type="file" wire:model="single_gallery_photo" accept="image/*" class="w-full text-xs border border-gray-300 rounded-lg p-2">
                            @if($current_gallery_image)
                                <div class="mt-2 text-[10px] text-gray-500">Foto saat ini: {{ basename($current_gallery_image) }}</div>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Caption</label>
                        <input type="text" wire:model="gallery_caption" placeholder="Keterangan singkat saat foto diklik..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Urutan</label>
                            <input type="number" wire:model="gallery_sort_order" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        @if($editingGalleryId)
                            <div class="flex items-center pt-5">
                                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                    <input type="checkbox" wire:model="gallery_is_active" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>Aktif Ditampilkan</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" wire:click="$set('showGalleryModal', false)" class="cursor-pointer px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">Batal</button>
                        <button type="submit" class="cursor-pointer px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl">
                            {{ $editingGalleryId ? 'Simpan Perubahan' : 'Unggah Sekarang' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL MENU NAVIGASI DINAMIS -->
    <!-- ============================================================== -->
    @if($showMenuModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl relative" @click.outside="$wire.set('showMenuModal', false)">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-lg text-gray-900">
                        {{ $editingMenuId ? 'Edit Link Menu' : 'Tambah Link Menu Navigasi' }}
                    </h3>
                    <button type="button" wire:click="$set('showMenuModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer text-xl font-bold">&times;</button>
                </div>

                <form wire:submit="saveMenu" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Label Menu *</label>
                        <input type="text" wire:model="menu_label" placeholder="Misal: Pilihan Kamar, Wisata Garut" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                        @error('menu_label') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">URL / Link Tujuan *</label>
                        <input type="text" wire:model="menu_url" placeholder="Misal: #kamar, /berita, atau /halaman-anda" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                        <p class="text-[10px] text-gray-500 mt-1">Dapat berupa anchor (<code>#fasilitas</code>), link internal (<code>/berita</code>), atau URL luar.</p>
                        @error('menu_url') <span class="text-rose-600 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Target Link</label>
                            <select wire:model="menu_target" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="_self">Buka di Tab yang Sama (_self)</option>
                                <option value="_blank">Buka Tab Baru (_blank)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Urutan Tampil</label>
                            <input type="number" wire:model="menu_sort_order" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                            <input type="checkbox" wire:model="menu_is_active" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Tampilkan di Navbar</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" wire:click="$set('showMenuModal', false)" class="cursor-pointer px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">Batal</button>
                        <button type="submit" class="cursor-pointer px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl">Simpan Menu</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
