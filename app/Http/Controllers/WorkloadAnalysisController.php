<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;

class WorkloadAnalysisController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        if (!$currentUser->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }
        
        // MODIFIKASI: Kita akan memuat relasi tugas secara terpisah untuk akurasi
        $relations = [
            'specialAssignments' => fn($q) => $q->where('status', 'AKTIF')
        ];

        $loadRecursively = function ($query) use (&$loadRecursively, $relations) {
            return $query->with(array_merge($relations, ['children' => $loadRecursively]));
        };

        $userQuery = User::query();

        if ($currentUser->role === 'Eselon II') {
            $topLevelUsers = $userQuery->where('id', $currentUser->id)->with(['children' => $loadRecursively] + $relations)->get();
        } else {
            $topLevelUsers = $userQuery->whereNull('parent_id')->with(['children' => $loadRecursively] + $relations)->get();
        }

        // BARU: Dapatkan semua ID pengguna dalam hirarki untuk satu query efisien
        $allUserIds = $this->fetchAllUserIdsFromCollection($topLevelUsers);

        // BARU: Hitung total jam tugas (proyek & ad-hoc) untuk semua pengguna dalam satu query
        $tasksHoursByUser = Task::whereIn('status', ['pending', 'in_progress'])
            ->whereHas('assignees', function($q) use ($allUserIds) {
                $q->whereIn('user_id', $allUserIds);
            })
            ->join('task_user', 'tasks.id', '=', 'task_user.task_id')
            ->selectRaw('task_user.user_id, sum(tasks.estimated_hours) as total_hours')
            ->groupBy('task_user.user_id')
            ->pluck('total_hours', 'user_id');

        // BARU: "Suntikkan" data jam kerja ke setiap model user
        $this->injectTaskHours($topLevelUsers, $tasksHoursByUser);
        
        return view('workload-analysis', compact('topLevelUsers'));
    }

    // BARU: Helper function untuk mengambil semua ID user secara rekursif
    private function fetchAllUserIdsFromCollection($users)
    {
        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->id;
            if ($user->children->isNotEmpty()) {
                $ids = array_merge($ids, $this->fetchAllUserIdsFromCollection($user->children));
            }
        }
        return array_unique($ids);
    }

    // BARU: Helper function untuk "menyuntikkan" data jam ke model user
    private function injectTaskHours($users, $hoursData)
    {
        foreach ($users as $user) {
            $user->total_assigned_hours = $hoursData->get($user->id, 0);
            if ($user->children->isNotEmpty()) {
                $this->injectTaskHours($user->children, $hoursData);
            }
        }
    }
}