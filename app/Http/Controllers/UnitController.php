<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::with('parentUnit')->get();
        return view('admin.units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $units = Unit::all();
        return view('admin.units.create', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request)
    {
        Unit::create($request->validated());
        return redirect()->route('admin.units.index')->with('success', 'Unit created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return view('admin.units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        $units = Unit::where('id', '!=', $unit->id)->get();
        return view('admin.units.edit', compact('unit', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        return redirect()->route('admin.units.index')->with('success', 'Unit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('admin.units.index')->with('success', 'Unit deleted successfully.');
    }
}
