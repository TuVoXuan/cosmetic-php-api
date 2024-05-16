<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Middleware\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function(){
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    Route::group([
        'middleware' => ['auth:api']
    ], function() {
        Route::get('admin/categories', [CategoryController::class, 'index'])->middleware('permission:category.read');
        Route::post('admin/categories', [CategoryController::class, 'store'])->middleware('permission:category.create');
        Route::put('admin/categories/{id}', [CategoryController::class, 'update'])->middleware('permission:category.update');
        Route::delete('admin/categories/{id}', [CategoryController::class, 'destroy'])->middleware('permission:category.delete');

        Route::get('admin/brands', [BrandController::class, 'index'])->middleware('permission:brand.read');
        Route::post('admin/brands', [BrandController::class, 'store'])->middleware('permission:brand.create');
        Route::post('admin/brands/{id}', [BrandController::class, 'update'])->middleware('permission:brand.update');
        Route::delete('admin/brands/{id}', [BrandController::class, 'destroy'])->middleware('permission:brand.delete');

        Route::get('admin/products', [ProductController::class, 'index'])->middleware('permission:product.read');
        Route::post('admin/products', [ProductController::class, 'create'])->middleware('product.create');
        Route::post('admin/products/{id}', [ProductController::class, 'update'])->middleware('permission:product.update');
        Route::delete('admin/products/{id}', [ProductController::class, 'destroy'])->middleware('permission:product.delete');
    });    
});
