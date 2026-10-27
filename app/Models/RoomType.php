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
        'high_season_price',
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
        'high_season_price' => 'integer',
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
