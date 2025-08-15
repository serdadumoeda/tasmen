<?php

namespace App\Models;

use App\Scopes\HierarchicalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Project extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = ['name', 'description', 'leader_id', 'owner_id', 'start_date', 'end_date'];

    /**
     * [ACCESSOR BARU] Menghitung progres proyek secara dinamis.
     * Dihitung berdasarkan jumlah tugas yang selesai dibagi total tugas.
     *
     * @return int
     *
     * @warning Potensi N+1 Query! Pastikan relasi 'tasks' di-eager load (->with('tasks'))
     *          sebelum mengakses atribut ini pada koleksi proyek.
     */
    public function getProgressAttribute(): int
    {
        $totalTasks = $this->tasks->count();
        if ($totalTasks === 0) {
            return 0;
        }
        $completedTasks = $this->tasks->where('status', 'completed')->count();
        return round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * [ACCESSOR BARU] Menentukan status proyek secara dinamis.
     * Status ini tidak disimpan di database, tetapi dihitung setiap kali diakses.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if ($this->progress == 100) {
            return 'completed';
        }
        if ($this->end_date && $this->end_date < now() && $this->progress < 100) {
            return 'overdue';
        }
        if ($this->progress > 0) {
            return 'in_progress';
        }
        return 'pending'; // Jika progres 0 dan belum overdue
    }

    /**
     * [KODE SEBELUMNYA DIPERBAIKI] Mendapatkan kelas warna CSS berdasarkan status dinamis.
     *
     * @return string
     */
    public function getTasksCountAttribute()
    {
        return $this->tasks()->count();
    }

    public function getStatusColorClassAttribute(): string
    {
        return match ($this->status) { // Sekarang ini akan memanggil accessor getStatusAttribute
            'pending'     => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'overdue'     => 'bg-red-100 text-red-800',
            'completed'   => 'bg-green-100 text-green-800',
            default       => 'bg-gray-100 text-gray-800',
        };
    }
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
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

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

}