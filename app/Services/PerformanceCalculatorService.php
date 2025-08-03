<?php

namespace App\Services;

use App\Models\User;
use App\Models\TimeLog;
use Illuminate\Support\Carbon;

class PerformanceCalculatorService
{
    /**
     * Menghitung semua metrik kinerja untuk satu pengguna dan menyimpannya.
     */
    public function calculateForUser(User $user, $allUsersCollection = null): void
    {
        // Jika koleksi semua user tidak disediakan, ambil dari DB.
        // Ini untuk pemanggilan tunggal. Untuk pemanggilan massal, lebih baik sediakan dari luar.
        if (!$allUsersCollection) {
            $allUsersCollection = User::all()->keyBy('id');
        }

        // Langkah 1: Hitung dan simpan IKI
        $iki = $this->calculateIndividualPerformanceIndex($user);
        $user->individual_performance_index = $iki;

        // Langkah 2: Hitung dan simpan NKF
        $nkf = $this->calculateFinalPerformanceValue($user, $allUsersCollection);
        $user->final_performance_value = $nkf;

        // Langkah 3: Hitung dan simpan Peringkat & Predikat
        $workResultRating = $this->getWorkResultRating($nkf);
        $user->work_result_rating = $workResultRating;

        $performancePredicate = $this->getPerformancePredicate($workResultRating, $user->work_behavior_rating);
        $user->performance_predicate = $performancePredicate;

        // Langkah 4: Tandai waktu update dan simpan semua ke DB
        $user->performance_data_updated_at = now();
        $user->save();
    }

    private function calculateIndividualPerformanceIndex(User $user): float
    {
        $allTasks = $user->tasks()->where('status', '!=', 'cancelled')->get();
        if ($allTasks->isEmpty()) return 1.0;

        $totalEstimatedHours = $allTasks->sum('estimated_hours');
        $timeLogs = TimeLog::whereIn('task_id', $allTasks->pluck('id'))
                                 ->where('user_id', $user->id)
                                 ->whereNotNull('start_time')->whereNotNull('end_time')
                                 ->get();
        $totalSeconds = $timeLogs->reduce(fn ($carry, $log) => $carry + Carbon::parse($log->end_time)->diffInSeconds(Carbon::parse($log->start_time)), 0);
        $totalActualHours = $totalSeconds / 3600;
        $averageProgress = $allTasks->avg('progress') ?? 0;

        if ($totalEstimatedHours == 0) return ($averageProgress / 100) > 0 ? 1.15 : 1.0;
        if ($totalActualHours == 0) return $averageProgress > 0 ? 1.0 : 0.9;

        $progressRatio = $averageProgress / 100;
        $effortRatio = $totalActualHours / $totalEstimatedHours;

        if ($effortRatio == 0) return 1.0;

        return $progressRatio / $effortRatio;
    }

    private function calculateFinalPerformanceValue(User $user, $allUsers, array $visited = []): float
    {
        if (in_array($user->id, $visited)) return 1.0;

        $individualScore = $user->individual_performance_index ?? 1.0;

        if (!$user->isManager()) return $individualScore;

        $visited[] = $user->id;

        $subordinateIds = $user->getAllSubordinateIds();
        $subordinates = $allUsers->whereIn('id', $subordinateIds);

        if ($subordinates->isEmpty()) return $individualScore;

        $managerialScore = $subordinates->avg(function ($subordinate) use ($allUsers, $visited) {
            return $subordinate->final_performance_value ?? $this->calculateFinalPerformanceValue($subordinate, $allUsers, $visited);
        });

        $managerialWeights = [
            User::ROLE_ESELON_I => 0.9, User::ROLE_ESELON_II => 0.8,
            User::ROLE_KOORDINATOR => 0.7, User::ROLE_SUB_KOORDINATOR => 0.6,
        ];
        $weight = $managerialWeights[$user->role] ?? 0.5;

        return ($individualScore * (1 - $weight)) + ($managerialScore * $weight);
    }

    private function getWorkResultRating(float $finalScore): string
    {
        if ($finalScore >= 1.15) return 'Diatas Ekspektasi';
        if ($finalScore >= 0.90) return 'Sesuai Ekspektasi';
        return 'Dibawah Ekspektasi';
    }

    private function getPerformancePredicate(string $hasilKerja, ?string $perilakuKerja): string
    {
        $perilakuKerja = $perilakuKerja ?? 'Sesuai Ekspektasi';
        if ($hasilKerja === 'Diatas Ekspektasi' && $perilakuKerja === 'Diatas Ekspektasi') return 'Sangat Baik';
        if ($hasilKerja === 'Dibawah Ekspektasi' && $perilakuKerja === 'Dibawah Ekspektasi') return 'Sangat Kurang';
        if ($hasilKerja === 'Dibawah Ekspektasi' || $perilakuKerja === 'Dibawah Ekspektasi') return 'Butuh Perbaikan';
        return 'Baik';
    }
}
