<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LampiranSurat extends Model
{
    use HasFactory;

    protected $table = 'lampiran_surat';

    protected $fillable = [
        'surat_id',
        'nama_file',
        'path_file',
        'tipe_file',
        'ukuran_file',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }
}
