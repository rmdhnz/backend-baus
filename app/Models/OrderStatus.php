<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    public $timestamps = false;
    protected $fillable = ["name"];
    public function orders (): HasMany{
        return $this->hasMany(\App\Models\Order::class);
    }
}
