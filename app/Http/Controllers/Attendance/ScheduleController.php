<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ScheduleAssignment;
use App\Models\Shift;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(private ScheduleService $service)
    {
    }

    /** Assign shift baru ke karyawan */
    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'effective_from' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $shift = Shift::findOrFail($data['shift_id']);
        $this->service->assign($employee, $shift, $data['effective_from'], $data['notes'] ?? null);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', "Shift '{$shift->name}' berhasil di-assign mulai {$data['effective_from']}.");
    }

    /** Tutup/hapus assignment yang sudah expired */
    public function destroy(Employee $employee, ScheduleAssignment $schedule): RedirectResponse
    {
        $this->authorize('update', $employee);

        abort_if($schedule->employee_id !== $employee->id, 404);

        $this->service->delete($schedule);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Riwayat shift berhasil dihapus.');
    }
}
