<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order;
class Delivery extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    public function orders (): HasMany{
        return $this->hasMany(Order::class);
    }
}
