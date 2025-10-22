<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Services\DriverService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class DriverController extends Controller
{
    private $driverSrv;

    public function __construct (DriverService $driverSrv){
        $this->driverSrv = $driverSrv;
    }

    public function index (){
        // $data = $this->driverSrv->getAllDrivers();
        $data = Driver::with('user')->get();
        return response()->json($data);
    }
    public function getDriverByStatus($status)
    {
        $allowed = ['OFF', 'JALAN', 'STAY'];
        if (!in_array($status, $allowed)) {
            return response()->json(['error' => 'Status tidak valid'], 400);
        }
        $data = Driver::with('user')->where('status', $status)->get();
        return response()->json($data);
    }
    public function getDriverById ($id){
        $data = Driver::with('user')->find($id);
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
    public function addDriver (Request $request){
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'shift_id' => 'required|integer',
            "username" => "required|string|unique:users,username"
        ]);

        // Create User
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'phone' => $validated['phone'],
            'password' => Hash::make('anjay123'),
            'role_id' => 2, // role driver
        ]);

        // Create Driver
        $driver = Driver::create([
            'user_id' => $user->id,
            'shift_id' => $validated['shift_id'],
            'productivity_score' => 0,
            'total_transaction' => 0,
            'on_time_frequency' => 0,
            'late_frequency' => 0,
            'avg_remaining_time' => null,
            'avg_latest' => null,
            'status' => 'OFF',
        ]);

        return response()->json([
            'success' => true,
            'data' => $driver,
        ], 201);
    }

    // GET Profile

    public function getProfile (){

    }
}
