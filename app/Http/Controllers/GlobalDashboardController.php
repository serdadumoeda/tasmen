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

        // PERBAIKAN: Aktifkan dan perbaiki logika filter hierarkis untuk manajer.
        // Superadmin akan melewati blok ini dan melihat semua data.
        if (in_array($currentUser->role, [User::ROLE_ESELON_I, User::ROLE_ESELON_II])) {
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds->push($currentUser->id); // Sertakan diri sendiri
            
            // Filter proyek berdasarkan siapa yang memilikinya dalam hierarki
            $projectQuery->whereIn('owner_id', $subordinateIds);

            // Dapatkan ID proyek yang relevan SETELAH difilter
            $relevantProjectIds = $projectQuery->pluck('id');

            // Filter tugas dan pengguna berdasarkan hierarki
            $taskQuery->whereIn('project_id', $relevantProjectIds);
            $userQuery->whereIn('id', $subordinateIds);
        }
 
        $stats = [
            'total_projects' => $projectQuery->count(),
            'total_users' => $userQuery->count(),
            'total_tasks' => $taskQuery->count(),
            'completed_tasks' => (clone $taskQuery)->where('status', 'completed')->count(),
        ];

        // PERBAIKAN: Tambahkan withSum untuk mengambil total anggaran secara efisien
        $allProjects = $projectQuery->with(['leader'])
                                  ->withCount('tasks')
                                  ->withSum('budgetItems', 'total_cost')
                                  ->latest()
                                  ->get();

        $recentActivities = Activity::with('user', 'subject')->latest()->take(15)->get();

        return view('global-dashboard', compact('stats', 'allProjects', 'recentActivities'));
    }
}