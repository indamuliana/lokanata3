<?php

use App\Helpers\Rupiah;

if (!function_exists('rupiah')) {
    function rupiah(mixed $amount, bool $prefix = true): string
    {
        return Rupiah::format($amount, $prefix);
    }
}

if (!function_exists('parse_rupiah')) {
    function parse_rupiah(mixed $raw): int
    {
        return Rupiah::parse($raw);
    }
}
