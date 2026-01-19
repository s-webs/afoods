<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $slides = Slide::query()->where('is_active', 1)->get();
        $products = Product::all();
        return view('pages.home.index', compact('slides', 'products'));
    }
}
