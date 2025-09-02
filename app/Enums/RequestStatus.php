<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case APPROVED_BY_SUPERVISOR = 'approved_by_supervisor'; // Specific to LeaveRequest
}
