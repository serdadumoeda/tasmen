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
use App\Models\JabatanHistory;
use App\Models\Delegation;
use App\Scopes\HierarchicalScope;
use App\Services\LeaveDurationService;

class User extends Authenticatable
{
    // Trait HasApiTokens sekarang akan ditemukan
    use HasApiTokens, HasFactory, Notifiable, RecordsActivity;

    // Cache for subordinate unit IDs to prevent N+1 issues in policies.
    public ?\Illuminate\Support\Collection $subordinateUnitIdsCache = null;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'email',
        'nik',
        'password',
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
        'signature_image_path',
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

    /**
     * Get the direct supervisor, accounting for delegations (Plt./Plh.).
     *
     * @return User|null
     */
    public function getAtasanLangsung(): ?User
    {
        if (!$this->jabatan || !$this->jabatan->parent) {
            return null; // No position or no parent position
        }

        $atasanJabatan = $this->jabatan->parent;

        // 1. Check for an active delegation for the supervisor's position
        $activeDelegation = Delegation::active()
            ->where('jabatan_id', $atasanJabatan->id)
            ->first();

        if ($activeDelegation) {
            return $activeDelegation->user; // Return the delegated user (Plt./Plh.)
        }

        // 2. If no delegation, return the definitive office holder
        return $atasanJabatan->user;
    }

    public function jabatan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Jabatan::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function jabatanHistory(): HasMany
    {
        return $this->hasMany(JabatanHistory::class)->latest('start_date');
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class);
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

    public function hasRole(string|array $roleNames): bool
    {
        // Eager load roles if they haven't been loaded yet.
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        if (is_string($roleNames)) {
            return $this->roles->contains('name', $roleNames);
        }

        foreach ($roleNames as $roleName) {
            if ($this->roles->contains('name', $roleName)) {
                return true;
            }
        }
        return false;
    }
    
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
        // Also include the current user's own unit ID to fetch colleagues
        $unitIds[] = $this->unit->id;

