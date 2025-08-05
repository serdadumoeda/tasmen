<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetRealization extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_item_id',
        'amount',
        'transaction_date',
        'description',
        'receipt_path',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function budgetItem()
    {
        return $this->belongsTo(BudgetItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
