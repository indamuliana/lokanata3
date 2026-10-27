<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingReservationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_reservation_id',
        'meeting_service_id',
        'name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'subtotal' => 'integer',
    ];

    public function meetingReservation(): BelongsTo
    {
        return $this->belongsTo(MeetingReservation::class, 'meeting_reservation_id');
    }

    public function meetingService(): BelongsTo
    {
        return $this->belongsTo(MeetingService::class, 'meeting_service_id')->withTrashed();
    }
}
