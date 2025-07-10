<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanRequest extends Model
{
    use HasFactory;

    // Izinkan semua kolom untuk diisi secara massal
    protected $guarded = [];

    // Definisikan relasi untuk mempermudah pengambilan data terkait
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requestedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}