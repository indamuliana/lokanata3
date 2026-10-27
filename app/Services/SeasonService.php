<?php

namespace App\Services;

use App\Models\HighSeasonDate;
use Carbon\Carbon;

class SeasonService
{
    /**
     * Cache for active holidays to avoid repeated DB queries in loops.
     */
    private static ?array $holidayCache = null;

    /**
     * Determine if a given date is High Season (Saturday/Sunday or an active holiday).
     */
    public static function isHighSeason(string|Carbon $date): bool
    {
        return self::getSeasonInfo($date)['is_high_season'];
    }

    /**
     * Get detailed season information for a given date.
     */
    public static function getSeasonInfo(string|Carbon $date): array
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateStr = $carbon->toDateString();
        $dayOfWeek = $carbon->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // 1. Check if weekend (Saturday or Sunday)
        if (in_array($dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
            $dayName = $dayOfWeek === Carbon::SATURDAY ? 'Sabtu' : 'Minggu';
            return [
                'is_high_season' => true,
                'season_type' => 'weekend',
                'label' => "Akhir Pekan ({$dayName})",
                'holiday_name' => null,
            ];
        }

        // 2. Check if registered holiday date
        $holidays = self::getActiveHolidays();
        if (isset($holidays[$dateStr])) {
            $holidayName = $holidays[$dateStr];
            return [
                'is_high_season' => true,
                'season_type' => 'holiday',
                'label' => "Libur ({$holidayName})",
                'holiday_name' => $holidayName,
            ];
        }

        return [
            'is_high_season' => false,
            'season_type' => 'normal',
            'label' => 'Normal Season',
            'holiday_name' => null,
        ];
    }

    /**
     * Get active holiday dates keyed by YYYY-MM-DD.
     */
    public static function getActiveHolidays(): array
    {
        if (self::$holidayCache === null) {
            self::$holidayCache = HighSeasonDate::where('is_active', true)
                ->pluck('name', 'date')
                ->mapWithKeys(function ($name, $date) {
                    $key = Carbon::parse($date)->toDateString();
                    return [$key => $name];
                })
                ->toArray();
        }

        return self::$holidayCache;
    }

    /**
     * Clear holiday cache (e.g. after adding, deleting, or importing holidays).
     */
    public static function clearCache(): void
    {
        self::$holidayCache = null;
    }
}
