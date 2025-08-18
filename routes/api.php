<?php

use App\Http\Controllers\Api\V1\ProjectApiController;
use App\Http\Controllers\Api\V1\TaskApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| These routes are for external system-to-system integrations.
| They are protected by the `auth.apikey` middleware, which validates
| a bearer token against an active ApiClient.
|
*/

use App\Http\Controllers\Api\V1\StatusController;

Route::middleware(['auth.apikey', 'log.api'])->prefix('v1')->group(function () {
    // Health check / status endpoint
    Route::get('/status', StatusController::class);

    Route::apiResource('/projects', ProjectApiController::class)->only(['index', 'show']);
    Route::apiResource('/users', UserApiController::class)->only(['index', 'show']);
    Route::apiResource('/tasks', TaskApiController::class)->only(['index', 'show']);
});
