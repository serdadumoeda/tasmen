<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveApprovalService
{
    /**
     * Process the approval of a leave request and determine the next state.
     *
     * @param LeaveRequest $leaveRequest The request being approved.
     * @param User $approver The user who is approving the request.
     * @return array The next state of the request, containing 'status' and 'next_approver_id'.
     */
    public function processApproval(LeaveRequest $leaveRequest, User $approver): array
    {
        $applicant = $leaveRequest->user;
        $nextApprover = $approver->atasan;

        // Rule 1: Approval is final if the current approver is Eselon II.
        if ($approver->role === User::ROLE_ESELON_II) {
            return $this->finalApproval();
        }

        // Rule 2: Approval is final if the current approver is Eselon I and the applicant is Eselon II.
        if ($approver->role === User::ROLE_ESELON_I && $applicant->role === User::ROLE_ESELON_II) {
            return $this->finalApproval();
        }

        // If a next approver exists and we haven't hit a final approval rule, forward it.
        if ($nextApprover) {
            return $this->forwardTo($nextApprover);
        }

        // Default case: No next approver, so this is the final approval.
        return $this->finalApproval();
    }

    /**
     * Returns the state for a final approval.
     */
    private function finalApproval(): array
    {
        return [
            'status' => 'approved',
            'next_approver_id' => null,
        ];
    }

    /**
     * Returns the state for forwarding the request.
     */
    private function forwardTo(User $nextApprover): array
    {
        return [
            'status' => 'approved_by_supervisor',
            'next_approver_id' => $nextApprover->id,
        ];
    }
}
