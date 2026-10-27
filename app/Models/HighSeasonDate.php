<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HighSeasonDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];
}
