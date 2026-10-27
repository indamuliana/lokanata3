<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price_per_pax',
        'high_price_per_pax',
        'min_pax',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price_per_pax' => 'integer',
        'high_price_per_pax' => 'integer',
        'min_pax' => 'integer',
        'is_active' => 'boolean',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(MeetingReservation::class);
    }
}
