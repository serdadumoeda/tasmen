<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\RecordsActivity;

class Project extends Model
{
    use HasFactory, RecordsActivity;

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

    public function activities()
    {
        return $this->hasMany(Activity::class)->latest();
    }
}
