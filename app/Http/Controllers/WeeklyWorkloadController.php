<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyWorkloadController extends Controller
{
    public function index(Request $request)
    {
        // 1. Dapatkan pengguna yang sedang login, input pencarian, dan pengaturan
        $manager = Auth::user();
        $search = $request->input('search');

        $standardHours = config('tasmen.workload.standard_hours', 37.5);
        $thresholdNormal = config('tasmen.workload.threshold_normal', 0.75);
        $thresholdWarning = config('tasmen.workload.threshold_warning', 1.0);

        // 2. Otorisasi
        if (!$manager || !$manager->canManageUsers()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    
        // 3. Query dasar untuk bawahan
        if ($manager->role === User::ROLE_SUPERADMIN) {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            $subordinatesQuery = User::teamMembers($manager);
        }
    
        // 4. Filter pencarian
        if ($search) {
            $subordinatesQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }
        
        // 5. Eager load tugas
        $subordinatesQuery->with(['tasks' => function ($query) {
            $query->whereHas('status', function ($q) {
                $q->where('key', '!=', 'completed');
            })->whereNotNull('deadline');
        }]);
    
        // 6. Paginasi
        $teamMembers = $subordinatesQuery->paginate(20)->withQueryString();
    
        // 7. Hitung beban kerja
        $workloadData = $teamMembers->map(function ($member) use ($standardHours) {
            $today = Carbon::now();
            $startOfWeek = $today->copy()->startOfWeek();
            $endOfWeek = $today->copy()->endOfWeek();

            $effectiveWeeklyHours = $member->getEffectiveWorkingHours($startOfWeek, $endOfWeek);

            $totalWeeklyHours = $member->tasks->reduce(function ($carry, $task) {
                $remainingHours = $task->estimated_hours * ((100 - $task->progress) / 100);
                if ($remainingHours <= 0) return $carry;
    
                $remainingWorkingDays = Carbon::now()->diffInWeekdays($task->deadline);
                if ($remainingWorkingDays == 0 && Carbon::now()->isWeekday()) $remainingWorkingDays = 1;
    
                if ($remainingWorkingDays <= 0) return $carry + $remainingHours;
    
                $dailyWorkload = $remainingHours / $remainingWorkingDays;
                $weeklyWorkloadForTask = $dailyWorkload * 5; 
    
                return $carry + $weeklyWorkloadForTask;
            }, 0);
    
            $workloadPercentage = ($effectiveWeeklyHours > 0)
                ? ($totalWeeklyHours / $effectiveWeeklyHours)
                : ($totalWeeklyHours > 0 ? 2 : 0); // Return a ratio directly
    
            return [
                'user' => $member,
                'assigned_hours' => round($totalWeeklyHours, 1),
                'effective_hours' => $effectiveWeeklyHours,
                'workload_ratio' => $workloadPercentage // Changed from percentage to ratio
            ];
        });
    
        // 8. Kirim data ke view
        return view('weekly_workload.index', [
            'workloadData' => $workloadData,
            'teamMembers' => $teamMembers,
            'search' => $search,
            'thresholdNormal' => $thresholdNormal,
            'thresholdWarning' => $thresholdWarning,
            'standardHours' => $standardHours,
        ]);
    }
}
