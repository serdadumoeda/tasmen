<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_workflow_id',
        'step',
        'approver_role',
        'is_final_approval',
        'condition_type',
        'condition_value',
        'action',
    ];

    protected $casts = [
        'is_final_approval' => 'boolean',
    ];

    /**
     * Get the workflow that this step belongs to.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }
}
