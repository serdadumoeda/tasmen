<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Unit extends Model
{
    use HasFactory, RecordsActivity;

    public const LEVEL_MENTERI = 'Menteri';
    public const LEVEL_ESELON_I = 'Eselon I';
    public const LEVEL_ESELON_II = 'Eselon II';
    public const LEVEL_KOORDINATOR = 'Koordinator';
    public const LEVEL_SUB_KOORDINATOR = 'Sub Koordinator';
    public const LEVEL_STAF = 'Staf';

    public const LEVELS = [
        ['name' => self::LEVEL_MENTERI],
        ['name' => self::LEVEL_ESELON_I],
        ['name' => self::LEVEL_ESELON_II],
        ['name' => self::LEVEL_KOORDINATOR],
        ['name' => self::LEVEL_SUB_KOORDINATOR],
    ];

    protected $fillable = [
        'name',
        'level',
        'type',
        'parent_unit_id',
        'kepala_unit_id',
    ];

    /**
     * Relasi ke unit induk (parent).
     */
    public function parentUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_unit_id');
    }

    /**
     * Relasi ke kepala unit.
     */
    public function kepalaUnit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_unit_id');
    }
    
    /**
     * Relasi ke unit anak (children).
     */
    public function childUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_unit_id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->childUnits()->with('childrenRecursive');
    }

    

    /**
     * Relasi rekursif untuk memuat semua anak unit secara hierarkis.
     * Penting: Menggunakan with() agar tidak terjadi infinite loop.
     */
    public function allChildren(): HasMany
    {
        return $this->childUnits()->with('allChildren');
    }

    public function jabatans(): HasMany
    {
        return $this->hasMany(Jabatan::class);
    }
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function approvalWorkflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }
    
    public function getAllSubordinateUnitIds(): array
    {
        return $this->descendants()->pluck('id')->toArray();
    }

    /**
     * Recursively get all descendant unit IDs using the parent-child relationship.
     * This is a more robust alternative to the `descendants()` relationship,
     * which can be incorrect if the `unit_paths` table is out of sync.
     *
     * @return array
     */
    public function getAllDescendantIds(): array
    {
        $descendantIds = [];

        // Use `with('childUnits')` to prevent N+1 issues during recursion.
        foreach ($this->childUnits()->with('childUnits')->get() as $child) {
            $descendantIds[] = $child->id;
            $descendantIds = array_merge($descendantIds, $child->getAllDescendantIds());
        }

        return array_unique($descendantIds);
    }
    
    public function getLevelNumber(): int
    {
        return match ($this->level) {
            self::LEVEL_MENTERI => 0,
            self::LEVEL_ESELON_I => 1,
            self::LEVEL_ESELON_II => 2,
            self::LEVEL_KOORDINATOR => 3,
            self::LEVEL_SUB_KOORDINATOR => 4,
            default => 99,
        };
    }
    
    // --- HIERARCHY MANAGEMENT (CLOSURE TABLE) ---
    
    /**
     * Rebuilds the entire unit hierarchy using a robust, non-recursive method.
     * This is the definitive method to ensure data integrity in the unit_paths table.
     * It is designed to be safe from stale Eloquent relationships and N+1 problems.
     *
     * @return array An array containing counts of processed units and paths.
     */
    public static function rebuildHierarchy(): array
    {
        $processedPaths = 0;
        $units = self::all()->keyBy('id');
        $totalUnits = $units->count();

        DB::transaction(function () use ($units, &$processedPaths) {
            DB::table('unit_paths')->truncate();

            if ($units->isEmpty()) {
                return;
            }

            foreach ($units as $unit) {
                // Start with the unit itself as an ancestor (depth 0).
                $ancestors = collect();
                $current = $unit;

                // Traverse up the tree using the parent_unit_id from the reliable collection.
                while ($current) {
                    $ancestors->push($current);
                    $current = $units->get($current->parent_unit_id);
                }

                // Insert the paths in reverse order (from root to the unit).
                $depth = 0;
                foreach ($ancestors->reverse() as $ancestor) {
                    DB::table('unit_paths')->insert([
                        'ancestor_id' => $ancestor->id,
                        'descendant_id' => $unit->id,
                        'depth' => $depth,
                    ]);
                    $processedPaths++;
                    $depth++;
                }
            }
        });

        return ['total_units' => $totalUnits, 'processed_paths' => $processedPaths];
    }

    /**
     * @deprecated Use rebuildHierarchy() instead. This method is flawed and will be removed.
     */
    public static function rebuildPaths()
    {
        self::rebuildHierarchy();
    }
    
    private static function insertPaths(Unit $unit)
    {
        // This method is now obsolete as its logic is contained within rebuildHierarchy.
        // It is kept temporarily to avoid breaking any other potential internal calls,
        // but it should be considered deprecated and removed in the future.
        \Illuminate\Support\Facades\DB::table('unit_paths')->insert([
            'ancestor_id' => $unit->id,
            'descendant_id' => $unit->id,
            'depth' => 0,
        ]);
    
        $depth = 1;
        $parent = $unit->parentUnit;
        while ($parent) {
            \Illuminate\Support\Facades\DB::table('unit_paths')->insert([
                'ancestor_id' => $parent->id,
                'descendant_id' => $unit->id,
                'depth' => $depth,
            ]);
            $parent = $parent->parentUnit;
            $depth++;
        }
    }
    
    public function disconnect()
    {
        \Illuminate\Support\Facades\DB::table('unit_paths')
            ->whereIn('descendant_id', $this->descendants()->pluck('id'))
            ->whereIn('ancestor_id', $this->ancestors()->where('id', '!=', $this->id)->pluck('id'))
            ->delete();
    
        foreach ($this->childUnits as $child) {
            $child->parent_unit_id = $this->parent_unit_id;
            $child->save();
        }
    }
    
    public function ancestors()
    {
        return $this->belongsToMany(self::class, 'unit_paths', 'descendant_id', 'ancestor_id');
    }
    
    public function descendants()
    {
        return $this->belongsToMany(self::class, 'unit_paths', 'ancestor_id', 'descendant_id')
                    ->where('depth', '>', 0);
    }

    /**
     * Get the Eselon II ancestor for this unit.
     *
     * @return Unit|null
     */
    public function getEselonIIAncestor(): ?Unit
    {
        // The depth of the unit itself relative to its own ancestors (i.e., its level in the hierarchy)
        $selfDepth = $this->ancestors()->count();

        // If this unit is an Eselon II unit (depth 2), return itself.
        // Depth is 0-indexed: 0=Menteri, 1=Eselon I, 2=Eselon II
        if ($selfDepth === 2) {
            return $this;
        }

        // Otherwise, find the ancestor that is at depth 2.
        // The 'depth' in the unit_paths table is relative from the ancestor to the descendant.
        // So we need to find an ancestor where the path from it to `this` unit has a certain depth.
        // A more direct way is to just find an ancestor whose own depth is 2.
        return $this->ancestors()->get()->first(function ($ancestor) {
            return $ancestor->ancestors()->count() === 2;
        });
    }

    /**
     * Recursive relationship to get all parent units.
     * This is used for efficient hierarchy traversal.
     */
    public function parentUnitRecursive()
    {
        return $this->parentUnit()->with('parentUnitRecursive');
    }

    /**
     * Determines the expected role for the head of this unit based on its hierarchy level.
     *
     * @return string|null The name of the role, or null if no specific role is mapped.
     */
    public function getExpectedHeadRole(): ?string
    {
        // The depth is the number of ancestors, which is 1-based (root is 1).
        $depth = $this->ancestors()->count();

        // Mapping from hierarchy depth to the expected role name for the HEAD of that unit.
        $roleMap = [
            1 => 'Menteri',          // A unit with depth 1 (root) is headed by a Menteri.
            2 => 'Eselon I',        // A unit with depth 2 is headed by an Eselon I.
            3 => 'Eselon II',       // A unit with depth 3 is headed by an Eselon II.
            4 => 'Koordinator',     // A unit with depth 4 is headed by a Koordinator.
            5 => 'Sub Koordinator', // A unit with depth 5 is headed by a Sub Koordinator.
        ];

        return $roleMap[$depth] ?? null;
    }

    /**
     * Get the displayable head of the unit, accounting for delegations.
     *
     * @return User|null
     */
    public function getDisplayableHeadAttribute(): ?User
    {
        // Priority 1: Return the definitive head (kepalaUnit) if one is assigned.
        if ($this->kepalaUnit) {
            $this->kepalaUnit->is_delegate = false;
            return $this->kepalaUnit;
        }

        // Priority 2: If the definitive head is vacant, look for an active delegation
        // specifically for the 'Kepala' position of this unit.

        // Construct the expected name for the head of unit's Jabatan.
        $kepalaJabatanName = 'Kepala ' . $this->name;

        // Find the specific 'Kepala' Jabatan from the eager-loaded collection.
        $kepalaJabatan = $this->jabatans->firstWhere('name', $kepalaJabatanName);

        // If we found the correct Jabatan, check for an active delegation.
        if ($kepalaJabatan) {
            $activeDelegation = $kepalaJabatan->delegations->first(function ($delegation) {
                $today = now()->startOfDay();
                return $delegation->start_date <= $today && $delegation->end_date >= $today;
            });

            if ($activeDelegation && $activeDelegation->user) {
                // If an active delegation is found, return the delegated user.
                // Add temporary attributes for display purposes.
                $delegatedUser = $activeDelegation->user;
                $delegatedUser->is_delegate = true;
                $delegatedUser->delegation_type = $activeDelegation->type;
                return $delegatedUser;
            }
        }

        // Priority 3: If no definitive head and no active delegation, the position is vacant.
        return null;
    }

    /**
     * Get the acting head of the unit, whether definitive or temporary (Plt./Plh.).
     * This is the single source of truth for determining who is in charge of a unit.
     *
     * @return User|null
     */
    public function getActingHead(): ?User
    {
        return $this->displayable_head;
    }
}