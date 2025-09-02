<?php

namespace App\Services;

use App\Models\PerformanceSetting;
use App\Models\User;
use App\Models\TimeLog;
use Illuminate\Support\Facades\Log;

class PerformanceCalculatorService
{
    private $calculatedFinalScores = [];

    public function calculateForAllUsers(): void
    {
        Log::info('Starting performance calculation for all users.');
        $allUsers = User::with(['tasks.priorityLevel', 'timeLogs', 'role', 'bawahan'])->get()->keyBy('id');

        if ($allUsers->isEmpty()) {
            Log::info('No users found to calculate performance.');
            return;
        }

        $individualScores = [];
        foreach ($allUsers as $user) {
            $baseScore = $this->calculateBaseScore($user);
            $totalEstimated = $user->tasks->sum('estimated_hours');
            $totalActual = $user->timeLogs->sum('duration_in_minutes') / 60;
            $efficiencyFactor = $this->calculateEfficiencyFactor($totalEstimated, $totalActual);
            $individualScores[$user->id] = $this->calculateIndividualScore($baseScore, $efficiencyFactor);
        }

        $roleOrder = ['staf', 'sub_koordinator', 'koordinator', 'eselon_iv', 'eselon_iii', 'eselon_ii', 'eselon_i', 'menteri'];
        foreach ($roleOrder as $roleName) {
            $usersInRole = $allUsers->filter(fn($u) => $u->role->name === $roleName);
            foreach ($usersInRole as $user) {
                $this->calculateAndCacheFinalScore($user, $allUsers, $individualScores);
            }
        }

        foreach($allUsers as $user) {
            $finalScore = $this->calculatedFinalScores[$user->id] ?? 0.0;
            $user->individual_performance_index = $individualScores[$user->id] ?? 0.0;
            $user->final_performance_value = $finalScore;
            $user->work_result_rating = $this->getPerformancePredicate($finalScore);
            // Assuming work_behavior_rating is set elsewhere
            // $user->performance_predicate = $this->getPerformancePredicate($user->work_result_rating, $user->work_behavior_rating);
            $user->performance_data_updated_at = now();
            $user->save();
        }
        Log::info('Performance calculation finished.');
    }

    private function calculateAndCacheFinalScore(User $user, $allUsers, $individualScores): float
    {
        if (isset($this->calculatedFinalScores[$user->id])) {
            return $this->calculatedFinalScores[$user->id];
        }

        $individualScore = $individualScores[$user->id] ?? 0.0;
        $subordinates = $allUsers->whereIn('id', $user->bawahan->pluck('id'));
        $subordinatesAverage = null;

        if ($subordinates->isNotEmpty()) {
            $subordinatesAverage = $subordinates->avg(function ($sub) use ($allUsers, $individualScores) {
                return $this->calculateAndCacheFinalScore($sub, $allUsers, $individualScores);
            });
        }

        $finalScore = $this->calculateFinalScore($user, $individualScore, $subordinatesAverage);
        $this->calculatedFinalScores[$user->id] = $finalScore;

        return $finalScore;
    }

    private function calculateBaseScore(User $user): float
    {
        $totalWeight = 0;
        $weightedProgressSum = 0;

        foreach ($user->tasks as $task) {
            $weight = $task->priorityLevel->weight ?? 1;
            $totalWeight += $weight;
            $weightedProgressSum += ($task->progress / 100) * $weight;
        }

        return ($totalWeight > 0) ? ($weightedProgressSum / $totalWeight) : 0;
    }

    protected function getEfficiencyCap(): array
    {
        $caps = PerformanceSetting::get('efficiency_cap', ['min' => 0.5, 'max' => 1.5]);
        return ['min' => (float)($caps['min'] ?? 0.5), 'max' => (float)($caps['max'] ?? 1.5)];
    }

    protected function getRatingThresholds(): array
    {
        return PerformanceSetting::get('rating_thresholds', ['excellent' => 1.15, 'satisfactory' => 0.9, 'needs_improvement' => 0.0]);
    }

    public function calculateEfficiencyFactor(float $estimated, float $actual): float
    {
        if ($actual <= 0 || $estimated <= 0) {
            return 1.0; // Neutral factor if no time is logged or estimated
        }
        $factor = $estimated / $actual;
        $cap = $this->getEfficiencyCap();
        return max($cap['min'], min($factor, $cap['max']));
    }

    public function calculateIndividualScore(float $baseScore, float $efficiencyFactor): float
    {
        return $baseScore * $efficiencyFactor;
    }

    public function calculateFinalScore(User $user, float $individualScore, ?float $subordinatesAverage = null): float
    {
        $weight = $user->role->managerial_weight ?? 0.0;
        if ($weight <= 0 || $subordinatesAverage === null) {
            return $individualScore;
        }
        return ($individualScore * (1 - $weight)) + ($subordinatesAverage * $weight);
    }

    public function getPerformancePredicate(float $score): string
    {
        $thresholds = $this->getRatingThresholds();
        arsort($thresholds); // Sort by score descending
        foreach ($thresholds as $label => $minScore) {
            if ($score >= (float)$minScore) {
                return $label;
            }
        }
        return array_key_last($thresholds) ?? 'needs_improvement';
    }
}
