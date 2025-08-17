<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Unit::class);
        
        // PENTING: Perbaiki eager loading di sini.
        // Kita perlu secara eksplisit memuat relasi yang akan digunakan di view
        // untuk setiap level hierarki.
        $units = Unit::with([
            'kepalaUnit',
            'parentUnit',
            'childrenRecursive' => function ($query) {
                // Muat relasi-relasi yang dibutuhkan untuk anak-anak
                $query->with('kepalaUnit', 'parentUnit');
            }
        ])
        ->whereNull('parent_unit_id')
        ->orderBy('name')
        ->get();

        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        $this->authorize('create', Unit::class);
        $units = Unit::orderBy('name')->get();
        return view('admin.units.create', compact('units'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Unit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'parent_unit_id' => 'nullable|exists:units,id',
        ]);

        Unit::create($validated);

        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil dibuat.');
    }

    public function show(Unit $unit)
    {
        $this->authorize('view', $unit);
        $unit->load('users', 'parentUnit', 'childUnits');
        return view('admin.units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);
        $units = Unit::where('id', '!=', $unit->id)->orderBy('name')->get();
        $unit->load('jabatans.user');
        return view('admin.units.edit', compact('unit', 'units'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units')->ignore($unit->id)],
            'parent_unit_id' => 'nullable|exists:units,id',
        ]);

        $newParentId = $request->input('parent_unit_id');
        if ($newParentId) {
            if ($newParentId == $unit->id) {
                 return back()->withInput()->withErrors(['parent_unit_id' => 'Sebuah unit tidak dapat menjadi induk bagi dirinya sendiri.']);
            }
            $subordinateIds = $unit->getAllSubordinateUnitIds();
            if (in_array($newParentId, $subordinateIds)) {
                return back()->withInput()->withErrors(['parent_unit_id' => 'Tidak dapat menetapkan unit ini sebagai anak dari salah satu turunannya sendiri.']);
            }
        }

        $unit->update($validated);

        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);

        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Hanya Superadmin yang dapat menghapus unit organisasi.');
        }

        try {
            DB::transaction(function () use ($unit) {
                $this->deleteUnitRecursively($unit);
            });

            return redirect()->route('admin.units.index')->with('success', 'Unit dan semua kontennya berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus unit: ' . $e->getMessage());
        }
    }

    private function deleteUnitRecursively(Unit $unit)
    {
        // 1. Hapus semua anak (sub-unit) terlebih dahulu
        foreach ($unit->childUnits as $child) {
            $this->deleteUnitRecursively($child);
        }

        // 2. Hapus semua konten di dalam unit ini
        $this->deleteUnitContents($unit);
    }

    private function deleteUnitContents(Unit $unit)
    {
        $unit->users()->chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->deleteUserAndItsRelations($user);
            }
        });

        $unit->jabatans()->delete();
        $unit->delete();
    }

    private function deleteUserAndItsRelations(\App\Models\User $user)
    {
        $user->projects()->detach();
        $user->tasks()->detach();
        $user->specialAssignments()->detach();

        $user->timeLogs()->delete();
        $user->notifications()->delete();

        if (method_exists($user, 'peminjamanRequests')) {
            $user->peminjamanRequests()->delete();
        }

        $user->delete();
    }

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

    public function getChildren(Unit $unit)
    {
        $children = $unit->childUnits()->orderBy('name')->get(['id', 'name']);
        return response()->json($children);
    }

    public function getVacantJabatans(Request $request, Unit $unit)
    {
        $userIdBeingEdited = $request->query('user_id');

        $query = $unit->jabatans()->where(function ($q) use ($userIdBeingEdited) {
            $q->whereNull('user_id');
            if ($userIdBeingEdited) {
                $q->orWhere('user_id', $userIdBeingEdited);
            }
        });

        $jabatans = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($jabatans);
    }
}