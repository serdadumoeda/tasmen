<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class JabatanController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Simple index method to list all jabatans.
        // In a real app, this would be paginated.
        $jabatans = Jabatan::with('unit', 'user')->latest()->get();
        return view('admin.jabatans.index', compact('jabatans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Unit::class);
        $units = Unit::orderBy('name')->get();
        $availableRoles = User::getAvailableRoles();
        return view('admin.jabatans.create', compact('units', 'availableRoles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Jabatan::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'can_manage_users' => ['nullable', 'boolean'],
            'role' => ['required', 'string', Rule::in(User::getAvailableRoles())],
        ]);

        $unit = Unit::find($validated['unit_id']);
        $this->authorize('update', $unit);

        $dataToCreate = [
            'name' => $validated['name'],
            'can_manage_users' => $request->has('can_manage_users'),
            'role' => $validated['role'],
        ];

        $unit->jabatans()->create($dataToCreate);

        return redirect()->route('admin.units.edit', $unit)->with('success', 'Jabatan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jabatan $jabatan)
    {
        $unit = $jabatan->unit;
        // The authorization might need to be adapted depending on the policy.
        // Assuming authorization is based on the unit.
        $this->authorize('update', $unit);

        $availableRoles = User::getAvailableRoles();
        $user = $jabatan->user;

        return view('admin.jabatans.edit', compact('jabatan', 'unit', 'user', 'availableRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jabatan $jabatan)
    {
        $unit = $jabatan->unit;
        $this->authorize('update', $unit);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('jabatans')->ignore($jabatan->id)],
            'can_manage_users' => ['nullable', 'boolean'],
            'role' => ['required', 'string', Rule::in(User::getAvailableRoles())],
        ]);

        $oldRole = $jabatan->role;
        $jabatan->name = $validated['name'];
        $jabatan->can_manage_users = $request->has('can_manage_users');
        $jabatan->role = $validated['role'];
        $jabatan->save();

        if ($oldRole !== $validated['role'] && $jabatan->user) {
            User::recalculateAndSaveRole($jabatan->user);
        }

        // Redirect back to the unit's edit page for a consistent user experience.
        return redirect()->route('admin.units.edit', $unit)->with('success', 'Jabatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jabatan $jabatan)
    {
        $unit = $jabatan->unit;
        $this->authorize('update', $unit);

        if ($jabatan->user_id) {
            return back()->with('error', 'Tidak dapat menghapus jabatan yang masih diisi oleh pengguna.');
        }

        $jabatan->delete();

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
