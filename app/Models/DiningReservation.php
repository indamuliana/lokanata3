<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiningReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number',
        'customer_id',
        'group_name',
        'pic_name',
        'pic_phone',
        'activity_link',
        'is_wikrama_teachers',
        'dining_package_id',
        'custom_menu_name',
        'table_number_or_area',
        'reservation_date',
        'time_slot',
        'pax',
        'price_per_pax',
        'subtotal',
        'additional_charges',
        'discount_amount',
        'apply_tax',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'payment_status',
        'special_requests',
        'status',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'pax' => 'integer',
        'price_per_pax' => 'integer',
        'subtotal' => 'integer',
        'additional_charges' => 'integer',
        'discount_amount' => 'integer',
        'apply_tax' => 'boolean',
        'is_wikrama_teachers' => 'boolean',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function diningPackage(): BelongsTo
    {
        return $this->belongsTo(DiningPackage::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(DiningServiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getOrCreateQrToken(): string
    {
        $token = PublicVerificationToken::where('document_type', 'dining_reservation')
            ->where('document_id', $this->id)
            ->first();

        if (!$token) {
            $token = PublicVerificationToken::create([
                'token' => \Illuminate\Support\Str::random(32),
                'document_type' => 'dining_reservation',
                'document_id' => $this->id,
            ]);
        }

        return $token->token;
    }
}
