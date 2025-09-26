<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
    ];

    private const DEFAULT_PROGRESS_MAP = [
        'pending' => 0,
        'in_progress' => 50,
        'for_review' => 90,
        'completed' => 100,
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function defaultProgress(): ?int
    {
        return self::DEFAULT_PROGRESS_MAP[$this->key] ?? null;
    }

    public static function defaultProgressForKey(string $key): ?int
    {
        return self::DEFAULT_PROGRESS_MAP[$key] ?? null;
    }
}
