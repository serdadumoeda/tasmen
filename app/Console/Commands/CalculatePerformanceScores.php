<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\TimeLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CalculatePerformanceScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-performance-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store performance scores for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting performance score calculation...');
        Log::info('Scheduled task: Starting performance score calculation.');

        $users = User::all();

        // Hitung IKI untuk semua user terlebih dahulu
        foreach ($users as $user) {
            $iki = $this->calculateIndividualPerformanceIndex($user);
            $user->individual_performance_index = $iki;
            // Simpan sementara, akan di-save nanti setelah NKF dihitung
        }

        // Hitung NKF setelah semua IKI tersedia
        foreach ($users as $user) {
            $nkf = $this->calculateFinalPerformanceValue($user, $users);
            $user->final_performance_value = $nkf;
            $user->work_result_rating = $this->getWorkResultRating($nkf);
            $user->performance_predicate = $this->getPerformancePredicate($user->work_result_rating, $user->work_behavior_rating);
            $user->performance_data_updated_at = now();
            $user->save();
        }

        Log::info('Scheduled task: Finished performance score calculation.');
        $this->info('Performance score calculation completed successfully.');
        return 0;
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

        $individualScore = $user->individual_performance_index ?? 1.0; // Ambil IKI yang sudah dihitung

        if (!$user->isManager()) return $individualScore;

        $visited[] = $user->id;

        $subordinateIds = $user->getAllSubordinateIds();
        $subordinates = $allUsers->whereIn('id', $subordinateIds);

        if ($subordinates->isEmpty()) return $individualScore;

        $managerialScore = $subordinates->avg(function ($subordinate) use ($allUsers, $visited) {
            // Gunakan data yang sudah dihitung jika memungkinkan, atau hitung jika perlu (untuk hirarki multi-level)
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
