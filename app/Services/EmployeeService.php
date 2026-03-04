<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmployeeBpjs;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    /**
     * Generate nomor karyawan: RKS-YYYY-NNNN
     * Contoh: RKS-2026-0001
     */
    public function generateEmployeeNumber(): string
    {
        $year = now()->year;
        $prefix = "RKS-{$year}-";

        $last = Employee::withTrashed()
            ->where('employee_number', 'like', "{$prefix}%")
            ->orderByDesc('employee_number')
            ->value('employee_number');

        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cek apakah NIK sudah digunakan karyawan lain.
     * Menggunakan HMAC hash (tidak perlu decrypt semua data).
     */
    public function isNikTaken(string $nik, ?string $excludeEmployeeId = null): bool
    {
        $hash = hmac_hash($nik);
        return Employee::where('nik_hash', $hash)
            ->when($excludeEmployeeId, fn($q) => $q->where('id', '!=', $excludeEmployeeId))
            ->exists();
    }

    public function isNpwpTaken(string $npwp, ?string $excludeEmployeeId = null): bool
    {
        $hash = hmac_hash($npwp);
        return Employee::where('npwp_hash', $hash)
            ->when($excludeEmployeeId, fn($q) => $q->where('id', '!=', $excludeEmployeeId))
            ->exists();
    }

    public function all(): Collection
    {
        return Employee::with(['currentEmployment.department', 'currentEmployment.position'])
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Buat karyawan baru + employment pertama dalam satu transaction.
     */
    public function create(array $personal, array $employment, array $bank = [], array $bpjs = []): Employee
    {
        return DB::transaction(function () use ($personal, $employment, $bank, $bpjs) {
            // 1. Employee number
            $personal['employee_number'] = $this->generateEmployeeNumber();

            // 2. Employee (personal data)
            $employee = new Employee();
            $employee->fill([
                'employee_number' => $personal['employee_number'],
                'full_name' => $personal['full_name'],
                'email' => $personal['email'],
                'birth_date' => $personal['birth_date'] ?? null,
                'gender' => $personal['gender'] ?? null,
                'marital_status' => $personal['marital_status'] ?? null,
                'blood_type' => $personal['blood_type'] ?? null,
                'religion' => $personal['religion'] ?? null,
            ]);

            // Encrypted fields via Encryptable trait
            if (!empty($personal['nik']))
                $employee->nik = $personal['nik'];
            if (!empty($personal['npwp']))
                $employee->npwp = $personal['npwp'];
            if (!empty($personal['phone']))
                $employee->phone = $personal['phone'];
            if (!empty($personal['personal_email']))
                $employee->personal_email = $personal['personal_email'];
            if (!empty($personal['birth_place']))
                $employee->birth_place = $personal['birth_place'];
            if (!empty($personal['address']))
                $employee->address = $personal['address'];

            $employee->save();

            // 3. Employment awal
            Employment::create([
                'employee_id' => $employee->id,
                'department_id' => $employment['department_id'],
                'position_id' => $employment['position_id'],
                'job_level_id' => $employment['job_level_id'],
                'employment_status' => $employment['employment_status'] ?? 'permanent',
                'join_date' => $employment['join_date'],
                'end_date' => $employment['end_date'] ?? null,
                'effective_from' => $employment['join_date'],
                'effective_to' => null, // current
            ]);

            // 4. Bank account (opsional)
            if (!empty($bank['account_number'])) {
                $ba = new BankAccount(['employee_id' => $employee->id, 'is_primary' => true]);
                $ba->bank_name = $bank['bank_name'];
                $ba->branch = $bank['branch'] ?? null;
                $ba->account_number = $bank['account_number'];
                $ba->account_holder = $bank['account_holder'];
                $ba->save();
            }

            // 5. BPJS (opsional)
            if (!empty($bpjs['bpjs_kes']) || !empty($bpjs['bpjs_tk'])) {
                $b = new EmployeeBpjs(['employee_id' => $employee->id, 'bpjs_class' => $bpjs['bpjs_class'] ?? null]);
                if (!empty($bpjs['bpjs_kes']))
                    $b->bpjs_kes = $bpjs['bpjs_kes'];
                if (!empty($bpjs['bpjs_tk']))
                    $b->bpjs_tk = $bpjs['bpjs_tk'];
                $b->save();
            }

            return $employee->load('currentEmployment');
        });
    }

    public function update(Employee $employee, array $personal, array $employment): Employee
    {
        return DB::transaction(function () use ($employee, $personal, $employment) {
            // Update personal
            $employee->fill([
                'full_name' => $personal['full_name'],
                'email' => $personal['email'],
                'birth_date' => $personal['birth_date'] ?? null,
                'gender' => $personal['gender'] ?? null,
                'marital_status' => $personal['marital_status'] ?? null,
                'blood_type' => $personal['blood_type'] ?? null,
                'religion' => $personal['religion'] ?? null,
            ]);

            if (isset($personal['nik']))
                $employee->nik = $personal['nik'];
            if (isset($personal['npwp']))
                $employee->npwp = $personal['npwp'];
            if (isset($personal['phone']))
                $employee->phone = $personal['phone'];
            if (isset($personal['personal_email']))
                $employee->personal_email = $personal['personal_email'];
            if (isset($personal['birth_place']))
                $employee->birth_place = $personal['birth_place'];
            if (isset($personal['address']))
                $employee->address = $personal['address'];
            $employee->save();

            // Jika employment berubah buat history entry
            $current = $employee->currentEmployment;
            $changed = !$current
                || $current->department_id !== $employment['department_id']
                || $current->position_id !== $employment['position_id'];

            if ($changed) {
                // Tutup entry lama
                $current?->update(['effective_to' => now()->toDateString()]);

                // Buat entry baru
                Employment::create([
                    'employee_id' => $employee->id,
                    'department_id' => $employment['department_id'],
                    'position_id' => $employment['position_id'],
                    'job_level_id' => $employment['job_level_id'],
                    'employment_status' => $employment['employment_status'] ?? $current?->employment_status ?? 'permanent',
                    'join_date' => $current?->join_date?->toDateString() ?? now()->toDateString(),
                    'end_date' => $current?->end_date?->toDateString(),
                    'effective_from' => now()->toDateString(),
                    'effective_to' => null,
                ]);
            }

            return $employee->fresh('currentEmployment');
        });
    }

    public function delete(Employee $employee): void
    {
        $employee->delete(); // SoftDelete
    }
}
