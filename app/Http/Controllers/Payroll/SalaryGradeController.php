<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayrollConfig;
use App\Models\JobLevel;
use App\Models\PayrollComponent;
use App\Models\Position;
use App\Models\SalaryGrade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalaryGradeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Payroll/SalaryGrades/Index', [
            'grades' => SalaryGrade::with(['position', 'jobLevel', 'component'])
                ->orderBy('position_id')
                ->orderBy('job_level_id')
                ->get(),
            'positions' => Position::active()->get(['id', 'name']),
            'jobLevels' => JobLevel::active()->orderBy('level_order')->get(['id', 'name']),
            'components' => PayrollComponent::active()->earnings()->orderBy('sort_order')->get(['id', 'name', 'code', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'position_id' => ['nullable', 'exists:positions,id'],
            'job_level_id' => ['nullable', 'exists:job_levels,id'],
            'component_id' => ['required', 'exists:payroll_components,id'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'is_mandatory' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        SalaryGrade::create($validated);

        return back()->with('success', 'Skema gaji ditambahkan.');
    }

    public function update(Request $request, SalaryGrade $salaryGrade)
    {
        $validated = $request->validate([
            'position_id' => ['nullable', 'exists:positions,id'],
            'job_level_id' => ['nullable', 'exists:job_levels,id'],
            'component_id' => ['required', 'exists:payroll_components,id'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'is_mandatory' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $salaryGrade->update($validated);

        return back()->with('success', 'Skema gaji diperbarui.');
    }

    public function destroy(SalaryGrade $salaryGrade)
    {
        $salaryGrade->delete();
        return back()->with('success', 'Skema gaji dihapus.');
    }

    /**
     * Auto-populate employee_payroll_configs from matching salary grades.
     * Called when employee is assigned a new position/job_level.
     */
    public function applyToEmployee(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'job_level_id' => ['nullable', 'exists:job_levels,id'],
        ]);

        $grades = SalaryGrade::where('is_active', true)
            ->where(function ($q) use ($validated) {
                $q->where(function ($q2) use ($validated) {
                    $q2->where('position_id', $validated['position_id'])
                        ->where('job_level_id', $validated['job_level_id']);
                })
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('position_id', $validated['position_id'])
                            ->whereNull('job_level_id');
                    })
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->whereNull('position_id')
                            ->where('job_level_id', $validated['job_level_id']);
                    });
            })
            ->get();

        $today = Carbon::today()->toDateString();
        $created = 0;

        foreach ($grades as $grade) {
            // Only insert if no config for this component exists yet
            $exists = EmployeePayrollConfig::where('employee_id', $validated['employee_id'])
                ->where('component_id', $grade->component_id)
                ->whereNull('end_date')
                ->exists();

            if (!$exists) {
                EmployeePayrollConfig::create([
                    'employee_id' => $validated['employee_id'],
                    'component_id' => $grade->component_id,
                    'amount' => $grade->default_amount,
                    'effective_date' => $today,
                    'end_date' => null,
                ]);
                $created++;
            }
        }

        return back()->with('success', "Berhasil menerapkan {$created} komponen dari skema gaji.");
    }
}
