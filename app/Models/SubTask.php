<?php

namespace App\Models;

use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = ['task_id', 'title', 'is_completed'];
    protected $touches = ['task'];
    protected $casts = ['is_completed' => 'boolean'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Accessor for project_id to be used by RecordsActivity trait.
     */
    public function getProjectIdAttribute()
    {
        return $this->task->project_id;
    }
}