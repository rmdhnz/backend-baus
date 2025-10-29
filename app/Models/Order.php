<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Driver;
use App\Models\Delivery;

class Order extends Model
{
    // protected $guarded = ['id'];

    protected $fillable = [
    'order_no', 'order_id', 'cust_name', 'delivery_lat', 'delivery_lon',
    'subtotal', 'shipping_fee', 'discount_amount', 'distance_km',
    'receipt_link', 'notes', 'payment_type_id',"driver_id",
    'status'
];

    public function drivers (): BelongsTo{
        return $this->belongsTo(Driver::class);
    } 
    public function delivery (): BelongsTo{
        return $this->belongsTo(Delivery::class);
    }
}
