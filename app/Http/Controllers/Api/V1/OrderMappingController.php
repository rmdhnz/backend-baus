<?php 

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderMappingService;

class OrderMappingController extends Controller
{
    protected $service;

    public function __construct(OrderMappingService $service)
    {
        $this->service = $service;
    }

    public function handle(Request $request)
    {
        $payload = $request->input('payload.orders', []);
        $result = $this->service->mapOrders($payload);
        return response()->json([
            'success' => true,
            ...$result
        ]);
    }
}
