<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectController extends Controller
{
    
    use AuthorizesRequests;

    /**
     * Menampilkan daftar proyek di dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'superadmin' || $user->role === 'manager') {
            return redirect()->route('global.dashboard');
        }

        // Logika untuk user biasa dan leader tetap sama
        $projects = Project::where('leader_id', $user->id)
                            ->orWhereHas('members', function ($query) use ($user) {
  
                                $query->where('user_id', $user->id);
                            })
                            ->with('leader')
                            ->distinct()
                            ->latest()
                            ->get();

        return view('dashboard', compact('projects'));
    }

    /**
     * Menampilkan form untuk membuat proyek baru.
     */
    public function create()
    {
        $users = User::orderBy('name')->get(); // Ambil semua user untuk pilihan leader & anggota
        return view('projects.create', compact('users'));
    }

    /**
     * Menyimpan proyek baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'leader_id' => 'required|exists:users,id',
            'members' => 'required|array',
            'members.*' => 'exists:users,id', // Pastikan semua member ada di tabel users
        ]);

        // 2. Buat Proyek
        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'leader_id' => $validated['leader_id'],
        ]);

        // 3. Gabungkan ID leader dengan ID member untuk memastikan leader juga terdaftar sebagai anggota
        $memberIds = collect($validated['members']);
        if (!$memberIds->contains($validated['leader_id'])) {
            $memberIds->push($validated['leader_id']);
        }
        
        // 4. Tambahkan Anggota ke tabel pivot
        $project->members()->sync($memberIds);

        // 5. Redirect ke halaman dashboard dengan pesan sukses
        return redirect()->route('dashboard')->with('success', 'Proyek "' . $project->name . '" berhasil dibuat!');
    }

    /**
     * Menampilkan detail proyek.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load('leader', 'members', 'tasks.timeLogs', 'tasks.assignedTo', 'tasks.comments.user', 'tasks.attachments', 'activities.user', 'tasks.subTasks');
        $projectMembers = $project->members()->orderBy('name')->get();

        
        $taskStatuses = $project->tasks->countBy('status');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];

        
        $tasksByUser = $project->tasks->groupBy('assigned_to_id');

        
        return view('projects.show', compact(
            'project',
            'projectMembers',
            'stats',
            'tasksByUser' 
        ));
    }

    public function teamDashboard(Project $project)
    {
        // Gunakan policy yang baru kita buat
        $this->authorize('viewTeamDashboard', $project);

        // Eager load semua data yang dibutuhkan untuk efisiensi
        $project->load(['members', 'tasks']);

        $teamSummary = collect();

        foreach ($project->members as $member) {
            // Filter tugas yang hanya milik anggota ini dalam proyek ini
            $memberTasks = $project->tasks->where('assigned_to_id', $member->id);

            if ($memberTasks->isEmpty()) {
                $averageProgress = 0;
            } else {
                $averageProgress = round($memberTasks->avg('progress'));
            }

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

        return view('projects.team-dashboard', [
            'project' => $project,
            'teamSummary' => $teamSummary
        ]);
    }
    public function downloadReport(Project $project)
    {
        // Otorisasi, hanya manager dan superadmin yang bisa download
        if (!in_array(auth()->user()->role, ['superadmin', 'manager'])) {
            abort(403);
        }

        // Muat semua relasi yang dibutuhkan untuk laporan
        $project->load('leader', 'members', 'tasks.assignedTo', 'tasks.timeLogs');

        // Kalkulasi statistik seperti di halaman show
        $taskStatuses = $project->tasks->countBy('status');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];

        // Kalkulasi total jam kerja tercatat
        $totalMinutesLogged = $project->tasks->flatMap->timeLogs->sum('duration_in_minutes');
        $totalHoursLogged = floor($totalMinutesLogged / 60);
        $remainingMinutes = $totalMinutesLogged % 60;

        // Siapkan semua data untuk dikirim ke view
        $data = [
            'project' => $project,
            'stats' => $stats,
            'totalLoggedTime' => "{$totalHoursLogged} jam {$remainingMinutes} menit",
        ];

        // Muat view Blade khusus untuk PDF dengan data di atas
        $pdf = Pdf::loadView('reports.project-summary', $data);

        // Beri nama file dan kirim sebagai download ke browser
        return $pdf->download('laporan-proyek-' . $project->name . '-' . now()->format('Y-m-d') . '.pdf');
    }

}