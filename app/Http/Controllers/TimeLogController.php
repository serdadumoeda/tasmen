<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimeLogController extends Controller
{
    // Memulai timer untuk sebuah tugas
    public function start(Task $task)
    {
        DB::transaction(function () use ($task) {
            // Hentikan dulu timer lain yang mungkin sedang berjalan untuk user ini
            // Kunci baris untuk mencegah race condition
            $runningLog = Auth::user()->timeLogs()->whereNull('end_time')->lockForUpdate()->first();

            if ($runningLog) {
                $runningLog->end_time = now();
                $diffInSeconds = $runningLog->start_time->diffInSeconds($runningLog->end_time);
                $runningLog->duration_in_minutes = round($diffInSeconds / 60);
                $runningLog->save();
            }

            // Buat log waktu baru untuk tugas yang sekarang dimulai
            $task->timeLogs()->create([
                'user_id' => Auth::id(),
                'start_time' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Timer dimulai.',
            'running_task_id' => $task->id
        ]);
    }

    // Menghentikan timer yang sedang berjalan
    public function stop(Task $task)
    {
        $runningLog = Auth::user()->timeLogs()
            ->where('task_id', $task->id)
            ->whereNull('end_time')
            ->first();

        if (!$runningLog) {
            return response()->json(['message' => 'Tidak ada timer yang berjalan untuk tugas ini.'], 422);
        }

        $runningLog->end_time = now();
        $diffInSeconds = $runningLog->start_time->diffInSeconds($runningLog->end_time);
        $runningLog->duration_in_minutes = round($diffInSeconds / 60);
        $runningLog->save();
        
        // Hitung ulang total waktu tercatat untuk tugas ini
        $totalMinutes = $task->timeLogs()->sum('duration_in_minutes');
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return response()->json([
            'message' => 'Timer dihentikan.',
            'time_log_summary' => [
                'estimated' => (float)$task->estimated_hours ?? 0,
                'logged' => "{$hours} jam {$minutes} menit"
            ]
        ]);
    }

    public function storeManual(Request $request, Task $task)
    {
        $validated = $request->validate([
            'duration_in_minutes' => 'required|integer|min:1',
            'log_date' => 'required|date',
        ]);

        $logTime = Carbon::parse($validated['log_date']);
        
        $durationInMinutes = (int) $validated['duration_in_minutes'];

        $timeLog = $task->timeLogs()->create([
            'user_id' => Auth::id(),
            'start_time' => $logTime,
            'end_time' => $logTime->copy()->addMinutes($durationInMinutes), 
            'duration_in_minutes' => $durationInMinutes,
        ]);

        return back()->with('success', 'Catatan waktu berhasil ditambahkan.');
    }
}