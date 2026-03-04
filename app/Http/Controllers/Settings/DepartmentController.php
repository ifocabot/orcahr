<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentService $service)
    {
    }

    public function index(): View
    {
        $departments = $this->service->all();
        return view('settings.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $parents = $this->service->getForDropdown();
        return view('settings.departments.form', ['department' => null, 'parents' => $parents]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments,code|alpha_dash',
            'parent_id' => 'nullable|ulid|exists:departments,id',
        ]);

        $this->service->create($data);

        return redirect()->route('settings.departments.index')
            ->with('success', 'Department berhasil ditambahkan.');
    }

    public function edit(Department $department): View
    {
        $parents = $this->service->getForDropdown()->reject(fn($d) => $d->id === $department->id);
        return view('settings.departments.form', compact('department', 'parents'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:20|unique:departments,code,{$department->id},id|alpha_dash",
            'parent_id' => 'nullable|ulid|exists:departments,id',
        ]);

        try {
            $this->service->update($department, $data);
            return redirect()->route('settings.departments.index')
                ->with('success', 'Department berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Department $department): RedirectResponse
    {
        try {
            $this->service->delete($department);
            return redirect()->route('settings.departments.index')
                ->with('success', 'Department berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return redirect()->route('settings.departments.index')
                ->with('error', $e->getMessage());
        }
    }
}
