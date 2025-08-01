<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use AuthorizesRequests;

    private $VALID_PARENT_ROLES = [
        Unit::LEVEL_ESELON_II => [Unit::LEVEL_ESELON_I],
        Unit::LEVEL_KOORDINATOR => [Unit::LEVEL_ESELON_II],
        Unit::LEVEL_SUB_KOORDINATOR => [Unit::LEVEL_KOORDINATOR],
        Unit::LEVEL_STAF => [Unit::LEVEL_KOORDINATOR, Unit::LEVEL_SUB_KOORDINATOR],
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('unit')->orderBy('name');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }
    
    public function hierarchy()
    {
        $this->authorize('viewAny', User::class);
        
        $loggedInUser = Auth::user();

        if ($loggedInUser->role === User::ROLE_SUPERADMIN) {
            // Untuk Superadmin, tampilkan seluruh hirarki dengan mengambil user dari unit teratas
            $users = User::whereHas('unit', function ($query) {
                $query->whereNull('parent_unit_id');
            })->with('unit.childrenRecursive.users')->get();
        } else {
            // Untuk pengguna lain, tampilkan hirarki yang dimulai dari unit mereka sendiri
            $users = User::where('id', $loggedInUser->id)
                         ->with('unit.childrenRecursive.users')
                         ->get();
        }

        return view('users.hierarchy', compact('users'));
    }

    public function modern(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('unit')->orderBy('name');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.modern', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $potentialParents = User::with('unit')->where('role', '!=', User::ROLE_STAF)->orderBy('name')->get();
        return view('users.create', compact('potentialParents'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:'.implode(',', [User::ROLE_SUPERADMIN, User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR, User::ROLE_SUB_KOORDINATOR, User::ROLE_STAF])],
            'status' => ['required', 'in:active,suspended'],
            'parent_user_id' => ['nullable', 'required_if:role,'.User::ROLE_ESELON_II.','.User::ROLE_KOORDINATOR.','.User::ROLE_SUB_KOORDINATOR.','.User::ROLE_STAF, 'exists:users,id'],
        ]);

        $role = $validated['role'];
        $parentUserId = $validated['parent_user_id'] ?? null;
        $parentUser = $parentUserId ? User::find($parentUserId) : null;

        if ($parentUser) {
            if (!$parentUser->unit) {
                return back()->with('error', "Atasan yang dipilih tidak memiliki unit kerja yang valid.")->withInput();
            }
            if (isset($this->VALID_PARENT_ROLES[$role]) && !in_array($parentUser->unit->level, $this->VALID_PARENT_ROLES[$role])) {
                $validRoles = implode(', ', $this->VALID_PARENT_ROLES[$role]);
                return back()->with('error', "Atasan untuk role '{$role}' harus memiliki unit dengan level: {$validRoles}.")->withInput();
            }
        }

        DB::transaction(function () use ($validated, $role, $parentUser, $parentUserId) {
            $parentUnitId = ($parentUser && $parentUser->unit_id) ? $parentUser->unit_id : null;

            $unit = Unit::firstOrCreate(
                ['name' => $validated['unit_name']],
                [
                    'level' => $role,
                    'parent_unit_id' => $parentUnitId,
                ]
            );

            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'unit_id' => $unit->id,
                'status' => $validated['status'],
                'parent_user_id' => $parentUserId,
            ]);
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat dengan unit kerja baru.');
    }
    
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $potentialParents = User::with('unit')->where('role', '!=', User::ROLE_STAF)
                                 ->where('id', '!=', $user->id)
                                 ->orderBy('name')->get();
        
        return view('users.edit', compact('user', 'potentialParents'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:'.implode(',', [User::ROLE_SUPERADMIN, User::ROLE_ESELON_I, User::ROLE_ESELON_II, User::ROLE_KOORDINATOR, User::ROLE_SUB_KOORDINATOR, User::ROLE_STAF])],
            'status' => ['required', 'in:active,suspended'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'parent_user_id' => ['nullable', 'required_if:role,'.User::ROLE_ESELON_II.','.User::ROLE_KOORDINATOR.','.User::ROLE_SUB_KOORDINATOR.','.User::ROLE_STAF, 'exists:users,id'],
        ]);

        DB::transaction(function() use ($validated, $request, $user){
            $role = $validated['role'];
            $parentUserId = $validated['parent_user_id'] ?? null;
            $parentUser = $parentUserId ? User::find($parentUserId) : null;

            if ($parentUser) {
                if (!$parentUser->unit) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['parent_user_id' => "Atasan yang dipilih tidak memiliki unit kerja yang valid."]);
                }
                if (isset($this->VALID_PARENT_ROLES[$role]) && !in_array($parentUser->unit->level, $this->VALID_PARENT_ROLES[$role])) {
                    $validRoles = implode(', ', $this->VALID_PARENT_ROLES[$role]);
                    throw \Illuminate\Validation\ValidationException::withMessages(['parent_user_id' => "Atasan untuk role '{$role}' harus memiliki unit dengan level: {$validRoles}."]);
                }
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $role;
            $user->status = $validated['status'];
            $user->parent_user_id = $parentUserId;

            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }

            if ($user->unit) {
                $user->unit->name = $validated['unit_name'];
                $user->unit->level = $role;

                $newParentUnitId = ($parentUser) ? $parentUser->unit_id : null;

                if ($newParentUnitId !== $user->unit_id) {
                    $user->unit->parent_unit_id = $newParentUnitId;
                }
                $user->unit->save();
            }

            $user->save();
        });

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        DB::transaction(function() use ($user) {
            $unit = $user->unit;
            // Ini akan menyebabkan error karena children tidak ada di model User
            // $user->children()->update(['parent_user_id' => $user->parent_user_id]);
            $user->delete();
            if($unit && $unit->users()->count() === 0) {
                $newParentId = $unit->parent_unit_id;
                Unit::where('parent_unit_id', $unit->id)->update(['parent_unit_id' => $newParentId]);
                $unit->delete();
            }
        });

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