<?php

namespace App\Http\Controllers;

use App\Models\Project;
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

    
    public function index()
    {
        $user = Auth::user();
        if ($user->isTopLevelManager()) {
            return redirect()->route('global.dashboard');
        }
        $projects = $user->projects()->with('owner', 'leader')->latest()->get();
        return view('dashboard', compact('projects'));
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

        $memberIds = collect($request->members);
        if (!$memberIds->contains($request->leader_id)) {
            $memberIds->push($request->leader_id);
        }
        $project->members()->sync($memberIds->unique());

        return redirect()->route('projects.show', $project)->with('success', 'Tim proyek berhasil dibentuk!');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load('owner', 'leader', 'members', 'tasks.assignees', 'tasks.comments.user', 'tasks.attachments', 'activities.user', 'tasks.subTasks');
        
        // --- AWAL PERBAIKAN ---
        // Ambil data riwayat permintaan peminjaman yang terkait dengan proyek ini
        $loanRequests = PeminjamanRequest::where('project_id', $project->id)
                            ->with(['requester', 'requestedUser', 'approver'])
                            ->latest()
                            ->get();
        // --- AKHIR PERBAIKAN ---

        $tasksByUser = collect();
        foreach ($project->tasks as $task) {
            foreach ($task->assignees as $assignee) {
                if (!$tasksByUser->has($assignee->id)) {
                    $tasksByUser->put($assignee->id, collect());
                }
                $tasksByUser->get($assignee->id)->push($task);
            }
        }

        $projectMembers = $project->members()->orderBy('name')->get();
        $taskStatuses = $project->tasks->countBy('status');
        
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];

        // Kirim variabel $loanRequests ke view
        return view('projects.show', compact('project', 'projectMembers', 'stats', 'tasksByUser', 'loanRequests'));
    }


    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        
        // --- AWAL PERBAIKAN ---
        $currentMembers = $project->members;
        $referenceUser = $project->owner ?? auth()->user();

        // Ambil ID bawahan sebagai Collection untuk kemudahan pengecekan di view
        $subordinateIds = collect($referenceUser->getAllSubordinateIds());
        $subordinateIds->push($referenceUser->id);
        
        $subordinates = User::whereIn('id', $subordinateIds)->get();
        $potentialMembers = $currentMembers->merge($subordinates)->unique('id')->sortBy('name');

        // 2. Ambil semua permintaan peminjaman untuk proyek ini.
        // `keyBy` digunakan agar kita bisa mencari status dengan mudah berdasarkan ID user di view.
        $loanRequests = PeminjamanRequest::where('project_id', $project->id)
            ->latest() // Ambil yang terbaru jika ada permintaan ganda
            ->get()
            ->keyBy('requested_user_id');
        
        // --- AKHIR PERBAIKAN ---

        $taskStatuses = $project->tasks->countBy('status');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];
        
        // 3. Kirim data baru ($loanRequests dan $subordinateIds) ke view
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => ['required', 'exists:users,id', Rule::in($validMemberIds)],
            'members' => 'required|array',
            'members.*' => ['exists:users,id', Rule::in($validMemberIds)],
        ]);

        $project->update($validated);
        
        $memberIds = collect($request->members);
        if (!$memberIds->contains($request->leader_id)) {
            $memberIds->push($request->leader_id);
        }
        $project->members()->sync($memberIds->unique());
        
        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil diperbarui.');
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
        $startDate = \Carbon\Carbon::parse($project->start_date);
        $endDate = \Carbon\Carbon::parse($project->end_date);
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        $labels = [];
        foreach ($period as $date) {
            $labels[] = $date->format('d M');
        }
        $totalHours = $project->tasks()->sum('estimated_hours');
        $projectDurationDays = $startDate->diffInDays($endDate) + 1;
        $plannedHoursPerDay = ($projectDurationDays > 0) ? $totalHours / $projectDurationDays : 0;
        $plannedCumulative = [];
        $cumulative = 0;
        for ($i = 0; $i < count($labels); $i++) {
            $cumulative += $plannedHoursPerDay;
            $plannedCumulative[] = round($cumulative, 2);
        }
        $timeLogs = DB::table('time_logs')
            ->join('tasks', 'time_logs.task_id', '=', 'tasks.id')
            ->where('tasks.project_id', $project->id)
            ->whereNotNull('end_time')
            ->select(DB::raw('DATE(time_logs.end_time) as date'), DB::raw('SUM(time_logs.duration_in_minutes) as total_minutes'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');
        $actualCumulative = [];
        $cumulative = 0;
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            if (isset($timeLogs[$dateString])) {
                $cumulative += $timeLogs[$dateString]->total_minutes / 60;
            }
            $actualCumulative[] = round($cumulative, 2);
        }
        $chartData = [
            'labels' => $labels,
            'planned' => $plannedCumulative,
            'actual' => $actualCumulative,
            'total_hours' => round($totalHours, 2)
        ];
        return view('projects.s-curve', compact('project', 'chartData'));
    }

    public function teamDashboard(Project $project)
    {
        $this->authorize('viewTeamDashboard', $project);
        $project->load(['members', 'tasks.assignees']);
        $teamSummary = collect();
        foreach ($project->members as $member) {
            $memberTasks = $project->tasks->filter(function ($task) use ($member) {
                return $task->assignees->contains($member);
            });
            $averageProgress = $memberTasks->isEmpty() ? 0 : round($memberTasks->avg('progress'));
            $teamSummary->push([
                'member_id' => $member->id,
                'member_name' => $member->name,
                'total_tasks' => $memberTasks->count(),
                'pending_tasks' => $memberTasks->where('status', 'pending')->count(),
                'inprogress_tasks' => $memberTasks->where('status', 'in_progress')->count(),
                'completed_tasks' => $memberTasks->where('status', 'completed')->count(),
                'overdue_tasks' => $memberTasks->where('deadline', '<', now())->where('status', '!=', 'completed')->count(),
                'average_progress' => $averageProgress
            ]);
        }
        return view('projects.team-dashboard', compact('project', 'teamSummary'));
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
            ->get(['id', 'title', 'deadline', 'status', 'project_id']);

        $events = $tasks->map(function ($task) use ($project) {
            $color = '#3b82f6'; 
            switch ($task->status) {
                case 'pending': $color = '#facc15'; break;
                case 'completed': $color = '#22c55e'; break;
                case 'for_review': $color = '#f97316'; break;
            }
            if ($task->deadline < now() && $task->status !== 'completed') {
                $color = '#ef4444';
            }

            $assigneeNames = $task->assignees->pluck('name')->join(', ');

            return [
                'title' => $task->title,
                'start' => $task->deadline,
                'url'   => route('projects.show', $project->id) . '#task-' . $task->id,
                'color' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'project_name' => $project->name,
                    'assignees' => $assigneeNames ?: 'Belum ditugaskan',
                    'status' => ucfirst(str_replace('_', ' ', $task->status))
                ]
            ];
        });

        return response()->json($events);
    }
}