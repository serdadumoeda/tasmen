<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class WorkloadAnalysisController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        if (!$currentUser->isTopLevelManager()) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        // Fungsi rekursif untuk memuat data bawahan secara efisien
        $relations = [
            'tasks' => fn($q) => $q->whereIn('status', ['pending', 'in_progress']),
            'specialAssignments' => fn($q) => $q->where('status', 'AKTIF')
        ];

        $loadRecursively = function ($query) use (&$loadRecursively, $relations) {
            return $query->with(array_merge($relations, ['children' => $loadRecursively]));
        };

        // Query dasar untuk mengambil user
        $userQuery = User::query();

        if ($currentUser->role === 'Eselon II') {
            // Jika Eselon II, ambil dirinya sendiri sebagai top level
            $topLevelUsers = $userQuery->where('id', $currentUser->id)->with(['children' => $loadRecursively] + $relations)->get();
        } else { // Untuk Superadmin dan Eselon I
            // Ambil semua user yang tidak punya atasan (pimpinan tertinggi)
            $topLevelUsers = $userQuery->whereNull('parent_id')->with(['children' => $loadRecursively] + $relations)->get();
        }
        
        return view('workload-analysis', compact('topLevelUsers'));
    }
}