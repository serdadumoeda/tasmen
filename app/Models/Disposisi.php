<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposisi extends Model
{
    use HasFactory;

    protected $table = 'disposisi';

    protected $fillable = [
        'surat_id',
        'pengirim_id',
        'penerima_id',
        'instruksi',
        'tanggal_disposisi',
        'status_baca',
        'parent_id',
    ];

    protected $casts = [
        'tanggal_disposisi' => 'datetime',
        'status_baca' => 'boolean',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    /**
     * Get the parent disposition.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class, 'parent_id');
    }

    /**
     * Get the child dispositions.
     */
    public function children()
    {
        return $this->hasMany(Disposisi::class, 'parent_id');
    }

    /**
     * The users who are CC'd on this disposition.
     */
    public function tembusanUsers()
    {
        return $this->belongsToMany(User::class, 'disposisi_tembusan', 'disposisi_id', 'user_id');
    }
}
