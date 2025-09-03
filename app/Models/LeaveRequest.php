<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'duration_days',
        'reason',
        'address_during_leave',
        'contact_during_leave',
        'status',
        'current_approver_id',
        'rejection_reason',
        'attachment_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => RequestStatus::class,
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function leaveType() { return $this->belongsTo(LeaveType::class); }
    public function approver() { return $this->belongsTo(User::class, 'current_approver_id'); }

    /**
     * Get the official decision letter (SK) for this leave request.
     */
    public function surat()
    {
        return $this->morphOne(Surat::class, 'suratable');
    }
}
