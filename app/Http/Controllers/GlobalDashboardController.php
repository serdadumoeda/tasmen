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
        // Pastikan hanya kepala pusdatik yang bisa akses
        if (auth()->user()->role !== 'kepala_pusdatik') {
            abort(403, 'Hanya Kepala Pusdatik yang dapat mengakses halaman ini.');
        }

        // Kumpulkan semua data untuk ringkasan
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