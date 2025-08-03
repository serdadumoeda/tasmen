<?php

use App\Http\Controllers\AdHocTaskController;
use App\Http\Controllers\Api\UnitApiController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\BudgetItemController;
use App\Http\Controllers\BudgetRealizationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExecutiveSummaryController;
use App\Http\Controllers\GlobalDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeminjamanRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResourcePoolController;
use App\Http\Controllers\SpecialAssignmentController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeeklyWorkloadController;
use App\Http\Controllers\WorkloadAnalysisController;
use App\Http\Controllers\HomeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Publicly accessible routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/get-users-by-unit/{eselon2_id}', [UserController::class, 'getUsersByUnit'])->name('users.by-unit');

// API routes for units, accessible without authentication
Route::get('/api/units/eselon-i', [UnitApiController::class, 'getEselonIUnits']);
Route::get('/api/units/{parentUnit}/children', [UnitApiController::class, 'getChildUnits']);
Route::get('/api/users/search', [UserController::class, 'search'])->name('api.users.search');
Route::get('/api/resource-pool/members', [ResourcePoolController::class, 'getAvailableMembers'])->name('api.resource-pool.members');
Route::get('/api/users/{user}/workload', [UserController::class, 'getWorkloadSummary'])->name('api.users.workload');


Route::middleware(['auth', 'verified'])->group(function () {

    // Rute default setelah login sekarang adalah Beranda baru yang komprehensif.
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Rute untuk daftar kegiatan (proyek) diganti namanya agar lebih jelas
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

    // Rute 'home' tidak lagi diperlukan karena /dashboard adalah beranda utama
    // Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/global-dashboard', [GlobalDashboardController::class, 'index'])->name('global.dashboard');
    Route::get('/executive-summary', [ExecutiveSummaryController::class, 'index'])->name('executive.summary');

    // Profile Routes
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // Project Routes
    Route::get('/projects/create-step-1', [ProjectController::class, 'createStep1'])->name('projects.create.step1');
    Route::post('/projects/store-step-1', [ProjectController::class, 'storeStep1'])->name('projects.store.step1');
    Route::get('/projects/{project}/create-step-2', [ProjectController::class, 'createStep2'])->name('projects.create.step2');
    Route::post('/projects/{project}/store-step-2', [ProjectController::class, 'storeStep2'])->name('projects.store.step2');
    Route::get('/projects/{project}/team', [ProjectController::class, 'teamDashboard'])->name('projects.team.dashboard');
    Route::get('/projects/{project}/report', [ProjectController::class, 'downloadReport'])->name('projects.report');
    Route::get('/projects/{project}/s-curve', [ProjectController::class, 'sCurve'])->name('projects.s-curve');
    Route::get('/projects/{project}/kanban', [ProjectController::class, 'showKanban'])->name('projects.kanban');
    Route::get('/projects/{project}/calendar', [ProjectController::class, 'showCalendar'])->name('projects.calendar');
    Route::get('/projects/{project}/tasks-json', [ProjectController::class, 'tasksJson'])->name('projects.tasks-json');

    // Resource controller harus didefinisikan setelah rute custom agar tidak tumpang tindih.
    // Rute 'index' sudah didefinisikan di atas, jadi kita kecualikan di sini.
    Route::resource('projects', ProjectController::class)->except(['index', 'create']);

    // Task & SubTask Routes
    Route::resource('tasks', TaskController::class)->except(['index', 'create', 'new', 'show']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::post('/tasks/{task}/approve', [TaskController::class, 'approve'])->name('tasks.approve');
    Route::patch('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/subtasks/{subTask}/toggle', [TaskController::class, 'toggleSubTask'])->name('subtasks.toggle');
    Route::resource('subtasks', SubTaskController::class)->only(['store', 'update', 'destroy']);
    Route::post('/tasks/{task}/subtasks', [SubTaskController::class, 'store'])->name('subtasks.store'); // Alias for resource route

    // Time Log Routes
    Route::prefix('tasks/{task}/time-log')->name('timelogs.')->controller(TimeLogController::class)->group(function () {
        Route::post('/start', 'start')->name('start');
        Route::post('/stop', 'stop')->name('stop');
        Route::post('/manual', 'storeManual')->name('storeManual');
    });

    // Ad-Hoc Task Routes (Corrected to use its own controller)
    Route::resource('adhoc-tasks', AdHocTaskController::class)
        ->parameters(['adhoc-tasks' => 'task']) // Uses {task} as the parameter name
        ->except(['show']);

    // Special Assignment Routes
    Route::resource('special-assignments', SpecialAssignmentController::class)->except(['show']);

    // Budget & Realization Routes
    Route::resource('projects.budget-items', BudgetItemController::class)
        ->except(['show'])
        ->parameters(['budget-items' => 'budgetItem']);
    Route::post('budget-items/{budgetItem}/realizations', [BudgetRealizationController::class, 'store'])->name('budget-items.realizations.store');
    Route::delete('budget-realizations/{realization}', [BudgetRealizationController::class, 'destroy'])->name('budget-realizations.destroy');

    // User Management & Workload Routes
    Route::get('users/hierarchy', [UserController::class, 'hierarchy'])->name('users.hierarchy');
    Route::get('users/modern', [UserController::class, 'modern'])->name('users.modern');
    Route::resource('users', UserController::class);
    Route::get('/workload-analysis', [WorkloadAnalysisController::class, 'index'])->name('workload.analysis');
    Route::patch('/workload-analysis/{user}/update-behavior', [WorkloadAnalysisController::class, 'updateBehavior'])->name('workload.updateBehavior');
    Route::get('/weekly-workload', [WeeklyWorkloadController::class, 'index'])->name('weekly-workload.index');

    // Resource Pool & Loan Request Routes
    Route::get('/resource-pool', [ResourcePoolController::class, 'index'])->name('resource-pool.index');
    Route::put('/resource-pool/update/{user}', [ResourcePoolController::class, 'update'])->name('resource-pool.update');
    Route::controller(PeminjamanRequestController::class)->prefix('peminjaman-requests')->name('peminjaman-requests.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/my', 'myRequests')->name('my-requests');
        Route::post('/', 'store')->name('store');
        Route::post('/{peminjamanRequest}/approve', 'approve')->name('approve');
        Route::post('/{peminjamanRequest}/reject', 'reject')->name('reject');
        Route::delete('/{peminjamanRequest}', 'destroy')->name('destroy');
    });

    // Notification Routes
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
});

// Admin-only Routes
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('units', UnitController::class);
});

require __DIR__.'/auth.php';