<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\RecordsActivity;

class Task extends Model
{
    use HasFactory, RecordsActivity; 

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    protected $fillable = [
        'title',
        'description',
        'deadline',
        'progress',
        'project_id',
        'estimated_hours',
        'status',
        'priority',
    ];

    /**
     * Proyek tempat tugas ini berada.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class)->latest();
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }
    // Relasi baru ke sub_tasks
    public function subTasks()
    {
        return $this->hasMany(SubTask::class);
    }

    public function getStatusColorClassAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'for_review'  => 'bg-orange-100 text-orange-800',
            'completed'   => 'bg-green-100 text-green-800',
            default       => 'bg-gray-100 text-gray-800',
        };
    }

    // Method baru untuk kalkulasi progress
    public function recalculateProgress()
    {
        // PERBAIKAN: Cek apakah relasi subTasks sudah di-load untuk menghindari N+1 query.
        $subTasks = $this->relationLoaded('subTasks') ? $this->subTasks : $this->subTasks();

        $totalSubTasks = $subTasks->count();
        
        if ($totalSubTasks > 0) {
            // Jika ada sub-tugas, hitung progress berdasarkan jumlah yang selesai.
            // Logika disederhanakan: where()->count() berfungsi baik pada collection maupun query builder.
            $completedSubTasks = $subTasks->where('is_completed', true)->count();

            $this->progress = round(($completedSubTasks / $totalSubTasks) * 100);
        } else {
            // Jika tidak ada sub-tugas, progress ditentukan oleh status manual.
            // Ini untuk tugas sederhana tanpa rincian.
            if ($this->status === 'completed') {
                $this->progress = 100;
            } elseif ($this->status === 'pending') {
                $this->progress = 0;
            }
            // Jika statusnya in_progress tapi tidak punya sub-tugas, progress-nya tidak diubah.
        }

        // ==========================================================
        // =============      LOGIKA PERPINDAHAN OTOMATIS      ============
        // ==========================================================
        // Logika ini hanya berjalan jika tugas tidak sedang dalam proses review manual.
        if ($this->status !== 'for_review') {
            if ($this->progress >= 100) {
                $this->status = 'completed'; // Jika progress 100%, otomatis pindah ke Selesai.
            } elseif ($this->progress > 0) {
                $this->status = 'in_progress'; // Jika progress antara 1-99%, otomatis pindah ke Dikerjakan.
            } else {
                $this->status = 'pending'; // Jika progress 0%, kembali ke Menunggu.
            }
        }
        
        // Simpan semua perubahan (progress dan status) ke database.
        $this->save();
    }
}
