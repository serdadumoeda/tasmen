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
        'assigned_to_id',
        'estimated_hours'
    ];

    /**
     * Proyek tempat tugas ini berada.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User yang ditugaskan untuk tugas ini.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
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
}
