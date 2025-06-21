<?php

namespace App\Http\Controllers;

use App\Models\SpecialAssignment;
use App\Models\User; // <-- TAMBAHKAN BARIS INI
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SpecialAssignmentController extends Controller
{
    use AuthorizesRequests;

    // Halaman utama daftar SK, sekarang dengan filter dan pencarian
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = SpecialAssignment::query()->with(['creator', 'members']);

        // Staff hanya bisa melihat SK-nya sendiri
        if (!$currentUser->canManageUsers()) {
            $query->whereHas('members', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            });
        } else {
            // Pimpinan melihat SK yang melibatkan bawahannya
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds[] = $currentUser->id;

            // Filter berdasarkan personil yang di-klik dari halaman analisis
            if ($request->filled('personnel_id') && $request->personnel_id) {
                $userId = $request->personnel_id;
                // Pastikan pimpinan hanya bisa memfilter bawahannya
                if (in_array($userId, $subordinateIds)) {
                     $query->whereHas('members', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
                }
            } else {
                 $query->whereHas('members', function ($q) use ($subordinateIds) {
                    $q->whereIn('user_id', $subordinateIds);
                });
            }
        }

        // Terapkan search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('sk_number', 'like', "%{$search}%");
            });
        }

        $assignments = $query->latest()->paginate(15);
        
        // Ambil data bawahan untuk filter dropdown
        $subordinates = $currentUser->canManageUsers() ? User::whereIn('id', $currentUser->getAllSubordinateIds())->orderBy('name')->get() : collect();

        return view('special-assignments.index', compact('assignments', 'subordinates'));
    }

    public function create()
    {
        $this->authorize('create', SpecialAssignment::class);
        $assignment = new SpecialAssignment();
        $subordinateIds = auth()->user()->getAllSubordinateIds();
        $assignableUsers = User::whereIn('id', $subordinateIds)->orWhere('id', auth()->id())->orderBy('name')->get();
        return view('special-assignments.create', compact('assignment', 'assignableUsers'));
    }

    public function store(Request $request)
    {
        
        $this->authorize('create', SpecialAssignment::class);
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
        if ($request->hasFile('file_upload')) {
            $validated['file_path'] = $request->file('file_upload')->store('sk_files', 'public');
        }

        $validated['creator_id'] = auth()->id();
        $sk = SpecialAssignment::create($validated);

        $sk = SpecialAssignment::create([
            'creator_id' => auth()->id(),
            'title' => $validated['title'],
            'sk_number' => $validated['sk_number'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'description' => $validated['description'],
        ]);

        $membersToSync = [];
        foreach ($validated['members'] as $member) {
            $membersToSync[$member['user_id']] = ['role_in_sk' => $member['role_in_sk']];
        }
        $sk->members()->sync($membersToSync);

        return redirect()->route('special-assignments.index')->with('success', 'SK Penugasan berhasil dibuat.');
    }

    public function edit(SpecialAssignment $specialAssignment)
    {
        // $this->authorize('update', $specialAssignment);
        $this->authorize('update', $specialAssignment);
        $subordinateIds = auth()->user()->getAllSubordinateIds();
        $assignableUsers = User::whereIn('id', $subordinateIds)->orWhere('id', auth()->id())->orderBy('name')->get();
        return view('special-assignments.edit', ['assignment' => $specialAssignment, 'assignableUsers' => $assignableUsers]);
    }

    public function update(Request $request, SpecialAssignment $specialAssignment)
    {
        // $this->authorize('update', $specialAssignment);
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

        // Proses upload file baru jika ada
        if ($request->hasFile('file_upload')) {
            // Hapus file lama jika ada
            if ($specialAssignment->file_path) {
                Storage::disk('public')->delete($specialAssignment->file_path);
            }
            // Simpan file baru dan update path
            $validated['file_path'] = $request->file('file_upload')->store('sk_files', 'public');
        }
        
        $specialAssignment->update($validated);

        $specialAssignment->update($request->except('members'));

        $membersToSync = [];
        foreach ($validated['members'] as $member) {
            $membersToSync[$member['user_id']] = ['role_in_sk' => $member['role_in_sk']];
        }
        $specialAssignment->members()->sync($membersToSync);

        return redirect()->route('special-assignments.index')->with('success', 'SK Penugasan berhasil diperbarui.');
    }


    public function destroy(SpecialAssignment $specialAssignment)
    {
        // $this->authorize('delete', $specialAssignment);
        // Untuk sementara, hanya pembuat yang bisa hapus
        $this->authorize('delete', $specialAssignment);
        if ($specialAssignment->file_path) {
            Storage::disk('public')->delete($specialAssignment->file_path);
        }

        if ($specialAssignment->creator_id !== auth()->id()) {
            abort(403);
        }
        $specialAssignment->delete();
        return back()->with('success', 'SK Penugasan berhasil dihapus.');
    }
}