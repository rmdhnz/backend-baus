<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): PrivateChannel
    {
        // channel per staff yang menerima order
        return new PrivateChannel('staff-im.' . $this->order->staff_im_id);
    }

    public function broadcastAs(): string
    {
        return 'order.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'order_no' => $this->order->order_no,
            'customer' => $this->order->cust_name,
            'delivery_type' => $this->order->delivery->alias ?? null,
            'created_at' => $this->order->created_at->toDateTimeString(),
        ];
    }
}
