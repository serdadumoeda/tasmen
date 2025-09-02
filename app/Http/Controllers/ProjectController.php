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
        $query = Project::with(['owner', 'leader', 'members', 'tasks'])
            ->withSum('budgetItems', 'total_cost');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $projects = $query->latest()->paginate(15)->appends($request->query());

        $completedStatus = \App\Models\TaskStatus::where('key', 'completed')->first();
        $stats = [
            'users' => User::count(),
            'tasks' => Task::count(),
            'tasks_completed' => $completedStatus ? Task::where('task_status_id', $completedStatus->id)->count() : 0,
        ];

        $activities = Activity::with('user', 'subject')->latest()->take(5)->get();

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

        $project->load(['owner', 'leader', 'members', 'activities.user', 'surat']);

        $taskQuery = $project->tasks()->with(['assignees', 'comments.user', 'attachments', 'subTasks', 'status', 'priorityLevel']);

        if ($user->isStaff()) {
            $taskQuery->whereHas('assignees', fn($q) => $q->where('user_id', $user->id));
        }

        if ($request->filled('task_search')) {
            $taskQuery->where('title', 'like', '%' . $request->input('task_search') . '%');
        }

        if ($request->filled('task_status_id')) {
            $taskQuery->where('task_status_id', $request->input('task_status_id'));
        }

        if ($request->filled('task_priority_id')) {
            $taskQuery->where('priority_level_id', $request->input('task_priority_id'));
        }

        if ($request->filled('task_assignee')) {
            $taskQuery->whereHas('assignees', fn($q) => $q->where('user_id', $request->input('task_assignee')));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        if (in_array($sortBy, ['title', 'deadline', 'created_at'])) {
            $taskQuery->orderBy($sortBy, $sortDir);
        }

        $tasks = $taskQuery->paginate(10, ['*'], 'tasksPage')->appends($request->query());
        
        $loanRequests = PeminjamanRequest::where('project_id', $project->id)
                            ->with(['requester', 'requestedUser', 'approver', 'status'])
                            ->latest()
                            ->get();
        
        $projectMembers = $project->members->sortBy('name');

        $allTasks = $project->tasks()->with('status')->get();
        $taskStatuses = $allTasks->countBy(fn($task) => $task->status->key ?? 'unknown');
        $stats = [
            'total' => $allTasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];

        $statuses = \App\Models\TaskStatus::all();
        $priorities = \App\Models\PriorityLevel::all();

        return view('projects.show', compact('project', 'tasks', 'projectMembers', 'stats', 'loanRequests', 'statuses', 'priorities'));
    }


    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $project->load('owner', 'members', 'tasks.status');
        
        $currentMembers = $project->members;
        $referenceUser = $project->owner ?? auth()->user();

        $subordinateIds = collect($referenceUser->getAllSubordinateIds());
        $subordinateIds->push($referenceUser->id);
        
        $subordinates = User::whereIn('id', $subordinateIds)->get();
        $potentialMembers = $currentMembers->merge($subordinates)->unique('id')->sortBy('name');

        $loanRequests = PeminjamanRequest::where('project_id', $project->id)
            ->latest()
            ->get()
            ->keyBy('requested_user_id');
        
        $taskStatuses = $project->tasks->countBy(fn($task) => $task->status->key ?? 'unknown');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];
        
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
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate)->toArray();

        $labels = array_map(fn($date) => $date->format('d M'), $period);
        $dateStrings = array_map(fn($date) => $date->format('Y-m-d'), $period);

        $plannedDailyHours = array_fill_keys($dateStrings, 0);
        $tasks = $project->tasks()->whereNotNull('deadline')->where('estimated_hours', '>', 0)->get();
        $totalHours = $tasks->sum('estimated_hours');

        foreach ($tasks as $task) {
            $taskStart = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : $startDate;
            $taskEnd = \Carbon\Carbon::parse($task->deadline);
            if ($taskStart->gt($taskEnd)) {
                continue;
            }
            $taskDurationDays = $taskStart->diffInDays($taskEnd) + 1;

            if ($taskDurationDays > 0) {
                $hoursPerDay = $task->estimated_hours / $taskDurationDays;
                for ($date = $taskStart->copy(); $date->lte($taskEnd); $date->addDay()) {
                    $currentDateStr = $date->format('Y-m-d');
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
        $project->load(['members', 'tasks.assignees', 'tasks.timeLogs', 'tasks.status', 'tasks.priorityLevel']);
        $priorities = \App\Models\PriorityLevel::all();
        $statuses = \App\Models\TaskStatus::all();

        $teamSummary = $project->members->map(function ($member) use ($project, $priorities, $statuses) {
            $memberTasks = $project->tasks->filter(fn ($task) => $task->assignees->contains($member));
            if ($memberTasks->isEmpty()) return null;

            $totalEstimatedHours = $memberTasks->sum('estimated_hours');
            $weightedProgressSum = $memberTasks->reduce(fn ($carry, $task) => $carry + ($task->progress * $task->estimated_hours), 0);
            $weightedAverageProgress = ($totalEstimatedHours > 0) ? round($weightedProgressSum / $totalEstimatedHours) : round($memberTasks->avg('progress'));

            $priorityCounts = $priorities->mapWithKeys(fn($p) => [$p->key => 0])->merge($memberTasks->countBy(fn($task) => $task->priorityLevel->key ?? 'unknown'));
            $statusCounts = $statuses->mapWithKeys(fn($s) => [$s->key => 0])->merge($memberTasks->countBy(fn($task) => $task->status->key ?? 'unknown'));
            $completedStatusKey = $statuses->firstWhere('key', 'completed')->key;

            return [
                'member_id' => $member->id, 'member_name' => $member->name,
                'total_tasks' => $memberTasks->count(),
                'pending_tasks' => $statusCounts->get('pending', 0),
                'inprogress_tasks' => $statusCounts->get('in_progress', 0),
                'completed_tasks' => $statusCounts->get('completed', 0),
                'overdue_tasks' => $memberTasks->where('deadline', '<', now())->filter(fn($task) => $task->status->key !== $completedStatusKey)->count(),
                'total_estimated_hours' => round($totalEstimatedHours, 1),
                'total_logged_hours' => round($memberTasks->flatMap->timeLogs->sum('duration_in_minutes') / 60, 1),
                'weighted_average_progress' => $weightedAverageProgress,
                'priority_counts' => $priorityCounts,
            ];
        })->filter();

        return view('projects.team-dashboard', compact('project', 'teamSummary'));
    }

    public function getTeamMemberTasks(Project $project, User $user)
    {
        $this->authorize('view', $project);

        if (!$project->members->contains($user)) {
            return response()->json(['error' => 'User not found in this project.'], 404);
        }

        $tasks = $user->tasks()
            ->where('project_id', $project->id)
            ->with('subTasks')
            ->orderBy('deadline', 'asc')
            ->get();

        return response()->json($tasks);
    }

    public function downloadReport(Project $project)
    {
        if (!auth()->user()->isTopLevelManager()) {
            abort(403);
        }
        $project->load('leader', 'members', 'tasks.assignees', 'tasks.timeLogs', 'tasks.status');
        $taskStatuses = $project->tasks->countBy(fn($task) => $task->status->key ?? 'unknown');
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

        $tasks = $project->tasks()->with(['assignees', 'comments', 'subTasks', 'status'])->get();
        $statuses = \App\Models\TaskStatus::all();

        $groupedTasks = $statuses->mapWithKeys(fn($status) => [$status->key => collect()])
                                  ->merge($tasks->groupBy(fn($task) => $task->status->key ?? 'unknown'));

        return view('projects.kanban', compact('project', 'groupedTasks', 'statuses'));
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
            ->with('assignees', 'status', 'priorityLevel')
            ->get();

        $events = $tasks->map(function ($task) use ($project) {
            $color = '#3b82f6'; // Default blue
            if ($task->priorityLevel) {
                $color = match ($task->priorityLevel->key) {
                    'high' => '#ef4444', 'critical' => '#8b5cf6', 'medium' => '#f97316', 'low' => '#22c55e',
                    default => $color,
                };
            }
            if ($task->status && $task->status->key === 'completed') {
                $color = '#6b7280';
            }

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->start_date ? $task->start_date->format('Y-m-d') : $task->deadline->format('Y-m-d'),
                'end' => $task->deadline->addDay()->format('Y-m-d'),
                'url'   => route('projects.show', $project->id) . '#task-' . $task->id,
                'color' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'project_name' => $project->name,
                    'assignees' => $task->assignees->pluck('name')->join(', ') ?: 'Belum ditugaskan',
                    'status' => $task->status->label ?? 'N/A',
                    'priority' => $task->priorityLevel->label ?? 'N/A',
                ]
            ];
        });

        if ($project->start_date && $project->end_date) {
            $events->prepend([
                'id' => 'project_duration',
                'title' => 'Durasi Proyek',
                'start' => $project->start_date->format('Y-m-d'),
                'end' => $project->end_date->addDay()->format('Y-m-d'),
                'display' => 'background',
                'color' => '#eef2ff'
            ]);
        }

        return response()->json($events);
    }
}