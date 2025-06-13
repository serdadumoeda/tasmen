<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    // Memulai timer untuk sebuah tugas
    public function start(Task $task)
    {
        // Hentikan dulu timer lain yang mungkin sedang berjalan untuk user ini
        Auth::user()->timeLogs()->whereNull('end_time')->update([
            'end_time' => now(),
        ]);

        $timeLog = $task->timeLogs()->create([
            'user_id' => Auth::id(),
            'start_time' => now(),
        ]);

        return response()->json(['message' => 'Timer dimulai.', 'time_log' => $timeLog]);
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
        $runningLog->duration_in_minutes = $runningLog->start_time->diffInMinutes($runningLog->end_time);
        $runningLog->save();

        return response()->json(['message' => 'Timer dihentikan.', 'time_log' => $runningLog]);
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