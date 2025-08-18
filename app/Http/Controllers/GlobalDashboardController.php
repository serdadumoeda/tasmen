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
        if ($currentUser->isStaff()) {
            // Staf melihat proyek di mana mereka menjadi anggota
            $projectQuery->whereHas('members', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            });
            $userQuery->where('id', $currentUser->id);
        } elseif (!$currentUser->isSuperAdmin()) {
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
            'total_tasks' => (clone $taskQuery)->count(),
            'completed_tasks' => (clone $taskQuery)->where('status', 'completed')->count(),
        ];

        if (!$currentUser->isStaff()) {
            $stats['total_users'] = (clone $userQuery)->count();
            $stats['active_users'] = (clone $userQuery)->where('status', 'active')->count();
            $stats['pending_requests'] = PeminjamanRequest::where('status', 'pending')
                                        ->whereIn('approver_id', (clone $userQuery)->pluck('id'))
                                        ->count();
        }

        // --- AWAL LOGIKA FILTER & PENCARIAN ---
        $search = request('search');
        $status = request('status');

        if ($search) {
            $projectQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // Eager load relationships for performance. `tasks` is needed for the status accessor.
        $projectQuery->with(['leader', 'tasks'])->withSum('budgetItems', 'total_cost');

        // Get all projects matching the search criteria first.
        $projects = $projectQuery->latest()->get();

        // Now, filter by the dynamic 'status' attribute on the collection.
        if ($status) {
            $projects = $projects->filter(function ($project) use ($status) {
                return $project->status === $status;
            });
        }

        // Manually paginate the filtered collection.
        $allProjects = new \Illuminate\Pagination\LengthAwarePaginator(
            $projects->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage('page'), 15),
            $projects->count(),
            15,
            \Illuminate\Pagination\Paginator::resolveCurrentPage('page'),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        $allProjects->withQueryString();
        // --- AKHIR LOGIKA FILTER & PENCARIAN ---


        // Menyiapkan data untuk chart (kini tidak lagi digunakan, tapi kita biarkan untuk potensi masa depan)
        $statusCounts = $allProjects->groupBy('status')->map->count();
        $chartData = [
            'labels' => $statusCounts->keys(),
            'data' => $statusCounts->values(),
        ];

        $activityQuery = Activity::with('user', 'subject')->latest();

        if (!$currentUser->isSuperAdmin()) {
            // Manajer melihat aktivitas dari hierarkinya, staf hanya melihat aktivitasnya sendiri.
            $visibleUserIds = $currentUser->getAllSubordinateIds();
            $visibleUserIds->push($currentUser->id);
            $activityQuery->whereIn('user_id', $visibleUserIds);
        }

        $recentActivities = $activityQuery->paginate(15, ['*'], 'activityPage');

        return view('global-dashboard', compact('stats', 'allProjects', 'recentActivities', 'chartData', 'search', 'status'));
    }
}