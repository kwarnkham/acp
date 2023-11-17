<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\RoundController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('fb/login', 'fbLogin');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('user', 'user');
        Route::post('change-password', 'changePassword');
        Route::post('logout', 'logout');
    });
});

Route::controller(ItemController::class)->prefix('items')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('', 'store');
        Route::put('{item}', 'update');
    });
    Route::get('', 'index');
    Route::get('{item}', 'find');
});

Route::controller(RoundController::class)->prefix('rounds')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('', 'store');
        Route::put('{round}', 'update');
        Route::post('{round}/settle', 'settle');
    });
    Route::get('', 'index');
    Route::get('{round}', 'find');
});

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('{order}/cancel', 'cancel');
        Route::post('{order}/confirm', 'confirm');
    });

    Route::post('guest', 'store');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('', 'store');
        Route::get('', 'index');
        Route::get('{order}', 'find');
        Route::post('{order}/pay', 'pay');
    });
});


Route::controller(PictureController::class)->middleware(['auth:sanctum'])->prefix('pictures')->group(function () {
    Route::middleware(['role:admin'])->delete('{picture}', 'destroy');
});

Route::controller(PaymentMethodController::class)->middleware(['auth:sanctum'])->prefix('payment-methods')->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::post('', 'store');
        Route::post('{paymentMethod}/toggle', 'toggle');
        Route::get('{paymentMethod}', 'find');
        Route::put('{paymentMethod}', 'update');
    });

    Route::get('', 'index');
});
