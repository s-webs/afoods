<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
Route::get('/categories', [\App\Http\Controllers\Public\CategoriesController::class, 'index'])->name('categories.index');
