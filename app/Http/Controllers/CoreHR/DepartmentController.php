<?php

namespace App\Http\Controllers\CoreHR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')
            ->with(['parent:id,name', 'manager:id,full_name'])
            ->orderBy('name')
            ->get();

        $allEmployees = Employee::active()->get(['id', 'full_name']);

        return Inertia::render('CoreHR/Departments/Index', [
            'departments' => $departments,
            'allDepartments' => Department::where('is_active', true)->get(['id', 'name']),
            'allEmployees' => $allEmployees,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:departments,code',
            'parent_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return back()->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:10|unique:departments,code,{$department->id}",
            'parent_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return back()->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return back()->with('success', 'Departemen berhasil dihapus.');
    }
}
