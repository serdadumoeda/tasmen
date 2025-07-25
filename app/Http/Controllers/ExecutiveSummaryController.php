<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\BudgetRealization;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExecutiveSummaryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

 
        $projects = Project::with(['tasks', 'budgetItems', 'leader'])
                           ->withSum('budgetItems as budget_items_sum_total_cost', 'total_cost')
                           ->get();



        // --- Data & KPI Utama (Logika lainnya tidak berubah) ---
        $activeProjects = $projects->whereNotIn('status', ['completed', 'cancelled'])->count();
        $totalBudget = $projects->sum('budget_items_sum_total_cost'); // Sekarang ini akan berisi nilai yang benar
        
        $allBudgetItemIds = $projects->flatMap->budgetItems->pluck('id');
        $totalRealization = BudgetRealization::whereIn('budget_item_id', $allBudgetItemIds)->sum('amount');
        
        $budgetAbsorptionRate = $totalBudget > 0 ? round(($totalRealization / $totalBudget) * 100) : 0;
        $overallProgress = $projects->count() > 0 ? round($projects->avg('progress')) : 0;

        $criticalProjects = $this->getCriticalProjects($projects);
        $overdueProjectsCount = $criticalProjects->count();

        $allSubordinates = $this->getAllSubordinatesIteratively($user);
        $topPerformers = $allSubordinates->sortByDesc(fn($sub) => $sub->getFinalPerformanceValueAttribute())->take(5);
        $mostUtilized = $this->getMostUtilizedSubordinates($allSubordinates);
        
        $performanceTrends = $this->getPerformanceTrends($projects);

        // --- Analisis Anggaran per Proyek (Logika ini sekarang akan bekerja dengan benar) ---
        $budgetByProject = $projects->map(function ($project) {
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
            'projects',
            'activeProjects',
            'overdueProjectsCount',
            'totalBudget',
            'budgetAbsorptionRate',
            'overallProgress',
            'criticalProjects',
            'topPerformers',
            'mostUtilized',
            'budgetByProject',
            'performanceTrends'
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
        $subordinates = collect();
        $queue = new Collection();
        $visitedIds = [];
        if ($includeSelf) {
            if (!in_array($user->id, $visitedIds)) {
                $subordinates->push($user);
                $visitedIds[] = $user->id;
            }
        }
        foreach($user->children as $child) {
            $queue->push($child);
        }
        while ($queue->isNotEmpty()) {
            $currentUser = $queue->shift();
            if (!in_array($currentUser->id, $visitedIds)) {
                $subordinates->push($currentUser);
                $visitedIds[] = $currentUser->id;
                $currentUser->loadMissing('children');
                foreach ($currentUser->children as $child) {
                    if (!in_array($child->id, $visitedIds)) {
                        $queue->push($child);
                    }
                }
            }
        }
        return $subordinates;
    }

    private function getPerformanceTrends(Collection $projects): array
    {
        $labels = [];
        $progressData = [];
        $absorptionData = [];

        $projectIds = $projects->pluck('id');

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i)->endOfMonth();
            $labels[] = $date->format('M Y');
            
            $totalTasks = Task::whereIn('project_id', $projectIds)
                              ->where('created_at', '<=', $date)
                              ->count();
                              
            $completedTasks = Task::whereIn('project_id', $projectIds)
                                  ->where('status', 'completed')
                                  ->where('updated_at', '<=', $date)
                                  ->count();

            $progressData[] = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            $allBudgetItemIds = $projects->flatMap->budgetItems->pluck('id');
            $totalBudgetInMonth = \App\Models\BudgetItem::whereIn('id', $allBudgetItemIds)->sum('total_cost');

            $realizationInMonth = BudgetRealization::whereIn('budget_item_id', $allBudgetItemIds)
                ->where('created_at', '<=', $date)
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