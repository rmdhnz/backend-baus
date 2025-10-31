<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Staff_IM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StaffIMService;

class StaffIMController extends Controller
{
    private $staffIMService;
    public function __construct (StaffIMService $staffIMService){
        $this->staffIMService = $staffIMService;
    }
    public function index (){
        $data = Staff_IM::with('user')->get();
        return response()->json([
            "success" => true,
            "total" => count($data),
            "data" => $data,
        ]);
    }

    public function getStaffInShift(Request $request)
    {
        $staffInShift = $this->staffIMService->getStaffInShift();

        return response()->json([
            "success" => true,
            "message" => "Staff currently in shift retrieved successfully",
            "count" => count($staffInShift),
            "data" => $staffInShift,
        ]);
    }


    // Get My Active Order In Packing
    public function getActiveOrderPacking (Request $request){
        $user = $request->user();
        $activeOrders = Order::where('staff_im_id',$user->id)->where('order_status_id',1)->get();
        if(!$activeOrders) { 
            return response()->json([
                "success" => false,
                "message" => "No Active Order in packing for this nigga",
                "data" => [],
            ],404);
        }

        return response()->json([
            "success" => true,
            "total" => count($activeOrders),
            "data" => $activeOrders,
        ]);
    }



    public function confirmPacking (Request $request){
        $validated = $request->validate([
            "order_id" => 'required',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);
        $user = $request->user();
        $user->load('staff_im');   
        $order = Order::where('order_id',$validated['order_id'])->where('staff_im_id',$user->id)->first();
        if(!$order) { 
            return response()->json([
                "success" => false,
                "message" => "Order not found njir",
            ],404);
        }
        $photoPath = $request->file('photo')->store('orders/proof','public');
        $order->update([
            'order_status_id' => 5,
            "proof_image_staff_im" => $photoPath,
            'arrived_time_delivered' => now(),
        ]);
        $order->update([
            "order_status_id" => 2,
        ]);
        return response()->json([
            "success" => true,
            "message" => "Successfully pack the shit",
            "status" => "Delivered"
        ]);
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

        // Query dasar: semua order milik staff ini
        $query = Order::with(['delivery', 'order_status'])
            ->where('staff_im_id', $user->id);

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
                'message' => 'Tidak ada riwayat pesanan ditemukan untuk staff ini.',
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
            'message' => 'Riwayat pesanan staff berhasil diambil.',
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

}
