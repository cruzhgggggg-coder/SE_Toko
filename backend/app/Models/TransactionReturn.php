<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionReturn extends Model
{
    protected $fillable = [
        'transaction_id',
        'transaction_item_id',
        'user_id',
        'qty',
        'reason',
        'status',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
