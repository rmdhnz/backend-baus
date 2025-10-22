<?php

use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\OrderMappingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MapperController;
use App\Http\Controllers\Api\V1\AllocationController;
use App\Http\Controllers\Api\V1\StaffIMController;


Route::prefix('v1')->group(function () {
    Route::prefix('drivers')->group(function () {
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
});
