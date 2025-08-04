<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Unit extends Model
{
    use HasFactory;

    public const LEVEL_ESELON_I = 'Eselon I';
    public const LEVEL_ESELON_II = 'Eselon II';
    public const LEVEL_KOORDINATOR = 'Koordinator';
    public const LEVEL_SUB_KOORDINATOR = 'Sub Koordinator';
    public const LEVEL_STAF = 'Staf'; // Note: This is a user role, not a unit level in the migration

    public const LEVELS = [
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
        return Cache::remember('subordinate_unit_ids_for_unit_'.$this->id, 3600, function () {
            $ids = [];
            $queue = [$this];
            $visited = [];

            while (!empty($queue)) {
                $current = array_shift($queue);

                if (in_array($current->id, $visited)) {
                    continue; // Lewati jika sudah dikunjungi untuk mencegah infinite loop
                }

                $ids[] = $current->id;
                $visited[] = $current->id;

                // Eager load child units to avoid N+1 problem
                $current->load('childUnits');

                foreach ($current->childUnits as $child) {
                    $queue[] = $child;
                }
            }
            return $ids;
        });
    }

    public function getLevelNumber(): int
    {
        return match ($this->level) {
            self::LEVEL_ESELON_I => 1,
            self::LEVEL_ESELON_II => 2,
            self::LEVEL_KOORDINATOR => 3,
            self::LEVEL_SUB_KOORDINATOR => 4,
            default => 0,
        };
    }
}
