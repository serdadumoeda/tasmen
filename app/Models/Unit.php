<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    public const LEVEL_ESELON_I = 'Eselon I';
    public const LEVEL_ESELON_II = 'Eselon II';
    public const LEVEL_KOORDINATOR = 'Koordinator';
    public const LEVEL_SUB_KOORDINATOR = 'Sub Koordinator';

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

    public function getAllSubordinateUnitIds(): array
    {
        return Cache::remember('subordinate_unit_ids_for_unit_'.$this->id, 3600, function () {
            $subordinateIds = [$this->id];

            foreach ($this->childUnits as $childUnit) {
                $subordinateIds = array_merge($subordinateIds, $childUnit->getAllSubordinateUnitIds());
            }

            return $subordinateIds;
        });
    }
}
