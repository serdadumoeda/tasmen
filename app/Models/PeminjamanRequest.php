<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanRequest extends Model
{
    use HasFactory;

    protected $guarded = [];



    public function project(): BelongsTo
    {
        // Peminjaman ini milik sebuah Project, melalui kolom 'project_id'.
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function requester(): BelongsTo
    {
        // Peminjaman ini dibuat oleh seorang User, melalui kolom 'requester_id'.
        return $this->belongsTo(User::class, 'requester_id', 'id');
    }

    public function requestedUser(): BelongsTo
    {
        // User yang diminta untuk dipinjam, melalui kolom 'requested_user_id'.
        return $this->belongsTo(User::class, 'requested_user_id', 'id');
    }

    public function approver(): BelongsTo
    {
        // User yang harus menyetujui, melalui kolom 'approver_id'.
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }
    // --- AKHIR PERBAIKAN FINAL ---
}