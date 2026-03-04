<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBpjs extends Model
{
    use HasUlids, Auditable, Encryptable;

    protected $table = 'employee_bpjs';

    protected $fillable = ['employee_id', 'bpjs_class'];

    protected array $encryptable = ['bpjs_kes', 'bpjs_tk'];

    protected array $auditMasked = ['bpjs_kes_encrypted', 'bpjs_tk_encrypted'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
