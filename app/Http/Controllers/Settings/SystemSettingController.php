<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    public function index(): Response
    {
        $settings = SystemSetting::orderBy('group')->orderBy('label')->get();

        return Inertia::render('Settings/System/Index', [
            'settings' => $settings->groupBy('group'),
            'groups' => $settings->pluck('group')->unique()->sort()->values(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'exists:system_settings,key'],
            'settings.*.value' => ['required', 'string'],
        ]);

        foreach ($validated['settings'] as $item) {
            SystemSetting::set($item['key'], $item['value']);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
