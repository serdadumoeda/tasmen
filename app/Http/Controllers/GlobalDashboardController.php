<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalDashboardController extends Controller
{
    public function index()
    {

        if (!in_array(auth()->user()->role, ['superadmin', 'manager'])) {
            // Pesan error juga diperbarui agar lebih akurat
            abort(403, 'Hanya Super Admin atau Manager yang dapat mengakses halaman ini.');
        }


 
        $stats = [
            'total_projects' => Project::count(),
            'total_users' => User::count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
        ];

        $allProjects = Project::with(['leader', 'tasks'])->latest()->get();
        $recentActivities = Activity::with('user', 'subject')->latest()->take(15)->get();

        return view('global-dashboard', compact('stats', 'allProjects', 'recentActivities'));
    }
}