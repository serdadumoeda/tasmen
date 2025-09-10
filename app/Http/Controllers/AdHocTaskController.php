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

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->input('date_range'));
            if (count($dates) > 0) {
                $startDate = trim($dates[0]);
                $endDate = $dates[1] ?? $startDate;

                $query->whereDate('deadline', '>=', $startDate)
                      ->whereDate('deadline', '<=', $endDate);
            }
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

    public function printReport(Request $request)
    {
        $user = Auth::user();
        $query = Task::whereNull('project_id')->with(['assignees', 'status', 'priorityLevel', 'asalSurat']);

        // === Re-use the exact same query logic from the index method ===
        $subordinates = collect();
        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds->push($user->id);
            $subordinates = User::whereIn('id', $subordinateIds)->orderBy('name')->get(); // This is needed for the filter check

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

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->input('date_range'));
            if (count($dates) > 0) {
                $startDate = trim($dates[0]);
                $endDate = $dates[1] ?? $startDate;

                $query->whereDate('deadline', '>=', $startDate)
                      ->whereDate('deadline', '<=', $endDate);
            }
        }

        $sortBy = $request->input('sort_by', 'deadline');
        $sortDir = $request->input('sort_dir', 'asc');
        if (in_array($sortBy, ['title', 'deadline', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest(); // Default sort
        }
        // === End of re-used logic ===

        $tasks = $query->get(); // Get all results, no pagination

        return view('adhoc-tasks.print', [
            'tasks' => $tasks,
            'user' => $user,
            // Pass filter values to the view to be displayed in the report header
            'filters' => $request->only(['search', 'task_status_id', 'priority_level_id', 'personnel_id', 'sort_by']),
            'statuses' => \App\Models\TaskStatus::all()->keyBy('id'),
            'priorities' => \App\Models\PriorityLevel::all()->keyBy('id'),
            'personnel' => $subordinates->keyBy('id'),
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
            'is_outside_office_hours' => 'nullable|boolean',
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
        $task->is_outside_office_hours = $request->has('is_outside_office_hours');
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