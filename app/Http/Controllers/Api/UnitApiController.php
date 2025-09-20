<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitApiController extends Controller
{
    public function getEselonIUnits()
    {
        $rootUnit = Unit::whereNull('parent_unit_id')->first();
        $eselonIUnits = $rootUnit ? $rootUnit->childUnits()->orderBy('name')->get() : collect();
        return response()->json($eselonIUnits);
    }

    public function getChildUnits($parentUnitId)
    {
        $parentUnit = Unit::find($parentUnitId);

        if (!$parentUnit) {
            return response()->json([]);
        }

        $childUnits = $parentUnit->childUnits()->orderBy('name')->get();
        return response()->json($childUnits);
    }
}
