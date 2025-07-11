<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use HasFactory;

    /**
     * REKOMENDASI: Definisikan konstanta untuk kategori agar terpusat dan mudah dikelola.
     * Ini akan memperbaiki error yang terjadi.
     */
    public const CATEGORIES = [
        'HONORARIUM' => 'Honorarium & Uang Harian',
        'PERJALANAN_DINAS' => 'Perjalanan Dinas',
        'PENGADAAN_BARANG_JASA' => 'Pengadaan Barang/Jasa',
        'LAINNYA' => 'Lainnya',
    ];

    protected $fillable = [
        'project_id',
        'task_id',
        'category',
        'item_name',
        'quantity',
        'frequency',
        'unit_price',
        'total_cost',
        'description',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function realizations()
    {
        return $this->hasMany(BudgetRealization::class);
    }

    // Tambahkan accessor untuk menghitung total realisasi dengan mudah
    public function getRealizedCostAttribute()
    {
        // Menggunakan cache agar tidak query berulang kali
        if (! $this->relationLoaded('realizations')) {
            $this->load('realizations');
        }
        return $this->realizations->sum('amount');
    }

    // Accessor untuk sisa anggaran
    public function getRemainingCostAttribute()
    {
        return $this->total_cost - $this->realized_cost;
    }
}