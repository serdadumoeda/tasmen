<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class InsightService
{
    /**
     * Generate all insights.
     *
     * @return \Illuminate\Support\Collection
     */
    public function generate(): Collection
    {
        $insights = collect();

        // Mengambil data yang dibutuhkan sekali saja untuk efisiensi
        $projects = Project::with(['tasks', 'budgetItems.realizations', 'leader'])->get();
        $users = User::with(['tasks', 'specialAssignments'])->where('status', 'active')->get();

        // Memanggil semua metode analisis dengan data yang sudah di-cache
        $insights = $insights->merge($this->findOverdueProjects($projects));
        $insights = $insights->merge($this->findAtRiskProjects($projects));
        $insights = $insights->merge($this->findOverloadedUsers($users));
        $insights = $insights->merge($this->findUnderutilizedUsers($users));
        $insights = $insights->merge($this->findBudgetIssues($projects));

        return $insights->sortBy('severity_score');
    }

    /**
     * Rule 1: Find overdue projects.
     */
    private function findOverdueProjects(Collection $projects): Collection
    {
        return $projects->filter(function ($project) {
            return $project->end_date && $project->end_date->isPast() && $project->status !== 'completed';
        })->map(function ($project) {
            return [
                'severity' => 'Peringatan Kritis',
                'severity_score' => 1,
                'message' => "Proyek '{$project->name}' telah melewati tenggat waktu pada " . $project->end_date->format('d M Y') . ".",
                'link' => route('projects.show', $project),
                'icon' => 'fa-triangle-exclamation',
                'color' => 'red',
            ];
        });
    }

    /**
     * Rule 2: Find projects at risk of being late.
     */
    private function findAtRiskProjects(Collection $projects): Collection
    {
        return $projects->filter(function ($project) {
            if ($project->status === 'completed' || !$project->start_date || !$project->end_date) {
                return false;
            }
            $totalDuration = $project->end_date->diffInDays($project->start_date);
            if ($totalDuration <= 0) return false;

            $daysElapsed = now()->diffInDays($project->start_date);
            $timeElapsedPercentage = ($daysElapsed / $totalDuration) * 100;

            // Berisiko jika waktu sudah berjalan > 70% tapi progres < 50%
            return $timeElapsedPercentage > 70 && $project->progress < 50;
        })->map(function ($project) {
            return [
                'severity' => 'Rekomendasi',
                'severity_score' => 2,
                'message' => "Progres Proyek '{$project->name}' ({$project->progress}%) tampak lambat dibandingkan waktu yang telah berjalan. Pertimbangkan untuk meninjau.",
                'link' => route('projects.show', $project),
                'icon' => 'fa-person-digging',
                'color' => 'yellow',
            ];
        });
    }

    /**
     * Rule 3: Find overloaded users.
     */
    private function findOverloadedUsers(Collection $users): Collection
    {
        return $users->filter(function ($user) {
            $weeklyHours = $user->tasks()->where('status', '!=', 'completed')->sum('estimated_hours') / 4; // Asumsi pengerjaan 4 minggu
            return $weeklyHours > 45; // Dianggap overload jika > 45 jam/minggu
        })->map(function ($user) {
            $weeklyHours = $user->tasks()->where('status', '!=', 'completed')->sum('estimated_hours') / 4;
            return [
                'severity' => 'Rekomendasi',
                'severity_score' => 3,
                'message' => "Beban kerja {$user->name} sangat tinggi (sekitar " . round($weeklyHours) . " jam/minggu). Pertimbangkan untuk redistribusi tugas.",
                'link' => route('workload.analysis'),
                'icon' => 'fa-bolt',
                'color' => 'yellow',
            ];
        });
    }

    /**
     * Rule 4: Find underutilized users.
     */
    private function findUnderutilizedUsers(Collection $users): Collection
    {
        return $users->filter(function ($user) {
            return $user->tasks()->where('status', '!=', 'completed')->count() === 0 &&
                   $user->specialAssignments()->where('status', '!=', 'selesai')->count() === 0;
        })->map(function ($user) {
            return [
                'severity' => 'Informasi',
                'severity_score' => 4,
                'message' => "{$user->name} saat ini tidak memiliki tugas aktif. Pertimbangkan untuk melibatkannya dalam proyek baru.",
                'link' => route('users.show', $user),
                'icon' => 'fa-lightbulb',
                'color' => 'blue',
            ];
        });
    }

    /**
     * Rule 5: Find projects with budget issues.
     */
    private function findBudgetIssues(Collection $projects): Collection
    {
        return $projects->filter(function ($project) {
            $totalBudget = $project->budgetItems->sum('total_cost');
            if ($totalBudget <= 0) return false;

            $realization = $project->budgetItems->flatMap->realizations->sum('amount');
            $absorptionRate = ($realization / $totalBudget) * 100;

            // Isu jika penyerapan > 90% tapi progres < 75%
            return $absorptionRate > 90 && $project->progress < 75;
        })->map(function ($project) {
            $totalBudget = $project->budgetItems->sum('total_cost');
            $realization = $project->budgetItems->flatMap->realizations->sum('amount');
            $absorptionRate = round(($realization / $totalBudget) * 100);
            return [
                'severity' => 'Peringatan',
                'severity_score' => 2,
                'message' => "Penyerapan anggaran Proyek '{$project->name}' sudah {$absorptionRate}% namun progres baru {$project->progress}%.",
                'link' => route('projects.budget-items.index', $project),
                'icon' => 'fa-wallet',
                'color' => 'orange',
            ];
        });
    }
}
