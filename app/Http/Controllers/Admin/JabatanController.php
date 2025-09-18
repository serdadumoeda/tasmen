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

        $jabatan = $unit->jabatans()->create($dataToCreate);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil ditambahkan.',
                'html' => view('admin.jabatans.partials._jabatan-list-item', compact('jabatan'))->render()
            ]);
        }

        return redirect()->route('admin.units.edit', $unit)->with('success', 'Jabatan berhasil ditambahkan.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jabatan $jabatan)
    {
        $this->authorize('update', $jabatan->unit);
        // No need to fetch roles here as we are not changing the role in this form.
        return view('admin.jabatans.edit', compact('jabatan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jabatan $jabatan)
    {
        $this->authorize('update', $jabatan->unit);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'can_manage_users' => ['nullable', 'boolean'],
        ]);

        $jabatan->update([
            'name' => $validated['name'],
            'can_manage_users' => $request->has('can_manage_users'),
        ]);

        return redirect()->route('admin.units.edit', $jabatan->unit_id)->with('success', 'Jabatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Jabatan $jabatan)
    {
        $unit = $jabatan->unit;
        $this->authorize('update', $unit);

        if ($jabatan->user_id) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus jabatan yang masih diisi oleh pengguna.'
                ], 422);
            }
            return back()->with('error', 'Tidak dapat menghapus jabatan yang masih diisi oleh pengguna.');
        }

        $jabatan->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil dihapus.'
            ]);
        }

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
