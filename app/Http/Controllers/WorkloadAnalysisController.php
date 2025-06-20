<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkloadAnalysisController extends Controller
{
    public function index()
    {
        if (!in_array(auth()->user()->role, ['superadmin', 'Eselon I', 'Eselon II'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // Ambil semua user yang memiliki role 'leader' atau 'user' untuk dianalisis
        $users = User::whereIn('role', ['leader', 'user'])->with('tasks')->get();
        $projects = Project::with('tasks', 'members')->get();

        // 1. Analisis Beban Kerja per Pengguna
        $userWorkload = $users->map(function ($user) {
            $activeTasks = $user->tasks->whereIn('status', ['pending', 'in_progress']);
            $overdueTasks = $activeTasks->where('deadline', '<', now());
            
            // Formula Skor Beban Kerja (bisa disesuaikan)
            // Bobot: 1 poin per tugas aktif, 3 poin tambahan per tugas overdue
            $workloadScore = $activeTasks->count() + ($overdueTasks->count() * 3);

            return [
                'name' => $user->name,
                'role' => $user->role,
                'active_tasks_count' => $activeTasks->count(),
                'overdue_tasks_count' => $overdueTasks->count(),
                'completed_last_30_days' => $user->tasks()->where('status', 'completed')->where('updated_at', '>=', now()->subDays(30))->count(),
                'workload_score' => $workloadScore,
            ];
        })->sortByDesc('workload_score');


        // 2. Analisis Beban Kerja per Proyek
        $projectWorkload = $projects->map(function ($project) {
            $totalTasks = $project->tasks->count();
            if ($totalTasks === 0) {
                $completionPercentage = 0;
            } else {
                $completedTasks = $project->tasks->where('status', 'completed')->count();
                $completionPercentage = round(($completedTasks / $totalTasks) * 100);
            }

            return [
                'name' => $project->name,
                'total_tasks' => $totalTasks,
                'active_tasks_count' => $project->tasks->whereIn('status', ['pending', 'in_progress'])->count(),
                'overdue_tasks_count' => $project->tasks->where('deadline', '<', now())->whereIn('status', ['pending', 'in_progress'])->count(),
                'member_count' => $project->members->count(),
                'completion_percentage' => $completionPercentage,
            ];
        })->sortByDesc('active_tasks_count');

        // 3. Statistik Global
        $globalStats = [
            'total_active_tasks' => $projectWorkload->sum('active_tasks_count'),
            'total_overdue_tasks' => $projectWorkload->sum('overdue_tasks_count'),
        ];


        return view('workload-analysis', compact('userWorkload', 'projectWorkload', 'globalStats'));
    }
}