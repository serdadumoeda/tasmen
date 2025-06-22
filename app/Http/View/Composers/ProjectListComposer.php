<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectListComposer
{
    public function compose(View $view)
    {
        $quickProjects = collect();
        if (Auth::check()) {
            $user = Auth::user();
            $quickProjects = $user->projects()
                                 ->latest('updated_at')
                                 ->take(7)
                                 ->get();
        }
        $view->with('quickProjects', $quickProjects);
    }
}