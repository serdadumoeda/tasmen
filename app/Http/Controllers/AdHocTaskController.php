<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdHocTaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar tugas ad-hoc.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Task::whereNull('project_id')->with('assignees')->latest();
        $subordinates = collect();

        // PERBAIKAN: Logika otorisasi sesuai permintaan baru
        // Superadmin dan Menteri bisa melihat semua tugas harian
        if ($user->role === User::ROLE_SUPERADMIN || $user->role === User::ROLE_MENTERI) {
            // Tidak perlu filter query, ambil semua bawahan untuk dropdown filter
            $subordinates = User::where('id', '!=', $user->id)->orderBy('name')->get();
        }
        // Manajer lain (Eselon I, II, Koordinator) melihat tugas dalam hierarkinya
        elseif ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds->push($user->id); // Termasuk tugas diri sendiri
            $subordinates = User::whereIn('id', $subordinateIds)->orderBy('name')->get();

            $query->whereHas('assignees', function ($q) use ($subordinateIds) {
                $q->whereIn('user_id', $subordinateIds);
            });
        }
        // Staf hanya melihat tugasnya sendiri
        else {
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Terapkan filter pencarian jika ada
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        // Terapkan filter personel jika ada (hanya untuk manajer)
        if ($user->canManageUsers() && $request->filled('personnel_id')) {
            $personnelId = $request->personnel_id;
            // Pastikan manajer tidak memfilter ID di luar hirarkinya
            if ($subordinates->pluck('id')->contains($personnelId)) {
                $query->whereHas('assignees', function ($q) use ($personnelId) {
                    $q->where('user_id', $personnelId);
                });
            }
        }

        $assignedTasks = $query->paginate(10)->withQueryString();

        return view('adhoc-tasks.index', compact('assignedTasks', 'subordinates'));
    }

    /**
     * Menampilkan form untuk membuat tugas ad-hoc baru.
     */
    public function create()
    {
        $user = Auth::user();
        $assignableUsers = collect();

        // Jika manajer, bisa menugaskan ke diri sendiri dan bawahan
        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds[] = $user->id; 
            $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        } else {
            // Jika staf, list hanya berisi diri sendiri (meskipun tidak ditampilkan di form)
            $assignableUsers->push($user);
        }
        
        return view('adhoc-tasks.create', compact('assignableUsers'));
    }

    /**
     * Menyimpan tugas ad-hoc baru ke database.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'estimated_hours' => 'required|numeric|min:0.1',
            'priority' => ['nullable', \Illuminate\Validation\Rule::in(Task::PRIORITIES)],
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);
        
        $assigneeIds = [];
        if ($user->canManageUsers()) {
            $request->validate(['assignees' => 'required|array', 'assignees.*' => 'exists:users,id']);
            $assigneeIds = $request->assignees;
        } else {
            $assigneeIds[] = $user->id;
        }

        // Menggunakan fill untuk keamanan dan kemudahan, dan menambahkan default
        $task = new Task();
        $task->fill($validated);
        $task->status = 'pending';
        $task->progress = 0;
        $task->priority = $request->input('priority', 'medium'); // Set default priority
        $task->project_id = null; // Menandakan ini tugas ad-hoc
        $task->save();
        
        $redirect = redirect()->route('adhoc-tasks.index');

        if ($request->hasFile('file_upload')) {
            try {
                $file = $request->file('file_upload');
                $path = $file->store('attachments', 'public');
                $task->attachments()->create([
                    'user_id' => $user->id,
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path
                ]);
                $redirect->with('success', 'Tugas harian berhasil dibuat dan file berhasil diunggah.');
            } catch (\Exception $e) {
                $redirect->with('error', 'Tugas harian berhasil dibuat, tetapi file gagal diunggah: ' . $e->getMessage());
            }
        } else {
            $redirect->with('success', 'Tugas harian berhasil dibuat.');
        }
        
        $task->assignees()->sync($assigneeIds);
        $usersToNotify = User::find($assigneeIds);
        foreach ($usersToNotify as $recipient) {
            $recipient->notify(new \App\Notifications\TaskAssigned($task));
        }

       
        return $redirect;
    }

}