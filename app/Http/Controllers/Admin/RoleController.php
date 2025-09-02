<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_settings');
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        Gate::authorize('manage_settings');
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|alpha_dash',
            'label' => 'required|string|max:255',
            'managerial_weight' => 'required|numeric|min:0|max:1',
        ]);

        Role::create($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Peran baru berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        Gate::authorize('manage_settings');
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('manage_settings');
        $validated = $request->validate([
            'name' => 'required|string|alpha_dash|unique:roles,name,' . $role->id,
            'label' => 'required|string|max:255',
            'managerial_weight' => 'required|numeric|min:0|max:1',
        ]);

        $role->update($validated);

        return redirect()->route('admin.roles.index')->with('success', 'Peran berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('manage_settings');

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus peran yang sedang digunakan oleh pengguna.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Peran berhasil dihapus.');
    }
}
