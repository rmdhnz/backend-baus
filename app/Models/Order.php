<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Driver;
use App\Models\Delivery;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    // protected $guarded = ['id'];

    // protected $fillable = [
    //     'order_no', 'order_id', 'cust_name', 'delivery_lat', 'delivery_lon',
    //     'subtotal', 'shipping_fee', 'discount_amount', 'distance_km',
    //     'receipt_link', 'notes', 'payment_type_id',"driver_id",
    //     'order_status_id',
    //     'estimated_time_delivered',
    //     'arrived_time_delivered',
    //     'delivery_id',
    // ];

    protected $guarded = ['id'];
    public function drivers (): BelongsTo{
        return $this->belongsTo(Driver::class);
    } 
    public function delivery (): BelongsTo{
        return $this->belongsTo(Delivery::class);
    }
    public function order_status ():BelongsTo{
        return  $this->belongsTo(\App\Models\OrderStatus::class,'order_status_id','id');
    }

    public function cancel ():HasOne{
        return $this->hasOne(\App\Models\CancelledOrder::class);
    }
    public function pending ():HasOne{
        return $this->hasOne(\App\Models\PendingOrder::class);
    }
}
