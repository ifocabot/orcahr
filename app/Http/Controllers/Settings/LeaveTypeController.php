<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-leave');
        $leaveTypes = LeaveType::orderBy('name')->get();
        return view('settings.leave-types.index', compact('leaveTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-leave');

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:leave_types,code',
            'default_quota' => 'required|integer|min:1|max:365',
            'is_paid' => 'boolean',
            'is_carry_forward' => 'boolean',
            'max_carry_forward' => 'integer|min:0|max:365',
            'requires_attachment' => 'boolean',
            'min_days_advance' => 'integer|min:0|max:365',
            'max_consecutive_days' => 'integer|min:0|max:365',
        ]);

        $data['is_paid'] = $request->boolean('is_paid', true);
        $data['is_carry_forward'] = $request->boolean('is_carry_forward');
        $data['requires_attachment'] = $request->boolean('requires_attachment');

        LeaveType::create($data);

        return redirect()->route('settings.leave-types.index')
            ->with('success', "Tipe cuti '{$data['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('manage-leave');

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:20|unique:leave_types,code,{$leaveType->id},id",
            'default_quota' => 'required|integer|min:1|max:365',
            'is_paid' => 'boolean',
            'is_carry_forward' => 'boolean',
            'max_carry_forward' => 'integer|min:0|max:365',
            'requires_attachment' => 'boolean',
            'min_days_advance' => 'integer|min:0|max:365',
            'max_consecutive_days' => 'integer|min:0|max:365',
        ]);

        $data['is_paid'] = $request->boolean('is_paid', true);
        $data['is_carry_forward'] = $request->boolean('is_carry_forward');
        $data['requires_attachment'] = $request->boolean('requires_attachment');

        $leaveType->update($data);

        return redirect()->route('settings.leave-types.index')
            ->with('success', "Tipe cuti '{$leaveType->name}' berhasil diubah.");
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('manage-leave');

        if ($leaveType->requests()->exists()) {
            return redirect()->route('settings.leave-types.index')
                ->with('error', 'Tipe cuti tidak bisa dihapus karena ada permohonan terkait.');
        }

        $leaveType->delete();
        return redirect()->route('settings.leave-types.index')
            ->with('success', "Tipe cuti dihapus.");
    }
}
