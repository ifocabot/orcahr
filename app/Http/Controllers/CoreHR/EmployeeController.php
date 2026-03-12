<?php

namespace App\Http\Controllers\CoreHR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with(['department', 'position', 'jobLevel'])
            ->search($request->search)
            ->when($request->department_id, fn($q, $v) => $q->where('department_id', $v))
            ->when($request->status, fn($q, $v) => $q->where('employment_status', $v))
            ->orderBy($request->sort_by ?? 'full_name', $request->sort_dir ?? 'asc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('CoreHR/Employees/Index', [
            'employees' => $employees,
            'departments' => Department::where('is_active', true)->get(['id', 'name']),
            'filters' => $request->only(['search', 'department_id', 'status', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('CoreHR/Employees/Create', [
            'departments' => Department::where('is_active', true)->get(['id', 'name']),
            'positions' => Position::where('is_active', true)->get(['id', 'name', 'department_id']),
            'jobLevels' => JobLevel::where('is_active', true)->orderBy('level_order')->get(['id', 'name']),
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        Employee::create($request->validated());

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'position', 'jobLevel', 'user']);

        return Inertia::render('CoreHR/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('CoreHR/Employees/Edit', [
            'employee' => $employee,
            'departments' => Department::where('is_active', true)->get(['id', 'name']),
            'positions' => Position::where('is_active', true)->get(['id', 'name', 'department_id']),
            'jobLevels' => JobLevel::where('is_active', true)->orderBy('level_order')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()->route('employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }
}
