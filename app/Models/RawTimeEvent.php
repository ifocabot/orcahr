<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawTimeEvent extends Model
{
    protected $fillable = [
        'employee_id',
        'event_time',
        'event_type',
        'source',
        'processed_flag',
        'selfie_path',
        'latitude',
        'longitude',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'processed_flag' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('processed_flag', false);
    }
}
