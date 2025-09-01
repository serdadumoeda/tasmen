<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surat extends Model
{
    use HasFactory;

    protected $table = 'surat';

    protected $fillable = [
        'nomor_surat',
        'perihal',
        'tanggal_surat',
        'jenis',
        'status',
        'pembuat_id',
        'penyetuju_id',
        'konten',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyetuju_id');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class);
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(LampiranSurat::class, 'surat_id');
    }
}
