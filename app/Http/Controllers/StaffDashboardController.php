<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Activity;
use App\Models\Task;

class StaffDashboardController extends Controller
{
    /**
     * Display the staff member's dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch tasks assigned to the user that are not completed.
        $tasks = $user->tasks()
            ->whereHas('status', function ($query) {
                $query->where('key', '!=', 'completed');
            })
            ->with('project') // Eager load project for context
            ->latest()
            ->get();

        // Fetch the user's 15 most recent activities.
        $activities = Activity::where('user_id', $user->id)
            ->with('subject') // Eager load the subject of the activity (e.g., the task that was updated)
            ->latest()
            ->limit(15)
            ->get();

        return view('staff.dashboard', compact('user', 'tasks', 'activities'));
    }
}
