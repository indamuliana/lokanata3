<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex text-xs text-gray-500 gap-2 items-center">
            <a href="{{ route('home') }}" class="hover:text-emerald-600 transition">Beranda</a>
            <span>/</span>
            <a href="{{ route('news.index') }}" class="hover:text-emerald-600 transition">Berita & Promo</a>
            <span>/</span>
            <span class="text-gray-800 font-semibold truncate">{{ $post->title }}</span>
        </nav>

        <!-- Article Card -->
        <article class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden p-6 sm:p-10 space-y-8">
            <!-- Article Header -->
            <header class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                        {{ $post->category }}
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500 font-medium">
                        {{ $post->published_at ? $post->published_at->format('l, d F Y') : '-' }}
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500 font-mono">{{ number_format($post->views_count) }}x dibaca</span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 leading-tight">
                    {{ $post->title }}
                </h1>

                @if($post->summary)
                    <p class="text-base text-gray-600 font-medium italic border-l-4 border-emerald-500 pl-4 py-1 bg-emerald-50/50 rounded-r-lg">
                        {{ $post->summary }}
                    </p>
                @endif
            </header>

            <!-- Featured Image -->
            @if($post->featured_image_url)
                <div class="rounded-2xl overflow-hidden shadow-sm bg-gray-100 border border-gray-100">
                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-auto max-h-[500px] object-cover">
                </div>
            @endif

            <!-- Article Content -->
            <div class="prose max-w-none text-gray-800 text-sm sm:text-base leading-relaxed space-y-4 whitespace-pre-line">
                {{ $post->content }}
            </div>

            <!-- Share & CTA Footer -->
            <div class="pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-600">Bagikan artikel ini:</span>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . url()->current()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                </div>

                <a href="{{ route('home') }}#booking" class="cursor-pointer px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    Pesan Kamar Sekarang
                </a>
            </div>
        </article>

        <!-- Related Posts Section -->
        @if($relatedPosts->isNotEmpty())
            <div class="space-y-4 pt-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-lg text-gray-900">Artikel & Kabar Lainnya</h3>
                    <a href="{{ route('news.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($relatedPosts as $rel)
                        <a href="{{ route('news.detail', $rel->slug) }}" class="bg-white rounded-2xl border border-gray-200 p-4 shadow-xs hover:shadow-md transition block group">
                            @if($rel->featured_image_url)
                                <div class="aspect-video rounded-xl overflow-hidden mb-3 bg-gray-100">
                                    <img src="{{ $rel->featured_image_url }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                </div>
                            @endif
                            <span class="text-[10px] font-bold uppercase text-emerald-700 block">{{ $rel->category }}</span>
                            <h4 class="font-bold text-xs text-gray-900 group-hover:text-emerald-600 line-clamp-2 mt-1">
                                {{ $rel->title }}
                            </h4>
                            <span class="text-[10px] text-gray-400 mt-2 block">{{ $rel->published_at ? $rel->published_at->format('d M Y') : '' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
