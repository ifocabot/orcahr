<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollComponent extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'is_taxable',
        'is_fixed',
        'formula',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'is_fixed' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function configs(): HasMany
    {
        return $this->hasMany(EmployeePayrollConfig::class, 'component_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class, 'component_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEarnings($query)
    {
        return $query->where('type', 'earning');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }
}
