<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobType;
use App\Models\WorkloadComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AbkController extends Controller
{
    /**
     * Display the main dashboard for ABK.
     */
    public function index()
    {
        Gate::authorize('manage_settings');
        $jobTypes = JobType::with('workloadComponents')->get();

        // Get effective working hours from performance settings, with a sensible default.
        $effectiveHoursPerYear = (int) \App\Models\PerformanceSetting::get('abk_effective_hours_per_year', 1500);

        $results = $jobTypes->map(function ($jobType) use ($effectiveHoursPerYear) {
            $totalHoursNeeded = $jobType->workloadComponents->sum(function ($component) {
                return $component->volume * $component->time_norm;
            });

            $employeeRequirement = ($effectiveHoursPerYear > 0) ? ($totalHoursNeeded / $effectiveHoursPerYear) : 0;

            return [
                'job_type' => $jobType,
                'total_hours' => $totalHoursNeeded,
                'employee_requirement' => round($employeeRequirement, 2),
            ];
        });

        return view('admin.abk.index', ['results' => $results]);
    }

    /**
     * Show the form for managing components of a job type.
     */
    public function show(JobType $jobType)
    {
        Gate::authorize('manage_settings');
        $jobType->load('workloadComponents');
        return view('admin.abk.show', compact('jobType'));
    }

    /**
     * Store a new job type.
     */
    public function storeJobType(Request $request)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_types,name',
            'description' => 'nullable|string',
        ]);
        JobType::create($validated);
        return redirect()->route('admin.abk.index')->with('success', 'Jenis pekerjaan baru berhasil dibuat.');
    }

    /**
     * Store a new workload component for a job type.
     */
    public function storeWorkloadComponent(Request $request, JobType $jobType)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'volume' => 'required|integer|min:0',
            'output_unit' => 'required|string|max:255',
            'time_norm' => 'required|numeric|min:0',
        ]);

        $jobType->workloadComponents()->create($validated);

        return redirect()->route('admin.abk.show', $jobType)->with('success', 'Komponen beban kerja berhasil ditambahkan.');
    }

    // Note: Edit/Update/Delete methods for these would also be needed for a full implementation.
}
