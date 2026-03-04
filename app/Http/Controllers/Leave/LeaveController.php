<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(private LeaveService $service)
    {
    }

    /** Daftar permohonan cuti saya + admin view */
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;

        $requests = LeaveRequest::with(['leaveType', 'approver'])
            ->where('employee_id', $employee?->id)
            ->latest()
            ->paginate(20);

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee?->id)
            ->where('year', now()->year)
            ->get();

        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('leave.index', compact('requests', 'balances', 'leaveTypes'));
    }

    /** Submit permohonan cuti */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        abort_unless($employee, 403, 'Akun belum terhubung ke data karyawan.');

        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->service->submit($employee, $data, $request->file('attachment'));

        return redirect()->route('leave.index')
            ->with('success', 'Permohonan cuti berhasil diajukan.');
    }

    /** Approve permohonan (HR/Dept Head) */
    public function approve(LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('approve-leave');
        $this->service->approve($leave, auth()->user());

        return back()->with('success', 'Permohonan cuti disetujui.');
    }

    /** Reject permohonan */
    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('approve-leave');

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->service->reject($leave, auth()->user(), $data['rejection_reason']);

        return back()->with('success', 'Permohonan cuti ditolak.');
    }
}
