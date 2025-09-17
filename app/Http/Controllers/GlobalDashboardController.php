<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\Activity;
use App\Models\LeaveRequest;
use App\Models\PeminjamanRequest;
use App\Models\Project;
use App\Models\Surat;
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

            // Filter: Tampilkan proyek yang dimiliki oleh hierarki ATAU di mana pengguna adalah anggota
            $projectQuery->where(function ($query) use ($subordinateIds, $currentUser) {
                $query->whereIn('owner_id', $subordinateIds)
                      ->orWhereHas('members', function ($q) use ($currentUser) {
                          $q->where('user_id', $currentUser->id);
                      });
            });

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
            'completed_tasks' => (clone $taskQuery)->whereHas('status', function ($q) {
                $q->where('key', 'completed');
            })->count(),
        ];

        if (!$currentUser->isStaff()) {
            $stats['total_users'] = (clone $userQuery)->count();
            $stats['active_users'] = (clone $userQuery)->where('status', 'active')->count();
            $stats['pending_requests'] = PeminjamanRequest::where('status', RequestStatus::PENDING)
                                        ->whereIn('approver_id', (clone $userQuery)->pluck('id'))
                                        ->count();
        }

        // --- AWAL LOGIKA FILTER & PENCARIAN ---
        $search = request('search');
        $status = request('status');

        if ($search) {
            $projectQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        // REFACTOR: Eager load counts for performance. This is crucial for the new progress accessor.
        $projectQuery->with(['leader'])
                     ->withCount(['tasks', 'completedTasks'])
                     ->withSum('budgetItems', 'total_cost');

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

        // --- APPROVAL INBOX LOGIC ---
        $approvalItems = collect();
        if ($currentUser->canManageUsers()) {
            // 1. Get Leave Requests awaiting approval
            $leaveRequests = LeaveRequest::where('current_approver_id', $currentUser->id)
                ->whereIn('status', [RequestStatus::PENDING, RequestStatus::APPROVED_BY_SUPERVISOR])
                ->with('user', 'leaveType')
                ->get();

            // 2. Get Surat Keluar awaiting approval
            // NOTE: This feature is temporarily disabled due to a missing 'jenis' column in the 'surat' table.
            // $subordinateIds = $currentUser->getAllSubordinateIds();
            // $suratKeluar = Surat::where('jenis', 'keluar')
            //     ->where('status', 'draft')
            //     ->whereIn('pembuat_id', $subordinateIds)
            //     ->with('pembuat')
            //     ->get();
            $suratKeluar = collect();

            // 3. Get Peminjaman Pegawai awaiting approval
            $peminjamanRequests = PeminjamanRequest::where('approver_id', $currentUser->id)
                ->where('status', RequestStatus::PENDING)
                ->with('requester', 'requestedUser')
                ->get();

            // 4. Get Tasks awaiting approval
            $taskRequests = Task::whereHas('status', function ($query) {
                    $query->where('key', 'pending_review');
                })
                ->whereHas('project.leader', function ($query) use ($currentUser) {
                    $query->where('id', $currentUser->id);
                })
                ->with('project', 'users')
                ->get();

            // Combine all items
            $approvalItems = $leaveRequests->concat($suratKeluar)
                                          ->concat($peminjamanRequests)
                                          ->concat($taskRequests)
                                          ->sortByDesc('created_at');
        }
        // --- END APPROVAL INBOX LOGIC ---

        // Data for Task Status Pie Chart for the logged-in user and their subordinates
        $userIds = $currentUser->getAllSubordinateIds();
        $userIds->push($currentUser->id);

        // Optimized query to count tasks by status directly in the database
        $myTaskStats = Task::whereHas('assignees', function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            })
            ->join('task_statuses', 'tasks.task_status_id', '=', 'task_statuses.id')
            ->select('task_statuses.key', DB::raw('count(*) as total'))
            ->groupBy('task_statuses.key')
            ->pluck('total', 'key');

        $taskStatusChartData = [
            'Selesai' => $myTaskStats->get('completed', 0),
            'Dikerjakan' => $myTaskStats->get('in_progress', 0),
            'Tertunda' => $myTaskStats->get('pending', 0),
        ];

        return view('global-dashboard', compact('stats', 'allProjects', 'recentActivities', 'chartData', 'search', 'status', 'approvalItems', 'taskStatusChartData'));
    }
}