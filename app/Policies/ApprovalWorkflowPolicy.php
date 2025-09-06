<?php

namespace App\Policies;

use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApprovalWorkflowPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->jabatan && $user->jabatan->can_manage_users) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApprovalWorkflow $approvalWorkflow): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApprovalWorkflow $approvalWorkflow): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalWorkflow $approvalWorkflow): bool
    {
        // Add a check to ensure it's not in use by any unit
        if ($approvalWorkflow->units()->exists()) {
            return false;
        }

        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApprovalWorkflow $approvalWorkflow): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApprovalWorkflow $approvalWorkflow): bool
    {
        return $this->viewAny($user);
    }
}
