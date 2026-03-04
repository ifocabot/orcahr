<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-holidays');
        $holidays = Holiday::orderBy('date')->get()->groupBy(fn($h) => substr($h->date, 0, 4));
        return view('settings.holidays.index', compact('holidays'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-holidays');

        $data = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:150',
            'is_national' => 'boolean',
        ]);

        $data['year'] = date('Y', strtotime($data['date']));
        $data['is_national'] = $request->boolean('is_national', true);

        Holiday::create($data);

        return redirect()->route('settings.holidays.index')
            ->with('success', "Hari libur '{$data['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        $this->authorize('manage-holidays');

        $data = $request->validate([
            'date' => "required|date|unique:holidays,date,{$holiday->id},id",
            'name' => 'required|string|max:150',
            'is_national' => 'boolean',
        ]);

        $data['year'] = date('Y', strtotime($data['date']));
        $data['is_national'] = $request->boolean('is_national', true);

        $holiday->update($data);

        return redirect()->route('settings.holidays.index')
            ->with('success', "Hari libur '{$holiday->name}' berhasil diubah.");
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $this->authorize('manage-holidays');
        $holiday->delete();

        return redirect()->route('settings.holidays.index')
            ->with('success', "Hari libur dihapus.");
    }
}
