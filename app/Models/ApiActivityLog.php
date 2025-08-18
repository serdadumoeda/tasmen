<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiActivityLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_activity_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'api_client_id',
        'ip_address',
        'method',
        'path',
        'status_code',
        'response_time_ms',
    ];

    /**
     * Get the client that owns the log.
     */
    public function apiClient()
    {
        return $this->belongsTo(ApiClient::class);
    }
}
