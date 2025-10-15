<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Services\DriverService;
class DriverController extends Controller
{
    private $driverSrv;

    public function __construct (DriverService $driverSrv){
        $this->driverSrv = $driverSrv;
    }

    public function index (){
        $data = $this->driverSrv->getAllDrivers();
        return response()->json($data);
    }
    public function getDriverByStatus($status)
    {
        $allowed = ['OFF', 'JALAN', 'STAY'];
        if (!in_array($status, $allowed)) {
            return response()->json(['error' => 'Status tidak valid'], 400);
        }
        $data = Driver::where('status', $status)->get();
        return response()->json($data);
    }
    public function getDriverById ($id){
        $data = Driver::find($id);
        if(!$data) { 
            return response()->json([
                "success" => false,
                "data" => "Not Found"
            ]);
        }
        return response()->json([
            "success" => true,
            "data" => $data
        ]);
    }  
    public function updateStatusDriver(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:drivers,user_id',
            'status' => 'required|in:OFF,JALAN,STAY',
        ]);

        $driver = Driver::where('user_id', $validated['id'])->first();

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver tidak ditemukan',
            ], 404);
        }

        $driver->status = $validated['status'];
        $driver->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengubah status driver',
        ]);
    }

}
