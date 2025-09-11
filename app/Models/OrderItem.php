<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total_price'
    ];
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
