<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $guarded = "id";
    public function drivers (): HasMany{
        return $this->hasMany(Driver::class,'shift_id','id');
    }
    public function staff_ims (): HasMany{
        return $this->hasMany(Staff_IM::class,'shift_id','id');
    }
}
