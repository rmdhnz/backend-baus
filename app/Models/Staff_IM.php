<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff_IM extends Model
{
    protected $table = "staff_ims";
    protected $fillable = ["user_id","shift_id","productivity_score","total_transaction","on_time_frequency","late_frequency","avg_remaining_time","avg_latest"];

    protected $primaryKey = "user_id";

    public function user (): BelongsTo{
        return $this->belongsTo(User::class);
    }
    public function shift (): BelongsTo{
        return $this->belongsTo(\App\Models\Shift::class,'shift_id','id');
    }
    public function orders (): HasMany{
        return $this->hasMany(\App\Models\Order::class,'staff_im_id','user_id');
    }
}
