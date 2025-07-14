<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController; 
use App\Http\Controllers\GlobalDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkloadAnalysisController;
use App\Http\Controllers\TimeLogController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\BudgetItemController;
use App\Http\Controllers\SpecialAssignmentController;
use App\Http\Controllers\AdHocTaskController;
use App\Http\Controllers\ExecutiveSummaryController;
use App\Http\Controllers\ResourcePoolController;
use App\Http\Controllers\PeminjamanRequestController;
use App\Http\Controllers\WeeklyWorkloadController;
use App\Http\Controllers\BudgetRealizationController;





Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/get-users-by-unit/{eselon2_id}', [UserController::class, 'getUsersByUnit'])->name('users.by-unit');

Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');

    // Langkah 1: Menampilkan form inisiasi proyek
    Route::get('/projects/create-step-1', [ProjectController::class, 'createStep1'])->name('projects.create.step1');
    Route::post('/projects/store-step-1', [ProjectController::class, 'storeStep1'])->name('projects.store.step1');

    // Rute dengan parameter {project} dimulai setelahnya.
    // Langkah 2: Menampilkan form penugasan anggota & menyimpannya
    Route::get('/projects/{project}/create-step-2', [ProjectController::class, 'createStep2'])->name('projects.create.step2');
    Route::post('/projects/{project}/store-step-2', [ProjectController::class, 'storeStep2'])->name('projects.store.step2');
    
    // Rute detail dan fitur lain yang menggunakan {project}
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');

    // Route profile bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');

    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/projects/{project}/team', [ProjectController::class, 'teamDashboard'])->name('projects.team.dashboard');

    Route::get('/global-dashboard', [GlobalDashboardController::class, 'index'])->name('global.dashboard');

    Route::resource('users', UserController::class);
    Route::get('/api/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('api.users.search');

    Route::get('/workload-analysis', [WorkloadAnalysisController::class, 'index'])->name('workload.analysis');
    Route::patch('/workload-analysis/{user}/update-behavior', [WorkloadAnalysisController::class, 'updateBehavior'])->name('workload.updateBehavior');

    Route::prefix('tasks/{task}/time-log')->name('timelogs.')->group(function () {
        Route::post('/start', [TimeLogController::class, 'start'])->name('start');
        Route::post('/stop', [TimeLogController::class, 'stop'])->name('stop');
        Route::post('/manual', [TimeLogController::class, 'storeManual'])->name('storeManual');
    });

    Route::post('/tasks/{task}/subtasks', [SubTaskController::class, 'store'])->name('subtasks.store');
    Route::patch('/subtasks/{subTask}', [SubTaskController::class, 'update'])->name('subtasks.update');
    Route::delete('/subtasks/{subTask}', [SubTaskController::class, 'destroy'])->name('subtasks.destroy');

    Route::get('/projects/{project}/report', [ProjectController::class, 'downloadReport'])
    ->name('projects.report');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{project}/s-curve', [ProjectController::class, 'sCurve'])->name('projects.s-curve');

    Route::resource('projects.budget-items', BudgetItemController::class)
        ->except(['show'])
        ->parameters(['budget-items' => 'budgetItem']); 
    Route::resource('special-assignments', SpecialAssignmentController::class)->except(['show']);
    Route::controller(AdHocTaskController::class)->prefix('adhoc-tasks')->name('adhoc-tasks.')->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });
    Route::post('/tasks/{task}/approve', [\App\Http\Controllers\TaskController::class, 'approve'])->name('tasks.approve')->middleware('auth');
    Route::get('/projects/{project}/kanban', [\App\Http\Controllers\ProjectController::class, 'showKanban'])->name('projects.kanban')->middleware('auth');
    Route::post('/tasks/{task}/update-status', [\App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update-status')->middleware('auth');
    Route::get('/projects/{project}/calendar', [\App\Http\Controllers\ProjectController::class, 'showCalendar'])->name('projects.calendar')->middleware('auth');
    Route::get('/projects/{project}/tasks-json', [\App\Http\Controllers\ProjectController::class, 'tasksJson'])->name('projects.tasks-json')->middleware('auth');
    Route::get('/executive-summary', [ExecutiveSummaryController::class, 'index'])->name('executive.summary');
    Route::post('/subtasks/{subTask}/toggle', [TaskController::class, 'toggleSubTask'])->name('subtasks.toggle');

    // Rute untuk Manajemen Resource Pool
    Route::get('/resource-pool', [ResourcePoolController::class, 'index'])->name('resource-pool.index');
    Route::put('/resource-pool/update/{user}', [ResourcePoolController::class, 'update'])->name('resource-pool.update');

    // Rute API untuk mengambil anggota pool (digunakan oleh AJAX di halaman proyek)
    Route::get('/api/resource-pool/members', [ResourcePoolController::class, 'getAvailableMembers'])->name('api.resource-pool.members');
    Route::controller(PeminjamanRequestController::class)->middleware('auth')->group(function () {
        Route::get('/peminjaman-requests', 'index')->name('peminjaman-requests.index');
        Route::post('/peminjaman-requests', 'store')->name('peminjaman-requests.store');
        Route::post('/peminjaman-requests/{peminjamanRequest}/approve', 'approve')->name('peminjaman-requests.approve');
        Route::post('/peminjaman-requests/{peminjamanRequest}/reject', 'reject')->name('peminjaman-requests.reject');
    });
    Route::get('/api/users/{user}/workload', [App\Http\Controllers\UserController::class, 'getWorkloadSummary'])
    ->name('api.users.workload');
    Route::get('/weekly-workload', [App\Http\Controllers\WeeklyWorkloadController::class, 'index'])->name('weekly-workload.index');
    Route::resource('projects.budget-items', BudgetItemController::class);

    // Route untuk realisasi, di-nest di dalam budget-items
    Route::post('budget-items/{budgetItem}/realizations', [BudgetRealizationController::class, 'store'])
        ->name('budget-items.realizations.store');
    Route::delete('budget-realizations/{realization}', [BudgetRealizationController::class, 'destroy'])
        ->name('budget-realizations.destroy');
    
});

require __DIR__.'/auth.php';