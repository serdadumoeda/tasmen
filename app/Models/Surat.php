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
        'template_surat_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateSurat::class, 'template_surat_id');
    }

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

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'surat_collaborators');
    }

    /**
     * The virtual folders that this letter belongs to.
     */
    public function berkas()
    {
        return $this->belongsToMany(Berkas::class, 'berkas_surat');
    }
}
