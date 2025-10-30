<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Staff_IM;
use Illuminate\Http\Request;
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
}
