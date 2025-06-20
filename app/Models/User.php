<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id', // <-- Tambahkan ini
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    // Relasi ke atasan langsung
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Relasi ke bawahan langsung
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // Fungsi REKURSIF untuk mendapatkan SEMUA ID bawahan di bawah user ini
    public function getAllSubordinateIds(): array
    {
        $subordinateIds = [];
        $children = $this->children()->with('children')->get(); // Eager load children

        foreach ($children as $child) {
            $subordinateIds[] = $child->id;
            // Gabungkan dengan ID bawahan dari si anak
            $subordinateIds = array_merge($subordinateIds, $child->getAllSubordinateIds());
        }

        return $subordinateIds;
    }

    // (Sisa method lainnya seperti ledProjects, projects, tasks, timeLogs tidak perlu diubah)
    public function ledProjects()
    {
        return $this->hasMany(Project::class, 'leader_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }
}