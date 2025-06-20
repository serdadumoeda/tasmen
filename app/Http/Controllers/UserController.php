<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('parent')->latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        // Kirim semua user sebagai calon atasan
        $users = User::orderBy('name')->get();
        return view('users.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:superadmin,Eselon I,Eselon II,Koordinator,Ketua Tim,Sub Koordinator,Staff'],
            'parent_id' => ['nullable', 'exists:users,id'],
        ]);

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
        // Kirim semua user KECUALI user itu sendiri sebagai calon atasan
        $users = User::where('id', '!=', $user->id)->orderBy('name')->get();
        return view('users.edit', compact('user', 'users'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:superadmin,Eselon I,Eselon II,Koordinator,Ketua Tim,Sub Koordinator,Staff'],
            'parent_id' => ['nullable', 'exists:users,id', 'not_in:'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->parent_id = $request->parent_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }
    
    // method destroy tidak perlu diubah
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function getUsersByUnit($eselon2_id)
    {
        // Cari user Eselon II
        $eselon2 = User::findOrFail($eselon2_id);
        
        // Ambil semua ID bawahannya
        $subordinateIds = $eselon2->getAllSubordinateIds();
        // Tambahkan ID Eselon II itu sendiri
        $subordinateIds[] = $eselon2->id;

        // Ambil semua user yang berada dalam lingkup unit tersebut
        $usersInUnit = User::whereIn('id', $subordinateIds)->orderBy('name')->get(['id', 'name', 'role']);

        return response()->json($usersInUnit);
    }
}