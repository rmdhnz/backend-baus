<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Services\DriverService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
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
            'status' => 'required|in:OFF,JALAN,STAY',
        ]);
        $user = $request->user();
        // ambil dan update data user 
        $driver = Driver::where('user_id', $user->id)->first();

        if(!$driver) { 
            return response()->json([
                "success" => false,
                "message" => "Driver not found",
            ],404);
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

    // GET Driver Shift Status
    public function getDriverShiftStatus (Request $request){

    }

    // GET ALL Driver ORDERS
    public function getAllDriverOrders (Request $request){
        $user = $request->user();
        $driver = Driver::with('orders')->where('user_id',$user->id)->first();
        if(!$driver){
            return response()->json([
                "success" => false,
                "message" => "User not found",
                "data" => [],
            ],404);
        }
        $orders = $driver->orders()->orderBy("delivery_id","desc")->orderBy("created_at","asc")->get();
        return response()->json([
            "success" => true,
            "message" => "All Orders for Driver ".$user->name,
            "data" => $orders,
        ]);
    }

    // Get Driver Order detail
    public function getOrderDetail(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'order_id is required',
            ], 400);
        }

        $order = Order::where('driver_id', $user->id)
            ->where(function ($q) use ($orderId) {
                $q->where('order_id', $orderId);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not assigned to this driver',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order detail retrieved successfully.',
            'data' => $order,
        ]);
    }

}
