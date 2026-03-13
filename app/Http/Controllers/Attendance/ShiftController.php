<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\ShiftMaster;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Attendance/Shifts/Index', [
            'shifts' => ShiftMaster::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:shift_masters,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_overnight' => 'boolean',
            'break_minutes' => 'integer|min:0|max:480',
            'overtime_threshold_minutes' => 'integer|min:0|max:120',
            'is_active' => 'boolean',
        ]);

        ShiftMaster::create($data);

        return back()->with('success', 'Shift berhasil ditambahkan.');
    }

    public function update(Request $request, ShiftMaster $shift)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:shift_masters,name,' . $shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_overnight' => 'boolean',
            'break_minutes' => 'integer|min:0|max:480',
            'overtime_threshold_minutes' => 'integer|min:0|max:120',
            'is_active' => 'boolean',
        ]);

        $shift->update($data);

        return back()->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(ShiftMaster $shift)
    {
        if ($shift->schedules()->exists()) {
            return back()->with('error', 'Shift tidak dapat dihapus karena masih digunakan oleh jadwal.');
        }

        $shift->delete();

        return back()->with('success', 'Shift berhasil dihapus.');
    }
}
