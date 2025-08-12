<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

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

        // Validasi anti-loop hirarki
        $newParentId = $request->input('parent_unit_id');
        if ($newParentId) {
            $subordinateIds = $unit->getAllSubordinateUnitIds();
            // Sebuah unit tidak bisa menjadi parent bagi dirinya sendiri atau turunannya.
            if (in_array($newParentId, $subordinateIds)) {
                return back()->withInput()->withErrors(['parent_unit_id' => 'Tidak dapat menetapkan unit ini sebagai anak dari salah satu turunannya sendiri.']);
            }
        }

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

        try {
            DB::transaction(function () use ($unit) {
                $this->deleteUnitRecursively($unit);
            });

            return redirect()->route('admin.units.index')->with('success', 'Unit dan semua kontennya (termasuk sub-unit, jabatan, dan pengguna) berhasil dihapus.');

        } catch (\Exception $e) {
            // Tangani jika ada error selama transaksi
            return back()->with('error', 'Terjadi kesalahan saat menghapus unit: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus unit dan semua kontennya secara rekursif.
     * Bekerja dari bawah ke atas (bottom-up).
     *
     * @param Unit $unit
     */
    private function deleteUnitRecursively(Unit $unit)
    {
        // 1. Hapus semua anak (sub-unit) terlebih dahulu
        foreach ($unit->childUnits as $child) {
            $this->deleteUnitRecursively($child);
        }

        // 2. Hapus semua konten di dalam unit ini
        $this->deleteUnitContents($unit);
    }

    /**
     * Menghapus semua konten yang terkait langsung dengan sebuah unit.
     * (Pengguna, Jabatan, dll.)
     *
     * @param Unit $unit
     */
    private function deleteUnitContents(Unit $unit)
    {
        // Ambil semua user dalam unit ini. Kita gunakan chunk untuk efisiensi memori jika user sangat banyak.
        $unit->users()->chunk(100, function ($users) {
            foreach ($users as $user) {
                // Hapus semua relasi dan data milik user
                $this->deleteUserAndItsRelations($user);
            }
        });

        // Hapus semua jabatan di dalam unit ini
        $unit->jabatans()->delete();

        // Terakhir, hapus unit itu sendiri
        $unit->delete();
    }

    /**
     * Menghapus seorang user beserta semua data dan relasi yang terkait dengannya.
     *
     * @param \App\Models\User $user
     */
    private function deleteUserAndItsRelations(\App\Models\User $user)
    {
        // a. Putuskan relasi Many-to-Many
        $user->projects()->detach();
        $user->tasks()->detach();
        $user->specialAssignments()->detach();

        // b. Hapus data HasMany yang terkait langsung
        $user->timeLogs()->delete();
        // Asumsi ada model Comment, Attachment, Notification, PeminjamanRequest
        // Jika tidak ada, baris ini bisa di-comment atau dihapus.
        // $user->comments()->delete();
        // $user->attachments()->delete();
        // $user->notifications()->delete();
        // $user->peminjamanRequests()->delete();

        // c. Hapus objek yang dimiliki/dipimpin oleh user
        // PENTING: Keputusan bisnis adalah menghapus, bukan me-reassign.
        \App\Models\Project::where('leader_id', $user->id)->orWhere('owner_id', $user->id)->delete();
        // Lakukan hal yang sama untuk Task jika ada kolom creator_id
        // \App\Models\Task::where('creator_id', $user->id)->delete();

        // d. Update atasan untuk bawahan
        \App\Models\User::where('atasan_id', $user->id)->update(['atasan_id' => $user->atasan_id]);

        // e. Jabatan sudah akan terhapus saat unit dihapus,
        // jadi kita tidak perlu mengosongkannya di sini.

        // f. Hapus user
        $user->delete();
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

    public function getChildren(Unit $unit)
    {
        // We only need id and name for the dropdown
        $children = $unit->childUnits()->orderBy('name')->get(['id', 'name']);
        return response()->json($children);
    }

    public function getVacantJabatans(Unit $unit)
    {
        // This is for the chained dropdown in user creation form
        $vacantJabatans = $unit->jabatans()->whereNull('user_id')->get(['id', 'name']);

        return response()->json($vacantJabatans);
    }
}
