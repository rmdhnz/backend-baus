<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
class Driver extends Model
{
    protected $guarded = ["id"];
    protected $primaryKey = "user_id";
    public function user (): BelongsTo{
        return $this->belongsTo(User::class);
    }
}
