<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'products',
        'price',
    ];

    protected $casts = [
        'products' => 'array',
    ];

    public function getProductsAsItems(): array
    {
        $products = Product::whereIn('id', collect($this->products)->pluck('product_id'))->get()->keyBy('id');

        return collect($this->products)->map(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            return [
                'product_id' => $item['product_id'],
                'name_snapshot' => $product?->name ?? '',
                'price' => $product ? ($product->sale_price_amount > 0 ? $product->sale_price_amount : $product->price_amount) : 0,
                'quantity' => (int) ($item['quantity'] ?? 1),
            ];
        })->values()->all();
    }
}
