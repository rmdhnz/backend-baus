<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderIngestService;

class MapperController extends Controller
{
    public function __construct(private OrderIngestService $ingest) {}

    public function ingest(Request $request)
    {
        $data = $request->validate([
            'payload.order_no' => 'required|string',
            'payload.outlet_id' => 'required|integer',
            'payload.customer.name' => 'required|string',
            'payload.customer.phone' => 'required|string',
            'payload.customer.lat' => 'required|numeric',
            'payload.customer.lon' => 'required|numeric',
            'payload.order.subtotal' => 'required|numeric',
            'payload.order.total' => 'required|numeric',
            'payload.order.order_time' => 'required|date',
            'payload.order.delivery_type' => 'required|string',
            'payload.order.estimasi_tiba' => 'required|date',
            'payload.order.notes' => 'nullable|string',
            'payload.distance_km' => 'nullable|string',
            'payload.receipt_url' => 'nullable|url',
            'payload.payment_type' => 'required|string',
        ]);

        $order = $this->ingest->storePending($data['payload']);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'message' => 'Order stored as PENDING',
        ]);
    }
}
