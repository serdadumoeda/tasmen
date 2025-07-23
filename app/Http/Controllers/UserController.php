<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', User::class);
        $currentUser = auth()->user();

        $usersQuery = User::with('unit');

        if ($currentUser->role !== User::ROLE_SUPERADMIN) {
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds[] = $currentUser->id;
            $usersQuery->whereIn('id', $subordinateIds);
        }

        $users = $usersQuery->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $eselon1Units = Unit::where('level', Unit::LEVEL_ESELON_I)->get();

        return view('users.create', compact('eselon1Units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:'.implode(',', [User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR, User::ROLE_SUB_KOORDINATOR, User::ROLE_STAF])],
            'unit_eselon_1' => ['nullable', 'exists:units,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $this->authorize('create', User::class);

        // Manual validation for role and unit
        $roleOrder = [User::ROLE_STAF => 0, User::ROLE_SUB_KOORDINATOR => 1, User::ROLE_KOORDINATOR => 2, User::ROLE_ESELON_II => 3, User::ROLE_ESELON_I => 4, User::ROLE_SUPERADMIN => 5];
        if ($roleOrder[$validated['role']] >= $roleOrder[auth()->user()->role]) {
            return back()->with('error', 'Anda tidak dapat membuat pengguna dengan role yang sama atau lebih tinggi dari Anda.')->withInput();
        }
        if (auth()->user()->unit && !in_array($validated['unit_id'], auth()->user()->unit->getAllSubordinateUnitIds())) {
            return back()->with('error', 'Anda hanya dapat membuat pengguna di dalam unit Anda atau unit bawahan.')->withInput();
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'unit_id' => $validated['unit_id'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }
    
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $eselon1Units = Unit::where('level', Unit::LEVEL_ESELON_I)->get();
        
        return view('users.edit', compact('user', 'eselon1Units'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:'.implode(',', [User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR, User::ROLE_SUB_KOORDINATOR, User::ROLE_STAF])],
            'unit_eselon_1' => ['nullable', 'exists:units,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'status' => ['required', 'in:active,suspended'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $this->authorize('update', $user);

        // Manual validation for role and unit
        $roleOrder = [User::ROLE_STAF => 0, User::ROLE_SUB_KOORDINATOR => 1, User::ROLE_KOORDINATOR => 2, User::ROLE_ESELON_II => 3, User::ROLE_ESELON_I => 4, User::ROLE_SUPERADMIN => 5];
        if (isset($validated['role']) && $roleOrder[$validated['role']] >= $roleOrder[auth()->user()->role]) {
            return back()->with('error', 'Anda tidak dapat memberikan role yang sama atau lebih tinggi dari Anda.')->withInput();
        }
        if (isset($validated['unit_id']) && auth()->user()->unit && !in_array($validated['unit_id'], auth()->user()->unit->getAllSubordinateUnitIds())) {
            return back()->with('error', 'Anda hanya dapat menempatkan pengguna di dalam unit Anda atau unit bawahan.')->withInput();
        }

        $user->fill($validated);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        // Cek jika user punya bawahan
        if (User::where('unit_id', $user->unit_id)->where('id', '!=', $user->id)->exists()) {
             // Cari unit atasan
             $parentUnit = $user->unit ? $user->unit->parentUnit : null;
             if ($parentUnit) {
                 User::where('unit_id', $user->unit_id)->update(['unit_id' => $parentUnit->id]);
             }
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function getWorkloadSummary(User $user)
    {
        $activeProjectsCount = $user->projects()
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', Carbon::today());
            })
            ->count();

        $activeAdhocTasksCount = $user->tasks()
                                     ->whereNull('project_id')
                                     ->where('status', '!=', 'Selesai')
                                     ->count();
        
        $activeSkCount = $user->getActiveSkCountAttribute();

        return response()->json([
            'success' => true,
            'data' => [
                'active_projects' => $activeProjectsCount,
                'active_adhoc_tasks' => $activeAdhocTasksCount,
                'active_sks' => $activeSkCount,
            ]
        ]);
    }

    public function search(Request $request)
    {
        $this->authorize('create', Project::class);

        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $users = User::where('name', 'ilike', "%{$query}%")
                    ->where('id', '!=', auth()->id())
                    ->limit(10)
                    ->get(['id', 'name', 'role']);

        return response()->json($users);
    }
}
