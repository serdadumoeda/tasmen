<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\GlobalDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkloadAnalysisController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\CompleteProfileController;
use App\Http\Controllers\BudgetItemController;
use App\Http\Controllers\SpecialAssignmentController;
use App\Http\Controllers\AdHocTaskController;
use App\Http\Controllers\ExecutiveSummaryController;
use App\Http\Controllers\ResourcePoolController;
use App\Http\Controllers\PeminjamanRequestController;
use App\Http\Controllers\WeeklyWorkloadController;
use App\Http\Controllers\BudgetRealizationController;
use App\Http\Controllers\NotificationController;





Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/get-users-by-unit/{unitId}', [UserController::class, 'getUsersByUnitFromId'])->name('users.by-unit');

use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {
    // Rute default setelah login adalah Beranda baru.
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    // Rute untuk daftar kegiatan (proyek)
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');


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

    Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'view'])->name('attachments.view');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/projects/{project}/team', [ProjectController::class, 'teamDashboard'])->name('projects.team.dashboard');
    Route::get('/projects/{project}/team/{user}/tasks', [ProjectController::class, 'getTeamMemberTasks'])->name('projects.team-member-tasks');

    Route::get('/global-dashboard', [GlobalDashboardController::class, 'index'])->name('global.dashboard');

    Route::get('users/hierarchy', [UserController::class, 'hierarchy'])->name('users.hierarchy');
    Route::get('users/modern', [UserController::class, 'modern'])->name('users.modern');

    // Custom routes for user management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/archived', [UserController::class, 'archived'])->name('users.archived');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    // Wildcard routes must be last
    Route::get('/users/{user}', [UserController::class, 'profile'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
    Route::delete('/users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');

    Route::get('/api/users/search', [App\Http\Controllers\UserController::class, 'search'])->name('api.users.search');

    Route::get('/workload-analysis', [WorkloadAnalysisController::class, 'index'])->name('workload.analysis');
    Route::get('/workload-analysis/{user}', [WorkloadAnalysisController::class, 'show'])->name('workload.analysis.show');
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
    Route::get('/projects/{project}/s-curve', [ProjectController::class, 'sCurve'])->name('projects.s-curve');

    Route::resource('projects.budget-items', BudgetItemController::class)
        ->except(['show'])
        ->parameters(['budget-items' => 'budgetItem']);

    Route::resource('special-assignments', SpecialAssignmentController::class)->except(['show']);

    // Rute untuk Ad-Hoc Tasks (Tugas Harian)
    Route::prefix('adhoc-tasks')->name('adhoc-tasks.')->group(function() {
        Route::get('/', [AdHocTaskController::class, 'index'])->name('index');
        Route::get('/create', [AdHocTaskController::class, 'create'])->name('create');
        Route::post('/', [AdHocTaskController::class, 'store'])->name('store');
        // Edit, Update, dan Destroy ditangani oleh TaskController yang sudah terkonsolidasi
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
    });

    Route::post('/tasks/{task}/approve', [\App\Http\Controllers\TaskController::class, 'approve'])->name('tasks.approve')->middleware('auth');
    Route::post('/tasks/{task}/quick-complete', [\App\Http\Controllers\TaskController::class, 'quickComplete'])->name('tasks.quick-complete')->middleware('auth');
    Route::get('/projects/{project}/kanban', [\App\Http\Controllers\ProjectController::class, 'showKanban'])->name('projects.kanban')->middleware('auth');
    Route::patch('/tasks/{task}/update-status', [\App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update-status')->middleware('auth');
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
       Route::get('/my-loan-requests', 'myRequests')->name('peminjaman-requests.my-requests');
       Route::get('/peminjaman-requests', 'index')->name('peminjaman-requests.index');
       Route::post('/peminjaman-requests', 'store')->name('peminjaman-requests.store');
       Route::post('/peminjaman-requests/{peminjamanRequest}/approve', 'approve')->name('peminjaman-requests.approve');
       Route::post('/peminjaman-requests/{peminjamanRequest}/reject', 'reject')->name('peminjaman-requests.reject');
       Route::delete('/peminjaman-requests/{peminjamanRequest}', 'destroy')->name('peminjaman-requests.destroy');
    });
    Route::get('/api/users/{user}/workload', [App\Http\Controllers\UserController::class, 'getWorkloadSummary'])
    ->name('api.users.workload');
    Route::get('/weekly-workload', [App\Http\Controllers\WeeklyWorkloadController::class, 'index'])->name('weekly-workload.index');

    // Route untuk realisasi, di-nest di dalam budget-items
    Route::post('budget-items/{budgetItem}/realizations', [BudgetRealizationController::class, 'store'])
        ->name('budget-items.realizations.store');
    Route::delete('budget-realizations/{realization}', [BudgetRealizationController::class, 'destroy'])
        ->name('budget-realizations.destroy');

    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    // Leave Management Module Routes
    Route::group(['prefix' => 'leaves', 'as' => 'leaves.'], function () {
        Route::get('/', [\App\Http\Controllers\LeaveController::class, 'index'])->name('index');
        Route::get('/calendar', [\App\Http\Controllers\LeaveController::class, 'calendar'])->name('calendar');
        Route::get('/create', [\App\Http\Controllers\LeaveController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\LeaveController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}', [\App\Http\Controllers\LeaveController::class, 'show'])->name('show');
        Route::get('/{leaveRequest}/attachment', [\App\Http\Controllers\LeaveController::class, 'downloadAttachment'])->name('attachment');
        Route::post('/{leaveRequest}/approve', [\App\Http\Controllers\LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leaveRequest}/reject', [\App\Http\Controllers\LeaveController::class, 'reject'])->name('reject');
    });

    // Routes for completing user profile
    Route::get('/profile/complete', [CompleteProfileController::class, 'create'])->name('profile.complete.create');
    Route::post('/profile/complete', [CompleteProfileController::class, 'store'])->name('profile.complete.store');

    // Routes for Letter Templates
    Route::resource('templatesurat', \App\Http\Controllers\TemplateSuratController::class)->except(['show']);

    // Routes for Outgoing Letters
    Route::prefix('surat-keluar')->name('surat-keluar.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuratKeluarController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SuratKeluarController::class, 'create'])->name('create');
        Route::get('/create/from-template', [\App\Http\Controllers\SuratKeluarController::class, 'createFromTemplate'])->name('create.from-template');
        Route::get('/create/upload', [\App\Http\Controllers\SuratKeluarController::class, 'createUpload'])->name('create.upload');
        Route::post('/', [\App\Http\Controllers\SuratKeluarController::class, 'store'])->name('store');
        Route::get('/{surat}', [\App\Http\Controllers\SuratKeluarController::class, 'show'])->name('show');
        Route::delete('/{surat}', [\App\Http\Controllers\SuratKeluarController::class, 'destroy'])->name('destroy');
        Route::post('/{surat}/approve', [\App\Http\Controllers\SuratKeluarController::class, 'approve'])->name('approve');
    });

    // Routes for Incoming Letters & Dispositions
    Route::resource('surat-masuk', \App\Http\Controllers\SuratMasukController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/surat-masuk/{surat}/disposisi', [\App\Http\Controllers\DisposisiController::class, 'store'])->name('disposisi.store');

    // Route for viewing attachments securely
    Route::get('/lampiran/{lampiranSurat}', [\App\Http\Controllers\LampiranController::class, 'show'])->name('lampiran.show');
});

use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\CutiBersamaController;
use App\Http\Controllers\Admin\ApprovalWorkflowController;
use App\Http\Controllers\Api\UnitApiController;
use App\Http\Controllers\UnitController;

require __DIR__.'/auth.php';

// Public route for letter verification
Route::get('/surat/verify/{id}', [\App\Http\Controllers\SuratVerificationController::class, 'verify'])->name('surat.verify');

// API routes for units, accessible without authentication
Route::get('/api/units/eselon-i', [UnitApiController::class, 'getEselonIUnits']);
Route::get('/api/units/{parentUnit}/children', [UnitApiController::class, 'getChildUnits']);

Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('units', UnitController::class);
    Route::post('units/{unit}/jabatans', [UnitController::class, 'storeJabatan'])->name('units.jabatans.store');
    Route::delete('jabatans/{jabatan}', [UnitController::class, 'destroyJabatan'])->name('jabatans.destroy');

    // User Import Routes
    Route::get('/users/import', [UserController::class, 'showImportForm'])->name('users.import.show');
    Route::post('/users/import', [UserController::class, 'handleImport'])->name('users.import.handle');

    // Impersonation Routes
    Route::get('/users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::get('/users/impersonate/leave', [UserController::class, 'leaveImpersonate'])->name('users.impersonate.leave');

    // Manual Leave Balance Management
    Route::get('/users/{user}/leave-balance/edit', [UserController::class, 'editLeaveBalance'])->name('users.leave-balance.edit');
    Route::put('/users/{user}/leave-balance', [UserController::class, 'updateLeaveBalance'])->name('users.leave-balance.update');

    // Activity Log Route
    Route::get('/activities', [\App\Http\Controllers\ActivityController::class, 'index'])->name('activities.index');

    // API Key Management
    Route::get('api_keys/docs', [ApiKeyController::class, 'showDocs'])->name('api_keys.docs');
    Route::get('api_keys/query-helper', [ApiKeyController::class, 'showQueryHelper'])->name('api_keys.query_helper');
    Route::resource('api_keys', ApiKeyController::class)->except(['show', 'edit'])->parameters(['api_keys' => 'client']);
    Route::post('api_keys/{client}/tokens', [ApiKeyController::class, 'generateToken'])->name('api_keys.tokens.store');
    Route::delete('api_keys/{client}/tokens/{tokenId}', [ApiKeyController::class, 'revokeToken'])->name('api_keys.tokens.destroy');
    Route::patch('api_keys/{client}/status', [ApiKeyController::class, 'update'])->name('api_keys.status.update');
});

Route::middleware(['auth', 'can.manage.leave.settings'])->prefix('admin')->name('admin.')->group(function () {
    // Cuti Bersama Management
    Route::resource('cuti-bersama', CutiBersamaController::class)->parameters(['cuti-bersama' => 'cutiBersama']);
    // Approval Workflow Management
    Route::resource('approval-workflows', ApprovalWorkflowController::class);
    Route::post('approval-workflows/{approvalWorkflow}/steps', [ApprovalWorkflowController::class, 'addStep'])->name('approval-workflows.steps.store');
    Route::delete('approval-workflows/{approvalWorkflow}/steps/{step}', [ApprovalWorkflowController::class, 'destroyStep'])->name('approval-workflows.steps.destroy');
});

Route::get('/api/units/{unit}/vacant-jabatans', [UnitController::class, 'getVacantJabatans'])->name('api.units.vacant-jabatans')->middleware('auth');
Route::get('/api/units/{unit}/users', [UserController::class, 'getUsersByUnitFromModel'])->name('api.units.users')->middleware('auth');