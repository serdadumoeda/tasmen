<?php

namespace App\Services;

use App\Models\User;
use App\Models\TimeLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PerformanceCalculatorService
{
    private array $calculatedIki;
    private array $calculatedNkf;

    public function __construct()
    {
        $this->calculatedIki = [];
        $this->calculatedNkf = [];
    }

    /**
     * Menjalankan siklus perhitungan untuk semua pengguna dengan pendekatan bottom-up.
     */
    public function calculateForAllUsers(): void
    {
        $allUsers = User::with('tasks', 'unit', 'atasan')->get()->keyBy('id');
        if ($allUsers->isEmpty()) {
            return;
        }

        $this->calculatedIki = [];
        $this->calculatedNkf = [];

        // Langkah 1: Hitung IKI untuk semua pengguna.
        foreach ($allUsers as $user) {
            $this->calculatedIki[$user->id] = $this->calculateIndividualPerformanceIndex($user);
        }

        // Langkah 2: Hitung NKF dengan urutan dari level terendah ke tertinggi.
        $roleOrder = [
            User::ROLE_STAF,
            User::ROLE_SUB_KOORDINATOR,
            User::ROLE_KOORDINATOR,
            User::ROLE_ESELON_II,
            User::ROLE_ESELON_I,
            User::ROLE_MENTERI,
        ];

        foreach ($roleOrder as $role) {
            $usersInRole = $allUsers->where('role', $role);
            foreach ($usersInRole as $user) {
                $this->calculateFinalPerformanceValue($user, $allUsers);
            }
        }

        // Langkah 3: Simpan semua hasil ke database.
        foreach ($allUsers as $user) {
            $user->individual_performance_index = $this->calculatedIki[$user->id] ?? 0.0;
            $user->final_performance_value = $this->calculatedNkf[$user->id] ?? 0.0;
            $user->work_result_rating = $this->getWorkResultRating($user->final_performance_value);
            $user->performance_predicate = $this->getPerformancePredicate($user->work_result_rating, $user->work_behavior_rating);
            $user->performance_data_updated_at = now();
            $user->save();
        }
    }

    /**
     * Menghitung NKF untuk satu pengguna (non-rekursif).
     */
    private function calculateFinalPerformanceValue(User $user, Collection $allUsers): float
    {
        $individualScore = $this->calculatedIki[$user->id] ?? 0.0;

        if (!$user->isManager()) {
            return $this->calculatedNkf[$user->id] = $individualScore;
        }

        $subordinates = $allUsers->where('atasan_id', $user->id);
        if ($subordinates->isEmpty()) {
            return $this->calculatedNkf[$user->id] = $individualScore;
        }

        $managerialScore = $subordinates->avg(fn($sub) => $this->calculatedNkf[$sub->id] ?? 1.0);

        $managerialWeights = [
            User::ROLE_ESELON_I => 0.9, User::ROLE_ESELON_II => 0.8,
            User::ROLE_KOORDINATOR => 0.7, User::ROLE_SUB_KOORDINATOR => 0.6,
            User::ROLE_MENTERI => 0.95,
        ];
        $weight = $managerialWeights[$user->role] ?? 0.5;

        return $this->calculatedNkf[$user->id] = ($individualScore * (1 - $weight)) + ($managerialScore * $weight);
    }

    private function getPriorityWeight(string $priority): int
    {
        return match (strtolower($priority)) {
            'critical' => 4, 'high' => 3, 'low' => 1,
            default => 2, // Normal
        };
    }

    private function calculateIndividualPerformanceIndex(User $user): float
    {
        $allTasks = $user->tasks;
        if ($allTasks->isEmpty()) {
            return 0.0;
        }

        $totalWeight = 0;
        $weightedProgressSum = 0;
        foreach ($allTasks as $task) {
            $weight = $this->getPriorityWeight($task->priority);
            $totalWeight += $weight;
            $weightedProgressSum += ($task->progress / 100) * $weight;
        }
        $baseScore = ($totalWeight > 0) ? ($weightedProgressSum / $totalWeight) : 0;

        $totalEstimatedHours = $allTasks->sum('estimated_hours');

        $timeLogs = TimeLog::whereIn('task_id', $allTasks->pluck('id'))
            ->where('user_id', $user->id)
            ->whereNotNull('start_time')->whereNotNull('end_time')
            ->get();

        $totalSeconds = $timeLogs->reduce(fn ($carry, $log) => $carry + Carbon::parse($log->end_time)->diffInSeconds(Carbon::parse($log->start_time)), 0);
        $totalActualHours = $totalSeconds / 3600;

        if ($totalEstimatedHours == 0 || $totalActualHours == 0) {
            $efficiencyFactor = 1.0;
        } else {
            $efficiencyFactor = $totalEstimatedHours / $totalActualHours;
        }

        $cappedEfficiencyFactor = max(0.9, min($efficiencyFactor, 1.25));
        $finalIki = $baseScore * $cappedEfficiencyFactor;

        return round($finalIki, 3);
    }

    private function getWorkResultRating(float $finalScore): string
    {
        if ($finalScore == 0) return 'Tidak Dapat Dinilai';
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

    /**
     * Menghitung ulang skor untuk satu pengguna dan atasannya secara rekursif.
     * Metode ini dipanggil setelah ada perubahan yang memengaruhi skor, seperti update perilaku kerja.
     */
    public function calculateForSingleUserAndParents(User $user): void
    {
        // Dapatkan semua user untuk di-cache, ini kurang efisien tapi konsisten dengan implementasi yang ada
        $allUsers = User::with('tasks', 'unit', 'atasan')->get()->keyBy('id');

        // Inisialisasi cache IKI dan NKF dari data yang ada di DB
        $this->calculatedIki = $allUsers->map(fn($u) => $u->individual_performance_index ?? 0.0)->all();
        $this->calculatedNkf = $allUsers->map(fn($u) => $u->final_performance_value ?? 0.0)->all();

        // Loop dari user saat ini ke atas hirarki
        $currentUser = $user;
        while ($currentUser) {
            // IKI hanya dihitung ulang untuk user awal yang perilakunya berubah.
            // IKI atasan tidak terpengaruh oleh perubahan perilaku bawahan.
            if ($currentUser->id === $user->id) {
                $this->calculatedIki[$currentUser->id] = $this->calculateIndividualPerformanceIndex($currentUser);
            }

            // Hitung ulang NKF karena ini bergantung pada skor bawahan atau IKI diri sendiri.
            $this->calculatedNkf[$currentUser->id] = $this->calculateFinalPerformanceValue($currentUser, $allUsers);

            // Pindah ke atasan untuk iterasi berikutnya
            $currentUser = $allUsers->get($currentUser->atasan_id);
        }

        // Simpan semua hasil yang diperbarui ke database.
        // Kita hanya perlu menyimpan user yang di-update dan para atasannya.
        $currentUserToSave = $user;
        while ($currentUserToSave) {
            $currentUserToSave->individual_performance_index = $this->calculatedIki[$currentUserToSave->id] ?? 0.0;
            $currentUserToSave->final_performance_value = $this->calculatedNkf[$currentUserToSave->id] ?? 0.0;
            $currentUserToSave->work_result_rating = $this->getWorkResultRating($currentUserToSave->final_performance_value);
            $currentUserToSave->performance_predicate = $this->getPerformancePredicate($currentUserToSave->work_result_rating, $currentUserToSave->work_behavior_rating);
            $currentUserToSave->performance_data_updated_at = now();
            $currentUserToSave->save();

            $currentUserToSave = $allUsers->get($currentUserToSave->atasan_id);
        }
    }
}
