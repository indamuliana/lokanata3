<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NightAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_date',
        'performed_by',
        'performed_at',
        'rooms_occupied_count',
        'total_room_revenue',
        'total_meeting_revenue',
        'total_dining_revenue',
        'notes',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'performed_at' => 'datetime',
        'rooms_occupied_count' => 'integer',
        'total_room_revenue' => 'integer',
        'total_meeting_revenue' => 'integer',
        'total_dining_revenue' => 'integer',
    ];

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
