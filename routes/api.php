<?php

use App\Http\Controllers\Api\V1\DriverController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('v1')->group(function () {
    Route::prefix('drivers')->group(function () {
        // GET /api/v1/drivers
        Route::get('/', [DriverController::class, 'index']);
        Route::get('/status/{status}', [DriverController::class, 'getDriverByStatus']);
    });
});
