<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'identity_type',
        'identity_number',
        'company',
        'address',
        'notes',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Mutator to normalize Indonesian phone number to 62...
     */
    public function setPhoneAttribute(string $value): void
    {
        $cleaned = preg_replace('/[^\d]/', '', $value);
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '628' . substr($cleaned, 1);
        }
        $this->attributes['phone'] = $cleaned;
    }

    /**
     * Helper to get a ready-to-click WhatsApp link (PRD 18.2)
     */
    public function getWhatsAppUrl(?string $message = null): string
    {
        $base = "https://wa.me/{$this->phone}";
        return $message ? "{$base}?text=" . urlencode($message) : $base;
    }
}
