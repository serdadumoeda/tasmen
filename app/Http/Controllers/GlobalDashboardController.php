<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\PeminjamanRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GlobalDashboardController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        $projectQuery = Project::query();
        $userQuery = User::query();

        // Terapkan filter hierarkis untuk manajer, Superadmin melihat semua.
        if (!$currentUser->isSuperAdmin()) {
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds->push($currentUser->id); // Sertakan diri sendiri

            // Filter proyek berdasarkan siapa yang memilikinya dalam hierarki
            $projectQuery->whereIn('owner_id', $subordinateIds);
            $userQuery->whereIn('id', $subordinateIds);
        }

        $taskQuery = Task::query();

        // Jika bukan superadmin, filter tugas juga berdasarkan proyek yang relevan
        if (!$currentUser->isSuperAdmin()) {
            $relevantProjectIds = (clone $projectQuery)->pluck('id');
            $taskQuery->whereIn('project_id', $relevantProjectIds);
        }

        $stats = [
            'total_projects' => (clone $projectQuery)->count(),
            'total_users' => (clone $userQuery)->count(),
            'active_users' => (clone $userQuery)->where('status', 'active')->count(),
            'total_tasks' => (clone $taskQuery)->count(),
            'completed_tasks' => (clone $taskQuery)->where('status', 'completed')->count(),
            'pending_requests' => PeminjamanRequest::where('status', 'pending')
                                    ->whereIn('approver_id', (clone $userQuery)->pluck('id'))
                                    ->count(),
        ];

        // Mengambil data untuk list proyek, seperti yang dibutuhkan view
        $allProjects = $projectQuery->with(['leader', 'tasks'])
                                  ->withSum('budgetItems', 'total_cost')
                                  ->latest()
                                  ->get();

        $recentActivities = Activity::with('user', 'subject')->latest()->take(15)->get();

        return view('global-dashboard', compact('stats', 'allProjects', 'recentActivities'));
    }
}