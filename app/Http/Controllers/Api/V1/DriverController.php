<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Services\DriverService;
class DriverController extends Controller
{
    private $driverSrv;

    public function __construct (DriverService $driverSrv){
        $this->driverSrv = $driverSrv;
    }

    public function index (){
        $data = $this->driverSrv->getAllDrivers();
        return response()->json($data);
        // return $data["data"];
    }
    public function getDriverByStatus($status)
    {
        $allowed = ['OFF', 'JALAN', 'STAY'];
        if (!in_array($status, $allowed)) {
            return response()->json(['error' => 'Status tidak valid'], 400);
        }
        $data = Driver::where('status', $status)->get();
        return response()->json($data);
    }   
}
