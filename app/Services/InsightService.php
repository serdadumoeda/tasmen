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
     */
    public function generate(): Collection
    {
        $insights = collect();

        // Eager load semua relasi yang dibutuhkan untuk efisiensi
        $projects = Project::with(['tasks.assignees', 'budgetItems.realizations', 'leader.unit'])->get();
        $users = User::with(['tasks', 'specialAssignments', 'unit'])
                     ->where('status', 'active')
                     ->get();

        // Memanggil semua metode analisis dengan data yang sudah di-cache
        $insights = $insights->merge($this->findOverdueProjects($projects));
        $insights = $insights->merge($this->findAtRiskProjects($projects));
        $insights = $insights->merge($this->findOverloadedUsers($users));
        $insights = $insights->merge($this->findUnderutilizedUsers($users));
        $insights = $insights->merge($this->findBudgetIssues($projects));

        return $insights->sortBy('severity_score');
    }

    /**
     * Rule 1: Find overdue projects. (No change needed)
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
     * Rule 2: Find projects at risk, now with performance context.
     */
    private function findAtRiskProjects(Collection $projects): Collection
    {
        return $projects->filter(function ($project) {
            if ($project->status === 'completed' || !$project->start_date || !$project->end_date) return false;
            $totalDuration = $project->end_date->diffInDays($project->start_date);
            if ($totalDuration <= 0) return false;
            $daysElapsed = now()->diffInDays($project->start_date);
            $timeElapsedPercentage = ($daysElapsed / $totalDuration) * 100;
            return $timeElapsedPercentage > 70 && $project->progress < 50;
        })->map(function ($project) {
            $underperformingMembers = $project->tasks->flatMap->assignees->unique('id')->filter(function ($user) {
                return in_array($user->work_result_rating, ['Dibawah Ekspektasi', 'Butuh Perbaikan']);
            });

            $message = "Progres Proyek '{$project->name}' ({$project->progress}%) tampak lambat. Pertimbangkan untuk meninjau.";
            if ($underperformingMembers->isNotEmpty()) {
                $names = $underperformingMembers->pluck('name')->implode(', ');
                $message = "Progres Proyek '{$project->name}' ({$project->progress}%) lambat. Kinerja beberapa anggota tim ({$names}) juga di bawah ekspektasi.";
            }

            return [
                'severity' => 'Peringatan',
                'severity_score' => 2,
                'message' => $message,
                'link' => route('projects.show', $project),
                'icon' => 'fa-person-digging',
                'color' => 'orange',
            ];
        });
    }

    /**
     * Rule 3: Find overloaded users, now with performance context.
     */
    private function findOverloadedUsers(Collection $users): Collection
    {
        return $users->filter(function ($user) {
            // Perhitungan weeklyHours sekarang seharusnya bekerja dengan benar
            $weeklyHours = $user->tasks->where('status', '!=', 'completed')->sum('estimated_hours') / 4;
            return $weeklyHours > 45 && in_array($user->work_result_rating, ['Dibawah Ekspektasi', 'Butuh Perbaikan', 'Sangat Kurang']);
        })->map(function ($user) {
            $weeklyHours = $user->tasks->where('status', '!=', 'completed')->sum('estimated_hours') / 4;
            return [
                'severity' => 'Peringatan',
                'severity_score' => 3,
                'message' => "Beban kerja {$user->name} sangat tinggi (~" . round($weeklyHours) . " jam/minggu) dan kinerjanya dinilai '{$user->work_result_rating}'. Segera redistribusi tugas.",
                'link' => route('workload.analysis'),
                'icon' => 'fa-bolt',
                'color' => 'orange',
            ];
        });
    }

    /**
     * Rule 4: Find underutilized users, now a more actionable warning.
     */
    private function findUnderutilizedUsers(Collection $users): Collection
    {
        return $users->filter(function ($user) {
            return $user->work_result_rating === 'Tidak Dapat Dinilai';
        })->map(function ($user) {
            return [
                'severity' => 'Peringatan',
                'severity_score' => 3,
                'message' => "{$user->name} tidak memiliki tugas aktif sehingga kinerjanya tidak dapat dinilai. Segera alokasikan ke proyek baru.",
                'link' => route('users.show', $user),
                'icon' => 'fa-lightbulb',
                'color' => 'orange',
            ];
        });
    }

    /**
     * Rule 5: Find projects with budget issues, now with leader performance context.
     */
    private function findBudgetIssues(Collection $projects): Collection
    {
        return $projects->filter(function ($project) {
            $totalBudget = $project->budgetItems->sum('total_cost');
            if ($totalBudget <= 0) return false;
            $realization = $project->budgetItems->flatMap->realizations->sum('amount');
            $absorptionRate = ($realization / $totalBudget) * 100;
            return $absorptionRate > 90 && $project->progress < 75;
        })->map(function ($project) {
            $totalBudget = $project->budgetItems->sum('total_cost');
            $realization = $project->budgetItems->flatMap->realizations->sum('amount');
            $absorptionRate = round(($realization / $totalBudget) * 100);

            $message = "Penyerapan anggaran Proyek '{$project->name}' sudah {$absorptionRate}% namun progres baru {$project->progress}%.";
            if ($project->leader && in_array($project->leader->work_result_rating, ['Dibawah Ekspektasi', 'Butuh Perbaikan'])) {
                $message .= " Kinerja manajer proyek, {$project->leader->name}, juga perlu perhatian.";
            }

            return [
                'severity' => 'Peringatan Kritis',
                'severity_score' => 1,
                'message' => $message,
                'link' => route('projects.budget-items.index', $project),
                'icon' => 'fa-wallet',
                'color' => 'red',
            ];
        });
    }
}
