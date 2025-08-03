<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use App\Models\Activity;
use App\Models\PeminjamanRequest;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // --- Pengambilan Data untuk Widget ---

        // 1. Statistik Cepat & Tugas Mendesak
        $myTasks = Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
                       ->where('status', '!=', 'completed')
                       ->with('project:id,name') // Eager load nama proyek
                       ->get();

        $tasksInProgress = $myTasks->count();
        $overdueTasks = $myTasks->where('deadline', '<', now()->startOfDay())->sortBy('deadline');
        $dueSoonTasks = $myTasks->where('deadline', '>=', now()->startOfDay())
                                ->where('deadline', '<=', now()->addDays(3)->endOfDay())
                                ->sortBy('deadline');

        // 2. Aktivitas Terbaru Saya
        $myActivities = Activity::where('user_id', $user->id)
                                ->with('subject')
                                ->latest()
                                ->take(5)
                                ->get();

        // 3. Persetujuan Menunggu (jika manajer)
        $pendingApprovals = collect();
        if ($user->canManageUsers()) {
            // Ambil tugas yang perlu review dari bawahan
            $subordinateIds = $user->getAllSubordinateIds();
            $tasksForReview = Task::where('status', 'pending_review')
                                  ->whereIn('project_id', function ($query) use ($user) {
                                      $query->select('id')->from('projects')->where('leader_id', $user->id);
                                  })
                                  ->orWhereHas('assignees', fn($q) => $q->whereIn('user_id', $subordinateIds))
                                  ->with('assignees:name', 'project:id,name')
                                  ->take(5)->get();

            // Ambil permintaan peminjaman yang perlu disetujui
            $loanRequests = PeminjamanRequest::where('approver_id', $user->id)
                                             ->where('status', 'pending')
                                             ->with('requester:id,name', 'requestedUser:id,name')
                                             ->take(5)->get();

            $pendingApprovals = $tasksForReview->concat($loanRequests);
        }

        return view('home', compact(
            'user',
            'tasksInProgress',
            'overdueTasks',
            'dueSoonTasks',
            'myActivities',
            'pendingApprovals'
        ));
    }
}
