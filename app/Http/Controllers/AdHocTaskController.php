<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\PageTitleService;
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
    public function index(Request $request, PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Tugas Harian');
        $breadcrumbService->add('Dashboard', route('dashboard'));
        $breadcrumbService->add('Tugas Harian');

        $user = Auth::user();
        $query = Task::whereNull('project_id')->with(['assignees', 'status', 'priorityLevel', 'asalSurat'])->latest();
        $subordinates = collect();

        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds->push($user->id);
            $subordinates = User::whereIn('id', $subordinateIds)->orderBy('name')->get();

            $query->whereHas('assignees', function ($q) use ($subordinateIds) {
                $q->whereIn('user_id', $subordinateIds);
            });
        } else {
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        if ($user->canManageUsers() && $request->filled('personnel_id')) {
            $personnelId = $request->personnel_id;
            if ($subordinates->pluck('id')->contains($personnelId)) {
                $query->whereHas('assignees', fn ($q) => $q->where('user_id', $personnelId));
            }
        }

        if ($request->filled('task_status_id')) {
            $query->where('task_status_id', $request->input('task_status_id'));
        }

        if ($request->filled('priority_level_id')) {
            $query->where('priority_level_id', $request->input('priority_level_id'));
        }

        $sortBy = $request->input('sort_by', 'deadline');
        $sortDir = $request->input('sort_dir', 'asc');
        if (in_array($sortBy, ['title', 'deadline', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir);
        }

        $assignedTasks = $query->paginate(10)->withQueryString();
        $statuses = \App\Models\TaskStatus::all();
        $priorityLevels = \App\Models\PriorityLevel::all();

        return view('adhoc-tasks.index', [
            'assignedTasks' => $assignedTasks,
            'subordinates' => $subordinates,
            'statuses' => $statuses,
            'priorityLevels' => $priorityLevels,
        ]);
    }

    /**
     * Menampilkan form untuk membuat tugas ad-hoc baru.
     */
    public function create(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $this->authorize('create', Task::class);
        $pageTitleService->setTitle('Buat Tugas Harian Baru');
        $breadcrumbService->add('Dashboard', route('dashboard'));
        $breadcrumbService->add('Tugas Harian', route('adhoc-tasks.index'));
        $breadcrumbService->add('Buat Baru');

        $user = Auth::user();
        $assignableUsers = collect();

        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds[] = $user->id;
            $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        } else {
            $assignableUsers->push($user);
        }
        
        $priorities = \App\Models\PriorityLevel::all();

        return view('adhoc-tasks.create', [
            'task' => new Task(),
            'assignableUsers' => $assignableUsers,
            'priorities' => $priorities,
        ]);
    }

    /**
     * Menyimpan tugas ad-hoc baru ke database.
     */
    public function printReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:all,completed,pending',
        ]);

        $user = Auth::user();
        $startDate = $validated['start_date'] ?? now()->startOfWeek();
        $endDate = $validated['end_date'] ?? now()->endOfWeek();
        $statusFilter = $validated['status'] ?? 'completed';

        $query = Task::whereNull('project_id')
            ->whereHas('assignees', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('updated_at', 'desc');

        if ($statusFilter === 'completed') {
            $completedStatus = \App\Models\TaskStatus::where('key', 'completed')->firstOrFail();
            $query->where('task_status_id', $completedStatus->id);
            $query->whereBetween('updated_at', [$startDate, $endDate]); // Completion date is updated_at
        } elseif ($statusFilter === 'pending') {
            $completedStatus = \App\Models\TaskStatus::where('key', 'completed')->firstOrFail();
            $query->where('task_status_id', '!=', $completedStatus->id);
            $query->whereBetween('deadline', [$startDate, $endDate]); // Filter by deadline for pending tasks
        } else { // 'all'
            $query->whereBetween('deadline', [$startDate, $endDate]);
        }

        $tasks = $query->get();

        return view('adhoc-tasks.print', [
            'tasks' => $tasks,
            'user' => $user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);
        $user = auth()->user();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'deadline' => 'required|date|after_or_equal:start_date',
            'estimated_hours' => 'required|numeric|min:0.1',
            'priority_level_id' => 'required|exists:priority_levels,id',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);
        
        $assigneeIds = [];
        if ($user->canManageUsers()) {
            $request->validate(['assignees' => 'required|array', 'assignees.*' => 'exists:users,id']);
            $assigneeIds = $request->assignees;
        } else {
            $assigneeIds[] = $user->id;
        }

        $defaultStatus = \App\Models\TaskStatus::where('key', 'pending')->firstOrFail();

        $task = new Task();
        $task->fill($validated);
        $task->task_status_id = $defaultStatus->id;
        $task->progress = 0;
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

    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Tugas Harian');
        $breadcrumbService->add('Tugas Harian', route('adhoc-tasks.index'));
        $breadcrumbService->add('Alur Kerja');
        return view('adhoc-tasks.workflow');
    }
}