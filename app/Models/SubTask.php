<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;
    protected $fillable = ['task_id', 'title', 'is_completed'];
    protected $casts = ['is_completed' => 'boolean'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}