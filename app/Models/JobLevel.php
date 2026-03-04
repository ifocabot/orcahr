<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobLevel extends Model
{
    use HasUlids, Auditable;

    protected $fillable = ['name', 'level'];

    protected $casts = [
        'level' => 'integer',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
