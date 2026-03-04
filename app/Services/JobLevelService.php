<?php

namespace App\Services;

use App\Models\JobLevel;
use Illuminate\Database\Eloquent\Collection;

class JobLevelService
{
    public function all(): Collection
    {
        return JobLevel::orderBy('level')->get();
    }

    public function create(array $data): JobLevel
    {
        return JobLevel::create($data);
    }

    public function update(JobLevel $jobLevel, array $data): JobLevel
    {
        $jobLevel->update($data);
        return $jobLevel->fresh();
    }

    public function delete(JobLevel $jobLevel): void
    {
        if ($jobLevel->positions()->exists()) {
            throw new \RuntimeException('Tidak bisa menghapus level yang masih dipakai posisi.');
        }
        $jobLevel->delete();
    }
}
