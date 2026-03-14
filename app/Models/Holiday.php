<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Holiday extends Model
{
    protected $fillable = ['name', 'holiday_date', 'type', 'is_paid', 'year'];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_paid' => 'boolean',
            'year' => 'integer',
        ];
    }

    public function scopeYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeNational(Builder $query): Builder
    {
        return $query->where('type', 'national');
    }

    /** Check if a given date is a holiday */
    public static function isHoliday(string $date): bool
    {
        return static::where('holiday_date', $date)->exists();
    }
}
