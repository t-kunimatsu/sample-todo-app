<?php

use App\Http\Controllers\Task\Api\ListController;
use App\Http\Controllers\Task\Api\StoreController;
use App\Http\Controllers\Task\Api\UpdateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('tasks')->group(function () {
        Route::get('/', [ListController::class, '__invoke']);
        Route::post('/', [StoreController::class, '__invoke']);
        Route::patch('/{id}', [UpdateController::class, '__invoke']);
    });
});
