<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', ['name' => 'you', 'customer_counts' => 3]);
})->middleware(['verify.shopify'])->name('home');
