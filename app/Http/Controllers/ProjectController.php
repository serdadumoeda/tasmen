<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
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
        // Logika ini sudah benar, jika top level manager, arahkan ke global dashboard.
        if ($user->isTopLevelManager()) {
            return redirect()->route('global.dashboard');
        }
        // Staff biasa akan melihat proyek di mana mereka menjadi anggota.
        $projects = $user->projects()->with('owner', 'leader')->latest()->get();
        return view('dashboard', compact('projects'));
    }

    public function create()
    {
        $user = Auth::user();
        $this->authorize('create', Project::class);

        $subordinateIds = $user->getAllSubordinateIds();
        $subordinateIds[] = $user->id;
        $potentialMembers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();

        return view('projects.create', ['potentialMembers' => $potentialMembers, 'project' => new Project()]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->authorize('create', Project::class);

        $subordinateIds = $user->getAllSubordinateIds();
        $subordinateIds[] = $user->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => ['required', 'exists:users,id', Rule::in($subordinateIds)],
            'members' => 'required|array',
            'members.*' => ['exists:users,id', Rule::in($subordinateIds)],
        ]);
        
        // MODIFIKASI DIMULAI DI SINI
        $dataToCreate = $validated;
        // INI ADALAH PERBAIKAN UTAMA:
        // Tetapkan 'owner_id' sebagai ID pengguna yang sedang membuat proyek.
        $dataToCreate['owner_id'] = $user->id;

        $project = Project::create($dataToCreate);
        // AKHIR MODIFIKASI

        $memberIds = collect($request->members);
        if (!$memberIds->contains($request->leader_id)) {
            $memberIds->push($request->leader_id);
        }
        
        $project->members()->sync($memberIds->unique());

        return redirect()->route('dashboard')->with('success', 'Proyek "' . $project->name . '" berhasil dibuat!');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load('owner', 'leader', 'members', 'tasks.assignees', 'tasks.comments.user', 'tasks.attachments', 'activities.user', 'tasks.subTasks');
        
        $tasksByUser = $project->tasks->groupBy(function($task) {
            return $task->assignees->first()->id ?? 0;
        });

        $projectMembers = $project->members()->orderBy('name')->get();
        $taskStatuses = $project->tasks->countBy('status');
        
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];

        return view('projects.show', compact('project', 'projectMembers', 'stats', 'tasksByUser'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $referenceUser = $project->owner ?? auth()->user();
        $subordinateIds = $referenceUser->getAllSubordinateIds();
        $subordinateIds[] = $referenceUser->id;
        $potentialMembers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        $taskStatuses = $project->tasks->countBy('status');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];
        return view('projects.edit', compact('project', 'potentialMembers', 'stats'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $referenceUser = $project->owner ?? auth()->user();
        $subordinateIds = $referenceUser->getAllSubordinateIds();
        $subordinateIds[] = $referenceUser->id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'leader_id' => ['required', 'exists:users,id', Rule::in($subordinateIds)],
            'members' => 'required|array',
            'members.*' => ['exists:users,id', Rule::in($subordinateIds)],
        ]);
        $project->update($validated);
        $memberIds = collect($request->members);
        if (!$memberIds->contains($request->leader_id)) {
            $memberIds->push($request->leader_id);
        }
        $project->members()->sync($memberIds->unique());
        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil diperbarui.');
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
}