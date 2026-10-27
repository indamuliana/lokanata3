<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'reservation_id',
        'meeting_reservation_id',
        'dining_reservation_id',
        'amount',
        'payment_method',
        'bank_account',
        'reference_number',
        'proof_file_path',
        'is_refund',
        'refund_reason',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_refund' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function meetingReservation(): BelongsTo
    {
        return $this->belongsTo(MeetingReservation::class);
    }

    public function diningReservation(): BelongsTo
    {
        return $this->belongsTo(DiningReservation::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
