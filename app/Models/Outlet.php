<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    protected $guarded = ['id'];

    public function users ():HasMany{
        return $this->hasMany(\App\Models\User::class);
    }
    public function drivers ():HasMany{
        return $this->hasMany(\App\Models\Driver::class,'user_id','id');
    }
}
