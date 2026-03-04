<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(private ShiftService $service)
    {
    }

    public function index(): View
    {
        $this->authorize('manage-shifts', Shift::class);
        $shifts = Shift::orderBy('clock_in')->get();
        return view('settings.shifts.index', compact('shifts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-shifts', Shift::class);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:shifts,code',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'is_flexible' => 'boolean',
            'late_tolerance_minutes' => 'integer|min:0|max:120',
            'early_leave_tolerance_minutes' => 'integer|min:0|max:120',
            'description' => 'nullable|string|max:255',
        ]);

        $data['is_flexible'] = $request->boolean('is_flexible');
        $this->service->create($data);

        return redirect()->route('settings.shifts.index')
            ->with('success', "Shift '{$data['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorize('manage-shifts', Shift::class);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:20|unique:shifts,code,{$shift->id},id",
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'is_flexible' => 'boolean',
            'late_tolerance_minutes' => 'integer|min:0|max:120',
            'early_leave_tolerance_minutes' => 'integer|min:0|max:120',
            'description' => 'nullable|string|max:255',
        ]);

        $data['is_flexible'] = $request->boolean('is_flexible');
        $this->service->update($shift, $data);

        return redirect()->route('settings.shifts.index')
            ->with('success', "Shift '{$shift->name}' berhasil diubah.");
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $this->authorize('manage-shifts', Shift::class);

        $this->service->delete($shift);

        return redirect()->route('settings.shifts.index')
            ->with('success', "Shift '{$shift->name}' berhasil dihapus.");
    }
}
