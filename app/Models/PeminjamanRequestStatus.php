<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanRequestStatus extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_request_statuses';
    protected $fillable = ['key', 'label'];

    public function peminjamanRequests()
    {
        return $this->hasMany(PeminjamanRequest::class, 'status_id');
    }
}
