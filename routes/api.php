<?php

use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\OrderMappingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MapperController;
use App\Http\Controllers\Api\V1\AllocationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\StaffIMController;
use App\Http\Controllers\Api\V1\SupervisorController;

Route::prefix('v1')->group(function () {
    //ENDPOINT /api/v1/supervisor
    Route::prefix('supervisor')->middleware(['role:1','auth:sanctum'])->group(function(){
        Route::get('/all-users',[SupervisorController::class,'getAllUser']);
        Route::put('/activate-user',[SupervisorController::class,'activateUser']);
    });
    // ENDPOINT /api/v1/auth
    Route::prefix('auth')->group(function () {
        Route::middleware('guest')->group(function(){
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/register', [AuthController::class, 'register']);
        });
        Route::middleware('auth:sanctum')->group(function(){
            Route::get('/profile', [AuthController::class, 'getProfile']);
            Route::post('/logout',[AuthController::class,'logout']);
            Route::put('/update-my-profile',[AuthController::class,'updateMyProfile']);
            Route::put('/update-my-password',[AuthController::class,'updateMyPassword']);
        });
    });
    //ENDPOINT /api/v1/drivers
    Route::prefix('drivers')->group(function () {
        Route::get('/shift-status',[DriverController::class,'getDriverShiftStatus'])->middleware('auth:sanctum');
        Route::get('/my-gudang-position',[DriverController::class,'getMyGudangPosition'])->middleware('auth:sanctum');
        Route::get('/orders',[DriverController::class,'getAllDriverOrders'])->middleware(['auth:sanctum','role:2']);
        Route::put('/order/update-status',[DriverController::class,'pendingOrder'])->middleware(['auth:sanctum','role:2']);
        Route::get('/status/{status}', [DriverController::class, 'getDriverByStatus'])->middleware('api.key');
        Route::get('/order-detail',[DriverController::class,'getOrderDetail'])->middleware(['auth:sanctum','role:2']);
        Route::put('/update/status',[DriverController::class,'updateStatusDriver'])->middleware(['auth:sanctum','role:2']);
        Route::get('/', [DriverController::class, 'index'])->middleware('api.key');
        Route::get('/{id}',[DriverController::class,'getDriverById'])->middleware('api.key');
    });

    //ENDPOINT /api/v1/staff-im
    Route::prefix("staff-im")->group(function(){
        Route::get("/",[StaffIMController::class,'index'])->middleware('api.key');
    });

    // ENDPOINT /api/v1/payments
    Route::prefix("payments")->group(function(){
        Route::get('/',[PaymentController::class,'index'])->middleware('api.key');
    });

    Route::post('/order/mapping',[OrderMappingController::class,'handle']);
    Route::post('/mapper', [MapperController::class, 'ingest']);
    
    Route::post('/allocation/run', [AllocationController::class, 'run']); 
    Route::get("/tes",function(){ 
        return response()->json([
            "message" => "Hello, this is a test response from the API."
        ]);
    });
});
