<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
// Pastikan baris ini ada dan benar setelah menginstal Sanctum
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // Trait HasApiTokens sekarang akan ditemukan
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'Superadmin';
    public const ROLE_ESELON_I = 'Eselon I';
    public const ROLE_ESELON_II = 'Eselon II';
    public const ROLE_KOORDINATOR = 'Koordinator';
    public const ROLE_SUB_KOORDINATOR = 'Sub Koordinator';
    public const ROLE_STAF = 'Staf';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'unit_id',
        'status',
        'work_behavior_rating',
        'is_in_resource_pool',
        'pool_availability_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ... sisa kode model Anda tidak perlu diubah ...

    // --- RELASI ---

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
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
    
    public function isSubordinateOf(User $manager): bool
    {
        if (!$this->unit || !$manager->unit) {
            return false;
        }

        return in_array($this->unit->id, $manager->unit->getAllSubordinateUnitIds());
    }
    
    public function getAllSubordinateIds(): array
    {
        if (!$this->unit) {
            return [];
        }

        $unitIds = $this->unit->getAllSubordinateUnitIds();
        return User::whereIn('unit_id', $unitIds)->pluck('id')->toArray();
    }
    
    public function getAllSubordinates($includeSelf = false)
    {
        if (!$this->unit) {
            return collect();
        }

        $unitIds = $this->unit->getAllSubordinateUnitIds();
        $query = User::whereIn('unit_id', $unitIds);

        if (!$includeSelf) {
            $query->where('id', '!=', $this->id);
        }

        return $query->get();
    }

    public function canCreateProjects(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ESELON_I, self::ROLE_ESELON_II, self::ROLE_KOORDINATOR]);
    }

    public function isTopLevelManager(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ESELON_I, self::ROLE_ESELON_II]);
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ESELON_I, self::ROLE_ESELON_II, self::ROLE_KOORDINATOR]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isNotSuperAdmin(): bool
    {
        return !$this->isSuperAdmin();
    }

    public function isManager(): bool
    {
        $isStructuralManager = in_array($this->role, [self::ROLE_ESELON_I, self::ROLE_ESELON_II, self::ROLE_KOORDINATOR, self::ROLE_SUB_KOORDINATOR]);
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

    // public function getFinalPerformanceValueAttribute($visited = [])
    // {
    //     if (in_array($this->id, $visited)) {
    //         return 1.0; // Return a neutral value to break the cycle
    //     }

    //     $individualScore = $this->getIndividualPerformanceIndexAttribute();

    //     if (!$this->isManager()) {
    //         return $individualScore;
    //     }

    //     $visited[] = $this->id;

    //     $subordinates = $this->getAllSubordinates(false);
    //     if ($subordinates->isEmpty()) {
    //         return $individualScore;
    //     }

    //     $managerialScore = $subordinates->avg(function ($subordinate) use ($visited) {
    //         return $subordinate->getFinalPerformanceValueAttribute($visited);
    //     });

    //     $managerialWeights = [
    //         self::ROLE_ESELON_I => 0.9,
    //         self::ROLE_ESELON_II => 0.8,
    //         self::ROLE_KOORDINATOR => 0.7,
    //         self::ROLE_SUB_KOORDINATOR => 0.6,
    //     ];
    //     $weight = $managerialWeights[$this->role] ?? 0.5;

    //     return ($individualScore * (1 - $weight)) + ($managerialScore * $weight);
    // }

    // public function getWorkResultRatingAttribute(): string
    // {
    //     $finalScore = $this->getFinalPerformanceValueAttribute();
    //     if ($finalScore >= 1.15) return 'Diatas Ekspektasi';
    //     if ($finalScore >= 0.90) return 'Sesuai Ekspektasi';
    //     return 'Dibawah Ekspektasi';
    // }
    
    // public function getPerformancePredicateAttribute(): string
    // {
    //     $hasilKerja = $this->work_result_rating;
    //     $perilakuKerja = $this->work_behavior_rating ?? 'Sesuai Ekspektasi';

    //     if ($hasilKerja === 'Diatas Ekspektasi' && $perilakuKerja === 'Diatas Ekspektasi') return 'Sangat Baik';
    //     if ($hasilKerja === 'Dibawah Ekspektasi' && $perilakuKerja === 'Dibawah Ekspektasi') return 'Sangat Kurang';
    //     if ($hasilKerja === 'Dibawah Ekspektasi' || $perilakuKerja === 'Dibawah Ekspektasi') return 'Butuh Perbaikan';
    //     return 'Baik';
    // }

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