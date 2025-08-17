<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Models\Traits\RecordsActivity;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Unit; // Pastikan ini diimpor
use App\Models\Project;
use App\Models\Jabatan;
use App\Scopes\HierarchicalScope;

class User extends Authenticatable
{
    // Trait HasApiTokens sekarang akan ditemukan
    use HasApiTokens, HasFactory, Notifiable, RecordsActivity;

    // Cache for subordinate unit IDs to prevent N+1 issues in policies.
    public ?\Illuminate\Support\Collection $subordinateUnitIdsCache = null;

    public const ROLE_MENTERI = 'Menteri';
    public const ROLE_SUPERADMIN = 'Superadmin';
    public const ROLE_ESELON_I = 'Eselon I';
    public const ROLE_ESELON_II = 'Eselon II';
    public const ROLE_KOORDINATOR = 'Koordinator';
    public const ROLE_SUB_KOORDINATOR = 'Sub Koordinator';
    public const ROLE_STAF = 'Staf';

    public const ROLES = [
        ['name' => self::ROLE_MENTERI],
        ['name' => self::ROLE_SUPERADMIN],
        ['name' => self::ROLE_ESELON_I],
        ['name' => self::ROLE_ESELON_II],
        ['name' => self::ROLE_KOORDINATOR],
        ['name' => self::ROLE_SUB_KOORDINATOR],
        ['name' => self::ROLE_STAF],
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'atasan_id',
        'unit_id',
        'status',
        'nip',
        'tempat_lahir',
        'tgl_lahir',
        'alamat',
        'jenis_kelamin',
        'agama',
        'golongan',
        'eselon',
        'tmt_eselon',
        'grade',
        'no_hp',
        'telepon',
        'npwp',
        'pendidikan_terakhir',
        'pendidikan_jurusan',
        'pendidikan_universitas',
        'jenis_jabatan',
        'tmt_cpns',
        'tmt_pns',
        'work_behavior_rating',
        'is_in_resource_pool',
        'pool_availability_notes',
        'individual_performance_index',
        'final_performance_value',
        'work_result_rating',
        'performance_predicate',
        'performance_data_updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'performance_data_updated_at' => 'datetime',
    ];

    // --- RELASI ---

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan(): HasMany
    {
        return $this->hasMany(User::class, 'atasan_id');
    }

