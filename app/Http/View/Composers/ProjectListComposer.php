<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Project; 

class ProjectListComposer
{
    public function compose(View $view)
    {
        $quickProjects = collect();
        if (Auth::check()) {
            $user = Auth::user();
    
            // PERBAIKAN DIMULAI DI SINI
            // Query ini akan mengambil proyek jika user adalah:
            // 1. Owner
            // 2. Leader
            // 3. ATAU Anggota (member)
            $quickProjects = Project::where('owner_id', $user->id)
                                 ->orWhere('leader_id', $user->id)
                                 ->orWhereHas('members', function ($query) use ($user) {
                                     $query->where('users.id', $user->id);
                                 })
                                 ->latest('updated_at') // Urutkan berdasarkan yang terbaru di-update
                                 ->take(7)
                                 ->get();
            // AKHIR PERBAIKAN
        }
        $view->with('quickProjects', $quickProjects);
    }
}