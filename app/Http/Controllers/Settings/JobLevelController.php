<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\JobLevel;
use App\Services\JobLevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobLevelController extends Controller
{
    public function __construct(private JobLevelService $service)
    {
    }

    public function index(): View
    {
        $levels = $this->service->all();
        return view('settings.job-levels.index', compact('levels'));
    }

    public function create(): View
    {
        return view('settings.job-levels.form', ['jobLevel' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'level' => 'required|integer|min:1|max:99|unique:job_levels,level',
        ]);

        $this->service->create($data);

        return redirect()->route('settings.job-levels.index')
            ->with('success', 'Job level berhasil ditambahkan.');
    }

    public function edit(JobLevel $jobLevel): View
    {
        return view('settings.job-levels.form', compact('jobLevel'));
    }

    public function update(Request $request, JobLevel $jobLevel): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'level' => "required|integer|min:1|max:99|unique:job_levels,level,{$jobLevel->id},id",
        ]);

        $this->service->update($jobLevel, $data);

        return redirect()->route('settings.job-levels.index')
            ->with('success', 'Job level berhasil diperbarui.');
    }

    public function destroy(JobLevel $jobLevel): RedirectResponse
    {
        try {
            $this->service->delete($jobLevel);
            return redirect()->route('settings.job-levels.index')
                ->with('success', 'Job level berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return redirect()->route('settings.job-levels.index')
                ->with('error', $e->getMessage());
        }
    }
}
