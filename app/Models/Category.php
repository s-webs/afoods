<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'sort_order'
    ];

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany|Category
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
