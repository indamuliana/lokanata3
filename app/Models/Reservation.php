<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number',
        'customer_id',
        'room_type_id',
        'room_type_snapshot',
        'source',
        'ota_name',
        'ota_reference_number',
        'ota_gross_amount',
        'ota_commission',
        'ota_net_expected',
        'ota_settlement_status',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'adults',
        'children',
        'infants',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'service_charge_amount',
        'total_amount',
        'paid_amount',
        'payment_status',
        'expires_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'expires_at' => 'datetime',
        'room_type_snapshot' => 'array',
        'subtotal' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'service_charge_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
        'adults' => 'integer',
        'children' => 'integer',
        'infants' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function allocatedRooms(): HasMany
    {
        return $this->hasMany(ReservationRoom::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(ReservationServiceItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getBalanceAttribute(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getRoomTypeNameAttribute(): string
    {
        return $this->roomType?->name ?? ($this->room_type_snapshot['name'] ?? 'Tipe Dihapus');
    }

    public function getRoomTypeCodeAttribute(): string
    {
        return $this->roomType?->code ?? ($this->room_type_snapshot['code'] ?? '-');
    }

    public function getNightsCountAttribute(): int
    {
        return max(1, $this->check_in_date->diffInDays($this->check_out_date));
    }

    /**
     * Release expired website reservations (callable manually or via cron scheduler)
     */
    public static function releaseExpired(): int
    {
        $expiredReservations = static::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredReservations as $reservation) {
            $reservation->update([
                'status' => 'expired',
                'notes' => trim(($reservation->notes ?? '') . "\n[Sistem] Kedaluwarsa otomatis setelah batas masa tahan booking 2 jam."),
            ]);

            AuditLog::record('reservation_expired', $reservation, null, ['status' => 'expired'], 'Masa tahan 2 jam habis');
            $count++;
        }

        return $count;
    }
}
