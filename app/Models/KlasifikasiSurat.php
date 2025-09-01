<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KlasifikasiSurat extends Model
{
    use HasFactory;

    protected $table = 'klasifikasi_surat';

    protected $fillable = [
        'kode',
        'deskripsi',
        'parent_id',
    ];

    /**
     * Get the parent classification.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiSurat::class, 'parent_id');
    }

    /**
     * Get the child classifications.
     */
    public function children(): HasMany
    {
        return $this->hasMany(KlasifikasiSurat::class, 'parent_id');
    }

    /**
     * Get all surat associated with this classification.
     */
    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'klasifikasi_id');
    }
}
