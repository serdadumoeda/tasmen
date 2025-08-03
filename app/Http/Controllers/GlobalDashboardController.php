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
        if (!$currentUser->isTopLevelManager()) {
            abort(403, 'Hanya Super Admin, Eselon I, atau Eselon II yang dapat mengakses halaman ini.');
        }

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

        // Dapatkan semua proyek yang relevan untuk perhitungan status
        $relevantProjects = $projectQuery->with('tasks')->get();

        // Hitung status proyek menggunakan accessor di model
        $projectStatusCounts = $relevantProjects->countBy('status');

        $stats = [
            'total_projects' => $relevantProjects->count(),
            'active_users' => (clone $userQuery)->where('status', 'active')->count(),
            'total_users' => $userQuery->count(),
            'pending_requests' => PeminjamanRequest::where('status', 'pending')->count(),
        ];

        // Ambil 5 aktivitas terbaru
        $recentActivities = Activity::with('user', 'subject')
            ->latest()
            ->take(5)
            ->get();

        // Siapkan data untuk chart status proyek
        $chartData = [
            'labels' => ['Selesai', 'Beresiko', 'Berjalan', 'Baru'],
            'data' => [
                $projectStatusCounts->get('completed', 0),
                $projectStatusCounts->get('overdue', 0),
                $projectStatusCounts->get('in_progress', 0),
                $projectStatusCounts->get('pending', 0),
            ],
        ];

        return view('global-dashboard', compact('stats', 'recentActivities', 'chartData'));
    }
}