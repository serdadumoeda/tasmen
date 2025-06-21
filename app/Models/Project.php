<?php

namespace App\Models;

use App\Scopes\HierarchicalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\RecordsActivity;

class Project extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = ['name', 'description', 'leader_id', 'owner_id', 'start_date', 'end_date'];


    protected static function booted(): void
    {
        
        static::addGlobalScope(new HierarchicalScope);
    }

    /**
     * Mendapatkan pemilik (owner) dari proyek.
     * INI ADALAH METHOD BARU YANG PERLU DITAMBAHKAN
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

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