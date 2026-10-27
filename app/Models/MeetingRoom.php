<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'capacity',
        'hourly_rate',
        'high_hourly_rate',
        'halfday_rate',
        'high_halfday_rate',
        'fullday_rate',
        'high_fullday_rate',
        'facilities',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'hourly_rate' => 'integer',
        'high_hourly_rate' => 'integer',
        'halfday_rate' => 'integer',
        'high_halfday_rate' => 'integer',
        'fullday_rate' => 'integer',
        'high_fullday_rate' => 'integer',
        'facilities' => 'array',
        'is_active' => 'boolean',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(MeetingReservation::class);
    }
}
