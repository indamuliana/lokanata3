<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'room_id',
        'purchase_date',
        'purchase_cost',
        'useful_life_years',
        'salvage_value',
        'accumulated_depreciation',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'integer',
        'useful_life_years' => 'integer',
        'salvage_value' => 'integer',
        'accumulated_depreciation' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function getBookValueAttribute(): int
    {
        return max(0, (int) ($this->purchase_cost - $this->accumulated_depreciation));
    }

    public function getDepreciableBaseAttribute(): int
    {
        return max(0, (int) ($this->purchase_cost - $this->salvage_value));
    }

    public function isFullyDepreciated(): bool
    {
        return $this->accumulated_depreciation >= $this->depreciable_base;
    }
}
