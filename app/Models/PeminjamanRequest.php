<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanRequest extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'project_id',
        'requester_id',
        'requested_user_id',
        'approver_id',
        'status',
        'message',
        'rejection_reason',
        'due_date',
    ];

    protected $casts = [
        'status' => RequestStatus::class,
        'due_date' => 'datetime',
    ];

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

    /**
     * Get the official letter associated with this loan request.
     */
    public function surat()
    {
        return $this->morphOne(Surat::class, 'suratable');
    }
}