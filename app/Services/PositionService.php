<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Database\Eloquent\Collection;

class PositionService
{
    public function all(): Collection
    {
        return Position::with(['department', 'jobLevel'])->orderBy('name')->get();
    }

    public function create(array $data): Position
    {
        return Position::create($data);
    }

    public function update(Position $position, array $data): Position
    {
        $position->update($data);
        return $position->fresh();
    }

    public function delete(Position $position): void
    {
        // Cek apakah ada employment aktif yang pakai posisi ini
        if ($position->employments()->exists()) {
            throw new \RuntimeException('Tidak bisa menghapus posisi yang masih digunakan karyawan.');
        }
        $position->delete();
    }

    public function getForDropdown(?string $departmentId = null): Collection
    {
        return Position::with('jobLevel')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->orderBy('name')
            ->get(['id', 'name', 'department_id', 'job_level_id']);
    }
}
