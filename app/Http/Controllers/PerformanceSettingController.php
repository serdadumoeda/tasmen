<?php

namespace App\Http\Controllers;

use App\Models\PerformanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PerformanceSettingController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_settings');
        $settings = PerformanceSetting::all()->pluck('value', 'key');
        return view('admin.performance_settings.index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        Gate::authorize('manage_settings');

        $validated = $request->validate([
            'manager_weights' => 'array',
            'manager_weights.*' => 'numeric|min:0|max:1',
            'efficiency_cap.min' => 'required|numeric|min:0',
            'efficiency_cap.max' => 'required|numeric|gt:efficiency_cap.min',
            'rating_thresholds' => 'array',
            'rating_thresholds.*' => 'numeric|min:0',
            'weekly_workload_thresholds.green' => 'required|numeric|min:0',
            'weekly_workload_thresholds.yellow' => 'required|numeric|gt:weekly_workload_thresholds.green',
        ]);

        foreach ($validated as $key => $value) {
            $flattened = $request->input($key);
            PerformanceSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $flattened]
            );
        }

        return redirect()->route('admin.performance_settings.index')
                         ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
