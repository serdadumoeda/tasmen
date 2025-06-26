<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class GlobalDashboardController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        if (!$currentUser->isTopLevelManager()) {
            abort(403, 'Hanya Super Admin, Eselon I, atau Eselon II yang dapat mengakses halaman ini.');
        }

        $projectQuery = Project::query();
        $taskQuery = Task::query();
        $userQuery = User::query();

        // if ($currentUser->role === 'Eselon II') {
        //     $subordinateIds = $currentUser->getAllSubordinateIds();
        //     $subordinateIds[] = $currentUser->id;
            
        //     $projectQuery->whereIn('owner_id', $subordinateIds);
        //     $taskQuery->whereIn('project_id', $projectQuery->pluck('id'));
        //     $userQuery->whereIn('id', $subordinateIds);
        // }
 
        $stats = [
            'total_projects' => $projectQuery->count(),
            'total_users' => $userQuery->count(),
            'total_tasks' => $taskQuery->count(),
            'completed_tasks' => (clone $taskQuery)->where('status', 'completed')->count(),
        ];

        // PERBAIKAN: Tambahkan withSum untuk mengambil total anggaran secara efisien
        $allProjects = $projectQuery->with(['leader', 'tasks'])
                                  ->withSum('budgetItems', 'total_cost')
                                  ->latest()
                                  ->get();

        $recentActivities = Activity::with('user', 'subject')->latest()->take(15)->get();

        return view('global-dashboard', compact('stats', 'allProjects', 'recentActivities'));
    }
}