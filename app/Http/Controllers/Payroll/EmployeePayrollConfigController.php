<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePayrollConfig;
use App\Models\PayrollComponent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeePayrollConfigController extends Controller
{
    public function index(Employee $employee)
    {
        return Inertia::render('Payroll/Config/Index', [
            'employee' => $employee->only('id', 'full_name', 'employee_code'),
            'configs' => $employee->payrollConfigs()
                ->with('component')
                ->orderBy('effective_date', 'desc')
                ->get(),
            'components' => PayrollComponent::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'component_id' => ['required', 'exists:payroll_components,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
        ]);

        $employee->payrollConfigs()->create($validated);

        return back()->with('success', 'Konfigurasi gaji ditambahkan.');
    }

    public function update(Request $request, EmployeePayrollConfig $config)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
        ]);

        $config->update($validated);

        return back()->with('success', 'Konfigurasi gaji diperbarui.');
    }

    public function destroy(EmployeePayrollConfig $config)
    {
        $config->delete();

        return back()->with('success', 'Konfigurasi gaji dihapus.');
    }
}
