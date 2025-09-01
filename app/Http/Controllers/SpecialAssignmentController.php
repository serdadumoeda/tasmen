<?php

namespace App\Http\Controllers;

use App\Models\SpecialAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class SpecialAssignmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar SK dengan logika visibilitas yang disempurnakan.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = SpecialAssignment::query()->with(['creator', 'members']);

        // PERBAIKAN: Sederhanakan logika visibilitas hierarkis
        if ($currentUser->isSuperAdmin()) {
            // Superadmin melihat semua, tidak perlu filter.
        } elseif ($currentUser->canManageUsers()) {
            // Manajer (Eselon, Koordinator) melihat SK dalam hierarkinya.
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds->push($currentUser->id); // Termasuk diri sendiri

            $query->where(function ($q) use ($subordinateIds) {
                // Tampilkan jika pembuatnya ada di hierarki,
                $q->whereIn('creator_id', $subordinateIds)
                  // ATAU jika salah satu anggotanya ada di hierarki.
                  ->orWhereHas('members', function ($subQ) use ($subordinateIds) {
                      $subQ->whereIn('user_id', $subordinateIds);
                  });
            });
        } else {
            // Staf biasa hanya melihat SK di mana mereka menjadi anggota.
            $query->whereHas('members', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            });
        }

        // Terapkan filter pencarian
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(sk_number) LIKE ?', ["%{$search}%"]);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by member
        if ($request->filled('member_id')) {
            $query->whereHas('members', fn($q) => $q->where('user_id', $request->input('member_id')));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        if (in_array($sortBy, ['title', 'status', 'start_date', 'end_date', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir);
        }

        $assignments = $query->paginate(15)->appends($request->query());
        
        // Ambil data bawahan untuk dropdown filter (hanya untuk pimpinan)
        $subordinates = $currentUser->canManageUsers() ? User::whereIn('id', $currentUser->getAllSubordinateIds())->orderBy('name')->get() : collect();

        return view('special-assignments.index', compact('assignments', 'subordinates'));
    }

    /**
     * Menampilkan form pembuatan SK.
     */
    public function create()
    {
        $this->authorize('create', SpecialAssignment::class);
        $assignment = new SpecialAssignment();
        $user = auth()->user();

        $assignableUsers = collect();
        if ($user->canManageUsers()) {
            $subordinateIds = $user->getAllSubordinateIds();
            $subordinateIds[] = $user->id;
            $assignableUsers = User::whereIn('id', $subordinateIds)->orderBy('name')->get();
        } else {
            $assignableUsers->push($user);
        }

        return view('special-assignments.create', compact('assignment', 'assignableUsers'));
    }

    /**
     * Menyimpan SK baru ke database.
     */
    public function store(Request $request)
    {
        $this->authorize('create', SpecialAssignment::class);
        $user = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sk_number' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,SELESAI',
            'description' => 'nullable|string',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'members' => ($user->canManageUsers() ? 'required|array' : 'nullable|array'),
            'members.*.user_id' => ($user->canManageUsers() ? 'required|exists:users,id' : 'nullable|exists:users,id'),
            'members.*.role_in_sk' => ($user->canManageUsers() ? 'required|string|max:255' : 'nullable|string|max:255'),
        ]);

        $dataToCreate = $request->except(['members', 'file_upload']);
        $dataToCreate['creator_id'] = $user->id;

        if ($request->hasFile('file_upload')) {
            $dataToCreate['file_path'] = $request->file('file_upload')->store('sk_files', 'public');
        }

        $sk = SpecialAssignment::create($dataToCreate);

        $membersToSync = [];
        if ($user->canManageUsers()) {
            foreach ($validated['members'] as $member) {
                $membersToSync[$member['user_id']] = ['role_in_sk' => $member['role_in_sk']];
            }
        } else {
            $membersToSync[$user->id] = ['role_in_sk' => 'Pelaksana'];
        }

        $sk->members()->sync($membersToSync);

        return redirect()->route('special-assignments.index')->with('success', 'SK Penugasan berhasil dibuat.');
    }

    /**
     * Menampilkan form edit SK.
     */
    public function edit(SpecialAssignment $specialAssignment)
    {
        $this->authorize('update', $specialAssignment);
        $subordinateIds = auth()->user()->getAllSubordinateIds();
        $assignableUsers = User::whereIn('id', $subordinateIds)->orWhere('id', auth()->id())->orderBy('name')->get();
        return view('special-assignments.edit', ['assignment' => $specialAssignment, 'assignableUsers' => $assignableUsers]);
    }

    /**
     * Memperbarui data SK di database.
     */
    public function update(Request $request, SpecialAssignment $specialAssignment)
    {
        $this->authorize('update', $specialAssignment);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sk_number' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:AKTIF,SELESAI',
            'description' => 'nullable|string',
            'members' => 'required|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role_in_sk' => 'required|string|max:255',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        
        $dataToUpdate = $request->except(['members', 'file_upload', '_token', '_method']);

        if ($request->hasFile('file_upload')) {
            if ($specialAssignment->file_path) {
                Storage::disk('public')->delete($specialAssignment->file_path);
            }
            $dataToUpdate['file_path'] = $request->file('file_upload')->store('sk_files', 'public');
        }
        
        $specialAssignment->update($dataToUpdate);

        $membersToSync = [];
        foreach ($validated['members'] as $member) {
            $membersToSync[$member['user_id']] = ['role_in_sk' => $member['role_in_sk']];
        }
        $specialAssignment->members()->sync($membersToSync);

        return redirect()->route('special-assignments.index')->with('success', 'SK Penugasan berhasil diperbarui.');
    }

    /**
     * Menghapus SK.
     */
    public function destroy(SpecialAssignment $specialAssignment)
    {
        $this->authorize('delete', $specialAssignment);
        if ($specialAssignment->file_path) {
            Storage::disk('public')->delete($specialAssignment->file_path);
        }

        $specialAssignment->delete();
        return back()->with('success', 'SK Penugasan berhasil dihapus.');
    }
}