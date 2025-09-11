<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'discount',
        'images',
        'slug',
        'barcode',
        'expiry_date',
        'origin',
        'weight_unit',
        'quantity',
        'totalQuantity',
        'purchase',
        'category_id',
        'brand_id',
        'status',
        'avg_rating',
        'total_reviews',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'images' => 'string',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'expiry_date' => 'date',
    ];
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
