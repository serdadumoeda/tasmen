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
                     ->with(['childrenRecursive.kepalaUnit', 'kepalaUnit'])
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
            'parent_unit_id' => 'nullable|exists:units,id',
        ]);

        Unit::create($validated);

        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil dibuat.');
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
            'parent_unit_id' => 'nullable|exists:units,id',
        ]);

        // Anti-loop validation
        $newParentId = $request->input('parent_unit_id');
        if ($newParentId) {
            // A unit cannot be its own parent.
            if ($newParentId == $unit->id) {
                 return back()->withInput()->withErrors(['parent_unit_id' => 'Sebuah unit tidak dapat menjadi induk bagi dirinya sendiri.']);
            }
            // A unit cannot be a child of one of its own descendants.
            $subordinateIds = $unit->getAllSubordinateUnitIds();
            if (in_array($newParentId, $subordinateIds)) {
                return back()->withInput()->withErrors(['parent_unit_id' => 'Tidak dapat menetapkan unit ini sebagai anak dari salah satu turunannya sendiri.']);
            }
        }

        $unit->update($validated);

        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);

        // Hard-coded authorization check to ensure only superadmins can delete units.
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Hanya Superadmin yang dapat menghapus unit organisasi.');
        }

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
        $user->notifications()->delete();

        // Asumsi ada model PeminjamanRequest yang berelasi dengan user
        if (method_exists($user, 'peminjamanRequests')) {
            $user->peminjamanRequests()->delete();
        }

        // Komentar dan lampiran terikat pada Task, bukan User secara langsung.
        // Menghapusnya di sini akan rumit dan mungkin tidak diinginkan jika tugas
        // itu sendiri tidak dihapus.

        // c. Hapus objek yang dimiliki/dipimpin oleh user
        // PENTING: Logika ini terlalu destruktif dan dinonaktifkan. Menghapus user tidak seharusnya menghapus proyek.
        // Proyek harus dialihkan secara manual atau melalui fitur terpisah.
        // \App\Models\Project::where('leader_id', $user->id)->orWhere('owner_id', $user->id)->delete();

        // Lakukan hal yang sama untuk Task jika ada kolom creator_id
        // \App\Models\Task::where('creator_id', $user->id)->delete();

        // d. Update atasan untuk bawahan
        // Logika ini tidak diperlukan dan berpotensi salah.
        // Foreign key constraint `onDelete('set null')` pada tabel users sudah menangani ini secara otomatis dan aman di level database.
        // \App\Models\User::where('atasan_id', $user->id)->update(['atasan_id' => $user->atasan_id]);

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
