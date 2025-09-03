<?php

namespace App\Models;

use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Surat extends Model
{
    use HasFactory, RecordsActivity;

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
        'suratable_id',     // tambahkan ke fillable
        'suratable_type',   // tambahkan ke fillable
        'klasifikasi_id',
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

    /**
     * Polymorphic relation: surat dapat dimiliki oleh Project, SK, Cuti, dll.
     */
    public function suratable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the classification for the letter.
     */
    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiSurat::class, 'klasifikasi_id');
    }

    /**
     * Accessor for project_id to be used by RecordsActivity trait.
     */
    public function getProjectIdAttribute()
    {
        if ($this->suratable instanceof Project) {
            return $this->suratable->id;
        }

        return null;
    }
}
