<?php

use App\Http\Controllers\Api\V1\ProjectApiController;
use App\Http\Controllers\Api\V1\StatusController;
use App\Http\Controllers\Api\V1\TaskApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use Illuminate\Support\Facades\Route;

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
        Route::apiResource('/projects', ProjectApiController::class)->only(['index', 'show']);
    });

    // Group for endpoints requiring 'read:users' scope
    Route::middleware('auth.apikey:read:users')->group(function () {
        Route::apiResource('/users', UserApiController::class)->only(['index', 'show']);
    });

    // Group for endpoints requiring 'read:tasks' scope
    Route::middleware('auth.apikey:read:tasks')->group(function () {
        Route::apiResource('/tasks', TaskApiController::class)->only(['index', 'show']);
    });

    // Future endpoints for budgets, assignments, etc. would be added here in their own scoped groups.
});

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/calendar-events', function (Request $request) {
    $events = [];

    // 1. Ambil Cuti Pegawai yang Disetujui
    $leaveRequests = \App\Models\LeaveRequest::where('status', 'approved')->with('user')->get();
    foreach ($leaveRequests as $leave) {
        $events[] = [
            'title' => 'Cuti: ' . $leave->user->name,
            'start' => $leave->start_date,
            'end' => $leave->end_date->addDay(), // FullCalendar perlu +1 hari untuk rentang inklusif
            'backgroundColor' => '#1e90ff', // Biru untuk cuti pegawai
            'borderColor' => '#1e90ff'
        ];
    }

    // 2. Ambil Cuti Bersama
    $cutiBersama = \App\Models\CutiBersama::all();
    foreach ($cutiBersama as $cuti) {
        $events[] = [
            'title' => 'Cuti Bersama: ' . $cuti->keterangan,
            'start' => $cuti->tanggal,
            'allDay' => true,
            'backgroundColor' => '#ff6347', // Merah untuk cuti bersama
            'borderColor' => '#ff6347'
        ];
    }

    // 3. (Opsional) Ambil Hari Libur Nasional dari API Publik
    // Anda bisa menggunakan API seperti https://github.com/kav-made/holiday-api-indonesia
    try {
        $response = Http::get('https://api-harilibur.vercel.app/api');
        if ($response->successful()) {
            foreach ($response->json() as $holiday) {
                 if ($holiday['is_national_holiday']) {
                    $events[] = [
                        'title' => 'Libur: ' . $holiday['holiday_name'],
                        'start' => $holiday['holiday_date'],
                        'allDay' => true,
                        'backgroundColor' => '#228B22', // Hijau untuk libur nasional
                        'borderColor' => '#228B22'
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        // Abaikan jika API gagal, agar tidak merusak kalender
    }


    return response()->json($events);
});
