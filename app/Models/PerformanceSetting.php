<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceSetting extends Model
{
    use HasFactory;

    protected $table = 'performance_settings';
    protected $fillable = ['key', 'value'];

    // akses nilai (decode dari JSON)
    public function getValueAttribute($value)
    {
        $decoded = json_decode($value, true);
        return $decoded === null ? $value : $decoded;
    }

    // set nilai (encode ke JSON jika perlu)
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }

    // helper untuk mengambil nilai dengan kunci tertentu
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
