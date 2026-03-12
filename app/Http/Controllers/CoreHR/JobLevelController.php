<?php

namespace App\Http\Controllers\CoreHR;

use App\Http\Controllers\Controller;
use App\Models\JobLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobLevelController extends Controller
{
    public function index()
    {
        $jobLevels = JobLevel::withCount('employees')
            ->orderBy('level_order')
            ->get();

        return Inertia::render('CoreHR/JobLevels/Index', [
            'jobLevels' => $jobLevels,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        JobLevel::create($validated);

        return back()->with('success', 'Job level berhasil ditambahkan.');
    }

    public function update(Request $request, JobLevel $jobLevel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $jobLevel->update($validated);

        return back()->with('success', 'Job level berhasil diperbarui.');
    }

    public function destroy(JobLevel $jobLevel)
    {
        $jobLevel->delete();

        return back()->with('success', 'Job level berhasil dihapus.');
    }
}
