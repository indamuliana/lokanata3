<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number',
        'customer_id',
        'meeting_room_id',
        'meeting_package_id',
        'event_name',
        'event_date',
        'start_time',
        'end_time',
        'pax',
        'pricing_type',
        'subtotal',
        'additional_charges',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status',
        'notes',
        'facility_notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'pax' => 'integer',
        'subtotal' => 'integer',
        'additional_charges' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function meetingRoom(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class)->withTrashed();
    }

    public function meetingPackage(): BelongsTo
    {
        return $this->belongsTo(MeetingPackage::class)->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MeetingReservationItem::class, 'meeting_reservation_id');
    }

    public function getBalanceAttribute(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getOrCreateQrToken(): string
    {
        $token = PublicVerificationToken::where('document_type', 'meeting_reservation')
            ->where('document_id', $this->id)
            ->first();

        if (!$token) {
            $token = PublicVerificationToken::create([
                'token' => \Illuminate\Support\Str::random(32),
                'document_type' => 'meeting_reservation',
                'document_id' => $this->id,
            ]);
        }

        return $token->token;
    }
}
