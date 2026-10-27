<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'reservation_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'service_charge_amount',
        'total_amount',
        'paid_amount',
        'status',
        'qr_token',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'service_charge_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get or create a secure random public QR token
     */
    public function getOrCreateQrToken(): string
    {
        if (!$this->qr_token) {
            $this->qr_token = Str::random(32);
            $this->save();

            PublicVerificationToken::create([
                'token' => $this->qr_token,
                'document_type' => 'invoice',
                'document_id' => $this->id,
            ]);
        }

        return $this->qr_token;
    }
}
