<div>
    <!-- Hero Header -->
    <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-gray-900 text-white py-14 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-800/80 text-emerald-200 border border-emerald-700/50">
                Informasi & Penawaran Spesial
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Kabar, Promo & Artikel Hotel</h1>
            <p class="text-sm sm:text-base text-emerald-100/80 max-w-2xl mx-auto">
                Temukan tips liburan syariah di Garut, penawaran diskon musiman, serta kegiatan inspiratif di InSitu Hotel.
            </p>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white border-b border-gray-200 sticky top-16 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Category Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                <button wire:click="selectCategory('')" class="cursor-pointer px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition {{ $category === '' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua Kabar
                </button>
                @foreach($categories as $cat)
                    <button wire:click="selectCategory('{{ $cat }}')" class="cursor-pointer px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition {{ $category === $cat ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>

            <!-- Search Field -->
            <div class="relative w-full md:w-72">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari artikel atau promo..." class="w-full text-xs rounded-full border-gray-300 pl-9 pr-3 py-1.5 focus:border-emerald-500 focus:ring-emerald-500">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if($posts->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-800">Tidak ada artikel ditemukan</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto">
                    @if($search || $category)
                        Coba kata kunci pencarian lain atau pilih kategori lain.
                    @else
                        Belum ada postingan artikel yang dipublikasikan saat ini.
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                    <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                        <div>
                            <!-- Featured Image -->
                            <a href="{{ route('news.detail', $post->slug) }}" class="block aspect-[16/10] bg-gray-100 overflow-hidden relative">
                                @if($post->featured_image_url)
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-700/90 text-white backdrop-blur-xs shadow-xs">
                                        {{ $post->category }}
                                    </span>
                                </div>
                            </a>

                            <!-- Card Body -->
                            <div class="p-5 space-y-2.5">
                                <div class="flex items-center gap-2 text-[11px] text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $post->published_at ? $post->published_at->format('d M Y') : '-' }}</span>
                                    <span>•</span>
                                    <span>{{ number_format($post->views_count) }}x dibaca</span>
                                </div>

                                <h2 class="text-base font-bold text-gray-900 group-hover:text-emerald-700 transition leading-snug">
                                    <a href="{{ route('news.detail', $post->slug) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed">
                                    {{ $post->summary ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
                                </p>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-5 pt-0">
                            <a href="{{ route('news.detail', $post->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 transition group/link">
                                <span>Baca Selengkapnya</span>
                                <svg class="w-3.5 h-3.5 transform group-hover/link:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
