<?php 

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Staff_IM;

class StaffIMController extends Controller { 
  public function index (){
    $data = Staff_IM::with('user')->get();
    return response()->json($data);
  }
}