<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('change-password', 'changePassword');
        Route::post('logout', 'logout');
    });
});

Route::controller(ItemController::class)->middleware(['auth:sanctum'])->prefix('items')->group(function () {
    Route::post('', 'store');
    Route::get('', 'index');
    Route::get('{item}', 'find');
    Route::put('{item}', 'update');
});

Route::controller(TicketController::class)->middleware(['auth:sanctum'])->prefix('tickets')->group(function () {
    Route::get('', 'index');
    Route::get('{ticket}', 'find');
    Route::put('{ticket}', 'update');
});

Route::controller(UserController::class)->prefix('users')->group(function () {
    Route::post('', 'store');
    Route::post('login', 'login');
    Route::post('logout', 'logout');
});
