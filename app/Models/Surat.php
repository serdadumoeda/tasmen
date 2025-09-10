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
        'file_path', // Added file_path
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

    /**
     * Check if a user is a collaborator on this letter.
     *
     * @param User $user
     * @return boolean
     */
    public function isCollaborator(User $user): bool
    {
        return in_array($user->id, $this->collaborators ?? []);
    }

    /**
     * The virtual folders that this letter belongs to.
     */
    public function berkas()
    {
        return $this->belongsToMany(Berkas::class, 'berkas_surat');
    }
}
