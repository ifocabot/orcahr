<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /** Super-admin bypasses semua policy */
    public function before(User $user): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    /** Siapa yang boleh melihat daftar karyawan */
    public function viewAny(User $user): bool
    {
        return $user->can('view-employees');
    }

    /** Siapa yang boleh melihat detail satu karyawan */
    public function view(User $user, Employee $employee): bool
    {
        return $user->can('view-employees');
    }

    /** Siapa yang boleh membuat karyawan baru */
    public function create(User $user): bool
    {
        return $user->can('create-employees');
    }

    /** Siapa yang boleh update data karyawan */
    public function update(User $user, Employee $employee): bool
    {
        return $user->can('edit-employees');
    }

    /** Hanya super-admin yang boleh delete (via before()) */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('delete-employees');
    }

    /** Akses data sensitif: NIK, NPWP, rekening */
    public function viewSensitiveData(User $user, Employee $employee): bool
    {
        return $user->can('view-sensitive-data');
    }
}
