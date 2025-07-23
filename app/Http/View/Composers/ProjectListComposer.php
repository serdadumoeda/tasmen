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

            $query = Project::withoutGlobalScope(HierarchicalScope::class);

            if ($user->isSuperAdmin()) {
                // Superadmin melihat 7 proyek terbaru di seluruh sistem
                $quickProjects = $query->latest('updated_at')->take(7)->get();
            } else {
                // Pengguna biasa melihat proyek yang terkait dengan mereka
                $quickProjects = $query->where(function ($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhere('leader_id', $user->id)
                      ->orWhereHas('members', function ($subQuery) use ($user) {
                          $subQuery->where('users.id', $user->id);
                      });
                })
                ->latest('updated_at')
                ->take(7)
                ->get();
            }
        }
        $view->with('quickProjects', $quickProjects);
    }
}