<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\ExecutiveSummaryController;
use App\Http\Controllers\GlobalDashboardController;
use App\Services\InsightService;
use App\Models\Activity;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request, InsightService $insightService)
    {
        $user = Auth::user();

        if ($user->isTopLevelManager()) {
            // Forward the request to the ExecutiveSummaryController and return its response
            return app(ExecutiveSummaryController::class)->index($insightService);
        }

        // Forward the request to the GlobalDashboardController and return its response
        return app(GlobalDashboardController::class)->index();
    }

    public function myDashboard()
    {
        $user = Auth::user();

        // 1. Get all tasks assigned to the current user
        $tasks = $user->tasks()
            ->with('project') // Eager load project info
            ->orderBy('deadline', 'asc')
            ->get();

        // 2. Get stats for the user's tasks
        $myTasks = $user->tasks;
        $stats = [
            'total' => $myTasks->count(),
            'pending' => $myTasks->where('status', 'pending')->count(),
            'in_progress' => $myTasks->where('status', 'in_progress')->count(),
            'completed' => $myTasks->where('status', 'completed')->count(),
            'overdue' => $myTasks->where('deadline', '<', now())->where('status', '!=', 'completed')->count(),
        ];

        // 3. Get recent activities initiated by the user
        $myActivities = Activity::where('user_id', $user->id)
            ->with('subject')
            ->latest()
            ->take(10)
            ->get();

        return view('my-dashboard', compact('tasks', 'stats', 'myActivities'));
    }
}
