<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'leader_id'];

    /**
     * Mendapatkan ketua tim (leader) dari proyek.
     */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Mendapatkan semua anggota tim dari proyek.
     */
    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Mendapatkan semua tugas dalam proyek.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
