<?php

namespace App\Models;

use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory; // TAMBAHKAN BARIS INI
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory, RecordsActivity;
    
    protected $fillable = ['body', 'user_id', 'task_id'];

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
    
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