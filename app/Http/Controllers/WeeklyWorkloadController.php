<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyWorkloadController extends Controller
{
    public function index(Request $request)
    {
        // 1. Dapatkan pengguna yang sedang login & input pencarian
        $manager = Auth::user();
        $search = $request->input('search');
    
        // 2. Otorisasi: Hanya manajer yang diizinkan yang bisa mengakses
        if (!$manager || !$manager->canManageUsers()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    
        // 3. Dapatkan query dasar untuk mengambil data bawahan
        // Ini menangani kasus Superadmin dan Manajer biasa
        if ($manager->role === User::ROLE_SUPERADMIN) {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            // Gunakan scope yang sudah distandarisasi di model User
            $subordinatesQuery = User::teamMembers($manager);
        }
    
        // 4. Terapkan filter pencarian nama jika ada
        if ($search) {
            $subordinatesQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }
        
        // 5. Eager load tugas untuk setiap anggota tim
        // Ini akan digunakan untuk menghitung beban kerja
        $subordinatesQuery->with(['tasks' => function ($query) {
            // Get all unfinished tasks, including overdue ones.
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('deadline');
        }]);
    
        // 6. Ambil data dengan paginasi
        $teamMembers = $subordinatesQuery->paginate(20)->withQueryString();
    
        // 7. Hitung beban kerja untuk setiap anggota tim yang ditampilkan
        $standardHours = 37.5; // Standard weekly hours
        $workloadData = $teamMembers->map(function ($member) use ($standardHours) {
            $today = Carbon::now();
            $startOfWeek = $today->copy()->startOfWeek();
            $endOfWeek = $today->copy()->endOfWeek();

            // Calculate effective hours for the current week, considering leave.
            $effectiveWeeklyHours = $member->getEffectiveWorkingHours($startOfWeek, $endOfWeek);

            $totalWeeklyHours = $member->tasks->reduce(function ($carry, $task) {
                $remainingHours = $task->estimated_hours * ((100 - $task->progress) / 100);
                if ($remainingHours <= 0) {
                    return $carry;
                }
    
                $remainingWorkingDays = Carbon::now()->diffInWeekdays($task->deadline);
                if ($remainingWorkingDays == 0 && Carbon::now()->isWeekday()) {
                    $remainingWorkingDays = 1;
                }
    
                if ($remainingWorkingDays <= 0) {
                    return $carry + $remainingHours;
                }
    
                $dailyWorkload = $remainingHours / $remainingWorkingDays;
                $weeklyWorkloadForTask = $dailyWorkload * 5; 
    
                return $carry + $weeklyWorkloadForTask;
            }, 0);
    
            $workloadPercentage = ($effectiveWeeklyHours > 0)
                ? ($totalWeeklyHours / $effectiveWeeklyHours) * 100
                : ($totalWeeklyHours > 0 ? 200 : 0); // Handle case where user is on leave but has workload
    
            // Mengembalikan data dalam format yang dibutuhkan oleh view
            return [
                'user' => $member,
                'assigned_hours' => round($totalWeeklyHours, 1),
                'effective_hours' => $effectiveWeeklyHours,
                'workload_percentage' => round($workloadPercentage)
            ];
        });
    
        // 8. Kirim semua data yang dibutuhkan ke view
        return view('weekly_workload.index', [
            'workloadData' => $workloadData, // Gunakan data yang sudah dihitung
            'teamMembers' => $teamMembers, // Kirim juga data paginasi untuk link
            'standardHours' => $standardHours,
            'search' => $search
        ]);
    }
}
