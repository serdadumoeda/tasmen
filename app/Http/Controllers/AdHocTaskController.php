<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdHocTaskController extends Controller
{
    // ... method index() dan create() tidak berubah ...
    public function index()
    {
        $user = Auth::user();
        $assignedTasks = Task::whereNull('project_id')
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('assignees')
            ->latest()
            ->get();

        return view('adhoc-tasks.index', compact('assignedTasks'));
    }

    public function create()
    {
        $user = Auth::user();
        $assignableUsers = collect();

        if ($user->canManageUsers()) {
            $subordinateIds = $user->children()->pluck('id')->toArray();
            $subordinateIds[] = $user->id; 
            $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        }
        
        return view('adhoc-tasks.create', compact('assignableUsers'));
    }

    /**
     * Menyimpan tugas ad-hoc baru ke database.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // MODIFIKASI: Menambahkan validasi untuk field baru
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'estimated_hours' => 'required|numeric|min:0.1',
            'status' => 'required|in:pending,in_progress,completed',
            'progress' => 'required|integer|min:0|max:100',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120', // Max 5MB
        ]);
        
        $assigneeIds = [];

        if ($user->canManageUsers()) {
            $request->validate(['assignees' => 'required|array', 'assignees.*' => 'exists:users,id']);
            $assigneeIds = $request->assignees;
        } else {
            $assigneeIds[] = $user->id;
        }

        // Simpan data tugas utama terlebih dahulu untuk mendapatkan ID
        $task = new Task();
        $task->fill($validated); // Mengisi title, desc, deadline, dll dari hasil validasi
        $task->project_id = null;
        // Status dan progress sekarang diambil dari form, bukan di-hardcode
        $task->status = $request->status;
        $task->progress = $request->progress;
        $task->save();
        
        // BARU: Proses file lampiran SETELAH tugas disimpan
        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $path = $file->store('attachments', 'public');

            $task->attachments()->create([
                'user_id' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'path' => $path
            ]);
        }
        
        // Lampirkan penerima tugas
        $task->assignees()->sync($assigneeIds);

        // Kirim notifikasi
        $usersToNotify = User::find($assigneeIds);
        foreach ($usersToNotify as $recipient) {
            $recipient->notify(new TaskAssigned($task));
        }

        // Kembalikan ke halaman daftar setelah semua selesai
        return redirect()->route('adhoc-tasks.index')->with('success', 'Tugas harian berhasil ditambahkan!');
    }
}