<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index (){
        $outlets = Outlet::all();
        return response()->json([
            "success" => true,
            "total" => count($outlets),
            "data" => $outlets,
        ]);
    }
}
