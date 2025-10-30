<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Driver extends Model
{
    // protected $guarded = ["id"];
    protected $fillable = ["user_id","shift_id","productivity_score","total_transaction","on_time_frequency","late_frequency","avg_remaining_time","avg_latest","status"];
    protected $primaryKey = "user_id";
    public function user (): BelongsTo{
        return $this->belongsTo(User::class);
    }
    public function orders ():HasMany{
        return $this->hasMany(Order::class,'driver_id','user_id');
    }
    public function shift (): BelongsTo{
        return $this->belongsTo(Shift::class,'shift_id','id');
    }
    // public function outlet (): BelongsTo{
        // return $this->belongsTo(\App\Models\Outlet::class,'')
    // }
}
