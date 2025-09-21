<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveRequestPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return bool
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // As per the new requirement, all roles can access the leave detail page.
        return true;
    }
}
