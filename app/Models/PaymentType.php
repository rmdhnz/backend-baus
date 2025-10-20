<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order;

class PaymentType extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    public function orders (): HasMany{
        return $this->hasMany(Order::class);
    }
}
