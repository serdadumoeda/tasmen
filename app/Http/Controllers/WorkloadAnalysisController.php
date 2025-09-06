<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PageTitleService;
use App\Services\BreadcrumbService;
use Carbon\Carbon;

class WorkloadAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $manager = Auth::user();
        $search = $request->input('search');
        $period = $request->input('period', 'all');

        // --- Date Period Calculation ---
        $startDate = null;
        $endDate = null;
        switch ($period) {
            case 'weekly':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'monthly':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarterly':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'semester':
                $currentMonth = Carbon::now()->month;
                if ($currentMonth <= 6) {
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->startOfYear()->addMonths(5)->endOfMonth();
                } else {
                    $startDate = Carbon::now()->startOfYear()->addMonths(6);
                    $endDate = Carbon::now()->endOfYear();
                }
                break;
            case 'yearly':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
        }

        // --- Base Subordinates Query ---
        if ($manager->isSuperAdmin()) {
            $baseSubordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            $subordinateUnitIds = $manager->unit ? $manager->unit->getAllSubordinateUnitIds() : [];
            $baseSubordinatesQuery = User::whereIn('unit_id', $subordinateUnitIds)->where('id', '!=', $manager->id);
        }

        if ($search) {
            $baseSubordinatesQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // --- Eager Loading Closure ---
        $eagerLoadTasksAndAssignments = function ($query) use ($startDate, $endDate) {
            $query->with(['tasks' => function ($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $q->whereBetween('deadline', [$startDate, $endDate]);
                }
            }, 'specialAssignments' => function ($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $q->where('status', '!=', 'SELESAI')
                      ->whereBetween('end_date', [$startDate, $endDate]);
                }
            }]);
            return $query;
        };

        // --- Chart Data Calculation (ALL subordinates) ---
        $chartQuery = clone $baseSubordinatesQuery;
        $allSubordinatesForChart = $eagerLoadTasksAndAssignments($chartQuery)->get();
        $chartData = $allSubordinatesForChart->mapWithKeys(function ($user) {
            return [$user->name => $user->tasks->sum('estimated_hours')];
        });

        // --- Table Data Calculation (Paginated) ---
        $tableQuery = $eagerLoadTasksAndAssignments($baseSubordinatesQuery);
        $subordinates = $tableQuery->paginate(20)->withQueryString();
        $workloadData = [];

        foreach ($subordinates as $user) {
            $totalTaskHours = $user->tasks->sum('estimated_hours');

            if ($startDate && $endDate) {
                $effectiveHours = $user->getEffectiveWorkingHours($startDate, $endDate);
                $workloadPercentage = ($effectiveHours > 0) ? ($totalTaskHours / $effectiveHours) * 100 : 0;
            } else {
                $effectiveHours = null;
                $workloadPercentage = null;
                $internalHours = $user->internal_tasks_hours;
                $externalHours = $user->external_tasks_hours;
                $totalTaskHours = $internalHours + $externalHours; // Recalculate total for 'all'
            }

            $workloadData[$user->id] = [
                'total_hours' => $totalTaskHours,
                'effective_hours' => $effectiveHours,
                'percentage' => $workloadPercentage,
                'active_sk_count' => $user->specialAssignments->count(),
                // Add these for the 'all' view
                'internal_hours' => $internalHours ?? 0,
                'external_hours' => $externalHours ?? 0,
            ];
        }

        return view('workload-analysis.index', compact(
            'manager',
            'subordinates',
            'search',
            'chartData',
            'workloadData',
            'period'
        ));
    }

    /**
     * Update penilaian perilaku kerja oleh atasan.
     * Logika otorisasi diubah sesuai aturan baru.
     */
    public function updateBehavior(Request $request, User $user, \App\Services\PerformanceCalculatorService $calculator)
    {
        $this->authorize('rateBehavior', $user);

        $validated = $request->validate([
            'work_behavior_rating' => 'required|string|in:Diatas Ekspektasi,Sesuai Ekspektasi,Dibawah Ekspektasi',
        ]);

        $user->update($validated);

        $calculator->calculateForSingleUserAndParents($user);

        if ($request->ajax() || $request->wantsJson()) {
            $user->refresh();
            return response()->json(['success' => true, 'user' => $user]);
        }

        return back()->with('success', "Penilaian perilaku kerja untuk {$user->name} berhasil diperbarui.");
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(
            'tasks.project',
            'specialAssignments',
            'projects'
        );

        $settings = \App\Models\Setting::pluck('value', 'key')->all();
        $adhocTasks = $user->tasks()->whereNull('project_id')->get();
        $projectTasks = $user->tasks()->whereNotNull('project_id')->get()->groupBy('project.name');

        return view('workload-analysis.show', [
            'user' => $user,
            'adhocTasks' => $adhocTasks,
            'projectTasks' => $projectTasks,
            'specialAssignments' => $user->specialAssignments,
            'settings' => $settings,
        ]);
    }

    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Analisis Beban Kerja');
        $breadcrumbService->add('Analisis Beban Kerja', route('workload.analysis'));
        $breadcrumbService->add('Alur Kerja');
        return view('workload-analysis.workflow');
    }
}