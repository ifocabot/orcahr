<?php

namespace App\Services;

use App\Models\Shift;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift->fresh();
    }

    public function delete(Shift $shift): void
    {
        if ($shift->schedules()->whereNull('effective_to')->exists()) {
            throw ValidationException::withMessages([
                'shift' => 'Shift tidak bisa dihapus karena masih digunakan oleh karyawan aktif.',
            ]);
        }

        $shift->delete();
    }

    public function toggleActive(Shift $shift): Shift
    {
        $shift->update(['is_active' => !$shift->is_active]);
        return $shift->fresh();
    }
}
