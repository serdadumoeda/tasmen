<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();
        if ($user->isTopLevelManager()) {
            return redirect()->route('global.dashboard');
        }
        $projects = Project::with('owner', 'leader')->latest()->get();
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
            'leader_id' => ['required', 'exists:users,id', Rule::in($subordinateIds)],
            'members' => 'required|array',
            'members.*' => ['exists:users,id', Rule::in($subordinateIds)],
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => $user->id,
            'leader_id' => $request->leader_id,
        ]);

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

        $project->load('owner', 'leader', 'members', 'tasks.assignedTo', 'tasks.comments.user', 'tasks.attachments', 'activities.user', 'tasks.subTasks');
        $tasksByUser = $project->tasks->groupBy('assigned_to_id');
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

        // PERBAIKAN: Gunakan user yang login sebagai fallback jika owner tidak ada
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
        
        // PERBAIKAN: Gunakan user yang login sebagai fallback jika owner tidak ada
        $referenceUser = $project->owner ?? auth()->user();

        $subordinateIds = $referenceUser->getAllSubordinateIds();
        $subordinateIds[] = $referenceUser->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
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

    public function teamDashboard(Project $project)
    {
        $this->authorize('viewTeamDashboard', $project);
        $project->load(['members', 'tasks']);
        $teamSummary = collect();
        foreach ($project->members as $member) {
            $memberTasks = $project->tasks->where('assigned_to_id', $member->id);
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
        $project->load('leader', 'members', 'tasks.assignedTo', 'tasks.timeLogs');
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