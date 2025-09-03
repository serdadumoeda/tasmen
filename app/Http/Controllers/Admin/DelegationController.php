<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delegation;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelegationController extends Controller
{
    public function index()
    {
        $delegations = Delegation::with(['jabatan', 'user', 'creator'])->latest()->paginate(15);
        return view('admin.delegations.index', compact('delegations'));
    }

    public function create()
    {
        // Get all positions that are currently filled
        $jabatans = Jabatan::whereNotNull('user_id')->with('user')->get();
        // Get all users who can be assigned as delegates
        $users = User::orderBy('name')->get();

        return view('admin.delegations.create', compact('jabatans', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan_id' => 'required|exists:jabatans,id',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:Plt,Plh',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Prevent delegating a position to the person who holds it
        $jabatan = Jabatan::find($validated['jabatan_id']);
        if ($jabatan->user_id == $validated['user_id']) {
            return back()->with('error', 'Tidak dapat mendelegasikan jabatan kepada pemegang jabatan definitif.');
        }

        Delegation::create([
            'jabatan_id' => $validated['jabatan_id'],
            'user_id' => $validated['user_id'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.delegations.index')->with('success', 'Delegasi berhasil dibuat.');
    }

    public function destroy(Delegation $delegation)
    {
        $delegation->delete();
        return redirect()->route('admin.delegations.index')->with('success', 'Delegasi berhasil dihapus.');
    }
}
