<?php

use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\OrderMappingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MapperController;
use App\Http\Controllers\Api\V1\AllocationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StaffIMController;
use App\Http\Controllers\Api\V1\SupervisorController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'getProfile'])->middleware('auth:sanctum');

        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
    });
    Route::prefix('supervisor')->middleware(['auth','role:1'])->group(function(){
        Route::put('/activate/{username}',[SupervisorController::class,'activateUser']);
    });
    Route::prefix('drivers')->middleware('api.key')->group(function () {
        // GET /api/v1/drivers
        Route::get('/', [DriverController::class, 'index']);
        Route::get('/status/{status}', [DriverController::class, 'getDriverByStatus']);
        Route::get('/{id}',[DriverController::class,'getDriverById']);
        Route::put('/update/status',[DriverController::class,'updateStatusDriver']);
    });
    Route::prefix("staff-im")->group(function(){
        Route::get("/",[StaffIMController::class,'index']);
    });
    Route::post('/order/mapping',[OrderMappingController::class,'handle']);
    Route::post('/mapper', [MapperController::class, 'ingest']);
    
    Route::post('/allocation/run', [AllocationController::class, 'run']); 
    Route::get("/tes",function(){ 
        return response()->json([
            "message" => "Hello, this is a test response from the API."
        ]);
    });

    // /api/v1/supervisor
    Route::prefix('supervisor')->group(function(){
        Route::post('/activate-user',[SupervisorController::class,'activateUser'])->middleware(['auth:sanctum','role:1']);
    });
});
