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
use App\Models\Role;
use App\Scopes\HierarchicalScope;
use App\Services\LeaveDurationService;

class User extends Authenticatable
{
    // Trait HasApiTokens sekarang akan ditemukan
    use HasApiTokens, HasFactory, Notifiable, RecordsActivity;

    // Cache for subordinate unit IDs to prevent N+1 issues in policies.
    public ?\Illuminate\Support\Collection $subordinateUnitIdsCache = null;

    protected ?self $cachedAtasanLangsung = null;
    protected bool $atasanLangsungResolved = false;

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
     * Get the direct supervisor, accounting for unit hierarchy and delegations.
     */
    public function getAtasanLangsung(): ?User
    {
        if ($this->atasanLangsungResolved) {
            return $this->cachedAtasanLangsung;
        }

        $this->loadMissing('atasan.jabatan');

        if ($this->atasan) {
            return $this->cacheAtasanLangsung($this->atasan);
        }

        $this->loadMissing(['unit.kepalaUnit.jabatan', 'unit.parentUnit.kepalaUnit.jabatan']);

        if ($this->unit) {
            if ($this->unit->kepalaUnit && $this->unit->kepalaUnit->id !== $this->id) {
                return $this->cacheAtasanLangsung($this->unit->kepalaUnit);
            }

            if ($this->unit->parentUnit && $this->unit->parentUnit->kepalaUnit) {
                return $this->cacheAtasanLangsung($this->unit->parentUnit->kepalaUnit);
            }
        }

        $this->loadMissing(['jabatan.parent.user.jabatan']);

        if (!$this->jabatan || !$this->jabatan->parent) {
            return $this->cacheAtasanLangsung(null);
        }

        $atasanJabatan = $this->jabatan->parent;

        $activeDelegation = Delegation::active()
            ->where('jabatan_id', $atasanJabatan->id)
            ->first();

        if ($activeDelegation) {
            $activeDelegation->loadMissing('user.jabatan');
            return $this->cacheAtasanLangsung($activeDelegation->user);
        }

        if ($atasanJabatan->user) {
            $atasanJabatan->user->loadMissing('jabatan');
        }

        return $this->cacheAtasanLangsung($atasanJabatan->user);
    }

    protected function cacheAtasanLangsung(?User $atasan): ?User
    {
        $this->atasanLangsungResolved = true;
        $this->cachedAtasanLangsung = $atasan;

        return $this->cachedAtasanLangsung;
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
        return $this->hasRole([
            'Menteri',
            'Superadmin',
            'Eselon I',
            'Eselon II',
            'Eselon III',
            'Eselon IV',
            'Koordinator',
            'Sub Koordinator',
        ]);
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
 * Get the user's initials.
 * VERSI FINAL EXTRA-KUAT: Membersihkan data nama secara agresif.
 *
 * @return string
 */
public function getInitialsAttribute(): string
{
    // 1. Ambil nama, pastikan tidak null.
    $name = $this->name ?? '';

    // 2. Hapus gelar atau teks apapun setelah koma.
    $name = preg_replace('/,.*$/', '', $name);

    // 3. (PENTING) Normalisasi semua jenis spasi (termasuk yang tidak terlihat) menjadi spasi tunggal.
    $name = preg_replace('/\s+/u', ' ', $name);

    // 4. (PENTING) Hapus semua karakter selain huruf, angka, dan spasi.
    $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);

    // 5. Trim spasi di awal/akhir setelah pembersihan.
    $name = trim($name);

    // Jika nama jadi kosong setelah dibersihkan, beri fallback.
    if (empty($name)) {
        return '??';
    }

    $words = explode(' ', $name);

    // Ambil huruf pertama dari kata pertama.
    $initials = mb_substr($words[0] ?? '', 0, 1);

    // Jika ada lebih dari satu kata, ambil huruf pertama dari kata kedua.
    if (count($words) > 1) {
        $initials .= mb_substr($words[1], 0, 1);
    }
    // Jika hanya satu kata dan panjang, ambil dua huruf pertama.
    elseif (mb_strlen($words[0]) > 1) {
        $initials = mb_substr($words[0], 0, 2);
    }

    // Fallback terakhir untuk memastikan output tidak pernah kosong.
    return empty(trim($initials)) ? '??' : strtoupper($initials);
}

/**
 * Get the background and text color for the user's avatar.
 * VERSI FINAL: Menghasilkan kode warna HEX untuk inline style.
 *
 * @return array
 */
public function getAvatarColorsAttribute(): array
{
    $colorPairs = [
        ['bg' => '#dc2626', 'text' => '#ffffff'], // Merah
        ['bg' => '#f97316', 'text' => '#ffffff'], // Oranye
        ['bg' => '#d97706', 'text' => '#ffffff'], // Kuning Tua
        ['bg' => '#16a34a', 'text' => '#ffffff'], // Hijau
        ['bg' => '#2563eb', 'text' => '#ffffff'], // Biru
        ['bg' => '#4f46e5', 'text' => '#ffffff'], // Indigo
        ['bg' => '#9333ea', 'text' => '#ffffff'], // Ungu
        ['bg' => '#db2777', 'text' => '#ffffff'], // Pink
        ['bg' => '#0d9488', 'text' => '#ffffff'], // Teal
    ];

    // Logika pemilihan warna tetap sama, sangat tangguh.
    if (isset($this->id) && $this->id > 0) {
        $index = $this->id % count($colorPairs);
    } else {
        $hash = crc32($this->name ?? 'fallback');
        $index = abs($hash) % count($colorPairs);
    }

    return $colorPairs[$index];
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
        $user->load('jabatan', 'unit');

        if ($user->unit && $user->unit->type === 'Struktural') {
            $structuralRole = self::mapEselonToRole($user->eselon ?? null);
            if ($structuralRole) {
                self::assignRoleByName($user, $structuralRole);
                return;
            }
        }

        $newRoleName = 'Staf';

        if ($user->jabatan && $user->jabatan->role) {
            $newRoleName = $user->jabatan->role;
        }

        self::assignRoleByName($user, $newRoleName);
    }

