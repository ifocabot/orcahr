<?php

namespace App\Http\Controllers\CoreHR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('employees')
            ->with('department:id,name')
            ->orderBy('name')
            ->get();

        return Inertia::render('CoreHR/Positions/Index', [
            'positions' => $positions,
            'departments' => Department::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:positions,code',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        Position::create($validated);

        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:10|unique:positions,code,{$position->id}",
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        $position->update($validated);

        return back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
