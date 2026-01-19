<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
Route::get('/categories', [\App\Http\Controllers\Public\CategoriesController::class, 'index'])->name('categories.index');
Route::get('/{categorySlug}', [\App\Http\Controllers\Public\ProductsController::class, 'index'])->name('products.index');
Route::get('/products/{productSlug}', [\App\Http\Controllers\Public\ProductsController::class, 'show'])->name('products.show');
