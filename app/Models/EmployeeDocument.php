<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    use Auditable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'employee_id',
        'type',
        'original_name',
        'file_path',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    // ─── Labels ──────────────────────────────────────────────────────────────

    public static array $typeLabels = [
        'ktp' => 'KTP',
        'npwp' => 'NPWP',
        'kontrak' => 'Kontrak Kerja',
        'ijazah' => 'Ijazah',
        'sertifikasi' => 'Sertifikasi',
        'foto' => 'Foto',
        'other' => 'Lainnya',
    ];

    public function typeLabel(): string
    {
        return self::$typeLabels[$this->type] ?? ucfirst($this->type);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= 30;
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
