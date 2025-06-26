<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExecutiveSummaryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Pastikan hanya pimpinan yang bisa akses
        if (!$user->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // 1. Ambil semua proyek yang relevan dengan user (menggunakan HierarchicalScope otomatis)
        $projects = Project::with(['tasks', 'budgetItems'])->get();

        // 2. Kalkulasi KPI Utama
        $activeProjects = $projects->whereNotIn('status', ['completed', 'cancelled'])->count();
        $overdueProjects = $projects->filter(fn($p) => $p->end_date && $p->end_date < now() && $p->status !== 'completed')->count();
        $totalBudget = $projects->sum('budget_items_sum_total_cost'); // Menggunakan nilai yang sudah diagregasi
        $totalTasks = $projects->sum(fn($p) => $p->tasks->count());
        $completedTasks = $projects->sum(fn($p) => $p->tasks->where('status', 'completed')->count());
        $overallProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // 3. Sorotan Proyek (Proyek yang paling butuh perhatian)
        $criticalProjects = $projects->filter(function ($project) {
            $isOverdue = $project->end_date && $project->end_date < now() && $project->status !== 'completed';
            $isNearDeadline = $project->end_date && $project->end_date->isBetween(now(), now()->addDays(7));
            return $isOverdue || $isNearDeadline;
        })->sortBy('end_date')->take(5);


        // 4. Data Kinerja Tim (Bawahan langsung dari pimpinan)
        $subordinates = $user->children()->with(['tasks', 'ledProjects'])->get();

        // Tim/Orang Paling Produktif
        $topPerformers = $subordinates->sortByDesc('final_performance_value')->take(5);

        // Tim/Orang Paling Sibuk
        $mostLoaded = $subordinates->sortByDesc('total_project_hours')->take(5);

        return view('executive-summary', compact(
            'projects',
            'activeProjects',
            'overdueProjects',
            'totalBudget',
            'overallProgress',
            'criticalProjects',
            'topPerformers',
            'mostLoaded'
        ));
    }
}