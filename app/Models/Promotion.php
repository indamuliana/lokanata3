<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'max_discount_amount' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Calculate discount amount for a given subtotal
     */
    public function calculateDiscount(int $subtotal): int
    {
        if (!$this->is_active) {
            return 0;
        }

        $today = now()->toDateString();
        if ($this->start_date && $today < $this->start_date->toDateString()) {
            return 0;
        }
        if ($this->end_date && $today > $this->end_date->toDateString()) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = (int) round(($subtotal * $this->discount_value) / 100);
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                return $this->max_discount_amount;
            }
            return min($discount, $subtotal);
        }

        return min($this->discount_value, $subtotal);
    }
}
