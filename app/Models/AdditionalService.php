<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'default_price',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'default_price' => 'integer',
        'is_active' => 'boolean',
    ];

    public function reservationItems(): HasMany
    {
        return $this->hasMany(ReservationServiceItem::class);
    }
}
