<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
class Staff_IM extends Model
{
    protected $fillable = ["user_id","shift_id","productivity_score","total_transaction","on_time_frequency","late_frequency","avg_remaining_time","avg_latest"];

    protected $primaryKey = "user_id";

    public function user (): BelongsTo{
        return $this->belongsTo(User::class);
    }
}
