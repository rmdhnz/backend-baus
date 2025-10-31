<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CancelledOrder;
use App\Models\Order;
use App\Models\PendingOrder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index (){
        $orders = Order::all();
        return response()->json([
            "success" => true,
            "total" => count($orders),
            "data" => $orders
        ]);
    }

    public function getCancelledOrders()
    {
        $cancelledOrders = CancelledOrder::with('order')->get();

        $formatted = $cancelledOrders->map(function ($item) {
            return [
                'order_id' => $item->order_id,
                'reason' => $item->reason,
                'order' => $item->order, // otomatis include relasi 'order'
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $formatted->count(),
            'data' => $formatted,
        ]);
    }
    public function getPendingOrders()
    {
        $pendingOrders = PendingOrder::with('order')->get();

        $formatted = $pendingOrders->map(function ($item) {
            return [
                'order_id' => $item->order_id,
                'reason' => $item->reason,
                'order' => $item->order, 
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $formatted->count(),
            'data' => $formatted,
        ]);
    }

}
