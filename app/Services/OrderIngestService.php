<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Arr;

class OrderIngestService
{
    public function storePending(array $payload): Order
    {
        return Order::updateOrCreate(
            ['order_no' => $payload['order_no']],
            [
                'order_id'       => $payload['order_id'], // referensi ke ID eksternal
                'cust_name'      => Arr::get($payload, 'customer.name'),
                'delivery_lat'   => (float)Arr::get($payload, 'customer.lat'),
                'delivery_lon'   => (float)Arr::get($payload, 'customer.lon'),
                'subtotal'       => (float)Arr::get($payload, 'order.subtotal'),
                'discount_amount'=> 0,
                'shipping_fee'   => 0,
                'distance_km'    => (float)($payload['distance_km'] ?? 0),
                'receipt_link'   => Arr::get($payload, 'receipt_url'),
                'notes'          => Arr::get($payload, 'order.notes'),
                'payment_type_id'=> 1,
                'driver_id'      => null,
                'staff_im_id'    => null,
            ]
        );
    }
}
