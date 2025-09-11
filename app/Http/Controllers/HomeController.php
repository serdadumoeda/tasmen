<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\ExecutiveSummaryController;
use App\Http\Controllers\GlobalDashboardController;
use App\Services\InsightService;

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

        if ($user->isStaff()) {
            // Redirect staff to their specific dashboard
            return redirect()->route('staff.dashboard');
        }

        // Forward the request to the GlobalDashboardController and return its response
        return app(GlobalDashboardController::class)->index();
    }
}
