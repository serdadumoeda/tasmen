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
    public const LEVEL_STAF = 'Staf'; // Note: This is a user role, not a unit level in the migration

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
        'parent_unit_id',
    ];

    public function parentUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_unit_id');
    }

    public function childUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_unit_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function jabatans(): HasMany
    {
        return $this->hasMany(Jabatan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function children()
    {
        return $this->hasMany(Unit::class, 'parent_unit_id');
    }

    public function getAllSubordinateUnitIds(): array
    {
        // Use the new, performant Closure Table relationship
        return $this->descendants()->pluck('id')->toArray();
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
     * Rebuild the entire unit_paths table.
     * Should be called after manual database changes or for initial setup.
     */
    public static function rebuildPaths()
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            \Illuminate\Support\Facades\DB::table('unit_paths')->truncate();
            $units = self::all();
            foreach ($units as $unit) {
                self::insertPaths($unit);
            }
        });
    }

    /**
     * Insert the hierarchy paths for a single unit.
     * @param Unit $unit
     */
    private static function insertPaths(Unit $unit)
    {
        // Insert path to self
        \Illuminate\Support\Facades\DB::table('unit_paths')->insert([
            'ancestor_id' => $unit->id,
            'descendant_id' => $unit->id,
            'depth' => 0,
        ]);

        // Insert paths from ancestors
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

    /**
     * Detach a unit from its parent and re-attach its children to its grandparent.
     */
    public function disconnect()
    {
        // Delete paths from this unit's ancestors to its descendants
        \Illuminate\Support\Facades\DB::table('unit_paths')
            ->whereIn('descendant_id', $this->descendants()->pluck('id'))
            ->whereIn('ancestor_id', $this->ancestors()->where('id', '!=', $this->id)->pluck('id'))
            ->delete();

        // Re-link children to the grandparent
        foreach ($this->childUnits as $child) {
            $child->parent_unit_id = $this->parent_unit_id;
            $child->save(); // This will trigger path rebuilding if the model is observed
        }
    }

    public function ancestors()
    {
        return $this->belongsToMany(self::class, 'unit_paths', 'descendant_id', 'ancestor_id');
    }

    public function descendants()
    {
        return $this->belongsToMany(self::class, 'unit_paths', 'ancestor_id', 'descendant_id');
    }
}
