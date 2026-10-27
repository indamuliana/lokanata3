<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'room_id',
        'room_snapshot',
        'assigned_from',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'assigned_from' => 'date',
        'assigned_to' => 'date',
        'room_snapshot' => 'array',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getRoomNumberAttribute(): string
    {
        return $this->room?->room_number ?? ($this->room_snapshot['room_number'] ?? '-');
    }
}
