<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index (){
        $methods = PaymentType::all();
        return response()->json($methods);
    }
}
