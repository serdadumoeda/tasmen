<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorityLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
