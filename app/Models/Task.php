<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\RecordsActivity;

class Task extends Model
{
    use HasFactory, RecordsActivity;

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'deadline',
        'progress',
        'project_id',
        'surat_id',
        'creator_id',
        'estimated_hours',
        'task_status_id',
        'priority_level_id',
        'is_outside_office_hours',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
    ];

    /**
     * Proyek tempat tugas ini berada.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the source letter (surat) for this task, if any.
     */
    public function asalSurat()
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }

    /**
     * The status of the task.
     */
    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function priorityLevel()
    {
        return $this->belongsTo(PriorityLevel::class);
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
        // PERBAIKAN: Cek apakah relasi subTasks sudah di-load untuk menghindari N+1 query.
        $subTasks = $this->relationLoaded('subTasks') ? $this->subTasks : $this->subTasks();

        $totalSubTasks = $subTasks->count();
        
        if ($totalSubTasks > 0) {
            // Jika ada sub-tugas, hitung progress berdasarkan jumlah yang selesai.
            $completedSubTasks = $subTasks->where('is_completed', true)->count();
            $this->progress = round(($completedSubTasks / $totalSubTasks) * 100);
        }
        
        // Note: Automatic status change logic is removed from the model.
        // This responsibility is now handled by controllers or dedicated services
        // to better respect the application's approval workflow.

        $this->save();
    }
}
