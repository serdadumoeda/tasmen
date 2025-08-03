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
        $this->users = User::with('unit.parentUnit')->get()->keyBy('id');
        $this->calculateForAllUsers(); // Cara paling aman adalah hitung ulang semua untuk menjaga konsistensi.
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

        return round($progressRatio / $effortRatio, 3);
    }

    private function calculateFinalPerformanceValue(User $user): float
    {
        // Jika sudah dihitung dalam siklus ini, kembalikan hasilnya untuk menghindari kerja ganda.
        if (isset($this->calculatedNkf[$user->id])) {
            return $this->calculatedNkf[$user->id];
        }

        // Ambil IKI yang baru saja dihitung di langkah sebelumnya.
        $individualScore = $this->calculatedIki[$user->id] ?? 1.0;

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
