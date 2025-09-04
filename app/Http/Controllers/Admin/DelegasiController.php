<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delegasi;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelegasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $delegasi = Delegasi::with('user', 'jabatan', 'createdBy')->latest()->paginate(15);
        return view('admin.delegations.index', compact('delegasi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        return view('admin.delegations.create', compact('users', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan_id' => 'required|exists:jabatans,id',
            'user_id' => 'required|exists:users,id',
            'jenis' => 'required|in:Plt,Plh',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
        ]);

        Delegasi::create($validated + ['created_by_user_id' => Auth::id()]);

        return redirect()->route('admin.delegasi.index')->with('success', 'Delegasi berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Delegasi $delegasi)
    {
        // Not implemented as per user instructions (usually not needed for admin panels)
        return redirect()->route('admin.delegasi.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Delegasi $delegasi)
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        return view('admin.delegations.edit', compact('delegasi', 'users', 'jabatans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Delegasi $delegasi)
    {
        $validated = $request->validate([
            'jabatan_id' => 'required|exists:jabatans,id',
            'user_id' => 'required|exists:users,id',
            'jenis' => 'required|in:Plt,Plh',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
        ]);

        $delegasi->update($validated);

        return redirect()->route('admin.delegasi.index')->with('success', 'Delegasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delegasi $delegasi)
    {
        $delegasi->delete();
        return redirect()->route('admin.delegasi.index')->with('success', 'Delegasi berhasil dihapus.');
    }
}
