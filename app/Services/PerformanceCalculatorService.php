<?php

namespace App\Services;

use App\Models\User;
use App\Models\TimeLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PerformanceCalculatorService
{
    private Collection $users;
    private array $calculatedIki;
    private array $calculatedNkf;

    public function __construct()
    {
        $this->calculatedIki = [];
        $this->calculatedNkf = [];
    }

    /**
     * Menjalankan siklus perhitungan untuk semua pengguna.
     * Ini adalah method yang seharusnya dipanggil oleh scheduled command.
     */
    public function calculateForAllUsers(): void
    {
        $this->users = User::with('unit')->get()->keyBy('id');
        $this->calculatedIki = [];
        $this->calculatedNkf = [];

        // Langkah 1: Hitung IKI untuk semua orang
        foreach ($this->users as $user) {
            $this->calculatedIki[$user->id] = $this->calculateIndividualPerformanceIndex($user);
        }

        // Langkah 2: Hitung NKF untuk semua orang (fungsi rekursif akan menangani dependensi)
        foreach ($this->users as $user) {
            $this->calculateFinalPerformanceValue($user);
        }

        // Langkah 3: Simpan semua hasil ke database
        foreach ($this->users as $user) {
            $user->individual_performance_index = $this->calculatedIki[$user->id];
            $user->final_performance_value = $this->calculatedNkf[$user->id];
            $user->work_result_rating = $this->getWorkResultRating($user->final_performance_value);
            $user->performance_predicate = $this->getPerformancePredicate($user->work_result_rating, $user->work_behavior_rating);
            $user->performance_data_updated_at = now();
            $user->save();
        }
    }

    /**
     * Menghitung ulang kinerja untuk satu pengguna dan memperbarui hierarki atasannya.
     * Ini yang dipanggil oleh controller setelah ada aksi.
     */
    public function calculateForSingleUserAndParents(User $user): void
    {
        // Ambil semua user untuk referensi, tapi perhitungan hanya untuk yang terdampak.
        $this->users = User::with('unit.parentUnit')->get()->keyBy('id');
        $this->calculatedIki = $this->users->mapWithKeys(fn ($u) => [$u->id => $u->individual_performance_index])->all();
        $this->calculatedNkf = $this->users->mapWithKeys(fn ($u) => [$u->id => $u->final_performance_value])->all();

        // Hitung ulang hanya untuk user yang bersangkutan
        $this->recalculateUser($user);

        // Telusuri ke atas dan hitung ulang semua atasannya
        $currentUser = $user;
        while ($currentUser->unit && $currentUser->unit->parentUnit) {
            $parentUnit = $currentUser->unit->parentUnit;
            // Asumsi sederhana: atasan adalah user dengan role manajerial di unit induk.
            // Logika ini bisa lebih kompleks tergantung aturan bisnis.
            $manager = $this->users->first(function ($u) use ($parentUnit) {
                return $u->unit_id === $parentUnit->id && $u->isManager();
            });

            if ($manager) {
                $this->recalculateUser($manager);
                $currentUser = $manager;
            } else {
                break; // Hentikan jika tidak ada manajer di unit atas
            }
        }
    }

    /**
     * Helper untuk menghitung dan menyimpan data satu user.
     */
    private function recalculateUser(User $user): void
    {
        // Langkah 1: Hitung IKI baru
        $this->calculatedIki[$user->id] = $this->calculateIndividualPerformanceIndex($user);

        // Langkah 2: Hitung NKF baru
        $this->calculatedNkf[$user->id] = $this->calculateFinalPerformanceValue($user);

        // Langkah 3: Simpan semua data baru ke model User
        $userToUpdate = $this->users->get($user->id);
        if ($userToUpdate) {
            $userToUpdate->individual_performance_index = $this->calculatedIki[$user->id];
            $userToUpdate->final_performance_value = $this->calculatedNkf[$user->id];
            $userToUpdate->work_result_rating = $this->getWorkResultRating($userToUpdate->final_performance_value);
            $userToUpdate->performance_predicate = $this->getPerformancePredicate($userToUpdate->work_result_rating, $userToUpdate->work_behavior_rating);
            $userToUpdate->performance_data_updated_at = now();
            $userToUpdate->save();
        }
    }

    private function getPriorityWeight(string $priority): int
    {
        return match (strtolower($priority)) {
            'critical' => 4,
            'high' => 3,
            'low' => 1,
            default => 2, // Normal
        };
    }

    private function calculateIndividualPerformanceIndex(User $user): float
    {
        $allTasks = $user->tasks()->where('status', '!=', 'cancelled')->get();

        // Step 3: Handle "No Tasks" scenario
        if ($allTasks->isEmpty()) {
            return 0.0; // Return 0.0 for "Tidak Dapat Dinilai"
        }

        // Step 2a: Calculate Weighted Average Progress (Base Score)
        $totalWeight = 0;
        $weightedProgressSum = 0;

        foreach ($allTasks as $task) {
            $weight = $this->getPriorityWeight($task->priority);
            $totalWeight += $weight;
            $weightedProgressSum += ($task->progress / 100) * $weight;
        }

        $baseScore = ($totalWeight > 0) ? ($weightedProgressSum / $totalWeight) : 0;

        // Step 2b & 2c: Calculate and Cap the Efficiency Factor
        $totalEstimatedHours = $allTasks->sum('estimated_hours');
        $timeLogs = TimeLog::whereIn('task_id', $allTasks->pluck('id'))
            ->where('user_id', $user->id)
            ->whereNotNull('start_time')->whereNotNull('end_time')
            ->get();
        $totalSeconds = $timeLogs->reduce(fn ($carry, $log) => $carry + Carbon::parse($log->end_time)->diffInSeconds(Carbon::parse($log->start_time)), 0);
        $totalActualHours = $totalSeconds / 3600;

        if ($totalEstimatedHours == 0 || $totalActualHours == 0) {
            $efficiencyFactor = 1.0; // Neutral factor if time is not tracked
        } else {
            $efficiencyFactor = $totalEstimatedHours / $totalActualHours;
        }

        // Cap the efficiency factor to prevent extreme scores
        $cappedEfficiencyFactor = max(0.9, min($efficiencyFactor, 1.25));

        // Step 2d: Calculate Final IKI
        $finalIki = $baseScore * $cappedEfficiencyFactor;

        return round($finalIki, 3);
    }

    private function calculateFinalPerformanceValue(User $user): float
    {
        // Jika sudah dihitung dalam siklus ini, kembalikan hasilnya untuk menghindari kerja ganda.
        if (isset($this->calculatedNkf[$user->id])) {
            return $this->calculatedNkf[$user->id];
        }

        // Ambil IKI yang baru saja dihitung di langkah sebelumnya.
        $individualScore = $this->calculatedIki[$user->id] ?? 0.0;

        if (!$user->isManager()) {
            $this->calculatedNkf[$user->id] = $individualScore;
            return $individualScore;
        }

        $subordinateIds = $user->getAllSubordinateIds();
        if ($subordinateIds->isEmpty()) {
            $this->calculatedNkf[$user->id] = $individualScore;
            return $individualScore;
        }

        $subordinates = $this->users->whereIn('id', $subordinateIds);

        $managerialScore = $subordinates->avg(function ($subordinate) {
            // Panggil fungsi ini secara rekursif untuk memastikan bawahan dihitung terlebih dahulu.
            return $this->calculateFinalPerformanceValue($subordinate);
        });

        $managerialWeights = [
            User::ROLE_ESELON_I => 0.9, User::ROLE_ESELON_II => 0.8,
            User::ROLE_KOORDINATOR => 0.7, User::ROLE_SUB_KOORDINATOR => 0.6,
        ];
        $weight = $managerialWeights[$user->role] ?? 0.5;

        $finalScore = ($individualScore * (1 - $weight)) + ($managerialScore * $weight);
        $this->calculatedNkf[$user->id] = $finalScore;

        return $finalScore;
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
}
