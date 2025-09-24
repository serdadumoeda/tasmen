<?php

use App\Http\Controllers\Api\V1\ProjectApiController;
use App\Http\Controllers\Api\V1\StatusController;
use App\Http\Controllers\Api\V1\TaskApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use App\Http\Controllers\Api\UnitApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes (for internal UI)
|--------------------------------------------------------------------------
|
| These routes are stateless and used by the frontend UI components.
| They do not fall under the versioned API for external consumers.
|
*/
Route::get('/units/eselon-i', [UnitApiController::class, 'getEselonIUnits']);
Route::get('/units/{parentUnitId}/children', [UnitApiController::class, 'getChildUnits']);


/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('log.api')->group(function () {

    // This endpoint only requires a valid key, no specific scopes.
    Route::middleware('auth.apikey')->get('/status', StatusController::class);

    // Group for endpoints requiring 'read:projects' scope
    Route::middleware('auth.apikey:read:projects')->group(function () {
        Route::apiResource('projects', ProjectApiController::class)
            ->only(['index', 'show'])
            ->names([
                'index' => 'api.projects.index',
                'show' => 'api.projects.show',
            ]);
    });

    // Group for endpoints requiring 'read:users' scope
    Route::middleware('auth.apikey:read:users')->group(function () {
        Route::apiResource('users', UserApiController::class)
            ->only(['index', 'show'])
            ->names([
                'index' => 'api.users.index',
                'show' => 'api.users.show',
            ]);
    });

    // Group for endpoints requiring 'read:tasks' scope
    Route::middleware('auth.apikey:read:tasks')->group(function () {
        Route::apiResource('tasks', TaskApiController::class)
            ->only(['index', 'show'])
            ->names([
                'index' => 'api.tasks.index',
                'show' => 'api.tasks.show',
            ]);
    });

    // Future endpoints for budgets, assignments, etc. would be added here in their own scoped groups.
});
