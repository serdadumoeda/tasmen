<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id',
        'work_behavior_rating',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- RELASI ---

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }
    
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }
    
    public function specialAssignments(): BelongsToMany
    {
        return $this->belongsToMany(SpecialAssignment::class, 'special_assignment_user');
    }

    public function ledProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'leader_id');
    }


    // --- FUNGSI BANTUAN & HAK AKSES ---
    
    /**
     * FUNGSI YANG HILANG - DITAMBAHKAN KEMBALI
     * Mengecek secara rekursif apakah user ini adalah bawahan dari seorang manajer.
     *
     * @param User $manager
     * @return boolean
     */
    public function isSubordinateOf(User $manager)
    {
        $currentParent = $this->parent;

        // Telusuri hierarki ke atas
        while ($currentParent) {
            if ($currentParent->id === $manager->id) {
                return true; // Ditemukan sebagai bawahan
            }
            $currentParent = $currentParent->parent;
        }

        return false; // Bukan bawahan
    }
    
    public function getAllSubordinateIds(array &$visited = [])
    {
        return $this->getAllSubordinates($visited)->pluck('id');
    }
    
    public function getAllSubordinates(array &$visited = [])
    {
        $subordinates = collect();
        if (empty($visited)) {
            $visited[] = $this->id;
        }

        foreach ($this->children as $child) {
            if (in_array($child->id, $visited)) {
                continue;
            }
            $visited[] = $child->id;
            $subordinates->push($child);
            $subordinates = $subordinates->merge($child->getAllSubordinates($visited));
        }
        return $subordinates;
    }

    public function canCreateProjects(): bool
    {
        return in_array($this->role, ['Superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
    }

    public function isTopLevelManager(): bool
    {
        return in_array($this->role, ['Superadmin', 'Eselon I', 'Eselon II']);
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['Superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
    }

    public function isManager(): bool
    {
        $isStructuralManager = in_array($this->role, ['Eselon I', 'Eselon II', 'Koordinator', 'Sub Koordinator']);
        $isFunctionalManager = $this->ledProjects()->exists();
        return $isStructuralManager || $isFunctionalManager;
    }
    
    // --- FORMULA PERHITUNGAN KINERJA ---

    public function getIndividualPerformanceIndexAttribute()
    {
        return Cache::remember('ihk_final_v_structure_' . $this->id, now()->addMinutes(10), function () {
            $allTasks = $this->tasks()->where('status', '!=', 'cancelled')->get();
            
            if ($allTasks->isEmpty()) {
                return 1.0;
            }

            $totalEstimatedHours = $allTasks->sum('estimated_hours');
            $timeLogs = TimeLog::whereIn('task_id', $allTasks->pluck('id'))
                                     ->where('user_id', $this->id)
                                     ->whereNotNull('start_time')->whereNotNull('end_time')
                                     ->get();
            $totalSeconds = $timeLogs->reduce(function ($carry, $log) {
                return $carry + Carbon::parse($log->end_time)->diffInSeconds(Carbon::parse($log->start_time));
            }, 0);
            $totalActualHours = $totalSeconds / 3600;
            $averageProgress = $allTasks->avg('progress') ?? 0;
            
            if ($totalEstimatedHours == 0) return ($averageProgress / 100) > 0 ? 1.15 : 1.0;
            if ($totalActualHours == 0) return $averageProgress > 0 ? 1.0 : 0.9;

            $progressRatio = $averageProgress / 100;
            $effortRatio = $totalActualHours / $totalEstimatedHours;

            if ($effortRatio == 0) return 1.0;

            return $progressRatio / $effortRatio;
        });
    }

    public function getManagerialPerformanceScoreAttribute()
    {
        if ($this->children->isEmpty()) {
            return 0;
        }
        return $this->children->avg(function ($subordinate) {
            return $subordinate->getFinalPerformanceValueAttribute();
        });
    }

    public function getFinalPerformanceValueAttribute()
    {
        if (!$this->isManager()) {
            return $this->individual_performance_index;
        }

        $managerialWeights = [
            'Eselon I' => 0.9,
            'Eselon II' => 0.8,
            'Koordinator' => 0.7,
            'Sub Koordinator' => 0.6,
        ];
        
        $weight = $managerialWeights[$this->role] ?? 0.5;
        
        $individualScore = $this->individual_performance_index;
        $managerialScore = $this->managerial_performance_score;

        if ($managerialScore == 0 && $this->children->isEmpty()) {
            return $individualScore;
        }

        return ($individualScore * (1 - $weight)) + ($managerialScore * $weight);
    }

    public function getWorkResultRatingAttribute(): string
    {
        $finalScore = $this->getFinalPerformanceValueAttribute();
        if ($finalScore >= 1.15) return 'Diatas Ekspektasi';
        if ($finalScore >= 0.90) return 'Sesuai Ekspektasi';
        return 'Dibawah Ekspektasi';
    }
    
    public function getPerformancePredicateAttribute(): string
    {
        $hasilKerja = $this->work_result_rating;
        $perilakuKerja = $this->work_behavior_rating ?? 'Sesuai Ekspektasi';

        if ($hasilKerja === 'Diatas Ekspektasi' && $perilakuKerja === 'Diatas Ekspektasi') return 'Sangat Baik';
        if ($hasilKerja === 'Dibawah Ekspektasi' && $perilakuKerja === 'Dibawah Ekspektasi') return 'Sangat Kurang';
        if ($hasilKerja === 'Dibawah Ekspektasi' || $perilakuKerja === 'Dibawah Ekspektasi') return 'Butuh Perbaikan';
        return 'Baik';
    }

    // --- ACCESSOR BEBAN KERJA ---
    public function getTotalProjectHoursAttribute()
    {
        return $this->tasks()->whereNotNull('project_id')->sum('estimated_hours');
    }

    public function getTotalAdHocHoursAttribute()
    {
        return $this->tasks()->whereNull('project_id')->sum('estimated_hours');
    }

    public function getActiveSkCountAttribute()
    {
        return $this->specialAssignments()->where('status', 'disetujui')->count();
    }
}
