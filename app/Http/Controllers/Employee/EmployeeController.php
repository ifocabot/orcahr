<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\Position;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $service)
    {
        // Auth check via route middleware (web + auth)
    }

    public function index(): View
    {
        $this->authorize('viewAny', Employee::class);
        $employees = $this->service->all();
        return view('employees.index', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $personal = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|unique:employees,email',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'blood_type' => 'nullable|string|max:5',
            'religion' => 'nullable|in:islam,kristen,katolik,hindu,buddha,konghucu,other',
            'nik' => 'nullable|string|max:20',
            'npwp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        // NIK uniqueness via HMAC
        if (!empty($personal['nik']) && $this->service->isNikTaken($personal['nik'])) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar.'])->withInput();
        }
        if (!empty($personal['npwp']) && $this->service->isNpwpTaken($personal['npwp'])) {
            return back()->withErrors(['npwp' => 'NPWP sudah terdaftar.'])->withInput();
        }

        $employment = $request->validate([
            'department_id' => 'required|ulid|exists:departments,id',
            'position_id' => 'required|ulid|exists:positions,id',
            'job_level_id' => 'required|ulid|exists:job_levels,id',
            'employment_status' => 'required|in:permanent,contract,probation',
            'join_date' => 'required|date',
            'end_date' => 'nullable|date|after:join_date',
        ]);

        $bank = $request->validate([
            'bank_name' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:30',
            'account_holder' => 'nullable|string|max:150',
        ]);

        $bpjs = $request->validate([
            'bpjs_kes' => 'nullable|string|max:30',
            'bpjs_tk' => 'nullable|string|max:30',
            'bpjs_class' => 'nullable|in:1,2,3',
        ]);

        $this->service->create($personal, $employment, $bank, $bpjs);

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);
        $employee->load([
            'currentEmployment.department',
            'currentEmployment.position',
            'currentEmployment.jobLevel',
            'employments.department',
            'employments.position',
            'bankAccounts',
            'bpjs',
            'documents',
        ]);
        return view('employees.show', compact('employee'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $personal = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => "required|email|unique:employees,email,{$employee->id},id",
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'blood_type' => 'nullable|string|max:5',
            'religion' => 'nullable|in:islam,kristen,katolik,hindu,buddha,konghucu,other',
            'nik' => 'nullable|string|max:20',
            'npwp' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        if (!empty($personal['nik']) && $this->service->isNikTaken($personal['nik'], $employee->id)) {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar.'])->withInput();
        }

        $employment = $request->validate([
            'department_id' => 'required|ulid|exists:departments,id',
            'position_id' => 'required|ulid|exists:positions,id',
            'job_level_id' => 'required|ulid|exists:job_levels,id',
            'employment_status' => 'required|in:permanent,contract,probation',
        ]);

        $this->service->update($employee, $personal, $employment);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);
        $this->service->delete($employee);
        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    /** Helpers untuk dropdowns — dipanggil di view via PHP, bukan AJAX */
    private function dropdownData(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'positions' => Position::with('jobLevel')->orderBy('name')->get(),
            'jobLevels' => JobLevel::orderBy('level')->get(['id', 'name', 'level']),
        ];
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);
        return view('employees.create', $this->dropdownData());
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);
        $employee->load(['currentEmployment', 'bankAccounts', 'bpjs']);
        return view('employees.edit', array_merge(['employee' => $employee], $this->dropdownData()));
    }
}
