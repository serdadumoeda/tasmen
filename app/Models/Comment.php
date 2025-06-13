<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // TAMBAHKAN BARIS INI
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    
    protected $fillable = ['body', 'user_id', 'task_id'];

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
    
    public function task() 
    { 
        return $this->belongsTo(Task::class); 
    }
}