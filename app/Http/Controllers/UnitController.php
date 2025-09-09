<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\ApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\PageTitleService;
use App\Services\BreadcrumbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Delegation;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Validation\ValidationException;

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
            'type' => ['required', Rule::in(['Struktural', 'Fungsional'])],
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
        $unit->load('jabatans.user', 'users', 'approvalWorkflow');
        $usersInUnit = $unit->users()->orderBy('name')->get();
        $allUsers = User::orderBy('name')->get(); // Fetch all users for delegation dropdown
        $workflows = ApprovalWorkflow::orderBy('name')->get();

        return view('admin.units.edit', compact('unit', 'units', 'usersInUnit', 'allUsers', 'workflows'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units')->ignore($unit->id)],
            'parent_unit_id' => 'nullable|exists:units,id',
            'kepala_unit_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', Rule::in(['Struktural', 'Fungsional'])],
            'approval_workflow_id' => ['nullable', 'exists:approval_workflows,id'],
        ]);

        // Additional check to ensure the selected head is actually a member of the unit.
        if ($request->filled('kepala_unit_id') && !$unit->users()->where('id', $request->kepala_unit_id)->exists()) {
            return back()->withInput()->withErrors(['kepala_unit_id' => 'Pengguna yang dipilih bukan anggota unit ini.']);
        }

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

        $oldKepalaUnitId = $unit->kepala_unit_id;

        $unit->update($validated);

        $newKepalaUnitId = $unit->fresh()->kepala_unit_id;

        // Recalculate roles if the head of unit has changed.
        if ($oldKepalaUnitId !== $newKepalaUnitId) {
            if ($oldKepalaUnitId) {
                $oldKepala = \App\Models\User::find($oldKepalaUnitId);
                if ($oldKepala) {
                    \App\Models\User::syncRoleFromUnit($oldKepala);
                }
            }
            if ($newKepalaUnitId) {
                $newKepala = \App\Models\User::find($newKepalaUnitId);
                if ($newKepala) {
                    \App\Models\User::syncRoleFromUnit($newKepala);
                }
            }
        }

        return redirect()->route('admin.units.edit', $unit)->with('success', 'Unit berhasil diperbarui.');
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

    public function showWorkflow(PageTitleService $pageTitleService, BreadcrumbService $breadcrumbService)
    {
        $pageTitleService->setTitle('Alur Kerja Manajemen Unit');
        $breadcrumbService->add('Manajemen Unit', route('admin.units.index'));
        $breadcrumbService->add('Alur Kerja');
        return view('admin.units.workflow');
    }

    public function storeUnitDelegation(Request $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:Plt,Plh',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $userToDelegate = User::with('jabatan.unit')->findOrFail($validated['user_id']);
        $unitLevel = $unit->getLevelNumber();
        $userLevel = $userToDelegate->jabatan ? $userToDelegate->jabatan->unit->getLevelNumber() : 99; // 99 for staf/no unit

        if ($userLevel > $unitLevel) {
            throw ValidationException::withMessages([
                'user_id' => 'Pengguna yang didelegasikan harus memiliki level jabatan yang setara atau lebih tinggi.',
            ]);
        }

        // Find or create the "Kepala" Jabatan for this unit.
        $kepalaJabatan = Jabatan::firstOrCreate(
            [
                'name' => 'Kepala ' . $unit->name,
                'unit_id' => $unit->id,
            ],
            [
                'role' => $unit->level ?? 'Staf'
            ]
        );

        // Check if there's already a definitive head for this position
        if ($kepalaJabatan->user_id) {
             return back()->with('error', 'Jabatan Kepala Unit ini sudah memiliki pejabat definitif.');
        }

        Delegation::create([
            'jabatan_id' => $kepalaJabatan->id,
            'user_id' => $validated['user_id'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.units.edit', $unit)->with('success', 'Delegasi untuk Kepala Unit berhasil dibuat.');
    }
}