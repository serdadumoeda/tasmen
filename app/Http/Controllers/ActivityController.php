<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        // Eager load the user and subject to prevent N+1 query issues in the view
        $activities = Activity::with(['user', 'subject'])
                                ->latest()
                                ->paginate(50);

        return view('admin.activities.index', compact('activities'));
    }
}
