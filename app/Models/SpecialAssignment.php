<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sk_number',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'file_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}