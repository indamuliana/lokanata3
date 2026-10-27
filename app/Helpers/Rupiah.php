<?php

namespace App\Helpers;

class Rupiah
{
    /**
     * Format an integer/numeric amount to IDR representation: Rp 1.250.000
     */
    public static function format(mixed $amount, bool $prefix = true): string
    {
        $numeric = (int) round((float) $amount);
        $formatted = number_format($numeric, 0, ',', '.');
        return $prefix ? "Rp {$formatted}" : $formatted;
    }

    /**
     * Parse raw user string input (e.g. 'Rp 1.250.000' or '1.250.000' or '1250000') into integer
     */
    public static function parse(mixed $raw): int
    {
        if (is_int($raw)) {
            return $raw;
        }

        if (is_float($raw)) {
            return (int) round($raw);
        }

        if (empty($raw) || !is_string($raw)) {
            return 0;
        }

        // In Indonesian Rupiah, '.' is a thousand separator and there are no decimals.
        // Strip everything except digits and minus.
        $cleaned = preg_replace('/[^\d-]/', '', $raw);
        return (int) $cleaned;
    }
}
