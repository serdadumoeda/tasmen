<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'description', 
        'deadline', 
        'progress', 
        'status', 
        'project_id', 
        'assigned_to_id'
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
}
