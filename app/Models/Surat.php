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
        'status',
        'pembuat_id',
        'file_path',
        'suratable_id',
        'suratable_type',
        'klasifikasi_id',
        'collaborators',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'collaborators' => 'array',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class);
    }

    public function suratable(): MorphTo
    {
        return $this->morphTo();
    }

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiSurat::class, 'klasifikasi_id');
    }

    public function getProjectIdAttribute()
    {
        if ($this->suratable instanceof Project) {
            return $this->suratable->id;
        }
        return null;
    }

    public function isCollaborator(User $user): bool
    {
        return in_array($user->id, $this->collaborators ?? []);
    }

    public function berkas()
    {
        return $this->belongsToMany(Berkas::class, 'berkas_surat');
    }
}
