<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitApiController extends Controller
{
    public function getEselonIUnits()
    {
        $units = Unit::where('level', Unit::LEVEL_ESELON_I)->get();
        return response()->json($units);
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
