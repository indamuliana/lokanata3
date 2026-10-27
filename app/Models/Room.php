<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_type_id',
        'floor',
        'notes',
        'operational_status',
        'cleaning_status',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ReservationRoom::class);
    }

    /**
     * Check if room is physically available for a given date range
     */
    public function isAvailable(string $checkIn, string $checkOut, ?int $ignoreReservationId = null): bool
    {
        if ($this->operational_status !== 'active') {
            return false;
        }

        $checkInDate = \Carbon\Carbon::parse($checkIn)->toDateString();
        $checkOutDate = \Carbon\Carbon::parse($checkOut)->toDateString();

        return !$this->allocations()
            ->whereHas('reservation', function ($query) use ($ignoreReservationId) {
                $query->whereNotIn('status', ['cancelled', 'expired']);
                if ($ignoreReservationId) {
                    $query->where('id', '!=', $ignoreReservationId);
                }
            })
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereDate('assigned_from', '<', $checkOutDate)
                      ->whereDate('assigned_to', '>', $checkInDate);
            })
            ->exists();
    }
}
