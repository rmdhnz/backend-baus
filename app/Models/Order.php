<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Driver;

class Order extends Model
{
    protected $guarded = ['id'];
    public function drivers (): BelongsTo{
        return $this->belongsTo(Driver::class);
    } 
}