    public function jabatan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Jabatan::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the full hierarchical path of the user's unit.
     * MENGGUNAKAN CLOSURE TABLE UNTUK MENCEGAH REKURSIF
     * @return string
     */
    public function getUnitPathAttribute(): string
    {
        if (!$this->unit) {
            return '-';
        }

        // Use the Closure Table relationship to get ancestors in the correct order.
        $ancestors = $this->unit->ancestors()->orderBy('depth', 'asc')->get();
        $path = $ancestors->pluck('name')->toArray();
        $path[] = $this->unit->name;

        return implode(' - ', $path);
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




    // --- QUERY SCOPES ---

    public function scopeTeamMembers($query, User $manager)
    {
        if (!$manager->unit) {
            // Return a query that yields no results if the manager has no unit.
            return $query->whereRaw('1 = 0');
        }

        // Get IDs of all subordinate units.
        $unitIds = $manager->unit->getAllSubordinateUnitIds();
        // Add the manager's own unit ID to include colleagues.
        $unitIds[] = $manager->unit->id;

        // Chain the query conditions.
        return $query->whereIn('unit_id', array_unique($unitIds))
                     ->where('id', '!=', $manager->id);
    }

    public function scopeInUnitAndSubordinatesOf($query, User $manager)
    {
        if (!$manager->unit) {
            // If manager has no unit, scope to only themself.
            return $query->where('id', $manager->id);
        }

        $unitIds = $manager->unit->getAllSubordinateUnitIds();
        $unitIds[] = $manager->unit->id;

        return $query->whereIn('unit_id', array_unique($unitIds));
    }


    // --- FUNGSI BANTUAN & HAK AKSES ---
    
    public function isSubordinateOf(User $manager): bool
    {
        if (!$this->unit || !$manager->unit) {
            return false;
        }

        // Use the cached method to prevent N+1 performance issues.
        return in_array($this->unit->id, $manager->getSubordinateUnitIdsWithCache()->toArray());
    }

    /**
     * Get all subordinate unit IDs, with in-request caching to prevent N+1 problems.
     */
    public function getSubordinateUnitIdsWithCache(): \Illuminate\Support\Collection
    {
        if ($this->subordinateUnitIdsCache !== null) {
            return $this->subordinateUnitIdsCache;
        }

        if (!$this->unit) {
            return $this->subordinateUnitIdsCache = collect();
        }

        // Note: The toArray() and collect() might seem redundant, but it ensures
        // the result is a new collection instance, preventing accidental modification
        // of the original model's relations if it were an Eloquent collection.
        return $this->subordinateUnitIdsCache = collect($this->unit->getAllSubordinateUnitIds());
    }
    
    public function getAllSubordinateIds()
    {
        if (!$this->unit) {
            return collect();
        }

        $unitIds = $this->unit->getAllSubordinateUnitIds();
        return User::whereIn('unit_id', $unitIds)->pluck('id');
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
        return in_array($this->role, [self::ROLE_MENTERI, self::ROLE_SUPERADMIN, self::ROLE_ESELON_I, self::ROLE_ESELON_II, self::ROLE_KOORDINATOR]);
    }

    public function isTopLevelManager(): bool
    {
        return in_array($this->role, [self::ROLE_MENTERI, self::ROLE_SUPERADMIN, self::ROLE_ESELON_I, self::ROLE_ESELON_II]);
    }

    public function canManageUsers(): bool
    {
        // Delegated admin check
        if ($this->jabatan?->can_manage_users) {
            return true;
        }

        // Default role-based check
        return in_array($this->role, [self::ROLE_MENTERI, self::ROLE_SUPERADMIN, self::ROLE_ESELON_I, self::ROLE_ESELON_II, self::ROLE_KOORDINATOR]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isNotSuperAdmin(): bool
    {
        return !$this->isSuperAdmin();
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAF;
    }

    public function isManager(): bool
    {
        $isStructuralManager = in_array($this->role, [
            self::ROLE_MENTERI,
            self::ROLE_ESELON_I,
            self::ROLE_ESELON_II,
            self::ROLE_KOORDINATOR,
            self::ROLE_SUB_KOORDINATOR
        ]);

        // To prevent infinite recursion with HierarchicalScope, we query without it.
        // The ledProjects() relationship is on the Project model, which has the scope.
        $isFunctionalManager = $this->ledProjects()
                                    ->withoutGlobalScope(HierarchicalScope::class)
                                    ->exists();

        return $isStructuralManager || $isFunctionalManager;
    }
    
    // --- FORMULA PERHITUNGAN KINERJA (VERSI PRE-CALCULATED) ---

    /**
     * Mengambil Indeks Kinerja Individu (IKI) dari database.
     * Nilai ini dihitung oleh command 'app:calculate-performance-scores'.
     */
    public function getIndividualPerformanceIndexAttribute(): float
    {
        // Berikan nilai default jika belum pernah dihitung.
        return $this->attributes['individual_performance_index'] ?? 1.0;
    }

    /**
     * Mengambil Nilai Kinerja Final (NKF) dari database.
     * Nilai ini dihitung oleh command 'app:calculate-performance-scores'.
     */
    public function getFinalPerformanceValueAttribute(): float
    {
        // Berikan nilai default jika belum pernah dihitung.
        return $this->attributes['final_performance_value'] ?? 1.0;
    }

    /**
     * Mengambil Peringkat Hasil Kerja dari database.
     * Nilai ini dihitung oleh command 'app:calculate-performance-scores'.
     */
    public function getWorkResultRatingAttribute(): string
    {
        // Berikan nilai default jika belum pernah dihitung.
        return $this->attributes['work_result_rating'] ?? 'Sesuai Ekspektasi';
    }
    
    /**
     * Mengambil Predikat Kinerja (SKP) dari database.
     * Nilai ini dihitung oleh command 'app:calculate-performance-scores'.
     */
    public function getPerformancePredicateAttribute(): string
    {
        // Berikan nilai default jika belum pernah dihitung.
        return $this->attributes['performance_predicate'] ?? 'Baik';
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

    /**
     * Get the valid supervisor roles for a given subordinate role.
     * This centralizes the business logic for organizational hierarchy.
     *
     * @param string $subordinateRole
     * @return array|null
     */
    public static function getValidSupervisorRolesFor(string $subordinateRole): ?array
    {
        $validParentRolesMap = [
            self::ROLE_ESELON_II => [self::ROLE_ESELON_I],
            self::ROLE_KOORDINATOR => [self::ROLE_ESELON_II],
            self::ROLE_SUB_KOORDINATOR => [self::ROLE_KOORDINATOR],
            self::ROLE_STAF => [self::ROLE_KOORDINATOR, self::ROLE_SUB_KOORDINATOR],
        ];

        return $validParentRolesMap[$subordinateRole] ?? null;
    }
}