    public function leaveRequests() { return $this->hasMany(LeaveRequest::class); }
    public function leaveBalances() { return $this->hasMany(LeaveBalance::class); }

    /**
     * Sync the user's role based on their unit leadership status.
     * This function should ONLY run for users who are designated as a 'kepala_unit'.
     */
    public static function syncRoleFromUnit(User $user): void
    {
        // Find all units where this user is the designated head.
        $unitsHeaded = Unit::where('kepala_unit_id', $user->id)->get();

        // If the user is not a head of any unit, DO NOTHING.
        if ($unitsHeaded->isEmpty()) {
            return;
        }

        // If the user heads multiple units, find the one highest in the hierarchy (smallest depth).
        $highestUnit = $unitsHeaded->sortBy(function ($unit) {
            return $unit->getHierarchyDepth();
        })->first();

        if ($highestUnit && $highestUnit->type === 'Struktural') {
            $eselonRole = self::mapEselonToRole($user->eselon ?? null);
            if ($eselonRole) {
                self::assignRoleByName($user, $eselonRole);
                return;
            }
        }

        $depth = $highestUnit?->getHierarchyDepth() ?? 0;

        // Determine the base functional role based on the depth of the highest unit they lead.
        $newRoleName = match ($depth) {
            2 => 'Eselon I',
            3 => 'Eselon II',
            4 => 'Koordinator',
            5 => 'Sub Koordinator',
            default => 'Staf',
        };

        if ($highestUnit && $highestUnit->type === 'Struktural') {
            $roleMap = [
                'Koordinator' => 'Eselon III',
                'Sub Koordinator' => 'Eselon IV',
            ];

            if (isset($roleMap[$newRoleName])) {
                $newRoleName = $roleMap[$newRoleName];
            }
        }

        self::assignRoleByName($user, $newRoleName);
    }

    protected static function assignRoleByName(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->roles()->sync([$role->id]);
            return;
        }

        $stafRole = Role::where('name', 'Staf')->first();
        if ($stafRole) {
            $user->roles()->sync([$stafRole->id]);
        }
    }

    protected static function mapEselonToRole(?string $eselon): ?string
    {
        if (!$eselon) {
            return null;
        }

        $normalized = strtoupper($eselon);

        foreach (['IV', 'III', 'II', 'I'] as $roman) {
            if (str_contains($normalized, $roman)) {
                return 'Eselon ' . $roman;
            }
        }

        if (preg_match('/([1-4])/', $normalized, $matches)) {
            $map = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV'];
            $digit = $matches[1];

            return isset($map[$digit]) ? 'Eselon ' . $map[$digit] : null;
        }

        return null;
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
