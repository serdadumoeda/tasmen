<?php

namespace App\Enums;

enum TaskStatusKey: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case FOR_REVIEW = 'for_review';
    case COMPLETED = 'completed';
}
