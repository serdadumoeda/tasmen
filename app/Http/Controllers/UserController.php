<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

        $loggedInUser = Auth::user();
        $query = User::with('unit')->orderBy('name');

        // Jika pengguna yang login bukan Superadmin, batasi daftar pengguna
        if (!$loggedInUser->isSuperAdmin()) {
            // 1. Jangan tampilkan superadmin lain
            $query->where('role', '!=', User::ROLE_SUPERADMIN);

            // 2. Ambil semua ID unit bawahan
            if ($loggedInUser->unit) {
                $subordinateUnitIds = $loggedInUser->unit->getAllSubordinateUnitIds();
                // Tambahkan unit pengguna itu sendiri ke dalam daftar
                $subordinateUnitIds[] = $loggedInUser->unit->id;

                // Filter pengguna berdasarkan unit-unit ini
                $query->whereIn('unit_id', $subordinateUnitIds);
            } else {
                // Jika pengguna tidak punya unit, dia hanya bisa melihat dirinya sendiri
                $query->where('id', $loggedInUser->id);
            }
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }
    
    public function hierarchy()
    {
        $this->authorize('viewAny', User::class);
        
        $loggedInUser = Auth::user();

        if ($loggedInUser->isSuperAdmin()) {
            // Superadmin melihat seluruh pohon unit dari level teratas.
            $units = Unit::whereNull('parent_unit_id')
                         ->with(['users', 'childrenRecursive.users'])
                         ->orderBy('name')
                         ->get();
        } else {
            // Pengguna lain melihat sub-pohon yang dimulai dari unit mereka sendiri.
            $units = Unit::where('id', $loggedInUser->unit_id)
                         ->with(['users', 'childrenRecursive.users'])
                         ->orderBy('name')
                         ->get();
        }

        return view('users.hierarchy', compact('units'));
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
        $units = Unit::orderBy('name')->get();
        $supervisors = User::orderBy('name')->get();
        return view('users.create', compact('units', 'supervisors'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'unit_id' => ['required', 'exists:units,id'],
            'jabatan_id' => ['required', Rule::exists('jabatans', 'id')->where('unit_id', $request->unit_id)->whereNull('user_id')],
            'atasan_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $jabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
        $unit = $jabatan->unit;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'atasan_id' => $validated['atasan_id'],
            'unit_id' => $unit->id,
            'role' => $unit->level, // Role is derived from the Unit's level
        ]);

        // Assign user to the Jabatan
        $jabatan->user_id = $user->id;
        $jabatan->save();

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat dan ditempatkan pada jabatan.');
    }
    
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $units = Unit::orderBy('name')->get();
        $supervisors = User::where('id', '!=', $user->id)->orderBy('name')->get(); // User cannot be their own supervisor
        
        return view('users.edit', compact('user', 'units', 'supervisors'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'unit_id' => ['required', 'exists:units,id'],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'atasan_id' => ['required', 'exists:users,id', 'not_in:'.$user->id],
            'status' => ['required', 'in:active,suspended'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $newJabatan = \App\Models\Jabatan::find($validated['jabatan_id']);

        // Check if the new Jabatan is already filled by someone else
        if ($newJabatan->user_id && $newJabatan->user_id !== $user->id) {
            return back()->withInput()->with('error', 'Jabatan yang dipilih sudah diisi oleh pengguna lain.');
        }

        DB::transaction(function () use ($user, $validated, $newJabatan, $request) {
            // Vacate the old position if it exists
            if ($user->jabatan) {
                $user->jabatan->user_id = null;
                $user->jabatan->save();
            }

            // Update user details
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
                'atasan_id' => $validated['atasan_id'],
                'unit_id' => $newJabatan->unit_id,
                'role' => $newJabatan->unit->level,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            // Assign user to the new Jabatan
            $newJabatan->user_id = $user->id;
            $newJabatan->save();
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