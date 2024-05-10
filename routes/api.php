<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function(){
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    Route::group([
        'middleware' => ['auth:api']
    ], function() {
        Route::get('admin/categories', [CategoryController::class, 'index']);
        Route::post('admin/categories', [CategoryController::class, 'store']);
        Route::put('admin/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('admin/categories/{id}', [CategoryController::class, 'destroy']);

        Route::get('admin/brands', [BrandController::class, 'index']);
        Route::post('admin/brands', [BrandController::class, 'store']);
    });    
});
