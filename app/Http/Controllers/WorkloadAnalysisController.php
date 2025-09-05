<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PageTitleService;
use App\Services\BreadcrumbService;

class WorkloadAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $manager = Auth::user();
        $search = $request->input('search');
        $highlightUserId = $request->input('highlight_user_id');

        if (!$manager->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // Dapatkan query dasar untuk bawahan
        if ($manager->isSuperAdmin()) {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            // Replikasi logika dari getAllSubordinates untuk mendapatkan query builder
            $subordinateUnitIds = $manager->unit ? $manager->unit->getAllSubordinateUnitIds() : [];
            $subordinatesQuery = User::whereIn('unit_id', $subordinateUnitIds)->where('id', '!=', $manager->id);
        }

        // Terapkan filter pencarian jika ada
        if ($search) {
            $subordinatesQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // --- Chart Data Preparation ---
        // Clone the query before pagination to get all subordinates for the chart
        $allSubordinatesForChart = (clone $subordinatesQuery)->with('tasks')->get();

        $chartData = $allSubordinatesForChart->mapWithKeys(function ($user) {
            // Use accessors from User model if they exist, otherwise calculate here
            $totalHours = $user->tasks->sum('estimated_hours');
            return [$user->name => $totalHours];
        });

        // Ambil hasil dengan paginasi dan pertahankan query string
        $subordinates = $subordinatesQuery->paginate(20)->withQueryString();
        
        return view('workload-analysis.index', compact('manager', 'subordinates', 'search', 'highlightUserId', 'chartData'));
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