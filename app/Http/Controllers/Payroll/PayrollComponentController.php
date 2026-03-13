<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollComponent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollComponentController extends Controller
{
    public function index()
    {
        return Inertia::render('Payroll/Components/Index', [
            'components' => PayrollComponent::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:payroll_components,code'],
            'type' => ['required', 'in:earning,deduction,benefit'],
            'is_taxable' => ['boolean'],
            'is_fixed' => ['boolean'],
            'formula' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        PayrollComponent::create($validated);

        return back()->with('success', 'Komponen gaji ditambahkan.');
    }

    public function update(Request $request, PayrollComponent $component)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:payroll_components,code,' . $component->id],
            'type' => ['required', 'in:earning,deduction,benefit'],
            'is_taxable' => ['boolean'],
            'is_fixed' => ['boolean'],
            'formula' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $component->update($validated);

        return back()->with('success', 'Komponen gaji diperbarui.');
    }

    public function destroy(PayrollComponent $component)
    {
        if ($component->details()->exists()) {
            return back()->withErrors(['delete' => 'Tidak dapat menghapus komponen yang sudah digunakan dalam payroll.']);
        }

        $component->delete();

        return back()->with('success', 'Komponen gaji dihapus.');
    }
}
