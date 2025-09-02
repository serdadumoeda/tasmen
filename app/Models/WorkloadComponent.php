<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_type_id',
        'name',
        'volume',
        'output_unit',
        'time_norm',
    ];

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }
}
