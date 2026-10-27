<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'days_of_week',
        'adjustment_type',
        'adjustment_value',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'adjustment_value' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
