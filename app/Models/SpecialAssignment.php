<?php

namespace App\Models;

use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SpecialAssignment extends Model
{
    use HasFactory, RecordsActivity;

    protected $fillable = [
        'creator_id',
        'sk_number',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'file_path',
    ];

    /**
     * PERBAIKAN: Beritahu Laravel untuk memperlakukan kolom-kolom ini sebagai objek Tanggal (Carbon).
     * Ini akan menyelesaikan error 'format() on string'.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'special_assignment_user')
                    ->withPivot('role_in_sk')
                    ->withTimestamps();
    }

    /**
     * Get all of the special assignment's letters.
     */
    public function surat(): MorphMany
    {
        return $this->morphMany(Surat::class, 'suratable');
    }
}