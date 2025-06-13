<?php

namespace App\Http\Controllers;

// TAMBAHKAN BARIS INI
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    // DAN TAMBAHKAN BARIS INI
    use AuthorizesRequests;

    /**
     * Menampilkan daftar proyek di dashboard.
     */
    public function index()
    {

        if (Auth::user()->role === 'kepala_pusdatik') {
            return redirect()->route('global.dashboard');
        }

        $user = Auth::user();
        // Ambil proyek dimana user adalah anggota ATAU leader
        $projects = Project::where('leader_id', $user->id)
                            ->orWhereHas('members', function ($query) use ($user) {
                                $query->where('user_id', $user->id);
                            })
                            ->with('leader') // Eager load relasi leader
                            ->distinct() // Menghindari duplikat jika user adalah leader sekaligus member
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

        $project->load('leader', 'members', 'tasks', 'tasks.assignedTo', 'tasks.comments.user', 'tasks.attachments', 'activities.user');
        $projectMembers = $project->members()->orderBy('name')->get();

        // Kalkulasi statistik proyek
        $taskStatuses = $project->tasks->countBy('status');
        $stats = [
            'total' => $project->tasks->count(),
            'pending' => $taskStatuses->get('pending', 0),
            'in_progress' => $taskStatuses->get('in_progress', 0),
            'completed' => $taskStatuses->get('completed', 0),
        ];

        // BAGIAN PENTING: Pastikan baris ini ada
        $tasksByUser = $project->tasks->groupBy('assigned_to_id');

        // PASTIKAN SEMUA VARIABEL DIKIRIM KE VIEW
        return view('projects.show', compact(
            'project',
            'projectMembers',
            'stats',
            'tasksByUser' // Pastikan '$tasksByUser' ada di sini
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

}