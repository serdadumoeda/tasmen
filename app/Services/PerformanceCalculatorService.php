<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\TimeLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class PerformanceCalculatorService
{
    private array $calculatedIki;
    private array $calculatedNkf;
    private ExpressionLanguage $expressionLanguage;
    private array $settings;

    public function __construct()
    {
        $this->calculatedIki = [];
        $this->calculatedNkf = [];
        $this->expressionLanguage = new ExpressionLanguage();
        // Cache settings for the duration of the service's lifecycle
        $this->settings = Setting::pluck('value', 'key')->all();
    }

    public function calculateForAllUsers(): void
    {
        $allUsers = User::with('tasks', 'unit', 'atasan')->get()->keyBy('id');
        if ($allUsers->isEmpty()) return;

        $this->calculatedIki = [];
        $this->calculatedNkf = [];

        foreach ($allUsers as $user) {
            $this->calculatedIki[$user->id] = $this->calculateIndividualPerformanceIndex($user);
        }

        $roleOrder = [
            User::ROLE_STAF, User::ROLE_SUB_KOORDINATOR, User::ROLE_KOORDINATOR,
            User::ROLE_ESELON_II, User::ROLE_ESELON_I, User::ROLE_MENTERI,
        ];

        foreach ($roleOrder as $role) {
            $usersInRole = $allUsers->where('role', $role);
            foreach ($usersInRole as $user) {
                $this->calculateFinalPerformanceValue($user, $allUsers);
            }
        }

        foreach ($allUsers as $user) {
            $user->individual_performance_index = $this->calculatedIki[$user->id] ?? 0.0;
            $user->final_performance_value = $this->calculatedNkf[$user->id] ?? 0.0;
            $user->work_result_rating = $this->getWorkResultRating($user->final_performance_value);
            $user->performance_predicate = $this->getPerformancePredicate($user->work_result_rating, $user->work_behavior_rating);
            $user->performance_data_updated_at = now();
            $user->save();
        }
    }

    private function calculateFinalPerformanceValue(User $user, Collection $allUsers): float
    {
        $individualScore = $this->calculatedIki[$user->id] ?? 0.0;

        if (!$user->isManager()) {
            $formula = $this->settings['nkf_formula_staf'] ?? 'individual_score';
            return $this->calculatedNkf[$user->id] = $this->expressionLanguage->evaluate($formula, ['individual_score' => $individualScore]);
        }

        $subordinates = $allUsers->where('atasan_id', $user->id);
        if ($subordinates->isEmpty()) {
            $formula = $this->settings['nkf_formula_staf'] ?? 'individual_score'; // A manager with no subordinates is treated as staff for calculation
            return $this->calculatedNkf[$user->id] = $this->expressionLanguage->evaluate($formula, ['individual_score' => $individualScore]);
        }

        $managerialScore = $subordinates->avg(fn($sub) => $this->calculatedNkf[$sub->id] ?? 1.0);

        // Fetch weight from settings, falling back to a default
        $weight = (float)($this->settings['managerial_weight_' . strtolower($user->role)] ?? 0.5);

        $formula = $this->settings['nkf_formula_pimpinan'] ?? '(individual_score * (1 - weight)) + (managerial_score * weight)';

        $nkf = $this->expressionLanguage->evaluate($formula, [
            'individual_score' => $individualScore,
            'managerial_score' => $managerialScore,
            'weight' => $weight,
        ]);

        return $this->calculatedNkf[$user->id] = $nkf;
    }

    private function getPriorityWeight(string $priority): int
    {
        return match (strtolower($priority)) {
            'critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1,
            default => 2,
        };
    }

    private function calculateIndividualPerformanceIndex(User $user): float
    {
        $allTasks = $user->tasks;
        if ($allTasks->isEmpty()) return 0.0;

        // Calculate base_score
        $totalWeight = 0;
        $weightedProgressSum = 0;
        foreach ($allTasks as $task) {
            $weight = $this->getPriorityWeight($task->priority);
            $totalWeight += $weight;
            $weightedProgressSum += ($task->progress / 100) * $weight;
        }
        $baseScore = ($totalWeight > 0) ? ($weightedProgressSum / $totalWeight) : 0;

        // Calculate efficiency_factor
        $totalEstimatedHours = $allTasks->sum('estimated_hours');
        $timeLogs = TimeLog::whereIn('task_id', $allTasks->pluck('id'))->where('user_id', $user->id)->whereNotNull('end_time')->get();
        $totalActualHours = $timeLogs->sum('duration_in_minutes') / 60;
        $efficiencyFactor = ($totalEstimatedHours > 0 && $totalActualHours > 0) ? ($totalEstimatedHours / $totalActualHours) : 1.0;

        // Calculate capped_efficiency_factor from settings
        $minEfficiency = (float)($this->settings['min_efficiency_factor'] ?? 0.9);
        $maxEfficiency = (float)($this->settings['max_efficiency_factor'] ?? 1.25);
        $cappedEfficiencyFactor = max($minEfficiency, min($efficiencyFactor, $maxEfficiency));

        // Evaluate IKI using the formula from settings
        $formula = $this->settings['iki_formula'] ?? 'base_score * capped_efficiency_factor';
        $finalIki = $this->expressionLanguage->evaluate($formula, [
            'base_score' => $baseScore,
            'efficiency_factor' => $efficiencyFactor,
            'capped_efficiency_factor' => $cappedEfficiencyFactor,
        ]);

        return round($finalIki, 3);
    }

    private function getWorkResultRating(float $finalScore): string
    {
        if ($finalScore == 0) return 'Tidak Dapat Dinilai';
        if ($finalScore >= (float)($this->settings['rating_threshold_high'] ?? 1.15)) return 'Diatas Ekspektasi';
        if ($finalScore >= (float)($this->settings['rating_threshold_medium'] ?? 0.90)) return 'Sesuai Ekspektasi';
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

    public function calculateForSingleUserAndParents(User $user): void
    {
        $allUsers = User::with('tasks', 'unit', 'atasan')->get()->keyBy('id');
        $this->calculatedIki = $allUsers->map(fn($u) => $u->individual_performance_index ?? 0.0)->all();
        $this->calculatedNkf = $allUsers->map(fn($u) => $u->final_performance_value ?? 0.0)->all();

        $currentUser = $user;
        while ($currentUser) {
            if ($currentUser->id === $user->id) {
                $this->calculatedIki[$currentUser->id] = $this->calculateIndividualPerformanceIndex($currentUser);
            }
            $this->calculatedNkf[$currentUser->id] = $this->calculateFinalPerformanceValue($currentUser, $allUsers);
            $currentUser = $allUsers->get($currentUser->atasan_id);
        }

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
