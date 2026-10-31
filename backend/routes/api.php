<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [SiteController::class, 'settings']);
Route::get('home', [SiteController::class, 'home']);
Route::get('pages/{slug}', [SiteController::class, 'page']);
Route::get('delivery-zones', [SiteController::class, 'deliveryZones']);
Route::get('sitemap', [SiteController::class, 'sitemap']);
Route::get('sections/{key}', [SiteController::class, 'sections']);

Route::get('categories', [CatalogController::class, 'categories']);
Route::get('categories/{slug}', [CatalogController::class, 'category']);
Route::get('products', [CatalogController::class, 'products']);
Route::get('products/{slug}', [CatalogController::class, 'product']);
Route::post('products/{slug}/view', [CatalogController::class, 'view'])->middleware('throttle:60,1');

Route::post('orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
Route::get('orders/{number}', [OrderController::class, 'track'])->middleware('throttle:20,1');
