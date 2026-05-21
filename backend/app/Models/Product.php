<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 
        'sku', 
        'category',
        'category_id', 
        'supplier_id',
        'unit', 
        'min_stock', 
        'is_low_stock', 
        'base_buy_price', 
        'base_sell_price',
        'description',
        'image'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->stockBatches()->sum('current_qty');
    }

    public function updateLowStockStatus()
    {
        $totalStock = $this->getTotalStockAttribute();
        $this->is_low_stock = ($totalStock <= $this->min_stock);
        $this->save();
        return $this->is_low_stock;
    }
}
