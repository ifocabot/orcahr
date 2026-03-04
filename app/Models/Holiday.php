<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasUlids;

    protected $fillable = ['date', 'name', 'is_national', 'year'];

    protected $casts = [
        'date' => 'date',
        'is_national' => 'boolean',
    ];

    /**
     * Cek apakah tanggal tertentu adalah hari libur.
     */
    public static function isHoliday(\Carbon\Carbon $date): bool
    {
        return static::where('date', $date->toDateString())->exists();
    }
}
