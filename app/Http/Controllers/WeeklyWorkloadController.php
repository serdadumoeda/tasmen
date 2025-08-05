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
        // 1. Dapatkan pengguna yang sedang login & input pencarian
        $manager = Auth::user();
        $search = $request->input('search');
    
        // 2. Otorisasi: Hanya manajer yang diizinkan yang bisa mengakses
        // Anda bisa sesuaikan logikanya, di sini saya asumsikan semua manajer bisa
        if (!$manager) { // Contoh sederhana, pastikan user adalah manajer
            abort(403, 'Anda harus login sebagai manajer untuk mengakses halaman ini.');
        }
    
        // 3. Dapatkan query dasar untuk mengambil data bawahan
        // Ini menangani kasus Superadmin dan Manajer biasa
        if ($manager->role === User::ROLE_SUPERADMIN) {
            $subordinatesQuery = User::where('id', '!=', $manager->id);
        } else {
            // Ambil ID unit dari semua unit di bawah manajer ini
            $subordinateUnitIds = $manager->unit ? $manager->unit->getAllSubordinateUnitIds() : [];
            // Tambahkan unit manajer itu sendiri
            $subordinateUnitIds[] = $manager->unit_id;
            
            $subordinatesQuery = User::whereIn('unit_id', array_unique($subordinateUnitIds))
                                     ->where('id', '!=', $manager->id);
        }
    
        // 4. Terapkan filter pencarian nama jika ada
        if ($search) {
            $subordinatesQuery->where('name', 'like', '%' . $search . '%');
        }
        
        // 5. Eager load tugas untuk setiap anggota tim
        // Ini akan digunakan untuk menghitung beban kerja
        $subordinatesQuery->with(['tasks' => function ($query) {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('deadline')
                  ->where('deadline', '>', Carbon::now());
        }]);
    
        // 6. Ambil data dengan paginasi
        $teamMembers = $subordinatesQuery->paginate(20)->withQueryString();
    
        // 7. Hitung beban kerja untuk setiap anggota tim yang ditampilkan
        $workloadData = $teamMembers->map(function ($member) {
            $totalWeeklyHours = $member->tasks->reduce(function ($carry, $task) {
                // Perhitungan ini sama seperti logika Anda sebelumnya
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
    
            $workloadPercentage = (self::STANDARD_WEEKLY_HOURS > 0)
                ? ($totalWeeklyHours / self::STANDARD_WEEKLY_HOURS) * 100
                : 0;
    
            // Mengembalikan data dalam format yang dibutuhkan oleh view
            return [
                'user' => $member,
                'assigned_hours' => round($totalWeeklyHours, 1),
                'workload_percentage' => round($workloadPercentage)
            ];
        });
    
        // 8. Kirim semua data yang dibutuhkan ke view
        return view('weekly_workload.index', [
            'workloadData' => $workloadData, // Gunakan data yang sudah dihitung
            'teamMembers' => $teamMembers, // Kirim juga data paginasi untuk link
            'standardHours' => self::STANDARD_WEEKLY_HOURS,
            'search' => $search
        ]);
    }
}
