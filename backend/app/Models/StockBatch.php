<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $fillable = [
        'product_id', 
        'batch_number', 
        'qty', 
        'current_qty', 
        'buy_price', 
        'sell_price', 
        'expired_date',
        'rack_location'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
