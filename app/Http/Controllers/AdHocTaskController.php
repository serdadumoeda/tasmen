<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\PriorityLevel;
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
        $query = Task::whereNull('project_id')->with('assignees', 'status', 'priorityLevel')->latest();
        $subordinates = collect();

        $userRoleName = $user->role->name ?? '';
        if ($userRoleName === 'superadmin' || $userRoleName === 'menteri') {
            $subordinates = User::whereHas('role', fn($q) => $q->where('name', '!=', 'superadmin'))->orderBy('name')->get();
        }
        elseif ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds()->push($user->id);
            $subordinates = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
            $query->whereHas('assignees', fn ($q) => $q->whereIn('user_id', $subordinateIds));
        }
        else {
            $query->whereHas('assignees', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]));
        }

        if ($user->canManageUsers() && $request->filled('personnel_id')) {
            $personnelId = $request->personnel_id;
            if ($subordinates->pluck('id')->contains($personnelId)) {
                $query->whereHas('assignees', fn ($q) => $q->where('user_id', $personnelId));
            }
        }

        if ($request->filled('status_id')) {
            $query->where('task_status_id', $request->input('status_id'));
        }

        if ($request->filled('priority_id')) {
            $query->where('priority_level_id', $request->input('priority_id'));
        }

        $sortBy = $request->input('sort_by', 'deadline');
        $sortDir = $request->input('sort_dir', 'asc');
        if (in_array($sortBy, ['title', 'deadline', 'created_at'])) {
             $query->orderBy($sortBy, $sortDir);
        }

        $assignedTasks = $query->paginate(10)->withQueryString();
        $statuses = TaskStatus::all();
        $priorities = PriorityLevel::all();

        return view('adhoc-tasks.index', compact('assignedTasks', 'subordinates', 'statuses', 'priorities'));
    }

    /**
     * Menampilkan form untuk membuat tugas ad-hoc baru.
     */
    public function create()
    {
        $user = Auth::user();
        $assignableUsers = collect();

        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds()->push($user->id);
            $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        } else {
            $assignableUsers->push($user);
        }
        
        return view('adhoc-tasks.create', [
            'task' => new Task(),
            'assignableUsers' => $assignableUsers,
            'priorities' => PriorityLevel::all(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'required|date|after_or_equal:start_date',
            'estimated_hours' => 'required|numeric|min:0.1',
            'priority_level_id' => 'nullable|exists:priority_levels,id',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);
        
        $assigneeIds = [];
        if ($user->canManageUsers()) {
            $request->validate(['assignees' => 'required|array', 'assignees.*' => 'exists:users,id']);
            $assigneeIds = $request->assignees;
        } else {
            $assigneeIds[] = $user->id;
        }

        $defaultStatus = TaskStatus::where('key', 'pending')->first();
        $defaultPriority = PriorityLevel::where('key', 'medium')->first();

        $task = new Task();
        $task->fill($validated);
        $task->task_status_id = $defaultStatus->id;
        $task->progress = 0;
        $task->priority_level_id = $request->input('priority_level_id', $defaultPriority->id);
        $task->project_id = null;
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
            $recipient->notify(new TaskAssigned($task));
        }
       
        return $redirect;
    }

}