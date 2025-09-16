<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berkas extends Model
{
    use HasFactory;

    protected $table = 'berkas';

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    /**
     * Get the user who created this virtual folder.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The letters that belong to this virtual folder.
     */
    public function surat()
    {
        return $this->hasMany(Surat::class);
    }
}