        return User::whereIn('unit_id', array_unique($unitIds))->pluck('id');
    }
    
    public function getAllSubordinates($includeSelf = false)
    {
        if (!$this->unit) {
            return collect();
        }

        $unitIds = $this->unit->getAllSubordinateUnitIds();
        // Also include the current user's own unit ID to fetch colleagues
        $unitIds[] = $this->unit->id;

        $query = User::whereIn('unit_id', array_unique($unitIds));

        if (!$includeSelf) {
            $query->where('id', '!=', $this->id);
        }

        return $query->get();
    }

    public function canCreateProjects(): bool
    {
        return $this->hasRole(['Menteri', 'Superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
    }

    public function isTopLevelManager(): bool
    {
        return $this->hasRole(['Menteri', 'Superadmin', 'Eselon I', 'Eselon II']);
    }

    public function canManageUsers(): bool
    {
        // Delegated admin check
        if ($this->jabatan?->can_manage_users) {
            return true;
        }

        // Default role-based check
        return $this->hasRole(['Menteri', 'Superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
    }

    public function canManageLeaveSettings(): bool
    {
        return $this->isSuperAdmin() || ($this->jabatan && $this->jabatan->can_manage_users);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    public function isNotSuperAdmin(): bool
    {
        return !$this->isSuperAdmin();
    }

    public function isStaff(): bool
    {
        return $this->hasRole('Staf');
    }

    public function isManager(): bool
    {
        $isStructuralManager = $this->hasRole(['Menteri', 'Eselon I', 'Eselon II', 'Koordinator', 'Sub Koordinator']);

        // To prevent infinite recursion with HierarchicalScope, we query without it.
        // The ledProjects() relationship is on the Project model, which has the scope.
        $isFunctionalManager = $this->ledProjects()
                                    ->withoutGlobalScope(HierarchicalScope::class)
                                    ->exists();

        return $isStructuralManager || $isFunctionalManager;
    }

    /**
     * Get the user's initials from their name.
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        // Strip out academic titles and other suffixes starting with a comma.
        $name = trim(preg_replace('/,.*$/', '', $this->name));

        if (empty($name)) {
            return '??';
        }

        // Use a regex to split by any whitespace and filter out empty parts.
        $words = array_values(array_filter(preg_split('/\s+/', $name)));

        if (empty($words)) {
            return '??';
        }

        $firstName = $words[0];
        $secondName = $words[1] ?? '';

        $initials = mb_substr($firstName, 0, 1);

        if (!empty($secondName)) {
            $initials .= mb_substr($secondName, 0, 1);
        } elseif (mb_strlen($firstName) > 1) {
            // Fallback for single-word names: use the first two letters.
            $initials .= mb_substr($firstName, 1, 1);
        }

        // Final fallback to ensure a non-empty string is always returned.
        if (empty(trim($initials))) {
            return '??';
        }

        return strtoupper($initials);
    }

    /**
     * Get the color classes for the user's avatar.
     *
     * @return string
     */
    public function getAvatarColorClassesAttribute(): string
    {
        // Daftar kelas warna dengan kontras yang baik
        $colors = [
            'bg-red-500 text-white',
            'bg-blue-500 text-white',
            'bg-green-500 text-white',
            'bg-yellow-500 text-gray-800',
            'bg-indigo-500 text-white',
            'bg-purple-500 text-white',
            'bg-pink-500 text-white',
            'bg-teal-500 text-white',
            'bg-orange-500 text-white',
        ];

        // Jika id ada dan bukan 0, gunakan id untuk konsistensi
        if ($this->id) {
            $index = $this->id % count($colors);
        } else {
            // Fallback: Gunakan checksum dari nama untuk mendapatkan indeks acak yang konsisten
            $hash = crc32($this->name);
            $index = abs($hash) % count($colors);
        }

        return $colors[$index];
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

    public function getInternalTasksHoursAttribute()
    {
        // Tugas internal adalah semua tugas ad-hoc (tanpa proyek) ditambah tugas proyek
        // di mana pemimpin proyek berasal dari unit yang sama dengan pengguna.
        $adHocHours = $this->total_ad_hoc_hours;

        $internalProjectHours = $this->tasks()
            ->whereHas('project.leader', function ($query) {
                $query->where('unit_id', $this->unit_id);
            })
            ->sum('estimated_hours');

        return $adHocHours + $internalProjectHours;
    }

    public function getExternalTasksHoursAttribute()
    {
        // Tugas eksternal adalah tugas proyek di mana pemimpin proyek BUKAN dari unit yang sama.
        return $this->tasks()
            ->whereHas('project.leader', function ($query) {
                $query->where('unit_id', '!=', $this->unit_id);
            })
            ->sum('estimated_hours');
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
        // This logic may need to be updated to use Role levels instead of names
        // For now, we keep it as is, but acknowledge it's a candidate for further refactoring.
        $validParentRolesMap = [
            'Eselon II' => ['Eselon I'],
            'Koordinator' => ['Eselon II'],
            'Sub Koordinator' => ['Koordinator'],
            'Staf' => ['Koordinator', 'Sub Koordinator'],
        ];

        return $validParentRolesMap[$subordinateRole] ?? null;
    }

    /**
     * Get the list of available roles for assignment.
     *
     * @return array
     */
    public static function getAvailableRoles(): array
    {
        // This could also query the roles table, but a static list is fine for now.
        return ['Staf', 'Sub Koordinator', 'Koordinator', 'Eselon IV', 'Eselon III', 'Eselon II', 'Eselon I', 'Menteri', 'Superadmin'];
    }

    /**
     * Recalculate and save the user's role based on their current Jabatan.
     *
     * @param User $user
     * @return void
     */
    public static function recalculateAndSaveRole(User $user): void
    {
        $user->load('jabatan');

        $newRoleName = 'Staf'; // Default to 'Staf' if no specific role is found

        if ($user->jabatan && $user->jabatan->role) {
            $newRoleName = $user->jabatan->role;
        }

        // Find the role model by name
        $newRole = Role::where('name', $newRoleName)->first();

        if ($newRole) {
            // Sync the new role, replacing any old ones.
            $user->roles()->sync([$newRole->id]);
        } else {
            // If the role from Jabatan is not found in the roles table,
            // default to Staf as a fallback.
            $stafRole = Role::where('name', 'Staf')->first();
            if ($stafRole) {
                $user->roles()->sync([$stafRole->id]);
            }
        }
    }

    public function leaveRequests() { return $this->hasMany(LeaveRequest::class); }
    public function leaveBalances() { return $this->hasMany(LeaveBalance::class); }

    /**
     * Sync the user's role based on their unit leadership status.
     */
    public static function syncRoleFromUnit(User $user): void
    {
        $user->load('unit'); // Explicitly reload the relationship to prevent using stale data.
        if (!$user->unit) {
            $stafRole = Role::where('name', 'Staf')->first();
            if ($stafRole) {
                $user->roles()->sync([$stafRole->id]);
            }
            return;
        }

        $isHeadOfUnit = $user->unit->kepala_unit_id === $user->id;
        $newRoleName = 'Staf'; // Default role

        if ($isHeadOfUnit) {
            // The `ancestors()` method includes the unit itself, so the depth count is off by 1.
            // A top-level unit has 1 ancestor (itself). A child of it has 2, and so on.
            // We adjust the depth check to match this 1-based index.
            $depth = $user->unit->ancestors()->count();
            $isStruktural = $user->unit->type === 'Struktural';

            $newRoleName = match (true) {
                $depth === 2 => 'Eselon I', // Level 1 parent + self = 2
                $depth === 3 => 'Eselon II', // Level 2 parents + self = 3
                $depth === 4 && $isStruktural => 'Eselon III',
                $depth === 4 && !$isStruktural => 'Koordinator',
                $depth === 5 && $isStruktural => 'Eselon IV',
                $depth === 5 && !$isStruktural => 'Sub Koordinator',
                default => 'Staf',
            };
        }

        $newRole = Role::where('name', $newRoleName)->first();
        if ($newRole) {
            $user->roles()->sync([$newRole->id]);
        }
    }

    /**
     * Calculate effective working hours for a user within a date range, accounting for leave.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return float
     */
    public function getEffectiveWorkingHours(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): float
    {
        $hoursPerDay = 7.5; // Based on 37.5 hours / 5 days

        // Calculate total possible workdays in the period using the service
        $totalWorkdaysInPeriod = LeaveDurationService::calculate($startDate, $endDate);

        // Find approved leave requests that overlap with the period
        $approvedLeaveDays = $this->leaveRequests()
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->get()
            ->sum(function ($leave) use ($startDate, $endDate) {
                // Clamp the leave period to the query's date range
                $effectiveStart = Carbon::parse($leave->start_date)->max($startDate);
                $effectiveEnd = Carbon::parse($leave->end_date)->min($endDate);

                // Calculate the actual workdays for the overlapping leave period using the service
                return LeaveDurationService::calculate($effectiveStart, $effectiveEnd);
            });

        $netWorkdays = $totalWorkdaysInPeriod - $approvedLeaveDays;

        return max(0, $netWorkdays * $hoursPerDay);
    }
}