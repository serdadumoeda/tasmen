<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\RecordsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, RecordsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id',
        'eselon_2_id',
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
    
    // Definisikan event apa saja yang ingin dicatat untuk model User
    protected static array $recordableEvents = ['created', 'updated', 'deleted'];

    /**
     * Override method ini karena aktivitas User tidak memiliki project_id
     * dan untuk menangani kasus seeder.
     */
    public function recordActivity($description)
    {
        $this->activity()->create([
            // PERBAIKAN KUNCI ADA DI SINI:
            // Jika ada user yang login, gunakan ID-nya.
            // Jika tidak (misal: saat seeder berjalan), gunakan ID dari user yang baru dibuat itu sendiri.
            'user_id' => auth()->id() ?? $this->id,
            'description' => $description,
            'project_id' => null, // Aktivitas user tidak terikat proyek
            'before' => $this->getActivityChanges('before'),
            'after' => $this->getActivityChanges('after')
        ]);
    }
    
    // Override method ini karena User tidak punya relasi 'project'
    protected function activityOwner()
    {
        return auth()->user() ?? $this;
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
        // PERBAIKAN KECIL: eager load children secara rekursif untuk performa lebih baik
        $children = $this->children()->with('children')->get();

        foreach ($children as $child) {
            $subordinateIds[] = $child->id;
            // Gabungkan dengan ID bawahan dari si anak
            $subordinateIds = array_merge($subordinateIds, $child->getAllSubordinateIds());
        }

        return $subordinateIds;
    }
    
    public function isSubordinateOf(User $potentialSuperior): bool
    {
        $current = $this;
        while ($current->parent) {
            if ($current->parent->id === $potentialSuperior->id) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }
    
    public function isTopLevelManager(): bool
    {
        return in_array($this->role, ['superadmin', 'Eselon I', 'Eselon II']);
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
    }

    public function canCreateProjects(): bool
    {
        return in_array($this->role, ['superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
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