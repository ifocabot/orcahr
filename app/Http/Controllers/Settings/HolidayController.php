<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController extends Controller
{
    public function index(Request $request): Response
    {
        $year = (int) ($request->year ?? now()->year);

        return Inertia::render('Settings/Holidays/Index', [
            'holidays' => Holiday::year($year)->orderBy('holiday_date')->get(),
            'year' => $year,
            'years' => range(now()->year - 1, now()->year + 2),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'holiday_date' => ['required', 'date', 'unique:holidays,holiday_date'],
            'type' => ['required', 'in:national,company'],
            'is_paid' => ['boolean'],
        ]);

        $validated['year'] = (int) date('Y', strtotime($validated['holiday_date']));

        Holiday::create($validated);

        return back()->with('success', 'Hari libur ditambahkan.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'holiday_date' => ['required', 'date', 'unique:holidays,holiday_date,' . $holiday->id],
            'type' => ['required', 'in:national,company'],
            'is_paid' => ['boolean'],
        ]);

        $validated['year'] = (int) date('Y', strtotime($validated['holiday_date']));

        $holiday->update($validated);

        return back()->with('success', 'Hari libur diperbarui.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return back()->with('success', 'Hari libur dihapus.');
    }
}
