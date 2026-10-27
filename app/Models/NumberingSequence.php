<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberingSequence extends Model
{
    protected $fillable = [
        'code',
        'prefix',
        'date_format',
        'current_number',
        'padding',
    ];

    /**
     * Generate next sequential document number atomically
     */
    public static function next(string $code): string
    {
        return DB::transaction(function () use ($code) {
            $seq = static::where('code', $code)->lockForUpdate()->first();

            if (!$seq) {
                $seq = static::create([
                    'code' => $code,
                    'prefix' => $code,
                    'date_format' => 'Ymd',
                    'current_number' => 0,
                    'padding' => 4,
                ]);
            }

            $datePart = $seq->date_format ? now()->format($seq->date_format) : '';

            // Auto-detect max existing number to prevent duplicate entry on collision
            if ($code === 'RESERVATION' && $datePart) {
                $prefixPattern = "{$seq->prefix}-{$datePart}-";
                $maxExisting = Reservation::where('reservation_number', 'like', "{$prefixPattern}%")
                    ->pluck('reservation_number')
                    ->map(fn($num) => (int) str_replace($prefixPattern, '', $num))
                    ->max() ?? 0;

                if ($seq->current_number < $maxExisting) {
                    $seq->current_number = $maxExisting;
                }
            } elseif ($code === 'INVOICE' && $datePart) {
                $prefixPattern = "{$seq->prefix}-{$datePart}-";
                $maxExisting = Invoice::where('invoice_number', 'like', "{$prefixPattern}%")
                    ->pluck('invoice_number')
                    ->map(fn($num) => (int) str_replace($prefixPattern, '', $num))
                    ->max() ?? 0;

                if ($seq->current_number < $maxExisting) {
                    $seq->current_number = $maxExisting;
                }
            }

            $seq->current_number += 1;
            $seq->save();

            $numberPart = str_pad((string) $seq->current_number, $seq->padding, '0', STR_PAD_LEFT);

            return $datePart ? "{$seq->prefix}-{$datePart}-{$numberPart}" : "{$seq->prefix}-{$numberPart}";
        });
    }
}
