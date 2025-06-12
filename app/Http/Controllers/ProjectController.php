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
        // Pastikan user yang login adalah bagian dari proyek
        $this->authorize('view', $project);

        $project->load('leader', 'members', 'tasks', 'tasks.assignedTo');
        // Ambil anggota proyek untuk dropdown 'assign task'
        $projectMembers = $project->members()->orderBy('name')->get();
        return view('projects.show', compact('project', 'projectMembers'));
    }
}