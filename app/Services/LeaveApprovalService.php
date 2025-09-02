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
            return $this->fallbackApprovalLogic($approver);
        }

        $currentStepNumber = $leaveRequest->last_approved_step ?? 0;
        $steps = $workflow->steps()->where('step', '>', $currentStepNumber)->orderBy('step')->get();

        foreach ($steps as $step) {
            if ($this->checkCondition($leaveRequest, $step)) {
                // Condition met, this is our next step
                if ($step->is_final_approval) {
                    return $this->finalApproval($leaveRequest);
                }

                $nextApprover = $this->findNextApprover($applicant, $step->approver_role);
                if ($nextApprover) {
                    return $this->forwardTo($nextApprover, $step->step);
                }
            }
            // If condition is not met, the loop continues to the next step
        }

        // If loop finishes (no more steps or no conditions met), it's a final approval.
        return $this->finalApproval($leaveRequest);
    }

    /**
     * Check if the conditions for a workflow step are met.
     *
     * @param LeaveRequest $leaveRequest
     * @param ApprovalWorkflowStep $step
     * @return boolean
     */
    private function checkCondition(LeaveRequest $leaveRequest, ApprovalWorkflowStep $step): bool
    {
        // If no condition is set, it's always met.
        if (is_null($step->condition_type) || is_null($step->condition_value)) {
            return true;
        }

        switch ($step->condition_type) {
            case 'leave_duration_greater_than':
                $leaveDuration = $leaveRequest->duration_in_days; // Assuming this attribute exists
                return $leaveDuration > (int)$step->condition_value;

            case 'applicant_role_is':
                return $leaveRequest->user->role->name === $step->condition_value;

            case 'applicant_role_in':
                $roles = explode(',', $step->condition_value);
                return in_array($leaveRequest->user->role->name, $roles);

            // Add other conditions here as needed
            // case '...':

            default:
                // Unknown condition type defaults to true to not block the workflow.
                return true;
        }
    }

    private function getWorkflowForUser(User $user): ?ApprovalWorkflow
    {
        return $user->unit->approvalWorkflow ?? ApprovalWorkflow::find(1);
    }

    private function findNextApprover(User $applicant, string $requiredRoleName): ?User
    {
        $supervisor = $applicant->atasan;
        $depth = 0;
        $maxDepth = 10; // Failsafe

        while ($supervisor && $depth < $maxDepth) {
            if ($supervisor->role && $supervisor->role->name === $requiredRoleName) {
                return $supervisor;
            }
            $supervisor = $supervisor->atasan;
            $depth++;
        }
        return null;
    }

    private function finalApproval(LeaveRequest $leaveRequest): array
    {
        return [
            'status' => 'approved',
            'next_approver_id' => null,
            'last_approved_step' => $leaveRequest->last_approved_step
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

    private function fallbackApprovalLogic(User $approver): array
    {
        $nextApprover = $approver->atasan;
        if ($nextApprover) {
            return ['status' => 'approved_by_supervisor', 'next_approver_id' => $nextApprover->id, 'last_approved_step' => 0];
        }
        return ['status' => 'approved', 'next_approver_id' => null, 'last_approved_step' => 0];
    }
}
