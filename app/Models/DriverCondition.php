<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverCondition extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    public function drivers():HasMany { 
        return $this->hasMany(\App\Models\Driver::class);
    }
}
