<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $loggedInUser = Auth::user();
        $query = User::with(['unit', 'jabatan', 'atasan.jabatan']);

        if (!$loggedInUser->isSuperAdmin()) {
            $query->inUnitAndSubordinatesOf($loggedInUser)
                  ->whereHas('role', fn($q) => $q->where('name', '!=', 'superadmin'));
        }

        $query->orderBy('name');

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
            $units = Unit::whereNull('parent_unit_id')
                         ->with(['users', 'childrenRecursive'])
                         ->orderBy('name')
                         ->get();
        } else {
            $units = Unit::where('id', $loggedInUser->unit_id)
                         ->with(['users', 'childrenRecursive'])
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
        $supervisors = User::orderBy('name')->get();
        $eselonIUnits = Unit::whereNull('parent_unit_id')->orderBy('name')->get();
        $user = new User();
        $selectedUnitPath = [];

        return view('users.create', compact('user', 'supervisors', 'eselonIUnits', 'selectedUnitPath'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'nik' => ['nullable', 'string', 'digits:16', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'jabatan_id' => ['nullable', Rule::exists('jabatans', 'id')->whereNull('user_id')],
            'is_kepala_unit' => ['nullable', 'boolean'],
            'atasan_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:active,suspended'],
            'nip' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date_format:Y-m-d'],
            'alamat' => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:255'],
            'eselon' => ['nullable', 'string', 'max:255'],
            'tmt_eselon' => ['nullable', 'date_format:Y-m-d'],
            'grade' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'pendidikan_jurusan' => ['nullable', 'string', 'max:255'],
            'pendidikan_universitas' => ['nullable', 'string', 'max:255'],
            'jenis_jabatan' => ['nullable', 'string', 'max:255'],
            'tmt_cpns' => ['nullable', 'date_format:Y-m-d'],
            'tmt_pns' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $userData = $validated;
        $userData['password'] = Hash::make($validated['password']);

        // If no jabatan is selected, create a user without a unit and with a default role.
        // The middleware will force them to complete their profile upon login.
        if (empty($validated['jabatan_id'])) {
            $userData['unit_id'] = null;
            $jabatan = null;
        } else {
            $jabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
            $unit = $jabatan->unit;
            $userData['unit_id'] = $unit->id;
        }

        // Role will be calculated automatically. We will set a temporary role_id.
        $stafRole = \App\Models\Role::where('name', 'staf')->first();
        $userData['role_id'] = $stafRole->id;

        if ($request->filled('atasan_id')) {
            $atasan = User::find($request->atasan_id);
            // Validation for supervisor role is complex now, better handled by recalculateAndSaveRole
            // or a dedicated service. We'll skip this check here as it's less reliable
            // without knowing the definitive final role. The recalculation logic is the source of truth.
        }
        
        foreach(['tgl_lahir', 'tmt_eselon', 'tmt_cpns', 'tmt_pns'] as $dateField) {
            if (!empty($userData[$dateField])) {
                // Ensure the date is a Carbon instance for the model, but no re-formatting is needed.
                $userData[$dateField] = Carbon::parse($userData[$dateField]);
            }
        }

        DB::transaction(function () use ($userData, $jabatan, $request) {
            $user = User::create($userData);

            if ($jabatan) {
                $jabatan->user_id = $user->id;
                $jabatan->save();
            }

            if ($request->has('is_kepala_unit') && $request->is_kepala_unit && $user->unit) {
                $user->unit()->update(['kepala_unit_id' => $user->id]);
            }

            // After all assignments, recalculate and save the definitive role.
            User::recalculateAndSaveRole($user);
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat dan ditempatkan pada jabatan.');
    }
    
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $supervisors = User::where('id', '!=', $user->id)->orderBy('name')->get();
        $eselonIUnits = Unit::whereNull('parent_unit_id')->orderBy('name')->get();

        $selectedUnitPath = [];
        if ($user->unit) {
            $user->load('unit');
            $ancestors = $user->unit->ancestors()->orderBy('depth', 'asc')->get();
            $selectedUnitPath = $ancestors->pluck('id')->toArray();
            $selectedUnitPath[] = $user->unit->id;
        }

        return view('users.edit', compact('user', 'supervisors', 'eselonIUnits', 'selectedUnitPath'));
    }

    public function profile(User $user)
    {
        $this->authorize('view', $user);
        $user->load(['unit.parentUnit', 'jabatan', 'atasan']);
        return view('users.profile', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nik' => ['nullable', 'string', 'digits:16', Rule::unique('users', 'nik')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'jabatan_id' => ['nullable', 'exists:jabatans,id'],
            'is_kepala_unit' => ['nullable', 'boolean'],
            'atasan_id' => ['nullable', 'exists:users,id', 'not_in:'.$user->id],
            'status' => ['nullable', 'in:active,suspended'],
            'nip' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date_format:Y-m-d'],
            'alamat' => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:255'],
            'eselon' => ['nullable', 'string', 'max:255'],
            'tmt_eselon' => ['nullable', 'date_format:Y-m-d'],
            'grade' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'pendidikan_jurusan' => ['nullable', 'string', 'max:255'],
            'pendidikan_universitas' => ['nullable', 'string', 'max:255'],
            'jenis_jabatan' => ['nullable', 'string', 'max:255'],
            'tmt_cpns' => ['nullable', 'date_format:Y-m-d'],
            'tmt_pns' => ['nullable', 'date_format:Y-m-d'],
        ]);

        // If jabatan_id is not submitted, we assume no change in position is intended.
        if (!array_key_exists('jabatan_id', $validated)) {
            $updateData = $validated;
            unset($updateData['jabatan_id']);

            // Simple update for non-position related data
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            } else {
                unset($updateData['password']);
            }

            foreach(['tgl_lahir', 'tmt_eselon', 'tmt_cpns', 'tmt_pns'] as $dateField) {
                if (!empty($updateData[$dateField])) {
                    $updateData[$dateField] = Carbon::parse($updateData[$dateField]);
                }
            }

            $user->update($updateData);
            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
        }

        // Proceed with jabatan change logic if jabatan_id is present
        $newJabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
        if (!$newJabatan) {
            return back()->withInput()->with('error', 'Jabatan yang dipilih tidak valid.');
        }
        $validated['unit_id'] = $newJabatan->unit_id;

        if ($newJabatan->user_id && $newJabatan->user_id !== $user->id) {
            return back()->withInput()->with('error', 'Jabatan yang dipilih sudah diisi oleh pengguna lain.');
        }

        $oldUnit = $user->unit;
        $oldRole = $user->role; // Keep this for comparison after update
        $pindahUnit = $user->unit_id !== $newJabatan->unit_id;

        $newUnit = $newJabatan->unit;

        if ($request->filled('atasan_id')) {
            $atasan = User::find($request->atasan_id);
            // We use the user's current role for validation, as it's the most stable state before the update.
            $validSupervisorRoles = User::getValidSupervisorRolesFor($user->role->name);

            if ($validSupervisorRoles !== null) {
                if (!$atasan || !$atasan->role || !in_array($atasan->role->name, $validSupervisorRoles)) {
                    return back()->withInput()->with('error', 'Atasan yang dipilih memiliki peran yang tidak sesuai dengan hierarki yang diizinkan.');
                }
            }
        }
        
        DB::transaction(function () use ($user, $validated, $newJabatan, $request, $pindahUnit, $oldUnit, $newUnit) {
            // Detach old jabatan if a new one is being assigned
            if ($newJabatan && $user->jabatan && $user->jabatan->id !== $newJabatan->id) {
                $user->jabatan->user_id = null;
                $user->jabatan->save();
            }
        
            $updateData = $validated;
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            } else {
                unset($updateData['password']);
            }

            // Unset role from validation data as it will be calculated automatically
            unset($updateData['role']);
        
            foreach(['tgl_lahir', 'tmt_eselon', 'tmt_cpns', 'tmt_pns'] as $dateField) {
                if (!empty($updateData[$dateField])) {
                    // Ensure the date is a Carbon instance for the model, but no re-formatting is needed.
                    $updateData[$dateField] = Carbon::parse($updateData[$dateField]);
                }
            }
        
            if ($pindahUnit) {
                $updateData['atasan_id'] = null;
            }
        
            if ($user->isSuperAdmin()) {
                unset($updateData['role']);
            }
            $user->update($updateData);
            $user->refresh();
        
            if ($newJabatan) {
                $newJabatan->user_id = $user->id;
                $newJabatan->save();
            }

            if ($user->unit) {
                // If checkbox is checked, make user the head.
                if ($request->has('is_kepala_unit') && $request->is_kepala_unit) {
                    $user->unit->kepala_unit_id = $user->id;
                }
                // If checkbox is not checked, and this user was the head, remove them.
                elseif ($user->unit->kepala_unit_id === $user->id) {
                    $user->unit->kepala_unit_id = null;
                }
                $user->unit->save();
            }

            // After all assignments, recalculate and save the definitive role.
            User::recalculateAndSaveRole($user);
        });
        
        $roleChanged = $oldRole->id !== $user->role->id;
        $redirect = redirect()->route('users.index');

        if ($pindahUnit) {
            $redirect->with('success', 'User berhasil diperbarui. Atasan telah direset karena pindah unit, harap tetapkan atasan baru.');
        } else {
            $redirect->with('success', 'User berhasil diperbarui.');
        }

        if ($roleChanged && !$pindahUnit) {
             $redirect->with('warning', "Role pengguna telah diperbarui dari '{$oldRole->label}' menjadi '{$user->role->label}'.");
        }

        return $redirect;
    }

    public function deactivate(User $user)
    {
        $this->authorize('deactivate', $user);

        DB::transaction(function () use ($user) {
            // Set user status to suspended
            $user->status = User::STATUS_SUSPENDED;
            $user->save();

            // Free up their position
            if ($user->jabatan) {
                $user->jabatan->user_id = null;
                $user->jabatan->save();
            }

            // Reassign their subordinates to their own supervisor
            User::where('atasan_id', $user->id)->update(['atasan_id' => $user->atasan_id]);
        });

        return redirect()->route('users.index')->with('success', 'Pengguna telah berhasil diarsipkan/dinonaktifkan.');
    }

    public function getUsersByUnitFromId(int $unitId)
    {
        $users = User::where('unit_id', $unitId)
                     ->orderBy('name')
                     ->get(['id', 'name', 'email']);

        return response()->json(['users' => $users]);
    }

    public function getUsersByUnitFromModel(Unit $unit)
    {
        $users = $unit->users()
            ->with('jabatan')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'jabatan' => $user->jabatan ? $user->jabatan->name : 'N/A',
                ];
            });

        return response()->json($users);
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
                                     ->where('status', '!=', 'completed')
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

    public function impersonate(User $user)
    {
        if ($user->isSuperAdmin() && !$user->is(auth()->user())) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat meniru sesama Superadmin.');
        }

        // Penanganan kasus email belum terverifikasi
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat meniru pengguna yang belum memverifikasi emailnya.');
        }

        $impersonatorId = Auth::id();
        
        Auth::login($user);

        session(['impersonator_id' => $impersonatorId]);

        return redirect()->route('dashboard')->with('success', 'Anda sekarang meniru ' . $user->name);
    }

    public function leaveImpersonate()
    {
        if (!session()->has('impersonator_id')) {
            return redirect('/')->with('error', 'Tidak ada sesi peniruan untuk ditinggalkan.');
        }

        $originalUserId = session('impersonator_id');
        $originalUser = User::find($originalUserId);

        Auth::login($originalUser);
        session()->forget('impersonator_id');

        return redirect()->route('users.index')->with('success', 'Sesi peniruan telah berakhir.');
    }

    public function showImportForm()
    {
        $this->authorize('create', User::class);
        return view('users.import');
    }

    public function handleImport(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'user_import' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('user_import')->getRealPath();
        $file = fopen($path, 'r');

        $header = fgetcsv($file);
        $expectedHeaders = ['Nama', 'NIP', 'Jabatan'];
        if (count(array_intersect($expectedHeaders, $header)) !== count($expectedHeaders)) {
            return back()->with('error', 'Format file CSV tidak sesuai. Pastikan kolom ' . implode(', ', $expectedHeaders) . ' ada.');
        }

        $data = [];
        $skippedRows = [];
        $rowNumber = 1;
        while ($row = fgetcsv($file)) {
            $rowNumber++;
            if (count($header) !== count($row)) {
                $skippedRows[] = $rowNumber;
                continue;
            }
            $data[] = array_combine($header, $row);
        }

        fclose($file);

        $importer = new \App\Services\OrganizationalDataImporterService();
        $importer->processData($data);

        $successMessage = 'Data pengguna berhasil diimpor.';
        if (!empty($skippedRows)) {
            $successMessage .= ' Peringatan: Beberapa baris dilewati karena format tidak sesuai (Baris: ' . implode(', ', $skippedRows) . ').';
        }

        return redirect()->route('users.index')->with('success', $successMessage);
    }

    public function getUsersByUnit(Unit $unit)
    {
        $users = $unit->users()->with('jabatan')->orderBy('name')->get();
        return response()->json($users);
    }

    public function archived()
    {
        $this->authorize('viewAny', User::class);

        $archivedUsers = User::where('status', User::STATUS_SUSPENDED)
            ->with(['unit', 'jabatan'])
            ->orderBy('name')
            ->paginate(15);

        return view('users.archived', compact('archivedUsers'));
    }

    public function reactivate(User $user)
    {
        $this->authorize('reactivate', $user);

        $user->status = User::STATUS_ACTIVE;
        $user->save();

        return redirect()->route('users.archived')->with('success', 'Pengguna telah berhasil diaktifkan kembali.');
    }

    public function forceDelete(User $user)
    {
        $this->authorize('forceDelete', $user);

        DB::transaction(function () use ($user) {
            // Manually nullify relationships that are RESTRICTed on delete.
            \App\Models\Project::where('leader_id', $user->id)->update(['leader_id' => null]);

            // Other relationships like jabatan, project_user, task_user, etc., are handled
            // by onDelete('cascade') or onDelete('set null') at the database level.

            $user->delete();
        });

        return redirect()->route('users.archived')->with('success', 'Pengguna telah dihapus secara permanen.');
    }

    public function editLeaveBalance(User $user)
    {
        $this->authorize('update', $user); // Reuse existing authorization

        $balance = LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => now()->year],
            ['total_days' => 12, 'carried_over_days' => 0] // Defaults for new users
        );

        return view('admin.users.leave_balance', compact('user', 'balance'));
    }

    public function updateLeaveBalance(Request $request, User $user)
    {
        $this->authorize('update', $user); // Reuse existing authorization

        $validated = $request->validate([
            'carried_over_days' => 'required|integer|min:0',
        ]);

        $balance = LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => now()->year],
            ['total_days' => 12] // Defaults for new users
        );

        $balance->update([
            'carried_over_days' => $validated['carried_over_days'],
        ]);

        return redirect()->route('admin.users.leave-balance.edit', $user)->with('success', 'Sisa cuti tahun lalu berhasil diperbarui.');
    }
}