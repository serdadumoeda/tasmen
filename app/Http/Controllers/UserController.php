<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // TAMBAHKAN BARIS INI

class UserController extends Controller
{
    use AuthorizesRequests; 

    public function index()
    {
        $this->authorize('viewAny', User::class);
        $currentUser = auth()->user();

        // Ambil semua user yang berada dalam lingkup wewenang user yang sedang login
        $query = User::query()->with('children'); // Eager load children untuk efisiensi

        if ($currentUser->role !== 'superadmin') {
            $subordinateIds = $currentUser->getAllSubordinateIds();
            $subordinateIds[] = $currentUser->id; // Pimpinan juga ingin melihat dirinya sendiri
            $query->whereIn('id', $subordinateIds);
        }

        $allScopedUsers = $query->get();

        // Filter untuk mendapatkan user level teratas dalam lingkup ini
        // (yaitu user yang parent_id-nya tidak ada dalam daftar ID yang kita miliki, atau null)
        $scopedUserIds = $allScopedUsers->pluck('id');
        $topLevelUsers = $allScopedUsers->filter(function ($user) use ($scopedUserIds) {
            // User level atas adalah yang tidak punya parent, atau parent-nya di luar lingkup kita
            return $user->parent_id === null || !$scopedUserIds->contains($user->parent_id);
        });
        
        // Untuk superadmin, user level atas adalah yang tidak punya parent sama sekali
        if ($currentUser->role === 'superadmin') {
            $topLevelUsers = $allScopedUsers->where('parent_id', null);
        }

        return view('users.index', compact('topLevelUsers'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $currentUser = auth()->user();
        
        // Calon atasan adalah user itu sendiri dan semua bawahannya
        $subordinateIds = $currentUser->getAllSubordinateIds();
        $subordinateIds[] = $currentUser->id; // Tambahkan diri sendiri
        
        $potentialParents = User::whereIn('id', $subordinateIds)->orderBy('name')->get();

        return view('users.create', ['users' => $potentialParents]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $currentUser = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:Eselon I,Eselon II,Koordinator,Ketua Tim,Sub Koordinator,Staff'],
            'parent_id' => ['required', 'exists:users,id'],
        ]);

        // Pastikan parent_id yang dipilih berada dalam hirarki user yang sedang login
        $parent = User::find($request->parent_id);
        if ($parent->id !== $currentUser->id && !$parent->isSubordinateOf($currentUser)) {
            abort(403, 'Anda tidak dapat membuat user di bawah atasan yang bukan bawahan Anda.');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }
    
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $currentUser = auth()->user();

        // Calon atasan adalah user itu sendiri dan semua bawahannya
        $subordinateIds = $currentUser->getAllSubordinateIds();
        $subordinateIds[] = $currentUser->id;
        
        // Calon atasan tidak boleh user yang sedang diedit itu sendiri
        $potentialParents = User::whereIn('id', $subordinateIds)
                                ->where('id', '!=', $user->id)
                                ->orderBy('name')
                                ->get();

        return view('users.edit', ['user' => $user, 'users' => $potentialParents]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $currentUser = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:Eselon I,Eselon II,Koordinator,Ketua Tim,Sub Koordinator,Staff'],
            'parent_id' => ['required', 'exists:users,id', 'not_in:'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);
        
        $parent = User::find($request->parent_id);
        if ($parent->id !== $currentUser->id && !$parent->isSubordinateOf($currentUser)) {
             abort(403, 'Anda tidak dapat memindahkan user ke atasan yang bukan bawahan Anda.');
        }

        $user->fill($request->only('name', 'email', 'role', 'parent_id'));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        // Pindahkan bawahan langsung dari user yang akan dihapus ke atasan dari user yang dihapus
        $newParentId = $user->parent_id;
        foreach ($user->children as $child) {
            $child->parent_id = $newParentId;
            $child->save();
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus dan bawahannya telah dipindahkan.');
    }

    public function getUsersByUnit($eselon2_id)
    {
        $eselon2 = User::findOrFail($eselon2_id);
        $subordinateIds = $eselon2->getAllSubordinateIds();
        $subordinateIds[] = $eselon2->id;
        $usersInUnit = User::whereIn('id', $subordinateIds)->orderBy('name')->get(['id', 'name', 'role']);
        return response()->json($usersInUnit);
    }
}