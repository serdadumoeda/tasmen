<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\BudgetRealization;
use App\Services\InsightService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExecutiveSummaryController extends Controller
{
    public function index(InsightService $insightService)
    {
        $user = auth()->user();

        if (!$user->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // --- PERBAIKAN: Filter semua data berdasarkan hierarki manajer ---
        $subordinateIds = $user->getAllSubordinateIds();
        $relevantUserIds = $subordinateIds->push($user->id);

        // Generate insights based on the logged-in user's hierarchy
        $insights = $insightService->generate($user);
        $previewInsights = $insights->take(2);

        // Basis query untuk proyek yang relevan dengan hierarki
        $relevantProjectsQuery = Project::whereIn('leader_id', $relevantUserIds)
            ->orWhereHas('tasks', function ($query) use ($relevantUserIds) {
                $query->whereHas('assignees', function ($subQuery) use ($relevantUserIds) {
                    $subQuery->whereIn('users.id', $relevantUserIds);
                });
            });

        // Ambil semua proyek relevan untuk kalkulasi KPI
        $allProjects = (clone $relevantProjectsQuery)
            ->with(['tasks', 'budgetItems', 'leader'])
            ->withSum('budgetItems as budget_items_sum_total_cost', 'total_cost')
            ->get();

        // Ambil proyek relevan yang dipaginasi untuk tabel
        $projectsForTable = (clone $relevantProjectsQuery)
            ->with(['leader', 'tasks'])
            ->withSum('budgetItems', 'total_cost')
            ->latest()
            ->paginate(10);



        // --- Data & KPI Utama (Logika lainnya tidak berubah) ---
        $activeProjects = $allProjects->whereNotIn('status', ['completed', 'cancelled'])->count();
        $totalBudget = $allProjects->sum('budget_items_sum_total_cost'); // Sekarang ini akan berisi nilai yang benar
        
        $allBudgetItemIds = $allProjects->flatMap->budgetItems->pluck('id');
        $totalRealization = BudgetRealization::whereIn('budget_item_id', $allBudgetItemIds)->sum('amount');
        
        $budgetAbsorptionRate = $totalBudget > 0 ? round(($totalRealization / $totalBudget) * 100) : 0;
        $overallProgress = $allProjects->count() > 0 ? round($allProjects->avg('progress')) : 0;

        $criticalProjects = $this->getCriticalProjects($allProjects);
        $overdueProjectsCount = $criticalProjects->count();

        $allSubordinates = $this->getAllSubordinatesIteratively($user);
        $topPerformers = $allSubordinates->sortByDesc(fn($sub) => $sub->getFinalPerformanceValueAttribute())->take(5);
        $mostUtilized = $this->getMostUtilizedSubordinates($allSubordinates);
        
        $performanceTrends = $this->getPerformanceTrends($allProjects);

        // --- Analisis Anggaran per Proyek (Logika ini sekarang akan bekerja dengan benar) ---
        $budgetByProject = $allProjects->map(function ($project) {
            $totalBudget = $project->budget_items_sum_total_cost ?? 0; // Nilai ini sekarang sudah benar
            $budgetItemsIds = $project->budgetItems->pluck('id');
            $totalRealization = BudgetRealization::whereIn('budget_item_id', $budgetItemsIds)->sum('amount');
            
            $project->absorption_rate = $totalBudget > 0 ? round(($totalRealization / $totalBudget) * 100) : 0;
            if ($totalBudget == 0 && $totalRealization > 0) {
                $project->absorption_rate = 100; 
            }
            
            $project->total_realization = $totalRealization;

            return $project;
        })->sortByDesc('budget_items_sum_total_cost')->take(5);

        return view('executive-summary', compact(
            'projectsForTable',
            'activeProjects',
            'overdueProjectsCount',
            'totalBudget',
            'budgetAbsorptionRate',
            'overallProgress',
            'criticalProjects',
            'topPerformers',
            'mostUtilized',
            'budgetByProject',
            'performanceTrends',
            'insights',
            'previewInsights'
        ));
    }

    // --- Sisa fungsi di controller tidak perlu diubah ---

    private function getCriticalProjects(Collection $projects): Collection
    {
        return $projects->filter(function ($project) {
            if ($project->status === 'completed') return false;
            $timeElapsedPercentage = 0;
            if ($project->start_date && $project->end_date && $project->end_date > $project->start_date) {
                $totalDuration = $project->end_date->diffInDays($project->start_date) ?: 1;
                $daysElapsed = now()->diffInDays($project->start_date);
                $timeElapsedPercentage = ($daysElapsed / $totalDuration) * 100;
            }
            $isAtRisk = $timeElapsedPercentage > 75 && $project->progress < ($timeElapsedPercentage * 0.8);
            $isCritical = ($project->end_date && $project->end_date < now()) || ($project->end_date && $project->end_date->isBetween(now(), now()->addDays(14)) && $project->progress < 90);
            return $isAtRisk || $isCritical;
        })->sortBy('end_date')->take(5);
    }

    private function getMostUtilizedSubordinates(Collection $subordinates): Collection
    {
        return $subordinates->map(function ($sub) {
            $totalHours = $sub->total_project_hours + $sub->total_ad_hoc_hours;
            $sub->utilization = 40 > 0 ? round(($totalHours / 40) * 100) : 0;
            return $sub;
        })->sortByDesc('utilization')->take(5);
    }
    
    private function getAllSubordinatesIteratively(User $user, $includeSelf = false): Collection
    {
        // SUPERADMIN FIX: A Superadmin does not have organizational subordinates in the same way.
        // Loading all users is a performance killer and incorrect. Return an empty collection.
        if ($user->isSuperAdmin()) {
            return collect();
        }

        if (!$user->unit) {
            return collect();
        }

        $subordinateUnitIds = $user->unit->getAllSubordinateUnitIds();

        $query = User::whereIn('unit_id', $subordinateUnitIds);

        if (!$includeSelf) {
            $query->where('id', '!=', $user->id);
        }

        return $query->get();
    }

    private function getPerformanceTrends(Collection $projects): array
    {
        $labels = [];
        $progressData = [];
        $absorptionData = [];

        $projectIds = $projects->pluck('id');
        $allBudgetItemIds = $projects->flatMap->budgetItems->pluck('id');

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();
            $labels[] = $date->format('M Y');
            
            // --- Kalkulasi Progres ---
            $completedStatusId = \App\Models\TaskStatus::where('key', 'completed')->value('id');

            $totalTasks = Task::whereIn('project_id', $projectIds)
                              ->where('created_at', '<=', $date)
                              ->count();
                              
            $completedTasks = Task::whereIn('project_id', $projectIds)
                                  ->where('task_status_id', $completedStatusId)
                                  ->where('updated_at', '<=', $date)
                                  ->count();

            $progressData[] = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            // --- Kalkulasi Penyerapan Anggaran ---
            $totalBudgetInMonth = \App\Models\BudgetItem::whereIn('id', $allBudgetItemIds)
                                                        ->where('created_at', '<=', $date) // Anggaran yang sudah dibuat s/d bulan tsb
                                                        ->sum('total_cost');

            $realizationInMonth = BudgetRealization::whereIn('budget_item_id', $allBudgetItemIds)
                ->where('created_at', '<=', $date) // Realisasi yang terjadi s/d bulan tsb
                ->sum('amount');
            
            $absorptionData[] = $totalBudgetInMonth > 0 ? round(($realizationInMonth / $totalBudgetInMonth) * 100) : 0;
        }

        return [
            'labels' => $labels,
            'progress' => $progressData,
            'absorption' => $absorptionData,
        ];
    }
}