<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyWorkloadController extends Controller
{
    // Standar jam kerja per minggu
    const STANDARD_WEEKLY_HOURS = 37.5;

    public function index(Request $request)
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

        $search = $request->input('search');

        // Dapatkan query dasar untuk bawahan, tergantung pada peran manajer
        if ($manager->role === User::ROLE_SUPERADMIN || $manager->role === User::ROLE_MENTERI) {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            $subordinateUnitIds = $manager->unit ? $manager->unit->getAllSubordinateUnitIds() : [];
            $subordinatesQuery = User::whereIn('unit_id', $subordinateUnitIds)->where('id', '!=', $manager->id);
        }

        // Terapkan filter pencarian nama jika ada
        if ($search) {
            $subordinatesQuery->where('name', 'like', '%' . $search . '%');
        }

        // Eager load jumlah jam tugas untuk menghindari N+1 query problem
        // Hasilnya akan tersedia sebagai atribut 'total_assigned_hours' pada setiap model User.
        $subordinatesQuery->withSum(['tasks as total_assigned_hours' => function ($query) {
            $query->where('status', '!=', 'Selesai');
        }], 'estimated_hours');

        // Ambil data dengan paginasi
        $teamMembers = $subordinatesQuery->paginate(20)->withQueryString();

        return view('weekly_workload.index', [
            'teamMembers' => $teamMembers, // Kirim paginator ke view
            'standardHours' => self::STANDARD_WEEKLY_HOURS,
            'search' => $search

        ]);
    }
}