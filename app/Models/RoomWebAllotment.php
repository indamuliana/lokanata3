<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomWebAllotment extends Model
{
    use HasFactory;

    protected $fillable = [
        'allotment_date',
        'room_id',
        'is_enabled',
        'created_by',
    ];

    protected $casts = [
        'allotment_date' => 'date',
        'is_enabled' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
