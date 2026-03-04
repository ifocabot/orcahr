<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasUlids, Auditable, Encryptable;

    protected $fillable = ['employee_id', 'is_primary'];

    protected array $encryptable = ['bank_name', 'branch', 'account_number', 'account_holder'];

    protected array $auditMasked = [
        'bank_name_encrypted',
        'branch_encrypted',
        'account_number_encrypted',
        'account_holder_encrypted',
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
