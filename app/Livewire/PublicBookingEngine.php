<?php

namespace App\Livewire;

use App\Models\HotelSetting;
use App\Models\PricingService;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\WebGallery;
use App\Models\WebPost;
use App\Services\ReservationService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicBookingEngine extends Component
{
    // Booking Form State
    public string $check_in_date = '';
    public string $check_out_date = '';
    public ?int $room_type_id = null;
    public int $adults = 2;
    public int $children = 0;
    public string $promo_code = '';

    // Quick Search & Instant Modal State
    public int $rooms_count = 1;
    public bool $showQuickBookingModal = false;
    public bool $showSearchResultsModal = false;
    public int $modalStep = 1; // 1 = Room List, 2 = Guest Info & Cost Breakdown, 3 = Confirmation Success
    public ?int $selectedRoomTypeId = null;
    public bool $voucherInputOpen = false;

    // Guest Info State
    public string $guest_name = '';
    public string $guest_phone = '';
    public string $guest_email = '';
    public string $guest_nik = '';
    public string $guest_notes = '';

    // Confirmation State
    public ?Reservation $confirmedReservation = null;

    // Gallery Modal State
    public bool $showGalleryModal = false;
    public ?RoomType $galleryRoomType = null;
    public int $activePhotoIndex = 0;

    // Web Gallery Lightbox State
    public bool $showWebLightbox = false;
    public ?string $webLightboxImage = null;
    public ?string $webLightboxTitle = null;
    public ?string $webLightboxCaption = null;

    public function openWebLightbox(string $imageUrl, string $title, ?string $caption = null): void
    {
        $this->webLightboxImage = $imageUrl;
        $this->webLightboxTitle = $title;
        $this->webLightboxCaption = $caption;
        $this->showWebLightbox = true;
    }

    public function closeWebLightbox(): void
    {
        $this->showWebLightbox = false;
        $this->webLightboxImage = null;
        $this->webLightboxTitle = null;
        $this->webLightboxCaption = null;
    }

    public function openGallery(int $roomTypeId, int $startIndex = 0): void
    {
        $this->galleryRoomType = RoomType::find($roomTypeId);
        $this->activePhotoIndex = $startIndex;
        $this->showGalleryModal = true;
    }

    public function closeGallery(): void
    {
        $this->showGalleryModal = false;
        $this->galleryRoomType = null;
        $this->activePhotoIndex = 0;
    }

    public function nextPhoto(): void
    {
        if (!$this->galleryRoomType) return;
        $photos = $this->galleryRoomType->all_photos;
        $count = count($photos);
        if ($count > 0) {
            $this->activePhotoIndex = ($this->activePhotoIndex + 1) % $count;
        }
    }

    public function prevPhoto(): void
    {
        if (!$this->galleryRoomType) return;
        $photos = $this->galleryRoomType->all_photos;
        $count = count($photos);
        if ($count > 0) {
            $this->activePhotoIndex = ($this->activePhotoIndex - 1 + $count) % $count;
        }
    }

    // Quick Search Counter Methods
    public function incrementRooms(): void
    {
        if ($this->rooms_count < 10) $this->rooms_count++;
    }

    public function decrementRooms(): void
    {
        if ($this->rooms_count > 1) $this->rooms_count--;
    }

    public function incrementAdults(): void
    {
        if ($this->adults < 20) $this->adults++;
    }

    public function decrementAdults(): void
    {
        if ($this->adults > 1) $this->adults--;
    }

    public function incrementChildren(): void
    {
        if ($this->children < 10) $this->children++;
    }

    public function decrementChildren(): void
    {
        if ($this->children > 0) $this->children--;
    }

    public function clearPromoCode(): void
    {
        $this->promo_code = '';
    }

    public function searchRooms(): void
    {
        $this->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ], [
            'check_in_date.after_or_equal' => 'Tanggal check-in tidak boleh lewat dari hari ini.',
            'check_out_date.after' => 'Tanggal check-out harus setelah tanggal check-in.',
        ]);

        $this->modalStep = 1;
        $this->selectedRoomTypeId = null;
        $this->confirmedReservation = null;
        $this->showSearchResultsModal = true;
    }

    public function selectRoomForBooking(int $roomTypeId): void
    {
        $type = RoomType::find($roomTypeId);
        if (!$type || !$type->is_active) {
            session()->flash('error', 'Tipe kamar tidak ditemukan atau sedang tidak aktif.');
            return;
        }

        $availCount = ReservationService::getAvailableRoomCount($type, $this->check_in_date, $this->check_out_date, 'website');
        if ($availCount < 1) {
            $msg = "Maaf, kuota kamar tipe {$type->name} untuk pemesanan online tidak tersedia atau sudah habis.";
            session()->flash('error', $msg);
            $this->addError('room_type_id', $msg);
            return;
        }

        $this->selectedRoomTypeId = $roomTypeId;
        $this->room_type_id = $roomTypeId;
        $this->modalStep = 2;
    }

    public function backToRoomSelection(): void
    {
        $this->modalStep = 1;
        $this->selectedRoomTypeId = null;
    }

    public function closeSearchResultsModal(): void
    {
        $this->showSearchResultsModal = false;
        $this->modalStep = 1;
        $this->selectedRoomTypeId = null;
        $this->confirmedReservation = null;
    }

    public function openQuickBookingModal(int $roomTypeId): void
    {
        $type = RoomType::find($roomTypeId);
        if (!$type || !$type->is_active) {
            session()->flash('error', 'Tipe kamar tidak ditemukan atau sedang tidak aktif.');
            return;
        }

        $availCount = ReservationService::getAvailableRoomCount($type, $this->check_in_date, $this->check_out_date, 'website');
        if ($availCount < 1) {
            $msg = "Maaf, kuota kamar tipe {$type->name} untuk pemesanan online tidak tersedia atau sudah habis.";
            session()->flash('error', $msg);
            $this->addError('room_type_id', $msg);
            return;
        }

        $this->selectedRoomTypeId = $roomTypeId;
        $this->room_type_id = $roomTypeId;
        $this->modalStep = 2;
        $this->confirmedReservation = null;
        $this->showQuickBookingModal = true;
        $this->showSearchResultsModal = true;
    }

    public function closeQuickBookingModal(): void
    {
        $this->closeSearchResultsModal();
    }

    public function getNightsCountProperty(): int
    {
        try {
            $cIn = Carbon::parse($this->check_in_date);
            $cOut = Carbon::parse($this->check_out_date);
            if ($cIn->gte($cOut)) {
                return 0;
            }
            return max(1, $cIn->diffInDays($cOut));
        } catch (\Exception $e) {
            return 1;
        }
    }

    public function getAvailableRoomTypesListProperty(): array
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return [];
        }

        $types = RoomType::where('is_active', true)->with('rooms')->get();
        $results = [];

        foreach ($types as $type) {
            $availCount = ReservationService::getAvailableRoomCount($type, $this->check_in_date, $this->check_out_date, 'website');
            $pricing = null;
            try {
                $pricing = \App\Services\PricingService::calculateStay(
                    roomType: $type,
                    checkIn: $this->check_in_date,
                    checkOut: $this->check_out_date,
                    promoCode: $this->promo_code ?: null
                );
            } catch (\Exception $e) {
                // Ignore
            }

            $results[] = [
                'type' => $type,
                'available_count' => $availCount,
                'pricing' => $pricing,
            ];
        }

        return $results;
    }

    public function getFormattedStayDatesProperty(): string
    {
        try {
            $cIn = Carbon::parse($this->check_in_date)->locale('id');
            $cOut = Carbon::parse($this->check_out_date)->locale('id');
            $nights = max(1, $cIn->diffInDays($cOut));
            return $cIn->isoFormat('ddd, D MMM') . ' – ' . $cOut->isoFormat('ddd, D MMM') . ' • ' . $nights . ' Malam';
        } catch (\Exception $e) {
            return $this->check_in_date . ' – ' . $this->check_out_date;
        }
    }

    protected array $pricingCache = [];
    protected array $availabilityCache = [];

    public function getPricingForType(RoomType|int $roomType): ?array
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return null;
        }
        $id = ($roomType instanceof RoomType) ? $roomType->id : (int) $roomType;
        $cacheKey = "{$id}_{$this->check_in_date}_{$this->check_out_date}_{$this->promo_code}";
        if (isset($this->pricingCache[$cacheKey])) {
            return $this->pricingCache[$cacheKey];
        }

        $type = ($roomType instanceof RoomType) ? $roomType : RoomType::find($roomType);
        if (!$type) return null;

        try {
            $pricing = \App\Services\PricingService::calculateStay(
                roomType: $type,
                checkIn: $this->check_in_date,
                checkOut: $this->check_out_date,
                promoCode: $this->promo_code ?: null
            );
            return $this->pricingCache[$cacheKey] = $pricing;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAvailableCountForType(RoomType|int $roomType): int
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return 0;
        }
        $id = ($roomType instanceof RoomType) ? $roomType->id : (int) $roomType;
        $cacheKey = "{$id}_{$this->check_in_date}_{$this->check_out_date}";
        if (isset($this->availabilityCache[$cacheKey])) {
            return $this->availabilityCache[$cacheKey];
        }

        $type = ($roomType instanceof RoomType) ? $roomType : RoomType::find($roomType);
        if (!$type) return 0;

        $count = ReservationService::getAvailableRoomCount($type, $this->check_in_date, $this->check_out_date, 'website');
        return $this->availabilityCache[$cacheKey] = $count;
    }

    public function getSelectedRoomTypeProperty(): ?RoomType
    {
        return $this->selectedRoomTypeId ? RoomType::find($this->selectedRoomTypeId) : null;
    }

    public function getSelectedRoomPricingProperty(): ?array
    {
        return $this->selectedRoomTypeId ? $this->getPricingForType($this->selectedRoomTypeId) : null;
    }

    public function mount(): void
    {
        $this->check_in_date = now()->toDateString();
        $this->check_out_date = now()->addDay()->toDateString();

        $firstType = RoomType::where('is_active', true)->first();
        if ($firstType) {
            $this->room_type_id = $firstType->id;
        }
    }

    public function getEstimatedPricingProperty(): ?array
    {
        if (!$this->room_type_id || !$this->check_in_date || !$this->check_out_date) {
            return null;
        }

        $type = RoomType::find($this->room_type_id);
        if (!$type) {
            return null;
        }

        try {
            return \App\Services\PricingService::calculateStay(
                roomType: $type,
                checkIn: $this->check_in_date,
                checkOut: $this->check_out_date,
                promoCode: $this->promo_code ?: null
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAvailableCountProperty(): int
    {
        if (!$this->room_type_id || !$this->check_in_date || !$this->check_out_date) {
            return 0;
        }

        $type = RoomType::find($this->room_type_id);
        if (!$type) {
            return 0;
        }

        return ReservationService::getAvailableRoomCount($type, $this->check_in_date, $this->check_out_date, 'website');
    }

    public function submitBooking(): void
    {
        $isEnabled = (bool) HotelSetting::get('website_booking_enabled', true);
        if (!$isEnabled) {
            session()->flash('error', 'Pemesanan melalui website sedang dinonaktifkan sementara.');
            return;
        }

        $this->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guest_name' => 'required|string|min:3',
            'guest_phone' => 'required|string|min:8',
            'adults' => 'required|integer|min:1',
        ]);

        try {
            $reservation = ReservationService::createReservation([
                'room_type_id' => $this->room_type_id,
                'check_in_date' => $this->check_in_date,
                'check_out_date' => $this->check_out_date,
                'adults' => $this->adults,
                'children' => $this->children,
                'customer_name' => $this->guest_name,
                'customer_phone' => $this->guest_phone,
                'customer_identity_type' => 'KTP',
                'customer_identity_number' => $this->guest_nik ?: null,
                'promo_code' => $this->promo_code ?: null,
                'notes' => $this->guest_notes ?: null,
                'source' => 'website',
            ]);

            $this->confirmedReservation = $reservation;
            $this->modalStep = 3;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function resetBooking(): void
    {
        $this->confirmedReservation = null;
        $this->guest_name = '';
        $this->guest_phone = '';
        $this->guest_email = '';
        $this->guest_nik = '';
        $this->guest_notes = '';
        $this->showQuickBookingModal = false;
        $this->showSearchResultsModal = false;
        $this->modalStep = 1;
        $this->selectedRoomTypeId = null;
    }

    public function getCuratedReviewsProperty(): array
    {
        $jsonPath = storage_path('app/scraped_ota_reviews.json');
        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            if (!empty($data['curated_guest_reviews'])) {
                return $data['curated_guest_reviews'];
            }
        }

        // Fallback reviews
        return [
            [
                'platform' => 'Tiket.com',
                'reviewer_name' => 'CM',
                'origin' => 'Bandung',
                'date' => '2026-05-05',
                'guest_type' => 'Liburan Pasangan',
                'rating' => 5.0,
                'rating_scale' => 5,
                'title' => 'Senenglah pokoknya nginep sini!',
                'review_quote' => 'Nilai 10/10. Senenglah pokoknya nginep sini. Karyawannya ramah. Lokasi bagus dkt kemana aja. Kamarnya luasss, bersih dg fasilitas lengkap. View belakang hotel top. Makan paginya? Belom bisa move on dari nasi goreng kecombrangnya. Top bgt!',
            ],
            [
                'platform' => 'Agoda',
                'reviewer_name' => 'Febrianti',
                'origin' => 'Jakarta',
                'date' => '2026-09-09',
                'guest_type' => 'Liburan Pasangan',
                'rating' => 10.0,
                'rating_scale' => 10,
                'title' => 'Pemandangan pagi langsung Gunung Guntur MashaAllah',
                'review_quote' => 'Gak sia-sia ke Garut, hotelnya bersih bgtt dan pemandangan pagi langsung gunung mashaAllah. InSitu Hotel masih worth it banget dan deket dari mana-mana.',
            ],
            [
                'platform' => 'Agoda',
                'reviewer_name' => 'Vivi',
                'origin' => 'Bekasi',
                'date' => '2026-08-13',
                'guest_type' => 'Wisatawan Bisnis',
                'rating' => 10.0,
                'rating_scale' => 10,
                'title' => 'Dikelola siswa SMK dengan beasiswa',
                'review_quote' => 'Ternyata hotel ini dikelola oleh siswa SMK, dan siswa yang sekolah disini rata-rata sekolah dengan beasiswa. Keren, disini juga melihat pemandangan Gunung Guntur dan aktivitas siswa, pelayanannya sangat ramah.',
            ],
            [
                'platform' => 'Tiket.com',
                'reviewer_name' => 'Auralia Bidari',
                'origin' => 'Tangerang',
                'date' => '2025-07-10',
                'guest_type' => 'Rombongan Teman',
                'rating' => 5.0,
                'rating_scale' => 5,
                'title' => 'Impeccable room cleanliness',
                'review_quote' => 'Impeccable room cleanliness. Hotel terbersih yang pernah saya inepin. Terus kita dilayanin sama adik-adik SMK disana sangat sopan dan bersahabat.',
            ],
            [
                'platform' => 'Booking.com',
                'reviewer_name' => 'Wibowo',
                'origin' => 'Depok',
                'date' => '2026-01-24',
                'guest_type' => 'Liburan Keluarga',
                'rating' => 10.0,
                'rating_scale' => 10,
                'title' => 'Kamar besar dan sarapan banyak',
                'review_quote' => 'Lokasinya aman, karena parkiran mobil di dalam dan dipagar. Kamarnya besar dan luas, serta bersih.. pemandangan di belakang sangat bagus, sarapan yang disediakan juga enak dan porsinya banyak.',
            ],
            [
                'platform' => 'Agoda',
                'reviewer_name' => 'Justina',
                'origin' => 'Jakarta',
                'date' => '2026-07-18',
                'guest_type' => 'Liburan Pasangan',
                'rating' => 10.0,
                'rating_scale' => 10,
                'title' => 'Pelayanan tulus & sarapan box jam 5 subuh',
                'review_quote' => 'Hari ke-2 kami check-out jam 5 pagi, ibu pantry yang baik hati membuatkan breakfast box hangat. Luar biasa pelayanannya, terima kasih!',
            ],
        ];
    }

    public function getSliderImagesProperty(): array
    {
        $sliders = WebGallery::active()->where('category', 'slider')->orderBy('sort_order')->get();
        $images = [];

        foreach ($sliders as $s) {
            $images[] = [
                'url' => $s->image_url,
                'title' => $s->title,
            ];
        }

        // Add the newly downloaded mountain view photo if available locally
        if (file_exists(public_path('storage/web-galleries/ota-pemandangan-1.jpg'))) {
            array_unshift($images, [
                'url' => asset('storage/web-galleries/ota-pemandangan-1.jpg'),
                'title' => 'Panorama Persawahan & Gunung Guntur dari InSitu Hotel',
            ]);
        }

        if (empty($images)) {
            $images[] = [
                'url' => asset('storage/web-galleries/Hotel-Hero-Image.webp'),
                'title' => 'InSitu Hotel Syariah Garut',
            ];
        }

        return $images;
    }

    public function render()
    {
        $roomTypes = RoomType::where('is_active', true)->with('rooms')->get();
        $isWebsiteBookingEnabled = (bool) HotelSetting::get('website_booking_enabled', true);

        $heroTitle = (string) HotelSetting::get('hero_title', 'Hunian Nyaman Penuh Berkah di Kaki Gunung Papandayan');
        $heroSubtitle = (string) HotelSetting::get('hero_subtitle', 'Nikmati udara sejuk khas Garut, pemandian air panas alami Cipanas, dan kenyamanan istirahat dengan nilai-nilai syariah yang menentramkan hati.');
        $heroTagline = (string) HotelSetting::get('hero_tagline', '★ Hotel Syariah Bintang 3 Ramah Keluarga di Garut');

        $heroSliders = WebGallery::active()->where('category', 'slider')->orderBy('sort_order')->get();
        $webGalleries = WebGallery::active()->where('category', '!=', 'slider')->orderBy('sort_order')->take(8)->get();
        $latestPosts = WebPost::published()->latest('published_at')->take(3)->get();

        return view('livewire.public-booking-engine', [
            'roomTypes' => $roomTypes,
            'isWebsiteBookingEnabled' => $isWebsiteBookingEnabled,
            'pricing' => $this->estimatedPricing,
            'availableCount' => $this->availableCount,
            'heroTitle' => $heroTitle,
            'heroSubtitle' => $heroSubtitle,
            'heroTagline' => $heroTagline,
            'heroSliders' => $heroSliders,
            'sliderImages' => $this->sliderImages,
            'curatedReviews' => $this->curatedReviews,
            'webGalleries' => $webGalleries,
            'latestPosts' => $latestPosts,
        ]);
    }
}
