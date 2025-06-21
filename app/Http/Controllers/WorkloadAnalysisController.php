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
        $currentUser = auth()->user(); // BARIS BARU: Ambil user yang sedang login

        if (!in_array($currentUser->role, ['superadmin', 'Eselon I', 'Eselon II'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // MODIFIKASI: Gunakan Query Builder untuk persiapan filter
        $userQuery = User::query();
        $projectQuery = Project::query();

        // BARIS BARU: Jika user adalah Eselon II, terapkan filter hirarki
        if ($currentUser->role === 'Eselon II') {
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds[] = $currentUser->id;

            // Filter user yang akan dianalisis hanya dari bawahannya
            $userQuery->whereIn('id', $subordinateIds);
            // Filter proyek berdasarkan proyek yang dimiliki oleh bawahannya
            $projectQuery->whereIn('owner_id', $subordinateIds);
        }

        // MODIFIKASI: Ambil data dari query yang sudah difilter
        // Ambil semua user yang relevan untuk dianalisis
        $users = $userQuery->whereIn('role', ['Koordinator', 'Ketua Tim', 'Sub Koordinator', 'Staff', 'leader', 'user'])
                           ->with('tasks')
                           ->get();

        $projects = $projectQuery->with('tasks', 'members')->get();

        // 1. Analisis Beban Kerja per Pengguna (Logika ini tidak berubah)
        $userWorkload = $users->map(function ($user) {
            $activeTasks = $user->tasks->whereIn('status', ['pending', 'in_progress']);
            $overdueTasks = $activeTasks->where('deadline', '<', now());
            
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


        // 2. Analisis Beban Kerja per Proyek (Logika ini tidak berubah)
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

        // 3. Statistik Global (Logika ini tidak berubah)
        $globalStats = [
            'total_active_tasks' => $projectWorkload->sum('active_tasks_count'),
            'total_overdue_tasks' => $projectWorkload->sum('overdue_tasks_count'),
        ];


        return view('workload-analysis', compact('userWorkload', 'projectWorkload', 'globalStats'));
    }
}