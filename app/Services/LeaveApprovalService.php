<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;

class LeaveApprovalService
{
    /**
     * Process the approval of a leave request using the dynamic workflow engine.
     *
     * @param LeaveRequest $leaveRequest The request being approved.
     * @param User $approver The user who is approving the request.
     * @return array The next state of the request.
     */
    public function processApproval(LeaveRequest $leaveRequest, User $approver): array
    {
        $applicant = $leaveRequest->user;
        $workflow = $this->getWorkflowForUser($applicant);

        if (!$workflow) {
            // Fallback to old logic if no workflow is defined
            return $this->fallbackApprovalLogic($approver);
        }

        $currentStepNumber = $leaveRequest->last_approved_step ?? 0;
        $nextStep = $workflow->steps()->where('step', '>', $currentStepNumber)->orderBy('step')->first();

        if (!$nextStep || $nextStep->is_final_approval) {
            return $this->finalApproval($leaveRequest);
        }

        $nextApprover = $this->findNextApprover($applicant, $nextStep->approver_role);

        if ($nextApprover) {
            return $this->forwardTo($nextApprover, $nextStep->step);
        }

        // If no next approver can be found (e.g., top of hierarchy), it's a final approval.
        return $this->finalApproval($leaveRequest);
    }

    private function getWorkflowForUser(User $user): ?ApprovalWorkflow
    {
        // Try to find a workflow assigned to the user's unit, otherwise use a default.
        return $user->unit->approvalWorkflow ?? ApprovalWorkflow::find(1); // Assumes a default workflow with ID 1
    }

    private function findNextApprover(User $applicant, string $requiredRole): ?User
    {
        $supervisor = $applicant->getAtasanLangsung();
        $depth = 0;
        $maxDepth = 10; // Failsafe to prevent infinite loops

        while ($supervisor && $depth < $maxDepth) {
            if ($supervisor->hasRole($requiredRole)) {
                return $supervisor;
            }
            $supervisor = $supervisor->getAtasanLangsung();
            $depth++;
        }

        return null; // No approver with the required role found in the hierarchy.
    }

    private function finalApproval(LeaveRequest $leaveRequest): array
    {
        // On final approval, we don't need to update the step, just the status.
        return [
            'status' => 'approved',
            'next_approver_id' => null,
            'last_approved_step' => $leaveRequest->last_approved_step // Keep the last step number
        ];
    }

    private function forwardTo(User $nextApprover, int $nextStepNumber): array
    {
        return [
            'status' => 'approved_by_supervisor',
            'next_approver_id' => $nextApprover->id,
            'last_approved_step' => $nextStepNumber
        ];
    }

    // The old logic as a fallback.
    private function fallbackApprovalLogic(User $approver): array
    {
        $nextApprover = $approver->getAtasanLangsung();
        if ($nextApprover) {
            // Note: In fallback mode, we don't have a step number to track.
            return ['status' => 'approved_by_supervisor', 'next_approver_id' => $nextApprover->id, 'last_approved_step' => 0];
        }
        return ['status' => 'approved', 'next_approver_id' => null, 'last_approved_step' => 0];
    }
}
