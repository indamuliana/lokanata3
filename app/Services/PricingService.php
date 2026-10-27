<?php

namespace App\Services;

use App\Models\HotelSetting;
use App\Models\Promotion;
use App\Models\RateRule;
use App\Models\RoomType;
use App\Services\SeasonService;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate price for a single night of a room type based on PRD priority:
     * Specific Date > High Season > Weekend/Weekday > Base Price
     */
    public static function getNightRate(RoomType $roomType, string|Carbon $date): int
    {
        $carbonDate = Carbon::parse($date);
        $dateStr = $carbonDate->toDateString();
        $dayOfWeek = $carbonDate->dayOfWeek; // 0 (Sun) to 6 (Sat)

        // 1. Check Specific Date rule
        $specificRule = RateRule::where('is_active', true)
            ->where(function ($q) use ($roomType) {
                $q->where('room_type_id', $roomType->id)->orWhereNull('room_type_id');
            })
            ->where('type', 'specific_date')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->orderByDesc('priority')
            ->first();

        if ($specificRule) {
            return self::applyAdjustment($roomType->base_price, $specificRule);
        }

        // 2. Check High Season rule
        $highSeasonRule = RateRule::where('is_active', true)
            ->where(function ($q) use ($roomType) {
                $q->where('room_type_id', $roomType->id)->orWhereNull('room_type_id');
            })
            ->where('type', 'high_season')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->orderByDesc('priority')
            ->first();

        if ($highSeasonRule) {
            return self::applyAdjustment($roomType->base_price, $highSeasonRule);
        }

        // 3. Check Weekend rule (Friday=5, Saturday=6)
        if (in_array($dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
            $weekendRule = RateRule::where('is_active', true)
                ->where(function ($q) use ($roomType) {
                    $q->where('room_type_id', $roomType->id)->orWhereNull('room_type_id');
                })
                ->where('type', 'weekend')
                ->orderByDesc('priority')
                ->first();

            if ($weekendRule) {
                return self::applyAdjustment($roomType->base_price, $weekendRule);
            }
        }

        // 4. Check RoomType High Season price (Saturday, Sunday, or registered Holiday)
        if (SeasonService::isHighSeason($carbonDate) && !empty($roomType->high_season_price) && $roomType->high_season_price > 0) {
            return (int) $roomType->high_season_price;
        }

        // 5. Default: Base Price
        return $roomType->base_price;
    }

    /**
     * Calculate stay cost breakdown
     */
    public static function calculateStay(
        RoomType $roomType,
        string $checkIn,
        string $checkOut,
        ?string $promoCode = null,
        int $manualDiscount = 0,
        int $extraGuests = 0,
        ?int $customRatePerNight = null,
        bool $applyTax = true,
        int $additionalCharges = 0
    ): array {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $nights = max(1, $start->diffInDays($end));

        $nightlyRates = [];
        $subtotal = 0;

        for ($i = 0; $i < $nights; $i++) {
            $nightDate = $start->copy()->addDays($i);
            $seasonInfo = SeasonService::getSeasonInfo($nightDate);
            $rate = ($customRatePerNight !== null && $customRatePerNight >= 0)
                ? $customRatePerNight
                : self::getNightRate($roomType, $nightDate);

            $nightlyRates[] = [
                'date' => $nightDate->toDateString(),
                'rate' => $rate,
                'is_high_season' => $seasonInfo['is_high_season'],
                'season_label' => $seasonInfo['label'],
            ];
            $subtotal += $rate;
        }

        // Extra person fee if any
        if ($extraGuests > 0 && $roomType->extra_person_fee > 0) {
            $extraFeeTotal = $extraGuests * $roomType->extra_person_fee * $nights;
            $subtotal += $extraFeeTotal;
        }

        // Promotions / Diskon
        $discount = 0;
        if ($promoCode) {
            $promo = Promotion::where('code', $promoCode)->where('is_active', true)->first();
            if ($promo) {
                $discount = $promo->calculateDiscount($subtotal);
            }
        }

        if ($manualDiscount > 0) {
            $discount = min($subtotal, $discount + $manualDiscount);
        }

        $taxableAmount = max(0, $subtotal - $discount);

        $taxPercent = $applyTax ? (int) HotelSetting::get('tax_percentage', 10) : 0;
        $taxAmount = (int) round(($taxableAmount * $taxPercent) / 100);

        $servicePercent = (int) HotelSetting::get('service_charge_percentage', 0);
        $serviceAmount = (int) round(($taxableAmount * $servicePercent) / 100);

        $total = $taxableAmount + $taxAmount + $serviceAmount + $additionalCharges;

        return [
            'nights' => $nights,
            'nightly_rates' => $nightlyRates,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'taxable_amount' => $taxableAmount,
            'tax_amount' => $taxAmount,
            'service_charge_amount' => $serviceAmount,
            'additional_charges' => $additionalCharges,
            'total_amount' => $total,
            'apply_tax' => $applyTax,
            'custom_rate_per_night' => $customRatePerNight,
        ];
    }

    private static function applyAdjustment(int $basePrice, RateRule $rule): int
    {
        if ($rule->adjustment_type === 'percentage') {
            return (int) round($basePrice * (1 + ($rule->adjustment_value / 100)));
        }

        // Fixed price or fixed addition
        return (int) $rule->adjustment_value;
    }
}
