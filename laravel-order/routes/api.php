<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// 方法一：單獨定義路由
Route::get('/order', [OrderController::class, 'index']);
Route::post('/order', [OrderController::class, 'store']);
Route::get('/order/{order}', [OrderController::class, 'show']);
Route::put('/order/{order}', [OrderController::class, 'update']);
Route::delete('/order/{order}', [OrderController::class, 'destroy']);