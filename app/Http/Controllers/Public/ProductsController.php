<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index($categorySlug)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $products = $category->products()->get();

        return view('pages.products.index', compact('products', 'category'));
    }

    public function show($productSlug)
    {
        $product = Product::query()->where('slug', $productSlug)->firstOrFail();
        $products = Product::all()->take(5);

        return view('pages.products.show', compact('product', 'products'));
    }
}
