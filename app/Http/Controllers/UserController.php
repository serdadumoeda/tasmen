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
        $query = User::with(['unit', 'jabatan', 'atasan.jabatan'])->orderBy('name');

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
            $search = strtolower($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
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
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
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
            'atasan_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $jabatan = Jabatan::find($validated['jabatan_id']);
        $unit = $jabatan->unit;

        DB::transaction(function () use ($validated, $jabatan, $unit) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
                'atasan_id' => $validated['atasan_id'],
                'unit_id' => $unit->id,
                'role' => $unit->level,
            ]);

            $jabatan->user_id = $user->id;
            $jabatan->save();
        });

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
            'atasan_id' => ['nullable', 'exists:users,id', 'not_in:'.$user->id],
            'status' => ['required', 'in:active,suspended'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $newJabatan = Jabatan::find($validated['jabatan_id']);

        if ($newJabatan->user_id && $newJabatan->user_id !== $user->id) {
            return back()->withInput()->with('error', 'Jabatan yang dipilih sudah diisi oleh pengguna lain.');
        }

        $oldUnitId = $user->unit_id;
        $pindahUnit = $oldUnitId !== $newJabatan->unit_id;

        DB::transaction(function () use ($user, $validated, $newJabatan, $request, $pindahUnit) {
            // Kosongkan jabatan lama jika ada dan berbeda
            if ($user->jabatan && $user->jabatan->id !== $newJabatan->id) {
                $user->jabatan->user_id = null;
                $user->jabatan->save();
            }

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
                'atasan_id' => $validated['atasan_id'],
                'unit_id' => $newJabatan->unit_id,
                'role' => $newJabatan->unit->level,
                'password' => $request->filled('password') ? Hash::make($validated['password']) : $user->password,
            ];

            // Jika user pindah unit, reset atasannya untuk mencegah relasi yang tidak valid.
            if ($pindahUnit) {
                $updateData['atasan_id'] = null;
            }

            $user->update($updateData);

            // Tugaskan user ke jabatan baru
            $newJabatan->user_id = $user->id;
            $newJabatan->save();
        });

        $newRole = $newJabatan->unit->level;
        $roleChanged = $user->role !== $newRole;

        $redirect = redirect()->route('users.index');

        if ($pindahUnit) {
            $redirect->with('success', 'User berhasil diperbarui. Atasan telah direset karena pindah unit, harap tetapkan atasan baru.');
        } else {
            $redirect->with('success', 'User berhasil diperbarui.');
        }

        if ($roleChanged && !$pindahUnit) { // Tampilkan pesan role change hanya jika tidak ada pesan pindah unit
             $redirect->with('warning', "Role pengguna telah diperbarui dari '{$user->role}' menjadi '{$newRole}'.");
        }

        return $redirect;
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        DB::transaction(function () use ($user) {
            // Cek apakah user punya jabatan, jika iya, kosongkan.
            if ($user->jabatan) {
                $user->jabatan->user_id = null;
                $user->jabatan->save();
            }

            // Hapus relasi atasan-bawahan
            User::where('atasan_id', $user->id)->update(['atasan_id' => $user->atasan_id]);

            // Hapus user
            $user->delete();

            // Logika untuk menghapus unit jika kosong tidak lagi relevan di sini
            // karena jabatan dan user sudah tidak terikat secara langsung dengan unit
            // saat penghapusan user.
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus dan jabatan telah dikosongkan.');
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

        // PERBAIKAN: Cari berdasarkan nama atau email
        $search = strtolower($query);
        $users = User::where(function ($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
                    })
                    ->where('id', '!=', auth()->id())
                    ->limit(10)
                    ->get(['id', 'name', 'role']);

        return response()->json($users);
    }

    /**
     * Impersonate the given user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate(User $user)
    {
        // Cannot impersonate other superadmins
        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat meniru sesama Superadmin.');
        }

        // Store original user's id in session
        session(['impersonator_id' => Auth::id()]);

        // Login as the new user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Anda sekarang meniru ' . $user->name);
    }

    /**
     * Revert impersonation.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leaveImpersonate()
    {
        if (!session()->has('impersonator_id')) {
            return redirect('/')->with('error', 'Tidak ada sesi peniruan untuk ditinggalkan.');
        }

        // Login back as the original user
        $originalUserId = session('impersonator_id');
        Auth::login(User::find($originalUserId));

        // Forget the impersonator_id from session
        session()->forget('impersonator_id');

        return redirect()->route('users.index')->with('success', 'Sesi peniruan telah berakhir.');
    }
}