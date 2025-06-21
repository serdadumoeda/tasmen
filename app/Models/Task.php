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
        'deadline', 
        'progress', 
        'status', 
        'project_id', 
        'estimated_hours'
    ];

    /**
     * Proyek tempat tugas ini berada.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
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
    // Relasi baru ke sub_tasks
    public function subTasks()
    {
        return $this->hasMany(SubTask::class);
    }

    // Method baru untuk kalkulasi progress
    public function recalculateProgress()
    {
        $totalSubTasks = $this->subTasks()->count();
        if ($totalSubTasks === 0) {
            // Jika tidak ada sub-task, progress bisa dianggap 0 atau 100 tergantung status
            $this->progress = ($this->status === 'completed') ? 100 : 0;
        } else {
            $completedSubTasks = $this->subTasks()->where('is_completed', true)->count();
            $this->progress = round(($completedSubTasks / $totalSubTasks) * 100);
        }
        $this->save();
    }
}
