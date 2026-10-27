<?php

namespace App\Livewire;

use App\Models\HotelSetting;
use App\Models\WebGallery;
use App\Models\WebMenu;
use App\Models\WebPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class WebAdministration extends Component
{
    use WithFileUploads, WithPagination;

    public string $activeTab = 'posts'; // posts, galleries, menus, settings

    // --- Tab 1: Posts (Berita & Promo) ---
    public string $postSearch = '';
    public string $postCategoryFilter = '';
    public bool $showPostModal = false;
    public ?int $editingPostId = null;
    public string $post_title = '';
    public string $post_slug = '';
    public string $post_category = 'Berita';
    public string $post_summary = '';
    public string $post_content = '';
    public string $post_status = 'published';
    public ?string $post_published_at = null;
    public $post_featured_image = null;
    public ?string $current_featured_image = null;

    // --- Tab 2: Galleries (Banyak Gambar) ---
    public string $galleryCategoryFilter = '';
    public bool $showGalleryModal = false;
    public ?int $editingGalleryId = null;
    public string $gallery_title = '';
    public string $gallery_category = 'gallery'; // gallery, slider, facility
    public string $gallery_caption = '';
    public int $gallery_sort_order = 0;
    public bool $gallery_is_active = true;
    public $gallery_photos = []; // multiple upload
    public $single_gallery_photo = null; // single upload/edit
    public ?string $current_gallery_image = null;

    // --- Tab 3: Menus (Navigasi Dinamis) ---
    public bool $showMenuModal = false;
    public ?int $editingMenuId = null;
    public string $menu_label = '';
    public string $menu_url = '';
    public string $menu_target = '_self';
    public int $menu_sort_order = 0;
    public bool $menu_is_active = true;

    // --- Tab 4: Settings (Hero & Konten Teks) ---
    public string $hero_title = '';
    public string $hero_subtitle = '';
    public string $hero_tagline = '';
    public string $web_whatsapp = '';
    public string $web_phone = '';
    public string $web_address = '';
    public string $web_maps_url = '';

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->loadSettings();
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin'))) {
            abort(403, 'Akses ditolak. Hanya Manager yang berwenang mengelola CMS dan konten website.');
        }
    }

    public function loadSettings(): void
    {
        $this->hero_title = (string) HotelSetting::get('hero_title', 'Hunian Nyaman Penuh Berkah di Kaki Gunung Papandayan');
        $this->hero_subtitle = (string) HotelSetting::get('hero_subtitle', 'Nikmati udara sejuk khas Garut, pemandian air panas alami Cipanas, dan kenyamanan istirahat dengan nilai-nilai syariah yang menentramkan hati.');
        $this->hero_tagline = (string) HotelSetting::get('hero_tagline', '★ Hotel Syariah Bintang 3 Ramah Keluarga di Garut');
        $this->web_whatsapp = (string) HotelSetting::get('hotel_whatsapp', '628122334455');
        $this->web_phone = (string) HotelSetting::get('hotel_phone', '0262-123456');
        $this->web_address = (string) HotelSetting::get('hotel_address', 'Jl. Raya Cipanas No. 18, Tarogong Kaler, Kabupaten Garut, Jawa Barat 44151');
        $this->web_maps_url = (string) HotelSetting::get('web_maps_url', 'https://maps.google.com/?q=Garut');
    }

    public function updatedPostTitle($value): void
    {
        if (!$this->editingPostId) {
            $this->post_slug = Str::slug($value);
        }
    }

    // ==========================================
    // TAB 1: POSTS ACTIONS
    // ==========================================
    public function openCreatePostModal(): void
    {
        $this->resetPostForm();
        $this->post_published_at = now()->format('Y-m-d\TH:i');
        $this->showPostModal = true;
    }

    public function editPost(int $id): void
    {
        $post = WebPost::findOrFail($id);
        $this->editingPostId = $post->id;
        $this->post_title = $post->title;
        $this->post_slug = $post->slug;
        $this->post_category = $post->category;
        $this->post_summary = $post->summary ?? '';
        $this->post_content = $post->content;
        $this->post_status = $post->status;
        $this->post_published_at = $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : null;
        $this->current_featured_image = $post->featured_image;
        $this->post_featured_image = null;
        $this->showPostModal = true;
    }

    public function savePost(): void
    {
        $rules = [
            'post_title' => 'required|string|max:255',
            'post_slug' => 'required|string|max:255|unique:web_posts,slug,' . ($this->editingPostId ?? 'NULL') . ',id',
            'post_category' => 'required|string|max:100',
            'post_content' => 'required|string',
            'post_status' => 'required|in:published,draft',
            'post_featured_image' => 'nullable|image|max:3072',
        ];

        $this->validate($rules);

        $imagePath = $this->current_featured_image;
        if ($this->post_featured_image) {
            $imagePath = $this->post_featured_image->store('web-posts', 'public');
        }

        $data = [
            'title' => $this->post_title,
            'slug' => Str::slug($this->post_slug),
            'category' => $this->post_category,
            'summary' => $this->post_summary ?: null,
            'content' => $this->post_content,
            'featured_image' => $imagePath,
            'status' => $this->post_status,
            'published_at' => $this->post_published_at ? Carbon::parse($this->post_published_at) : ($this->post_status === 'published' ? now() : null),
        ];

        if ($this->editingPostId) {
            $post = WebPost::findOrFail($this->editingPostId);
            $post->update($data);
            session()->flash('post_success', 'Post berita/artikel berhasil diperbarui.');
        } else {
            $data['author_id'] = Auth::id();
            WebPost::create($data);
            session()->flash('post_success', 'Post berita/artikel baru berhasil ditambahkan.');
        }

        $this->showPostModal = false;
        $this->resetPostForm();
    }

    public function togglePostStatus(int $id): void
    {
        $post = WebPost::findOrFail($id);
        $newStatus = $post->status === 'published' ? 'draft' : 'published';
        $post->update([
            'status' => $newStatus,
            'published_at' => ($newStatus === 'published' && !$post->published_at) ? now() : $post->published_at,
        ]);
        session()->flash('post_success', "Status post '{$post->title}' diubah menjadi {$newStatus}.");
    }

    public function deletePost(int $id): void
    {
        $post = WebPost::findOrFail($id);
        $post->delete();
        session()->flash('post_success', 'Post berita/artikel berhasil dihapus.');
    }

    private function resetPostForm(): void
    {
        $this->editingPostId = null;
        $this->post_title = '';
        $this->post_slug = '';
        $this->post_category = 'Berita';
        $this->post_summary = '';
        $this->post_content = '';
        $this->post_status = 'published';
        $this->post_published_at = null;
        $this->post_featured_image = null;
        $this->current_featured_image = null;
        $this->resetValidation();
    }

    // ==========================================
    // TAB 2: GALLERIES ACTIONS
    // ==========================================
    public function openCreateGalleryModal(): void
    {
        $this->resetGalleryForm();
        $this->showGalleryModal = true;
    }

    public function editGallery(int $id): void
    {
        $item = WebGallery::findOrFail($id);
        $this->editingGalleryId = $item->id;
        $this->gallery_title = $item->title;
        $this->gallery_category = $item->category;
        $this->gallery_caption = $item->caption ?? '';
        $this->gallery_sort_order = $item->sort_order;
        $this->gallery_is_active = $item->is_active;
        $this->current_gallery_image = $item->image_path;
        $this->gallery_photos = [];
        $this->single_gallery_photo = null;
        $this->showGalleryModal = true;
    }

    public function saveGallery(): void
    {
        if ($this->editingGalleryId) {
            $this->validate([
                'gallery_title' => 'required|string|max:255',
                'gallery_category' => 'required|string|in:gallery,slider,facility',
                'single_gallery_photo' => 'nullable|image|max:4096',
                'gallery_sort_order' => 'integer',
            ]);

            $item = WebGallery::findOrFail($this->editingGalleryId);
            $imagePath = $item->image_path;

            if ($this->single_gallery_photo) {
                $imagePath = $this->single_gallery_photo->store('web-galleries', 'public');
            }

            $item->update([
                'title' => $this->gallery_title,
                'image_path' => $imagePath,
                'caption' => $this->gallery_caption ?: null,
                'category' => $this->gallery_category,
                'sort_order' => $this->gallery_sort_order,
                'is_active' => $this->gallery_is_active,
            ]);

            session()->flash('gallery_success', 'Foto galeri berhasil diperbarui.');
        } else {
            // Create mode: supports multiple photos
            $this->validate([
                'gallery_title' => 'required|string|max:255',
                'gallery_category' => 'required|string|in:gallery,slider,facility',
                'gallery_photos' => 'required|array|min:1',
                'gallery_photos.*' => 'image|max:4096',
                'gallery_sort_order' => 'integer',
            ]);

            $order = $this->gallery_sort_order;
            $count = 0;
            foreach ($this->gallery_photos as $photo) {
                $path = $photo->store('web-galleries', 'public');
                WebGallery::create([
                    'title' => count($this->gallery_photos) > 1 ? "{$this->gallery_title} (" . ($count + 1) . ")" : $this->gallery_title,
                    'image_path' => $path,
                    'caption' => $this->gallery_caption ?: null,
                    'category' => $this->gallery_category,
                    'sort_order' => $order + $count,
                    'is_active' => $this->gallery_is_active,
                ]);
                $count++;
            }

            session()->flash('gallery_success', "Berhasil mengunggah {$count} foto ke galeri website.");
        }

        $this->showGalleryModal = false;
        $this->resetGalleryForm();
    }

    public function toggleGalleryStatus(int $id): void
    {
        $item = WebGallery::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        session()->flash('gallery_success', "Status foto '{$item->title}' diperbarui.");
    }

    public function deleteGallery(int $id): void
    {
        $item = WebGallery::findOrFail($id);
        $item->delete();
        session()->flash('gallery_success', 'Foto berhasil dihapus dari galeri.');
    }

    private function resetGalleryForm(): void
    {
        $this->editingGalleryId = null;
        $this->gallery_title = '';
        $this->gallery_category = 'gallery';
        $this->gallery_caption = '';
        $this->gallery_sort_order = 0;
        $this->gallery_is_active = true;
        $this->gallery_photos = [];
        $this->single_gallery_photo = null;
        $this->current_gallery_image = null;
        $this->resetValidation();
    }

    // ==========================================
    // TAB 3: MENUS ACTIONS
    // ==========================================
    public function openCreateMenuModal(): void
    {
        $this->resetMenuForm();
        $this->showMenuModal = true;
    }

    public function editMenu(int $id): void
    {
        $menu = WebMenu::findOrFail($id);
        $this->editingMenuId = $menu->id;
        $this->menu_label = $menu->label;
        $this->menu_url = $menu->url;
        $this->menu_target = $menu->target;
        $this->menu_sort_order = $menu->sort_order;
        $this->menu_is_active = $menu->is_active;
        $this->showMenuModal = true;
    }

    public function saveMenu(): void
    {
        $this->validate([
            'menu_label' => 'required|string|max:100',
            'menu_url' => 'required|string|max:255',
            'menu_target' => 'required|in:_self,_blank',
            'menu_sort_order' => 'integer',
        ]);

        $data = [
            'label' => $this->menu_label,
            'url' => $this->menu_url,
            'target' => $this->menu_target,
            'sort_order' => $this->menu_sort_order,
            'is_active' => $this->menu_is_active,
        ];

        if ($this->editingMenuId) {
            $menu = WebMenu::findOrFail($this->editingMenuId);
            $menu->update($data);
            session()->flash('menu_success', "Menu '{$this->menu_label}' berhasil diperbarui.");
        } else {
            WebMenu::create($data);
            session()->flash('menu_success', "Menu baru '{$this->menu_label}' berhasil ditambahkan.");
        }

        $this->showMenuModal = false;
        $this->resetMenuForm();
    }

    public function toggleMenuStatus(int $id): void
    {
        $menu = WebMenu::findOrFail($id);
        $menu->update(['is_active' => !$menu->is_active]);
        session()->flash('menu_success', "Status menu '{$menu->label}' diperbarui.");
    }

    public function deleteMenu(int $id): void
    {
        $menu = WebMenu::findOrFail($id);
        $menu->delete();
        session()->flash('menu_success', 'Menu berhasil dihapus.');
    }

    public function seedDefaultMenus(): void
    {
        $defaults = [
            ['label' => 'Pilihan Kamar', 'url' => '#kamar', 'sort_order' => 10],
            ['label' => 'Fasilitas Syariah', 'url' => '#fasilitas', 'sort_order' => 20],
            ['label' => 'Galeri Foto', 'url' => '#galeri', 'sort_order' => 30],
            ['label' => 'Berita & Promo', 'url' => '/berita', 'sort_order' => 40],
            ['label' => 'Lokasi & Wisata', 'url' => '#lokasi', 'sort_order' => 50],
            ['label' => 'Pesan Sekarang', 'url' => '#booking', 'sort_order' => 60],
        ];

        foreach ($defaults as $item) {
            WebMenu::firstOrCreate(
                ['label' => $item['label']],
                [
                    'url' => $item['url'],
                    'target' => '_self',
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        session()->flash('menu_success', 'Preset menu standar berhasil dipasang.');
    }

    private function resetMenuForm(): void
    {
        $this->editingMenuId = null;
        $this->menu_label = '';
        $this->menu_url = '';
        $this->menu_target = '_self';
        $this->menu_sort_order = 0;
        $this->menu_is_active = true;
        $this->resetValidation();
    }

    // ==========================================
    // TAB 4: SETTINGS ACTIONS
    // ==========================================
    public function saveSettings(): void
    {
        $this->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'hero_tagline' => 'nullable|string|max:255',
            'web_whatsapp' => 'nullable|string|max:50',
            'web_phone' => 'nullable|string|max:50',
            'web_address' => 'nullable|string',
            'web_maps_url' => 'nullable|string|max:500',
        ]);

        HotelSetting::set('hero_title', $this->hero_title, 'string', 'landing');
        HotelSetting::set('hero_subtitle', $this->hero_subtitle, 'string', 'landing');
        HotelSetting::set('hero_tagline', $this->hero_tagline, 'string', 'landing');
        HotelSetting::set('hotel_whatsapp', $this->web_whatsapp, 'string', 'general');
        HotelSetting::set('hotel_phone', $this->web_phone, 'string', 'general');
        HotelSetting::set('hotel_address', $this->web_address, 'string', 'general');
        HotelSetting::set('web_maps_url', $this->web_maps_url, 'string', 'landing');

        session()->flash('settings_success', 'Pengaturan teks landing page dan kontak berhasil disimpan.');
    }

    public function render()
    {
        // Posts Query
        $postsQuery = WebPost::query();
        if ($this->postSearch) {
            $postsQuery->where(function ($q) {
                $q->where('title', 'like', "%{$this->postSearch}%")
                  ->orWhere('content', 'like', "%{$this->postSearch}%");
            });
        }
        if ($this->postCategoryFilter) {
            $postsQuery->where('category', $this->postCategoryFilter);
        }
        $posts = $postsQuery->latest('published_at')->paginate(10);

        // Galleries Query
        $galleriesQuery = WebGallery::query();
        if ($this->galleryCategoryFilter) {
            $galleriesQuery->where('category', $this->galleryCategoryFilter);
        }
        $galleries = $galleriesQuery->orderBy('sort_order')->orderByDesc('id')->get();

        // Menus Query
        $menus = WebMenu::orderBy('sort_order')->get();

        return view('livewire.web-administration', [
            'posts' => $posts,
            'galleries' => $galleries,
            'menus' => $menus,
        ]);
    }
}
