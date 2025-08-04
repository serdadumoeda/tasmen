<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UnitController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Unit::class);
        $units = Unit::whereNull('parent_unit_id')
                     ->with('childrenRecursive')
                     ->orderBy('name')
                     ->get();

        return view('admin.units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Unit::class);
        $units = Unit::orderBy('name')->get();
        return view('admin.units.create', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Unit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'level' => ['required', Rule::in(array_column(\App\Models\Unit::LEVELS, 'name'))],
            'parent_unit_id' => 'nullable|exists:units,id',
            'main_jabatan_name' => 'required|string|max:255',
        ]);

        $unit = Unit::create([
            'name' => $validated['name'],
            'level' => $validated['level'],
            'parent_unit_id' => $validated['parent_unit_id'],
        ]);

        $unit->jabatans()->create([
            'name' => $validated['main_jabatan_name'],
        ]);

        return redirect()->route('admin.units.index')->with('success', 'Unit dan Jabatan Pimpinan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        $this->authorize('view', $unit);
        $unit->load('users', 'parentUnit', 'childUnits');
        return view('admin.units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);
        $units = Unit::where('id', '!=', $unit->id)->orderBy('name')->get(); // Mencegah unit menjadi parent bagi dirinya sendiri
        $unit->load('jabatans.user'); // Eager load jabatans and the assigned user
        return view('admin.units.edit', compact('unit', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units')->ignore($unit->id)],
            'level' => ['required', Rule::in(array_column(\App\Models\Unit::LEVELS, 'name'))],
            'parent_unit_id' => 'nullable|exists:units,id',
        ]);

        // Simpan level lama untuk perbandingan
        $oldLevel = $unit->level;

        $unit->update($validated);

        // Jika level unit berubah, update role semua user di dalamnya
        if ($oldLevel !== $validated['level']) {
            $unit->users()->update(['role' => $validated['level']]);
        }

        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);

        if ($unit->users()->count() > 0 || $unit->childUnits()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus unit yang masih memiliki pengguna atau sub-unit.');
        }

        $unit->delete();

        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil dihapus.');
    }

    // --- JABATAN MANAGEMENT ---

    public function storeJabatan(Request $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $unit->jabatans()->create($validated);

        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function destroyJabatan(\App\Models\Jabatan $jabatan)
    {
        $unit = $jabatan->unit;
        $this->authorize('update', $unit);

        if ($jabatan->user_id) {
            return back()->with('error', 'Tidak dapat menghapus jabatan yang masih diisi oleh pengguna.');
        }

        $jabatan->delete();

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }

    // --- API METHODS ---

    public function getVacantJabatans(Unit $unit)
    {
        // This is for the chained dropdown in user creation form
        $vacantJabatans = $unit->jabatans()->whereNull('user_id')->get(['id', 'name']);

        return response()->json($vacantJabatans);
    }
}
