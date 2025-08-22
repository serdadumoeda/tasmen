<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    public $timestamps = false; // This table doesn't need created_at/updated_at

    protected $fillable = [
        'user_id',
        'year',
        'total_days',
        'days_taken',
        'carried_over_days',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
