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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/get-users-by-unit/{eselon2_id}', [UserController::class, 'getUsersByUnit'])->name('users.by-unit');

Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');

    // Route untuk menampilkan, membuat, dan menyimpan proyek
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show'); 

    // Route profile bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');

    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/projects/{project}/team', [ProjectController::class, 'teamDashboard'])->name('projects.team.dashboard');

    Route::get('/global-dashboard', [GlobalDashboardController::class, 'index'])->name('global.dashboard');

    Route::resource('users', UserController::class);

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
});

require __DIR__.'/auth.php';