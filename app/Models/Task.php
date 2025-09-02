<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\RecordsActivity;

class Task extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'deadline',
        'progress',
        'project_id',
        'estimated_hours',
        'task_status_id',
        'priority_level_id',
        // Keep old columns for data migration, they will be dropped later
        'status',
        'priority',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
    ];

    // Eager load relationships by default to avoid N+1 issues
    protected $with = ['status', 'priorityLevel'];

    /**
     * Relationships
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function priorityLevel()
    {
        return $this->belongsTo(PriorityLevel::class, 'priority_level_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class)->latest();
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    public function subTasks()
    {
        return $this->hasMany(SubTask::class);
    }

    /**
     * Accessors & Mutators
     */
    public function getStatusColorClassAttribute(): string
    {
        // This is no longer needed if color is stored in task_statuses table
        // But we can keep it as a fallback or if the color logic is complex.
        // For now, let's assume the color is handled on the frontend or a different way.
        // Or, let's assume the `task_statuses` table will have a `color_class` column.
        // The user's migration didn't specify it, but it's a logical step.
        // I will assume it will be added later or is not needed for now.
        return 'bg-gray-100 text-gray-800'; // Default fallback
    }

    /**
     * Methods
     */
    public function recalculateProgress()
    {
        $subTasks = $this->relationLoaded('subTasks') ? $this->subTasks : $this->subTasks();
        $totalSubTasks = $subTasks->count();
        
        if ($totalSubTasks > 0) {
            $completedSubTasks = $subTasks->where('is_completed', true)->count();
            $this->progress = round(($completedSubTasks / $totalSubTasks) * 100);
        } else {
            if ($this->status && $this->status->key === 'completed') {
                $this->progress = 100;
            } elseif ($this->status && $this->status->key === 'pending') {
                $this->progress = 0;
            }
        }

        if ($this->status && $this->status->key !== 'for_review') {
            $newStatusKey = 'pending';
            if ($this->progress >= 100) {
                $newStatusKey = 'completed';
            } elseif ($this->progress > 0) {
                $newStatusKey = 'in_progress';
            }

            if ($this->status->key !== $newStatusKey) {
                $newStatus = TaskStatus::where('key', $newStatusKey)->first();
                if ($newStatus) {
                    $this->task_status_id = $newStatus->id;
                }
            }
        }
        
        $this->save();
    }
}
