<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'barcode',
        'images', 'description', 'specs',
        'unit', 'price_amount', 'sale_price_amount',
        'quantity', 'obj',
    ];

    protected $casts = [
        'obj' => 'array',
        'specs' => 'array',
        'images' => 'array',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

}
