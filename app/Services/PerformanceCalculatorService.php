<?php

namespace App\Services;

use App\Models\PerformanceSetting;
use App\Models\User;

class PerformanceCalculatorService
{
    protected function getEfficiencyCap(): array
    {
        $caps = PerformanceSetting::get('efficiency_cap', ['min' => 0.5, 'max' => 1.5]);
        return [
            'min' => (float) ($caps['min'] ?? 0.5),
            'max' => (float) ($caps['max'] ?? 1.5),
        ];
    }

    protected function getRatingThresholds(): array
    {
        return PerformanceSetting::get('rating_thresholds', [
            'excellent' => 1.15,
            'satisfactory' => 0.9,
            'needs_improvement' => 0.0,
        ]);
    }

    public function calculateEfficiencyFactor(float $estimated, float $actual): float
    {
        if ($actual <= 0) {
            return 0.0;
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
        // Weight is now directly sourced from the user's role relationship
        $weight = $user->role->managerial_weight ?? 0.0;

        if ($weight <= 0 || $subordinatesAverage === null) {
            return $individualScore;
        }
        return ($individualScore * (1 - $weight)) + ($subordinatesAverage * $weight);
    }

    public function getPerformancePredicate(float $score): string
    {
        $thresholds = $this->getRatingThresholds();
        arsort($thresholds);
        foreach ($thresholds as $label => $minScore) {
            if ($score >= (float) $minScore) {
                return $label;
            }
        }
        return array_key_last($thresholds);
    }
}
