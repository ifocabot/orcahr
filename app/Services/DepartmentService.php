<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentService
{
    public function all(): Collection
    {
        return Department::with(['parent'])->orderBy('name')->get();
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        // Cegah set parent ke diri sendiri atau ke child-nya
        if (isset($data['parent_id']) && $data['parent_id'] === $department->id) {
            throw new \RuntimeException('Department tidak bisa menjadi parent diri sendiri.');
        }
        $department->update($data);
        return $department->fresh();
    }

    public function delete(Department $department): void
    {
        if ($department->children()->exists()) {
            throw new \RuntimeException('Tidak bisa menghapus department yang memiliki sub-department.');
        }
        if ($department->positions()->exists()) {
            throw new \RuntimeException('Tidak bisa menghapus department yang masih memiliki posisi.');
        }
        $department->delete();
    }

    public function getForDropdown(): Collection
    {
        return Department::orderBy('name')->get(['id', 'name', 'code']);
    }
}
