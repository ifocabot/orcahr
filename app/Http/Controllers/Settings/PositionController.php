<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Services\DepartmentService;
use App\Services\JobLevelService;
use App\Services\PositionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function __construct(
        private PositionService $service,
        private DepartmentService $departmentService,
        private JobLevelService $jobLevelService,
    ) {
    }

    public function index(): View
    {
        $positions = $this->service->all();
        return view('settings.positions.index', compact('positions'));
    }

    public function create(): View
    {
        $departments = $this->departmentService->getForDropdown();
        $jobLevels = $this->jobLevelService->all();
        return view('settings.positions.form', [
            'position' => null,
            'departments' => $departments,
            'jobLevels' => $jobLevels,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'department_id' => 'required|ulid|exists:departments,id',
            'job_level_id' => 'required|ulid|exists:job_levels,id',
        ]);

        $this->service->create($data);

        return redirect()->route('settings.positions.index')
            ->with('success', 'Posisi berhasil ditambahkan.');
    }

    public function edit(Position $position): View
    {
        $departments = $this->departmentService->getForDropdown();
        $jobLevels = $this->jobLevelService->all();
        return view('settings.positions.form', compact('position', 'departments', 'jobLevels'));
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'department_id' => 'required|ulid|exists:departments,id',
            'job_level_id' => 'required|ulid|exists:job_levels,id',
        ]);

        $this->service->update($position, $data);

        return redirect()->route('settings.positions.index')
            ->with('success', 'Posisi berhasil diperbarui.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        try {
            $this->service->delete($position);
            return redirect()->route('settings.positions.index')
                ->with('success', 'Posisi berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return redirect()->route('settings.positions.index')
                ->with('error', $e->getMessage());
        }
    }
}
