<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StaffIMService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class MapperController extends Controller
{
    protected StaffIMService $staffIMService;

    public function __construct(StaffIMService $staffIMService)
    {
        $this->staffIMService = $staffIMService;
    }

    public function allocateOrderToStaffIM(Request $request)
    {
        // Validasi payload
        $validate = $request->validate([
            'order_id' => 'required|string',
            'order_no' => 'required|string',
            'cust_name' => 'required|string',
            'receipt_link' => 'required|string',
            'items' => 'required|array|min:1',
            'subtotal' => 'required|numeric',
            'shipping_fee' => 'required|numeric',
            'discount_amount' => 'nullable|numeric',
            'distance_km' => 'nullable|numeric',
            'delivery_lat' => 'required|numeric',
            'delivery_lon' => 'required|numeric',
            'delivery_link' => 'nullable|string',
            'payment_type_id' => 'required|integer|exists:payment_types,id',
            'notes' => 'nullable|string',
            'delivery_id' => 'required|integer|exists:deliveries,id',
            'estimated_time_delivered' => 'required|date',
        ]);

        // Ambil semua staff yang sedang shift aktif
        $staffInShift = $this->staffIMService->getStaffInShift();

        if (empty($staffInShift)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada staff yang sedang shift saat ini',
                'data' => [],
            ], 400);
        }

        // Pilih 1 staff secara acak
        $selectedStaff = collect($staffInShift)->random();

        // Simpan pesanan ke database
        try {
                // Log::info('Payload diterima:', $validate);
            DB::beginTransaction();

            $order = Order::create([
                'order_id' => $validate['order_id'],
                'order_no' => $validate['order_no'],
                'cust_name' => $validate['cust_name'],
                'receipt_link' => $validate['receipt_link'],
                'items' => json_encode($validate['items']),
                'subtotal' => $validate['subtotal'],
                'shipping_fee' => $validate['shipping_fee'],
                'discount_amount' => $validate['discount_amount'] ?? 0,
                'distance_km' => $validate['distance_km'] ?? 0,
                'delivery_lat' => $validate['delivery_lat'],
                'delivery_lon' => $validate['delivery_lon'],
                'delivery_link' => $validate['delivery_link'] ?? null,
                'payment_type_id' => $validate['payment_type_id'],
                'notes' => $validate['notes'] ?? null,
                'delivery_id' => $validate['delivery_id'],
                'estimated_time_delivered' => $validate['estimated_time_delivered'],
                'arrived_time_delivered' => null,
                'staff_im_id' => $selectedStaff['user_id'],
                'order_status_id' => 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dialokasikan ke staff yang sedang shift',
                'data' => [
                    'order_no' => $order->order_no,
                    'staff_selected' => $selectedStaff,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
