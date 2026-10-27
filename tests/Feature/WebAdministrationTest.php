<?php

namespace Tests\Feature;

use App\Livewire\PublicBookingEngine;
use App\Livewire\PublicNewsDetail;
use App\Livewire\PublicNewsList;
use App\Livewire\WebAdministration;
use App\Models\HotelSetting;
use App\Models\User;
use App\Models\WebGallery;
use App\Models\WebMenu;
use App\Models\WebPost;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WebAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
        ]);

        $this->manager = User::where('email', 'manager@insitu.id')->first();
        Storage::fake('public');
    }

    public function test_guest_cannot_access_web_admin_route(): void
    {
        $response = $this->get(route('web-admin.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_web_admin(): void
    {
        $response = $this->actingAs($this->manager)->get(route('web-admin.index'));
        $response->assertOk();
        $response->assertSee('Administrasi Website');
    }

    public function test_can_create_publish_and_delete_web_post(): void
    {
        $image = UploadedFile::fake()->image('promo.jpg', 600, 400);

        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->call('openCreatePostModal')
            ->set('post_title', 'Promo Diskon Liburan Keluarga Garut')
            ->set('post_category', 'Promo')
            ->set('post_summary', 'Dapatkan potongan harga kamar sebesar 20%.')
            ->set('post_content', 'Syarat dan ketentuan berlaku selama periode high season.')
            ->set('post_status', 'published')
            ->set('post_featured_image', $image)
            ->call('savePost')
            ->assertHasNoErrors()
            ->assertSee('Post berita/artikel baru berhasil ditambahkan.');

        $this->assertDatabaseHas('web_posts', [
            'title' => 'Promo Diskon Liburan Keluarga Garut',
            'slug' => 'promo-diskon-liburan-keluarga-garut',
            'category' => 'Promo',
            'status' => 'published',
        ]);

        $post = WebPost::where('slug', 'promo-diskon-liburan-keluarga-garut')->first();
        $this->assertNotNull($post);
        $this->assertNotNull($post->featured_image);

        // Edit Post
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->call('editPost', $post->id)
            ->set('post_title', 'Promo Diskon Liburan Akhir Pekan Garut')
            ->call('savePost')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('web_posts', [
            'id' => $post->id,
            'title' => 'Promo Diskon Liburan Akhir Pekan Garut',
        ]);

        // Toggle Status
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->call('togglePostStatus', $post->id);

        $this->assertEquals('draft', $post->fresh()->status);

        // Delete Post
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->call('deletePost', $post->id);

        $this->assertSoftDeleted('web_posts', ['id' => $post->id]);
    }

    public function test_can_manage_web_galleries(): void
    {
        $photo1 = UploadedFile::fake()->image('hotel1.jpg', 800, 600);
        $photo2 = UploadedFile::fake()->image('hotel2.jpg', 800, 600);

        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->set('activeTab', 'galleries')
            ->call('openCreateGalleryModal')
            ->set('gallery_title', 'Suasana Kolam Air Panas')
            ->set('gallery_category', 'gallery')
            ->set('gallery_caption', 'Kolam air hangat alami untuk relaksasi keluarga.')
            ->set('gallery_sort_order', 1)
            ->set('gallery_photos', [$photo1, $photo2])
            ->call('saveGallery')
            ->assertHasNoErrors()
            ->assertSee('Berhasil mengunggah 2 foto ke galeri website.');

        $this->assertDatabaseCount('web_galleries', 2);
        $first = WebGallery::first();
        $this->assertTrue($first->is_active);

        // Toggle status
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->call('toggleGalleryStatus', $first->id);

        $this->assertFalse($first->fresh()->is_active);

        // Delete gallery item
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->call('deleteGallery', $first->id);

        $this->assertDatabaseCount('web_galleries', 1);
    }

    public function test_can_manage_web_menus_and_seed_defaults(): void
    {
        // Seed default menus
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->set('activeTab', 'menus')
            ->call('seedDefaultMenus')
            ->assertSee('Preset menu standar berhasil dipasang.');

        $this->assertDatabaseHas('web_menus', [
            'label' => 'Pilihan Kamar',
            'url' => '#kamar',
        ]);
        $this->assertDatabaseHas('web_menus', [
            'label' => 'Berita & Promo',
            'url' => '/berita',
        ]);

        // Add custom menu
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->set('activeTab', 'menus')
            ->call('openCreateMenuModal')
            ->set('menu_label', 'Halaman Statis Kustom')
            ->set('menu_url', '/panduan-ibadah')
            ->set('menu_target', '_blank')
            ->set('menu_sort_order', 70)
            ->call('saveMenu')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('web_menus', [
            'label' => 'Halaman Statis Kustom',
            'url' => '/panduan-ibadah',
            'target' => '_blank',
        ]);
    }

    public function test_can_update_hero_settings(): void
    {
        Livewire::actingAs($this->manager)
            ->test(WebAdministration::class)
            ->set('activeTab', 'settings')
            ->set('hero_title', 'Akomodasi Syariah Terbaik di Garut Kota')
            ->set('hero_subtitle', 'Pelayanan ramah berkelas bintang 3 dengan fasilitas air panas alami.')
            ->set('hero_tagline', '★ Hotel Keluarga No. 1 di Garut')
            ->set('web_whatsapp', '62899998888')
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->assertSee('Pengaturan teks landing page dan kontak berhasil disimpan.');

        $this->assertEquals('Akomodasi Syariah Terbaik di Garut Kota', HotelSetting::get('hero_title'));
        $this->assertEquals('62899998888', HotelSetting::get('hotel_whatsapp'));
    }

    public function test_public_news_list_and_detail_flow(): void
    {
        // Create 1 published post and 1 draft post
        $post = WebPost::create([
            'title' => 'Panduan Menikmati Cipanas Bersama Keluarga',
            'slug' => 'panduan-menikmati-cipanas-bersama-keluarga',
            'category' => 'Wisata Garut',
            'summary' => 'Inilah tips berwisata air hangat di Garut.',
            'content' => 'Air panas Cipanas Garut berkhasiat untuk relaksasi tubuh dan menjaga kesehatan.',
            'status' => 'published',
            'published_at' => now(),
            'views_count' => 5,
        ]);

        WebPost::create([
            'title' => 'Artikel Masih Konsep Rahasia',
            'slug' => 'artikel-masih-konsep-rahasia',
            'category' => 'Berita',
            'content' => 'Belum boleh dibaca oleh tamu umum.',
            'status' => 'draft',
        ]);

        // Public News List
        $response = $this->get(route('news.index'));
        $response->assertOk();
        $response->assertSee('Panduan Menikmati Cipanas Bersama Keluarga');
        $response->assertDontSee('Artikel Masih Konsep Rahasia');

        // Public News Detail
        $detailResponse = $this->get(route('news.detail', $post->slug));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Air panas Cipanas Garut berkhasiat');

        // Views count should have incremented
        $this->assertEquals(6, $post->fresh()->views_count);
    }

    public function test_landing_page_renders_dynamic_content(): void
    {
        // Add dynamic menu and gallery
        WebMenu::create([
            'label' => 'Tautan Menu Spesial',
            'url' => '#spesial',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        WebGallery::create([
            'title' => 'Foto Suasana Senja Cipanas',
            'image_path' => 'web-galleries/senja.jpg',
            'caption' => 'Pemandangan indah saat matahari terbenam.',
            'category' => 'gallery',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        WebPost::create([
            'title' => 'Diskon Awal Pekan Spesial',
            'slug' => 'diskon-awal-pekan-spesial',
            'category' => 'Promo',
            'summary' => 'Potongan harga khusus hari Senin hingga Kamis.',
            'content' => 'Dapatkan diskon 15% untuk reservasi kamar.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Tautan Menu Spesial');
        $response->assertSee('Foto Suasana Senja Cipanas');
        $response->assertSee('Diskon Awal Pekan Spesial');
    }

    public function test_landing_page_renders_ota_reviews_instagram_and_whatsapp_fab(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();

        // Trust Banner OTA
        $response->assertSee('8.7');
        $response->assertSee('Kebersihan');

        // Testimoni OTA
        $response->assertSee('Ulasan Tamu Terverifikasi OTA');
        $response->assertSee('Booking.com');
        $response->assertSee('Agoda');
        $response->assertSee('Tiket.com');

        // Momen & Kegiatan Instagram
        $response->assertSee('@insituhotelsyariahgarut');
        $response->assertSee('Momen Seru di InSitu Hotel Garut');

        // Floating WhatsApp Widget
        $response->assertSee('Front Desk InSitu Hotel');
        $whatsappNumber = \App\Models\HotelSetting::get('hotel_whatsapp', '628123456789');
        $response->assertSee($whatsappNumber);
    }
}
