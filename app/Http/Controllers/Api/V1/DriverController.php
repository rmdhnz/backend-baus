<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CancelledOrder;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Services\DriverService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\DB;
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
        return response()->json([
            "success" => true,
            "total" => count($data),
            "data" => $data
        ]);
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
        $user = $request->user();
        $driver = Driver::with('shift')->where('user_id',$user->id)->first();
        if(!$driver) { 
            return response()->json([
                "success" => false,
                "message" => "Driver not Found",
                "data" => [],
            ],404);
        }
        $shift = $driver->shift;
        if (!$shift) {
            return response()->json([
                "success" => false,
                "message" => "Shift not assigned",
                "data" => []
            ], 404);
        }
        $now =  now()->format('H:i:s');
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $shift->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $shift->end_time);
        $current = \Carbon\Carbon::createFromFormat('H:i:s', $now);

        if ($end->lessThan($start)) {
            $inShift = $current->greaterThanOrEqualTo($start) || $current->lessThanOrEqualTo($end);
        } else {
            $inShift = $current->between($start, $end);
        }

        return response()->json([
            "success" => true,
            "message" => "Shift Data Retrieved Successfully",
            "data" => [
                "shift" => $shift->name,
                "start_time" => $shift->start_time,
                "end_time" => $shift->end_time,
                "in_shift" => $inShift
            ]
        ]);
    }

    // GET ALL Driver ORDERS
    public function getAllDriverOrders (Request $request){
        $user = $request->user();
        $orders = $this->driverSrv->getAllOrders($user);
        return response()->json([
            "success" => true,
            "message" => "All Orders for Driver ".$user->name,
            "total" => count($orders),
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

        $order = $this->driverSrv->getOrderDetail($user,$orderId);

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


    // GET My Gudang Position
    public function getMyGudangPosition (Request $request){
        $user = $request->user();
        $driver = User::with('outlet')->find($user->id);

        if(!$driver) { 
            return response()->json([
                "success" => false,
                "message" => "driver not found",
                "data" => [],
            ],404);
        }

        return response()->json([
            "success" => true,
            "data" => [
                "lat" => $driver->outlet->latitude,
                "lon" => $driver->outlet->longitude,
            ]
        ]);
    }

    // GET Driver in shift
    public function getDriversInShift (){
        $driverInShift = $this->driverSrv->getDriverInShift();

        return response()->json([
            "success" => true,
            "count" => count($driverInShift),
            "data" => $driverInShift,
        ]);
    }

    public function cancelledOrder (Request $request){
        $user = $request->user();
        $validated = $request->validate([
            "order_id" => "required",
            "reason" => "string|required"
        ]);

        try { 
            DB::beginTransaction();

            $order = Order::where('order_id', $validated['order_id'])
                ->where('driver_id', $user->id)
                ->first();
            if(!$order) { 
                return response()->json([
                    "success" => false,
                    "message" => "Order Not Found"
                ],404);
            }
            $order->update([
                "order_status_id" => 7,
            ]);
            CancelledOrder::create([
                "order_id" => $order->id,
                "olsera_order_id" => $order->order_id,
                'olsera_order_no' => $order->order_no,
                'reason' => $validated['reason'],
            ]);
            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Successfully cancelled fucking order",
                "status" => "CANCELLED",
            ]);
        }catch(\Throwable $e) { 
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => "Something went wrong : " . $e->getMessage(),
            ],500);
        }
    }
    public function pendingOrder (Request $request){
        $user = $request->user();
        $validated = $request->validate([
            "order_id" => "required",
            "reason" => "string|required",
        ]);

        try { 
            DB::beginTransaction();

            $order = Order::where('order_id', $validated['order_id'])
                ->where('driver_id', $user->id)
                ->first();
            if(!$order) { 
                return response()->json([
                    "success" => false,
                    "message" => "Order Not Found"
                ],404);
            }
            $order->update([
                "order_status_id" =>6,
                "driver_id" => null,
            ]);
            PendingOrder::create([
                "order_id" => $order->id,
                "olsera_order_id" => $order->order_id,
                'olsera_order_no' => $order->order_no,
                'reason' => $validated['reason'],
            ]);
            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Successfully pending fucking order",
                "status" => "pending",
            ]);
        }catch(\Throwable $e) { 
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => "Something went wrong : " . $e->getMessage(),
            ],500);
        }
    }

    public function getOrderHistory(Request $request)
    {
        $user = $request->user();

        // Validasi body request (tidak wajib)
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        // Query dasar: semua order milik driver ini
        $query = Order::with(['delivery', 'order_status'])
            ->where('driver_id', $user->id);

        // Terapkan filter tanggal sesuai kondisi
        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        } elseif ($startDate && !$endDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif (!$startDate && $endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Ambil hasil (urut terbaru)
        $orders = $query->orderBy('created_at', 'desc')->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada riwayat pesanan ditemukan untuk driver ini.',
                'data' => [],
            ]);
        }

        // Format data output
        $data = $orders->map(function ($order) {
            return [
                'order_no' => $order->order_no,
                'cust_name' => $order->cust_name,
                'subtotal' => $order->subtotal,
                'shipping_fee' => $order->shipping_fee,
                'delivery_type' => $order->delivery->alias ?? null,
                'order_status' => $order->orderStatus->name ?? null,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'arrived_time_delivered' => $order->arrived_time_delivered?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pesanan driver berhasil diambil.',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
