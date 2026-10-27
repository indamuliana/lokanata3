<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiningServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dining_reservation_id',
        'dining_service_id',
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

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(DiningReservation::class, 'dining_reservation_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(DiningService::class, 'dining_service_id')->withTrashed();
    }
}
