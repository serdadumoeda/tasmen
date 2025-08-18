<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\PeminjamanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    
    public function index(Request $request)
    {
        // The HierarchicalScope is automatically applied.
        $query = Project::with(['owner', 'leader', 'members', 'tasks'])
            ->withSum('budgetItems', 'total_cost');

        // 1. Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Note: Filtering by the dynamic 'status' attribute is complex as it's calculated in the model.
        // This would require a significant refactor to implement efficiently.
        // For now, we are only implementing search.

        $projects = $query->latest()->paginate(15)->appends($request->query());

        // --- Dashboard Stats ---
        // These stats are global for the entire system for now.
        // This could be scoped to the user in the future if needed.
        $stats = [
            'users' => User::count(),
            'tasks' => Task::count(),
            'tasks_completed' => Task::where('status', 'completed')->count(),
        ];

        // --- Recent Activity Feed ---
        // Fetches the 5 most recent activities across all projects.
        $activities = Activity::with('user', 'subject')
            ->latest()
            ->take(5)
            ->get();

        // Pass all data to the dashboard view.
        return view('dashboard', compact('projects', 'stats', 'activities'));
    }

    public function createStep1()
    {
        $this->authorize('create', Project::class);
        return view('projects.create_step1', ['project' => new Project()]);
    }

    public function storeStep1(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:projects,name',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'owner_id' => Auth::id(),
            'leader_id' => Auth::id(),
        ]);

        return redirect()->route('projects.create.step2', $project);
    }

    public function createStep2(Project $project)
    {
        $this->authorize('update', $project);

        $user = Auth::user();
        $subordinateIds = $user->getAllSubordinateIds();
        $subordinateIds[] = $user->id;
        $potentialMembers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();

        return view('projects.create_step2', compact('project', 'potentialMembers'));
    }
    
    public function storeStep2(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $user = Auth::user();
        $subordinateIds = collect($user->getAllSubordinateIds());
        $subordinateIds->push($user->id);

        $existingMemberIds = $project->members()->pluck('users.id');
        $validMemberIds = $subordinateIds->merge($existingMemberIds)->unique();

        $validated = $request->validate([
            'leader_id' => ['required', 'exists:users,id', Rule::in($validMemberIds)],
            'members' => 'required|array',
            'members.*' => ['exists:users,id', Rule::in($validMemberIds)],
        ]);

        $project->update(['leader_id' => $validated['leader_id']]);

        $this->syncMembers($project, $validated['leader_id'], $validated['members']);

        return redirect()->route('projects.show', $project)->with('success', 'Tim proyek berhasil dibentuk!');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);
        $user = Auth::user();

        // Load project relationships, but handle tasks separately for filtering.
        $project->load(['owner', 'leader', 'members', 'activities.user']);

        // --- Task Query with Filtering, Sorting, and Pagination ---
        $taskQuery = $project->tasks()->with(['assignees', 'comments.user', 'attachments', 'subTasks']);

        // Base visibility for staff
        if ($user->isStaff()) {
            $taskQuery->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }

        // Search by title
        if ($request->filled('task_search')) {
            $taskQuery->where('title', 'like', '%' . $request->input('task_search') . '%');
        }

        // Filter by status
        if ($request->filled('task_status')) {
            $taskQuery->where('status', $request->input('task_status'));
        }

        // Filter by priority
        if ($request->filled('task_priority')) {
            $taskQuery->where('priority', $request->input('task_priority'));
        }

        // Filter by assignee
        if ($request->filled('task_assignee')) {
            $taskQuery->whereHas('assignees', fn($q) => $q->where('user_id', $request->input('task_assignee')));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        if (in_array($sortBy, ['title', 'status', 'priority', 'deadline', 'created_at'])) {
            $taskQuery->orderBy($sortBy, $sortDir);
        }

        // Paginate the filtered and sorted tasks
        $tasks = $taskQuery->paginate(10, ['*'], 'tasksPage')->appends($request->query());
        
        // --- Other Data ---
        $loanRequests = PeminjamanRequest::where('project_id', $project->id)
                            ->with(['requester', 'requestedUser', 'approver'])
                            ->latest()
                            ->get();
        
        $projectMembers = $project->members->sortBy('name');

        // Stats should be calculated on all tasks of the project, not just the paginated/filtered ones.
        $allTasks = $project->tasks()->get();
        $taskStatuses = $allTasks->countBy('status');
        $stats = [
            'total' => $allTasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];

        return view('projects.show', compact('project', 'tasks', 'projectMembers', 'stats', 'loanRequests'));
    }


    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        // PERBAIKAN: Eager load relasi untuk menghindari N+1 query
        $project->load('owner', 'members', 'tasks');
        
        $currentMembers = $project->members;
        $referenceUser = $project->owner ?? auth()->user();

        // Ambil ID bawahan sebagai Collection untuk kemudahan pengecekan di view
        $subordinateIds = collect($referenceUser->getAllSubordinateIds());
        $subordinateIds->push($referenceUser->id);
        
        $subordinates = User::whereIn('id', $subordinateIds)->get();
        $potentialMembers = $currentMembers->merge($subordinates)->unique('id')->sortBy('name');

        // Ambil semua permintaan peminjaman untuk proyek ini.
        // `keyBy` digunakan agar kita bisa mencari status dengan mudah berdasarkan ID user di view.
        $loanRequests = PeminjamanRequest::where('project_id', $project->id)
            ->latest() // Ambil yang terbaru jika ada permintaan ganda
            ->get()
            ->keyBy('requested_user_id');
        
        $taskStatuses = $project->tasks->countBy('status');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];
        
        // Kirim data baru ($loanRequests dan $subordinateIds) ke view
        return view('projects.edit', compact('project', 'potentialMembers', 'stats', 'loanRequests', 'subordinateIds'));
    }
    
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $referenceUser = $project->owner ?? auth()->user();
        $subordinateIds = collect($referenceUser->getAllSubordinateIds());
        $subordinateIds->push($referenceUser->id);

        $existingMemberIds = $project->members()->pluck('users.id');
        $validMemberIds = $subordinateIds->merge($existingMemberIds)->unique();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('projects')->ignore($project->id)],
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => ['required', 'exists:users,id', Rule::in($validMemberIds)],
            'members' => 'required|array',
            'members.*' => ['exists:users,id', Rule::in($validMemberIds)],
        ]);

        $project->update($validated);

        $this->syncMembers($project, $validated['leader_id'], $validated['members']);
        
        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil diperbarui.');
    }

    private function syncMembers(Project $project, int $leaderId, array $memberIds): void
    {
        $memberIds = collect($memberIds);
        if (!$memberIds->contains($leaderId)) {
            $memberIds->push($leaderId);
        }
        $project->members()->sync($memberIds->unique());
    }

    // ... (Sisa method tidak perlu diubah) ...
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $projectName = $project->name;
        $project->delete();
        return redirect()->route('dashboard')->with('success', "Proyek '{$projectName}' berhasil dihapus.");
    }
    
    public function sCurve(Project $project)
    {
        $this->authorize('view', $project);
        if (!$project->start_date || !$project->end_date) {
            return back()->with('error', 'Proyek ini belum memiliki tanggal mulai dan selesai untuk membuat Kurva S.');
        }

        if ($project->tasks()->count() === 0) {
            return back()->with('error', 'Proyek ini belum memiliki tugas untuk membuat Kurva S.');
        }

        $startDate = \Carbon\Carbon::parse($project->start_date);
        $endDate = \Carbon\Carbon::parse($project->end_date);
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate)->toArray(); // Convert to array once

        $labels = array_map(fn($date) => $date->format('d M'), $period);
        $dateStrings = array_map(fn($date) => $date->format('Y-m-d'), $period);

        // --- Rencana (Planned) ---
        $plannedDailyHours = array_fill_keys($dateStrings, 0);
        $tasks = $project->tasks()->whereNotNull('deadline')->where('estimated_hours', '>', 0)->get();
        $totalHours = $tasks->sum('estimated_hours');

        foreach ($tasks as $task) {
            // Gunakan start_date tugas jika ada, jika tidak, gunakan tanggal mulai proyek
            $taskStart = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : $startDate;
            $taskEnd = \Carbon\Carbon::parse($task->deadline);
            // Pastikan tanggal mulai tidak setelah tanggal selesai
            if ($taskStart->gt($taskEnd)) {
                continue; // Lewati tugas dengan data tanggal yang tidak valid
            }
            $taskDurationDays = $taskStart->diffInDays($taskEnd) + 1;

            if ($taskDurationDays > 0) {
                $hoursPerDay = $task->estimated_hours / $taskDurationDays;
                for ($date = $taskStart->copy(); $date->lte($taskEnd); $date->addDay()) {
                    $currentDateStr = $date->format('Y-m-d');
                    // Directly check if the date key exists in our planned hours array.
                    // This is the most robust way to prevent the "Undefined array key" error.
                    if (isset($plannedDailyHours[$currentDateStr])) {
                       $plannedDailyHours[$currentDateStr] += $hoursPerDay;
                    }
                }
            }
        }

        $plannedCumulative = [];
        $cumulative = 0;
        foreach ($plannedDailyHours as $hours) {
            $cumulative += $hours;
            $plannedCumulative[] = round($cumulative, 2);
        }

        // --- Aktual (Actual) ---
        $allTimeLogs = DB::table('time_logs')
            ->join('tasks', 'time_logs.task_id', '=', 'tasks.id')
            ->where('tasks.project_id', $project->id)
            ->whereNotNull('end_time')
            ->select('time_logs.end_time', 'time_logs.duration_in_minutes')
            ->get();

        $dailyActualHours = $allTimeLogs->map(function ($log) {
            $log->date = \Carbon\Carbon::parse($log->end_time)->format('Y-m-d');
            return $log;
        })->groupBy('date')->map(function ($group) {
            return $group->sum('duration_in_minutes') / 60;
        });

        $actualCumulative = [];
        $cumulative = 0;
        foreach ($dateStrings as $dateString) {
            if (isset($dailyActualHours[$dateString])) {
                $cumulative += $dailyActualHours[$dateString];
            }
            $actualCumulative[] = round($cumulative, 2);
        }

        // Normalize to percentage if totalHours is not zero
        if ($totalHours > 0) {
            $plannedCumulative = array_map(fn($val) => round(($val / $totalHours) * 100, 2), $plannedCumulative);
            $actualCumulative = array_map(fn($val) => round(($val / $totalHours) * 100, 2), $actualCumulative);
        }

        $chartData = [
            'labels' => $labels,
            'planned' => $plannedCumulative,
            'actual' => $actualCumulative,
            'total_hours' => round($totalHours, 2),
            'has_planned_data' => $totalHours > 0,
            'has_actual_data' => $dailyActualHours->sum() > 0,
        ];
        return view('projects.s-curve', compact('project', 'chartData'));
    }

    public function teamDashboard(Project $project)
    {
        $this->authorize('viewTeamDashboard', $project);
        // Eager load all necessary relationships to prevent N+1 queries
        $project->load(['members', 'tasks.assignees', 'tasks.timeLogs']);

        $teamSummary = collect();
        foreach ($project->members as $member) {
            $memberTasks = $project->tasks->filter(function ($task) use ($member) {
                return $task->assignees->contains($member);
            });

            if ($memberTasks->isEmpty()) {
                // Add member with zero stats if they have no tasks
                $teamSummary->push([
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'total_tasks' => 0,
                    'pending_tasks' => 0,
                    'inprogress_tasks' => 0,
                    'completed_tasks' => 0,
                    'overdue_tasks' => 0,
                    'total_estimated_hours' => 0,
                    'total_logged_hours' => 0,
                    'weighted_average_progress' => 0,
                    'priority_counts' => collect(\App\Models\Task::PRIORITIES)->mapWithKeys(fn($p) => [$p => 0]),
                ]);
                continue;
            }

            $totalEstimatedHours = $memberTasks->sum('estimated_hours');
            $totalLoggedMinutes = $memberTasks->flatMap->timeLogs->sum('duration_in_minutes');

            $weightedProgressSum = $memberTasks->reduce(function ($carry, $task) {
                return $carry + ($task->progress * $task->estimated_hours);
            }, 0);

            $weightedAverageProgress = ($totalEstimatedHours > 0)
                ? round($weightedProgressSum / $totalEstimatedHours)
                : round($memberTasks->avg('progress')); // Fallback to simple average if no hours estimated

            $teamSummary->push([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'total_tasks' => $memberTasks->count(),
                'pending_tasks' => $memberTasks->where('status', 'pending')->count(),
                'inprogress_tasks' => $memberTasks->where('status', 'in_progress')->count(),
                'completed_tasks' => $memberTasks->where('status', 'completed')->count(),
                'overdue_tasks' => $memberTasks->where('deadline', '<', now())->where('status', '!=', 'completed')->count(),
                'total_estimated_hours' => round($totalEstimatedHours, 1),
                'total_logged_hours' => round($totalLoggedMinutes / 60, 1),
                'weighted_average_progress' => $weightedAverageProgress,
                'priority_counts' => $memberTasks->countBy('priority'),
            ]);
        }
        return view('projects.team-dashboard', compact('project', 'teamSummary'));
    }

    public function getTeamMemberTasks(Project $project, User $user)
    {
        $this->authorize('view', $project);

        // Ensure the requested user is actually a member of this project
        if (!$project->members->contains($user)) {
            return response()->json(['error' => 'User not found in this project.'], 404);
        }

        $tasks = $user->tasks()
            ->where('project_id', $project->id)
            ->with('subTasks') // Eager load subtasks
            ->orderBy('deadline', 'asc')
            ->get();

        // We return a JSON response that can be fetched with JavaScript.
        return response()->json($tasks);
    }

    public function downloadReport(Project $project)
    {
        if (!auth()->user()->isTopLevelManager()) {
            abort(403);
        }
        $project->load('leader', 'members', 'tasks.assignees', 'tasks.timeLogs');
        $taskStatuses = $project->tasks->countBy('status');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];
        $totalMinutesLogged = $project->tasks->flatMap->timeLogs->sum('duration_in_minutes');
        $totalHoursLogged = floor($totalMinutesLogged / 60);
        $remainingMinutes = $totalMinutesLogged % 60;
        $data = [
            'project' => $project,
            'stats' => $stats,
            'totalLoggedTime' => "{$totalHoursLogged} jam {$remainingMinutes} menit",
        ];
        $pdf = Pdf::loadView('reports.project-summary', $data);
        return $pdf->download('laporan-proyek-' . $project->name . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function showKanban(Project $project)
    {
        $this->authorize('view', $project);
    
        $tasks = $project->tasks()->with(['assignees', 'comments', 'subTasks'])->get();
    
        $groupedTasks = $tasks->groupBy('status')->union([
            'pending'     => collect(),
            'in_progress' => collect(),
            'for_review'  => collect(),
            'completed'   => collect(),
        ]);
    
        return view('projects.kanban', compact('project', 'groupedTasks'));
    }

    public function showCalendar(Project $project)
    {
        $this->authorize('view', $project);
        return view('projects.calendar', compact('project'));
    }
    
    public function tasksJson(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->whereNotNull('deadline')
            ->with('assignees')
            ->get(['id', 'title', 'start_date', 'deadline', 'status', 'project_id', 'priority']);

        $events = $tasks->map(function ($task) use ($project) {
            $color = '#3b82f6'; // Default blue
            switch ($task->priority) {
                case 'high': $color = '#ef4444'; break; // red-500
                case 'critical': $color = '#8b5cf6'; break; // violet-500
                case 'medium': $color = '#f97316'; break; // orange-500
                case 'low': $color = '#22c55e'; break; // green-500
            }
            if ($task->status === 'completed') {
                $color = '#6b7280'; // gray-500
            }

            $assigneeNames = $task->assignees->pluck('name')->join(', ');

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->start_date ? $task->start_date->format('Y-m-d') : $task->deadline->format('Y-m-d'),
                'end' => $task->deadline->addDay()->format('Y-m-d'), // Add a day to make the end date inclusive
                'url'   => route('projects.show', $project->id) . '#task-' . $task->id,
                'color' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'project_name' => $project->name,
                    'assignees' => $assigneeNames ?: 'Belum ditugaskan',
                    'status' => ucfirst(str_replace('_', ' ', $task->status)),
                    'priority' => ucfirst($task->priority),
                ]
            ];
        });

        // Add the project duration as a background event
        if ($project->start_date && $project->end_date) {
            $events->prepend([
                'id' => 'project_duration',
                'title' => 'Durasi Proyek',
                'start' => $project->start_date->format('Y-m-d'),
                'end' => $project->end_date->addDay()->format('Y-m-d'),
                'display' => 'background',
                'color' => '#eef2ff' // indigo-50
            ]);
        }

        return response()->json($events);
    }
}