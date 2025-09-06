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
        $period = $request->input('period', 'all'); // Default to 'all'

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
                // Assuming semester 1 is Jan-Jun, semester 2 is Jul-Dec
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

        // --- Subordinates Query ---
        if ($manager->isSuperAdmin()) {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            $subordinateUnitIds = $manager->unit ? $manager->unit->getAllSubordinateUnitIds() : [];
            $subordinatesQuery = User::whereIn('unit_id', $subordinateUnitIds)->where('id', '!=', $manager->id);
        }

        if ($search) {
            $subordinatesQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // Eager load tasks with deadline filter if a period is selected
        $subordinatesQuery->with(['tasks' => function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('deadline', [$startDate, $endDate]);
            }
        }, 'specialAssignments' => function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->where('status', '!=', 'SELESAI') // or filter by dates if they exist
                      ->whereBetween('end_date', [$startDate, $endDate]);
            }
        }]);

        $subordinates = $subordinatesQuery->paginate(20)->withQueryString();

        // --- Workload & Chart Data Calculation ---
        $workloadData = [];
        $chartData = [];

        // Use the paginated results for calculation to avoid double queries
        foreach ($subordinates as $user) {
            $totalTaskHours = $user->tasks->sum('estimated_hours');

            // For periodic analysis, use effective hours. For 'all', use a standard.
            if ($startDate && $endDate) {
                $effectiveHours = $user->getEffectiveWorkingHours($startDate, $endDate);
                $workloadPercentage = ($effectiveHours > 0) ? ($totalTaskHours / $effectiveHours) * 100 : 0;
            } else {
                // Fallback for 'all' - shows total commitment, not capacity
                $effectiveHours = null; // Not applicable
                $workloadPercentage = null; // Not applicable
            }

            $workloadData[$user->id] = [
                'total_hours' => $totalTaskHours,
                'effective_hours' => $effectiveHours,
                'percentage' => $workloadPercentage,
                'active_sk_count' => $user->specialAssignments->count(),
            ];

            $chartData[$user->name] = $totalTaskHours;
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
        // Otorisasi dipindahkan ke UserPolicy untuk konsistensi dan perbaikan bug.
        $this->authorize('rateBehavior', $user);

        $validated = $request->validate([
            'work_behavior_rating' => 'required|string|in:Diatas Ekspektasi,Sesuai Ekspektasi,Dibawah Ekspektasi',
        ]);

        $user->update($validated);

        // Panggil service untuk menghitung ulang skor kinerja user ini dan atasannya.
        $calculator->calculateForSingleUserAndParents($user);

        // PERBAIKAN: Kembalikan respons JSON untuk permintaan AJAX.
        if ($request->ajax() || $request->wantsJson()) {
            // Muat ulang data user untuk mendapatkan nilai-nilai yang sudah dihitung ulang.
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

        $adhocTasks = $user->tasks()->whereNull('project_id')->get();
        $projectTasks = $user->tasks()->whereNotNull('project_id')->get()->groupBy('project.name');

        return view('workload-analysis.show', [
            'user' => $user,
            'adhocTasks' => $adhocTasks,
            'projectTasks' => $projectTasks,
            'specialAssignments' => $user->specialAssignments,
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