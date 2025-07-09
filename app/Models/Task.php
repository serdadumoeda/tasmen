<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\RecordsActivity;

class Task extends Model
{
    use HasFactory, RecordsActivity; 

    protected $fillable = [
        'title', 
        'description', 
        'deadline', 
        'progress', 
        'status', 
        'project_id', 
        'estimated_hours'
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

    // Method baru untuk kalkulasi progress
    public function recalculateProgress()
    {
        $totalSubTasks = $this->subTasks()->count();
        
        if ($totalSubTasks > 0) {
            // Jika ada sub-tugas, hitung progress berdasarkan jumlah yang selesai.
            $completedSubTasks = $this->subTasks()->where('is_completed', true)->count();
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
        if (!$this->pending_review) {
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
