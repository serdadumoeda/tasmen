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
        $currentUser = auth()->user();
        if (!$currentUser->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        $userQuery = User::query();

        if ($currentUser->role === 'Eselon II') {
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds[] = $currentUser->id;
            $userQuery->whereIn('id', $subordinateIds);
        }

        // Ambil hanya user yang punya peran untuk mengerjakan tugas
        $users = $userQuery->whereIn('role', ['Koordinator', 'Ketua Tim', 'Sub Koordinator', 'Staff'])
            ->with(['tasks' => function ($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            }])
            ->get();

        $userWorkload = $users->map(function ($user) {
            $weeklyCapacity = 40; // Kapasitas kerja standar per minggu (jam)
            $totalAssignedHours = $user->tasks->sum('estimated_hours');
            $utilization = ($weeklyCapacity > 0) ? round(($totalAssignedHours / $weeklyCapacity) * 100) : 0;

            return [
                'name' => $user->name,
                'role' => $user->role,
                'active_tasks_count' => $user->tasks->count(),
                'total_assigned_hours' => $totalAssignedHours,
                'utilization' => $utilization,
            ];
        })->sortByDesc('utilization');

        return view('workload-analysis', compact('userWorkload'));
    }
}