<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Leave/Types/Index', [
            'leaveTypes' => LeaveType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:10', 'unique:leave_types,code'],
            'accrual_rate_monthly' => ['required', 'numeric', 'min:0', 'max:99'],
            'max_balance' => ['required', 'integer', 'min:0'],
            'max_carryover' => ['required', 'integer', 'min:0'],
            'min_service_months' => ['required', 'integer', 'min:0'],
            'is_paid' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        LeaveType::create($validated);

        return back()->with('success', 'Jenis cuti berhasil ditambahkan.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:10', 'unique:leave_types,code,' . $leaveType->id],
            'accrual_rate_monthly' => ['required', 'numeric', 'min:0', 'max:99'],
            'max_balance' => ['required', 'integer', 'min:0'],
            'max_carryover' => ['required', 'integer', 'min:0'],
            'min_service_months' => ['required', 'integer', 'min:0'],
            'is_paid' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $leaveType->update($validated);

        return back()->with('success', 'Jenis cuti berhasil diperbarui.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->requests()->exists()) {
            return back()->withErrors(['delete' => 'Tidak dapat menghapus jenis cuti yang sudah digunakan.']);
        }

        $leaveType->delete();

        return back()->with('success', 'Jenis cuti berhasil dihapus.');
    }
}
