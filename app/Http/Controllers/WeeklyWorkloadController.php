<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyWorkloadController extends Controller
{
    // Standar jam kerja per minggu
    const STANDARD_WEEKLY_HOURS = 37.5;

    public function index()
    {
        $manager = Auth::user();
        // Ambil semua bawahan dari manajer yang sedang login, beserta tugas aktif mereka
        $teamMembers = $manager->getAllSubordinates()->load('tasks');

        $workloadData = $teamMembers->map(function ($member) {

            $totalWeeklyHours = $member->tasks->reduce(function ($carry, $task) {
                // Hanya hitung tugas yang aktif dan punya deadline di masa depan
                if ($task->status === 'completed' || !$task->deadline || $task->deadline->isPast()) {
                    return $carry;
                }

                // 1. Hitung sisa jam kerja untuk tugas ini
                $remainingHours = $task->estimated_hours * ((100 - $task->progress) / 100);
                if ($remainingHours <= 0) {
                    return $carry;
                }

                // 2. Hitung sisa hari kerja (hanya hari kerja, Senin-Jumat)
                $remainingWorkingDays = Carbon::now()->diffInWeekdays($task->deadline);
                // Tambahkan 1 hari jika hari ini adalah hari kerja, untuk menghindari pembagian dengan nol
                if ($remainingWorkingDays == 0 && Carbon::now()->isWeekday()) {
                    $remainingWorkingDays = 1;
                }

                if ($remainingWorkingDays <= 0) {
                    // Jika deadline sudah sangat dekat/lewat, bebankan semua sisa jam ke minggu ini
                    return $carry + $remainingHours;
                }

                // 3. Hitung beban harian dan mingguan untuk tugas ini
                $dailyWorkload = $remainingHours / $remainingWorkingDays;
                $weeklyWorkloadForTask = $dailyWorkload * 5; // Asumsi 5 hari kerja per minggu

                return $carry + $weeklyWorkloadForTask;
            }, 0);


            // Hitung persentase beban kerja
            $workloadPercentage = (self::STANDARD_WEEKLY_HOURS > 0)
                ? ($totalWeeklyHours / self::STANDARD_WEEKLY_HOURS) * 100
                : 0;

            return [
                'user' => $member,
                'assigned_hours' => round($totalWeeklyHours, 1),
                'workload_percentage' => round($workloadPercentage)
            ];
        });

        return view('weekly_workload.index', [
            'workloadData' => $workloadData->sortByDesc('workload_percentage'),
            'standardHours' => self::STANDARD_WEEKLY_HOURS
        ]);
    }
}