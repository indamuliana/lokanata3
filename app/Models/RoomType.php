<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'facilities',
        'standard_capacity',
        'max_capacity',
        'base_price',
        'price_without_breakfast',
        'high_season_price',
        'high_season_price_without_breakfast',
        'extra_person_fee',
        'photo_path',
        'photos',
        'is_active',
        'website_quota',
    ];

    protected $casts = [
        'facilities' => 'array',
        'photos' => 'array',
        'base_price' => 'integer',
        'price_without_breakfast' => 'integer',
        'high_season_price' => 'integer',
        'high_season_price_without_breakfast' => 'integer',
        'extra_person_fee' => 'integer',
        'standard_capacity' => 'integer',
        'max_capacity' => 'integer',
        'is_active' => 'boolean',
        'website_quota' => 'integer',
    ];

    public function getPrimaryPhotoAttribute(): ?string
    {
        return (!empty($this->photos) && isset($this->photos[0])) ? $this->photos[0] : $this->photo_path;
    }

    public function getAllPhotosAttribute(): array
    {
        if (!empty($this->photos) && is_array($this->photos)) {
            return $this->photos;
        }
        return $this->photo_path ? [$this->photo_path] : [];
    }

    public function getPriceWithoutBreakfastAttribute(?int $value): int
    {
        return $value !== null && $value > 0 ? $value : max(0, (int) $this->base_price - 40000);
    }

    public function getHighSeasonPriceWithoutBreakfastAttribute(?int $value): int
    {
        if ($value !== null && $value > 0) {
            return $value;
        }
        $high = $this->high_season_price ?: $this->base_price;
        return max(0, (int) $high - 40000);
    }

    public function getStructuredFacilitiesAttribute(): array
    {
        if (empty($this->facilities) || !is_array($this->facilities)) {
            return [];
        }

        $result = [];
        foreach ($this->facilities as $item) {
            if (is_array($item)) {
                $result[] = [
                    'icon' => $item['icon'] ?? '✓',
                    'text' => $item['text'] ?? ($item['name'] ?? ''),
                ];
            } elseif (is_string($item)) {
                $result[] = [
                    'icon' => '✓',
                    'text' => $item,
                ];
            }
        }
        return $result;
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function rateRules(): HasMany
    {
        return $this->hasMany(RateRule::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
