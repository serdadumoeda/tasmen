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
        // --- BLOK INI DIMODIFIKASI ---
        $currentUser = Auth::user();
        if (!in_array($currentUser->role, ['superadmin', 'Eselon I', 'Eselon II'])) {
            abort(403, 'Hanya Super Admin, Eselon I, atau Eselon II yang dapat mengakses halaman ini.');
        }

        // Siapkan query builder dasar
        $projectQuery = Project::query();
        $taskQuery = Task::query();
        $userQuery = User::query();

        // Jika user adalah Eselon II, terapkan filter hirarki
        if ($currentUser->role === 'Eselon II') {
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds[] = $currentUser->id;
            
            // Filter proyek berdasarkan owner yang merupakan bawahan
            $projectQuery->whereIn('owner_id', $subordinateIds);

            // Ambil ID proyek yang sudah terfilter untuk memfilter tugas
            $filteredProjectIds = (clone $projectQuery)->pluck('id');

            // Filter tugas berdasarkan proyek yang relevan
            $taskQuery->whereIn('project_id', $filteredProjectIds);

            // Filter user berdasarkan hirarki
            $userQuery->whereIn('id', $subordinateIds);
        }
        // --- AKHIR BLOK MODIFIKASI ---
 
        // Hitung statistik menggunakan query yang sudah disiapkan (global atau terfilter)
        $stats = [
            'total_projects' => $projectQuery->count(),
            'total_users' => $userQuery->count(),
            'total_tasks' => $taskQuery->count(),
            'completed_tasks' => (clone $taskQuery)->where('status', 'completed')->count(),
        ];

        $allProjects = $projectQuery->with(['leader', 'tasks'])->latest()->get();
        
        // Aktivitas terbaru bisa tetap global agar pimpinan tahu apa yang terjadi di sistem
        $recentActivities = Activity::with('user', 'subject')->latest()->take(15)->get();

        return view('global-dashboard', compact('stats', 'allProjects', 'recentActivities'));
    }
}