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
use Illuminate\Contracts\Auth\MustVerifyEmail;

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
        $query = User::with(['unit', 'jabatan', 'atasan.jabatan']);

        if (!$loggedInUser->isSuperAdmin()) {
            $query->inUnitAndSubordinatesOf($loggedInUser)
                  ->where('role', '!=', User::ROLE_SUPERADMIN);
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
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'jabatan_id' => ['required', Rule::exists('jabatans', 'id')->whereNull('user_id')],
            'atasan_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:active,suspended'],
            'nip' => ['nullable', 'string', 'max:255', 'unique:'.User::class],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tgl_lahir' => ['nullable', 'date_format:d-m-Y'],
            'alamat' => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:255'],
            'eselon' => ['nullable', 'string', 'max:255'],
            'tmt_eselon' => ['nullable', 'date_format:d-m-Y'],
            'grade' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'pendidikan_jurusan' => ['nullable', 'string', 'max:255'],
            'pendidikan_universitas' => ['nullable', 'string', 'max:255'],
            'jenis_jabatan' => ['nullable', 'string', 'max:255'],
            'tmt_cpns' => ['nullable', 'date_format:d-m-Y'],
            'tmt_pns' => ['nullable', 'date_format:d-m-Y'],
        ]);

        $jabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
        $unit = $jabatan->unit;

        $userData = $validated;
        $userData['unit_id'] = $unit->id;
        $userData['password'] = Hash::make($validated['password']);

        // NEW LOGIC: Role is inherited from the selected Jabatan
        $userData['role'] = $jabatan->role;

        if ($userData['role'] === User::ROLE_MENTERI && !auth()->user()->isSuperAdmin()) {
            return back()->withInput()->with('error', 'Anda tidak memiliki izin untuk membuat pengguna dengan peran Menteri.');
        }

        if ($request->filled('atasan_id')) {
            $atasan = User::find($request->atasan_id);
            if (isset($this->VALID_PARENT_ROLES[$userData['role']])) {
                $validParentRoles = $this->VALID_PARENT_ROLES[$userData['role']];
                if (!$atasan || !in_array($atasan->role, $validParentRoles)) {
                    return back()->withInput()->with('error', 'Atasan yang dipilih memiliki peran yang tidak sesuai dengan hierarki yang diizinkan.');
                }
            }
        }
        
        foreach(['tgl_lahir', 'tmt_eselon', 'tmt_cpns', 'tmt_pns'] as $dateField) {
            if (!empty($userData[$dateField])) {
                $userData[$dateField] = Carbon::createFromFormat('d-m-Y', $userData[$dateField])->format('Y-m-d');
            }
        }

        DB::transaction(function () use ($userData, $jabatan, $unit) {
            $user = User::create($userData);
            $jabatan->user_id = $user->id;
            $jabatan->save();

            if ($this->isLeadershipRole($user->role)) {
                $unit->kepala_unit_id = $user->id;
                $unit->save();
            }
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
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'atasan_id' => ['nullable', 'exists:users,id', 'not_in:'.$user->id],
            'status' => ['required', 'in:active,suspended'],
            'nip' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tgl_lahir' => ['nullable', 'date_format:d-m-Y'],
            'alamat' => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:255'],
            'eselon' => ['nullable', 'string', 'max:255'],
            'tmt_eselon' => ['nullable', 'date_format:d-m-Y'],
            'grade' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'pendidikan_jurusan' => ['nullable', 'string', 'max:255'],
            'pendidikan_universitas' => ['nullable', 'string', 'max:255'],
            'jenis_jabatan' => ['nullable', 'string', 'max:255'],
            'tmt_cpns' => ['nullable', 'date_format:d-m-Y'],
            'tmt_pns' => ['nullable', 'date_format:d-m-Y'],
        ]);

        $newJabatan = \App\Models\Jabatan::find($validated['jabatan_id']);
        $validated['unit_id'] = $newJabatan->unit_id;

        if ($newJabatan->user_id && $newJabatan->user_id !== $user->id) {
            return back()->withInput()->with('error', 'Jabatan yang dipilih sudah diisi oleh pengguna lain.');
        }

        $oldUnit = $user->unit;
        $oldRole = $user->role;
        $pindahUnit = $user->unit_id !== $newJabatan->unit_id;

        $newUnit = $newJabatan->unit;
        // NEW LOGIC: The new role is inherited from the Jabatan itself.
        $newRole = $newJabatan->role;

        if ($request->filled('atasan_id')) {
            $atasan = User::find($request->atasan_id);
            if (isset($this->VALID_PARENT_ROLES[$newRole])) {
                $validParentRoles = $this->VALID_PARENT_ROLES[$newRole];
                if (!$atasan || !in_array($atasan->role, $validParentRoles)) {
                    return back()->withInput()->with('error', 'Atasan yang dipilih memiliki peran yang tidak sesuai dengan hierarki yang diizinkan.');
                }
            }
        }
        
        DB::transaction(function () use ($user, $validated, $newJabatan, $request, $pindahUnit, $oldUnit, $newUnit, $newRole) {
            if ($oldUnit && $oldUnit->kepala_unit_id === $user->id) {
                // The isLeadershipRole check now uses the new role directly
                if ($pindahUnit || !$this->isLeadershipRole($newRole)) {
                    $oldUnit->kepala_unit_id = null;
                    $oldUnit->save();
                }
            }
        
            if ($user->jabatan && $user->jabatan->id !== $newJabatan->id) {
                $user->jabatan->user_id = null;
                $user->jabatan->save();
            }
        
            $updateData = $validated;
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            } else {
                unset($updateData['password']);
            }
        
            // NEW LOGIC: Role is inherited from the selected Jabatan
            $updateData['role'] = $newRole;
        
            if ($updateData['role'] === User::ROLE_MENTERI && !auth()->user()->isSuperAdmin()) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Anda tidak memiliki izin untuk menetapkan peran Menteri.');
            }
        
            foreach(['tgl_lahir', 'tmt_eselon', 'tmt_cpns', 'tmt_pns'] as $dateField) {
                if (!empty($updateData[$dateField])) {
                    $updateData[$dateField] = Carbon::createFromFormat('d-m-Y', $updateData[$dateField])->format('Y-m-d');
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
        
            $newJabatan->user_id = $user->id;
            $newJabatan->save();
        
            if ($this->isLeadershipRole($user->role)) {
                $newUnit->kepala_unit_id = $user->id;
                $newUnit->save();
            }
        });
        
        $roleChanged = $oldRole !== $user->role;
        $redirect = redirect()->route('users.index');

        if ($pindahUnit) {
            $redirect->with('success', 'User berhasil diperbarui. Atasan telah direset karena pindah unit, harap tetapkan atasan baru.');
        } else {
            $redirect->with('success', 'User berhasil diperbarui.');
        }

        if ($roleChanged && !$pindahUnit) {
             $redirect->with('warning', "Role pengguna telah diperbarui dari '{$oldRole}' menjadi '{$user->role}'.");
        }

        return $redirect;
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        DB::transaction(function () use ($user) {
            if ($user->jabatan) {
                $user->jabatan->user_id = null;
                $user->jabatan->save();
            }

            User::where('atasan_id', $user->id)->update(['atasan_id' => $user->atasan_id]);
            $user->delete();
        });

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus dan jabatan telah dikosongkan.');
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

    private function isLeadershipRole(string $role): bool
    {
        return in_array($role, [
            User::ROLE_MENTERI,
            User::ROLE_ESELON_I,
            User::ROLE_ESELON_II,
            User::ROLE_KOORDINATOR,
            User::ROLE_SUB_KOORDINATOR
        ]);
    }
}