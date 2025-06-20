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

    /**
     * Menampilkan daftar proyek di dashboard.
     * Logika ini sekarang disederhanakan berkat HierarchicalScope.
     */
    public function index()
    {
        $user = Auth::user();

        // Pimpinan tinggi akan diarahkan ke dashboard global.
        if (in_array($user->role, ['superadmin', 'Eselon I', 'Eselon II'])) {
            return redirect()->route('global.dashboard');
        }

        // Untuk semua role lain (Koordinator, Staff, dll),
        // HierarchicalScope akan secara otomatis memfilter proyek yang bisa mereka lihat.
        $projects = Project::with('owner', 'leader')->latest()->get();

        return view('dashboard', compact('projects'));
    }

    /**
     * Menampilkan form untuk membuat proyek baru.
     * Logika ini sekarang hanya mengambil bawahan dari user yang login.
     */
    public function create()
    {
        $user = Auth::user();
        $subordinateIds = $user->getAllSubordinateIds();
        $potentialMembers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();

        return view('projects.create', compact('potentialMembers'));
    }

    /**
     * Menyimpan proyek baru ke database.
     * Owner proyek adalah user yang membuatnya.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $subordinateIds = $user->getAllSubordinateIds();
        $subordinateIds[] = $user->id; // User bisa menunjuk dirinya sendiri sebagai pimpinan

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'leader_id' => ['required', 'exists:users,id', Rule::in($subordinateIds)],
            'members' => 'required|array',
            'members.*' => ['exists:users,id', Rule::in($subordinateIds)],
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => $user->id, // Pembuat proyek adalah OWNER
            'leader_id' => $request->leader_id, // Pimpinan proyek adalah yang dipilih
        ]);

        $memberIds = collect($request->members);
        if (!$memberIds->contains($request->leader_id)) {
            $memberIds->push($request->leader_id);
        }
        
        $project->members()->sync($memberIds->unique());

        return redirect()->route('dashboard')->with('success', 'Proyek "' . $project->name . '" berhasil dibuat!');
    }

    /**
     * Menampilkan detail proyek.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project); // Policy 'view' tetap berlaku

        // ... sisa method show tidak perlu diubah signifikan ...
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

    // ... sisa method lain seperti teamDashboard dan downloadReport tidak perlu diubah ...
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
        if (!in_array(auth()->user()->role, ['superadmin', 'Eselon I', 'Eselon II'])) {
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